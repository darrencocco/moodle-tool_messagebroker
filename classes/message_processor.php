<?php
namespace tool_messagebroker;

use coding_exception;
use Exception;
use ReflectionClass;
use tool_messagebroker\message\durable_dao_interface;
use tool_messagebroker\message\immutable_message;
use tool_messagebroker\receiver\message_receiver;
use tool_messagebroker\receiver\processing_style;
use tool_messagebroker\message\durable_dao_factory;

class message_processor {
    /**
     * @var message_receiver[]
     */
    private array $messagereceivers;

    private durable_dao_interface $dao;

    private string $receivermode;

    private static array $instances = [];

    public static function instance(string $receivermode, ?durable_dao_interface $dao = null): self {
        $instancehash = $receivermode . '_' . ($dao ? spl_object_hash($dao) : 'default');

        if (!isset(self::$instances[$instancehash])) {
            self::$instances[$instancehash] = new message_processor($receivermode, $dao);
        }

        return self::$instances[$instancehash];
    }

    protected function __construct(string $receivermode, ?durable_dao_interface $dao = null) {
        $receivermodes = (new \ReflectionClass(processing_style::class))->getConstants();
        $durabledaoplugin = get_config('tool_messagebroker', 'datastore');

        if (!in_array($receivermode, $receivermodes)) {
            throw new coding_exception('Invalid receiver mode. Receiver mode must be a valid processing style as per ' . processing_style::class);
        }

        $this->receivermode = $receivermode;
        $this->dao = $dao ?: durable_dao_factory::make_durable_dao($durabledaoplugin);
        $this->messagereceivers = $this->get_message_receivers();
    }

    /**
     * Process a message.
     *
     * This function returns a structured array of information about what
     * interested receivers did with the message and what processing style
     * was used for that receiver. An example of what it returns might be:
     *
     *    [
     *         receiver:280@/local/myplugin/messagebroker.php => [
     *             [
     *                 style => durable,
     *                 result => true
     *             ]
     *         ],
     *         receiver:279@/local/myplugin/messagebroker.php => [
     *             [
     *                 style => ephemeral,
     *                 result => true
     *             ]
     *    ]
     *
     * See the receive_message webservice and durable_message_processor classes
     * for examples of how this return value is used in practice.
     *
     * Note that a result of true simply means there was "no error".
     * Depending on if the message is "new" or "stored" what actually
     * happens behind the scenes can be different.
     *
     * In the case of a new, unpersisted message, durable receivers may
     * be interested, but they should not actually process the message
     * yet (this would be handled by the durable_message_processor task).
     * So a result of true could indicate that we know the receiver is
     * interested, and the message was persisted without error.
     *
     * In the case of a new, unpersisted message, epehemeral receivers
     * may be interested, and they should process the message immediately.
     * So a result of true could indicate that the receiver processed the
     * message without any issue.
     *
     * In the case of a persisted message, durable receivers may be interested
     * and they should process the message. So a result of true could indicate
     * that the receiver processed the message without any issue.
     *
     * @param immutable_message $message
     * @param message_receiver[] $receivers
     * @return array Array of receiverid => ['style' => , 'result' => ].
                     Receiver ID is a string which uniquely identifies each
                     receiver and where it came from. The format is:
                     classname:classid@file. See get_message_receivers for
                     how the ID is constructed and run_receivers for how
                     the result array is constructed.
     */
    public function process_message(immutable_message $message): array {
        $interestedreceivers = $this->filter_interested_receivers($message, $this->messagereceivers);

        return $message->is_persisted()
            ? $this->process_stored_message($message, $interestedreceivers)
            : $this->process_new_message($message, $interestedreceivers);
    }

    public function process_upto_n_durable_messages(int $maxmessagecount): array {
        return array_map(
            [$this, 'process_message'],
            $this->dao->get_upto_n_unprocessed_messages($maxmessagecount)
        );
    }

    protected function process_new_message(immutable_message $message, array $receivers): array {
        // Durable receivers should be processed by the durable_message_processor
        // task. So we provide a function to `run_receivers` which simply returns
        // true or false depending on if the message was persised.
        //
        // This is used below in the call to `run_receivers`.
        //
        // The scheduled task will ultimately call the `process` method of the receiver.
        // We just need to know if it was persisted or not so we can return that information
        // to whatever pushed/pulled the message in to the message broker.
        if ($this->contains_durable_receiver($receivers)) {
            try {
                $this->write_to_durable_storage($message);
                $written = fn() => true;
            } catch (Exception $e) {
                $written = fn() => false;
            }
        }

        // We run both durable and ephemeral receivers so that the resultant array
        // includes all receivers that were interested in the message.
        //
        // Note that due to the $written callable being used for durable receivers
        // they will not actully process the message (that will happen later, in
        // the background, via the durable_message_processor scheduled task).
        return self::run_receivers($message, $this->filter_durable_receivers($receivers), processing_style::DURABLE, $written ?? null)
             + self::run_receivers($message, $this->filter_ephemeral_receivers($receivers), processing_style::EPHEMERAL);
    }

    /**
     * @param immutable_message $message
     * @param message_receiver[] $receivers
     * @return void
     */
    protected function process_stored_message(immutable_message $message, array $receivers): array {
        $everythingsucceeds = fn (bool $everything, array $result): bool => $everything && $result['result'];

        $this->dao->notify_processing_attempt($message->get_id());
        $results = self::run_receivers($message, $this->filter_durable_receivers($receivers), processing_style::DURABLE);

        // If all receivers report that they are done with the message
        // we can mark it as completed so it won't get processed again.
        //
        // If one failed, we must not mark the message as processed so
        // that it will be tried again on subsequent runs of the
        // durable_message_processor scheduled task.
        if (array_reduce($results, $everythingsucceeds, true)) {
            $this->dao->mark_message_as_processed($message->get_id());
        }

        return $results;
    }

    /**
     * Helper to execute a list of message_receivers using an optionally
     * provided callable.
     *
     * Returns a list of "receiver results" (which is an associative array containing
     * a "style" and "result" key) corresponding to each receiver is returned.
     *
     * This helper is intended to be used by other parts of the message_processor
     * to ensure a consistent return type regardless of what processing method
     * is used.
     *
     * @param immutable_message $message
     * @param array $receivers
     * @param string $style
     * @param callable|null $processfn Optional callable which whill be passed
     *                                 a receiver and return a bool. When no
     *                                 callable is provided, `process` method
     *                                 of the message_receiver is used.
     *
     * @return array
     */
    protected static function run_receivers(immutable_message $message, array $receivers, string $style, ?callable $processfn = null): array {
        $getresult = $processfn ?: fn(message_receiver $receiver): bool => $receiver->process_message($message);

        return array_map(
            fn(message_receiver $receiver): array => ['style' => $style, 'result' => $getresult($receiver)],
            $receivers
        );
    }

    /**
     * @return message_receiver[]
     */
    protected function get_message_receivers(): array {
        global $CFG;

        $receiverinstances = array_merge(...array_values(array_map(
            fn(array $plugins): array => array_merge(...array_values(array_map('call_user_func', $plugins))),
            get_plugins_with_function('build_message_receivers', 'messagebroker.php')
        )));

        return array_combine(
            array_map(
                fn(message_receiver $receiver): string =>
                    get_class($receiver) . ':' .
                    spl_object_id($receiver) . '@' .
                    str_replace($CFG->dirroot, '', (new ReflectionClass($receiver))->getFileName()),
                $receiverinstances
            ),
            $receiverinstances
        );
    }

    /**
     * @param immutable_message $message
     * @param message_receiver[] $receivers
     * @return message_receiver[]
     */
    protected function filter_interested_receivers(immutable_message $message, array $receivers): array {
        return array_filter(
            $receivers,
            fn(message_receiver $receiver) => preg_match($receiver->get_registered_topic(), $message->get_topic())
        );
    }

    /**
     * Filter receivers which should be treated as ephemeral, taking receiver
     * preference and global receiver mode setting into account.
     *
     *
     * @param message_receiver[] $receivers Array of receivers to filter.
     * @return message_receiver[] Array of receivers to treat as ephemeral.
     */
    protected function filter_ephemeral_receivers(array $receivers): array {
        return $this->receivermode === processing_style::RECEIVER_PREFERENCE
            ? array_filter($receivers, self::prefers(processing_style::EPHEMERAL))
            : ($this->receivermode === processing_style::EPHEMERAL ? $receivers : []);
    }

    /**
     * Filter receivers which should be treated as durable, taking receiver
     * preference and global receiver mode setting into account.
     *
     * @param message_receiver[] $receivers Array of receivers to filter.
     * @return message_receiver[] Array of receivers to treat as durable.
     */
    protected function filter_durable_receivers(array $receivers): array {
        return $this->receivermode === processing_style::RECEIVER_PREFERENCE
            ? array_filter($receivers, self::prefers(processing_style::DURABLE))
            : ($this->receivermode === processing_style::DURABLE ? $receivers : []);
    }

    /**
     * Returns true if at least one of the receivers in the list
     * should be treated as durable. Taking receiver preference
     * and global receiver mode setting into account.
     *
     * @param message_receiver[] $receivers
     * @return bool
     */
    protected function contains_durable_receiver(array $receivers): bool {
        $prefersdurable = self::prefers(processing_style::DURABLE);
        $atleastonedurable = fn(bool $gotdurable, message_receiver $receiver): bool => $gotdurable || $prefersdurable($receiver);

        return $this->receivermode === processing_style::RECEIVER_PREFERENCE
            ? array_reduce($receivers, $atleastonedurable, false)
            : $this->receivermode === processing_style::DURABLE;
    }

    /**
     * Static helper to return a callback which can determine if a
     * receiver prefers a given processing style.
     *
     * @param string $style A processing style.
     * @return callable A callable which returns true if the given receiver prefers the specific style.
     *                  Otherwise false.
     */
    protected static function prefers(string $style): callable {
        return fn (message_receiver $receiver): bool => $receiver->get_preferred_message_processing_method() === $style;
    }

    /**
     * @param immutable_message $message
     */
    protected function write_to_durable_storage(immutable_message $message): void {
        $this->dao->write_new_message($message);
    }
}
