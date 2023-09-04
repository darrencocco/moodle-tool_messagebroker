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

        if (!in_array($receivermode, $receivermodes)) {
            throw new coding_exception('Invalid receiver mode. Receiver mode must be a valid processing style as per ' . processing_style::class);
        }

        $this->receivermode = $receivermode;

        if ($dao) {
            $this->dao = $dao;
        } else {
            $durabledaoplugin = get_config('tool_messagebroker', 'datastore');
            $this->dao = durable_dao_factory::make_durable_dao($durabledaoplugin);
        }

        $this->messagereceivers = $this->get_message_receivers();
    }


    /**
     * @param immutable_message $message
     * @param message_receiver[] $receivers
     * @return array Array of receiverid => ['style' => , 'result' => ].
                     Receiver ID is a string which uniquely identifies each
                     receiver and where it came from. The format is:
                     classname:classid@file. See get_message_receivers
     */
    public function process_message(immutable_message $message): array {
        $interestedreceivers = $this->filter_interested_receivers($message, $this->messagereceivers);

        if ($message->is_persisted()) {
            // TODO: This will get updated as part of #5
            $this->process_stored_message($message, $interestedreceivers);
        } else {
            return $this->process_new_message($message, $interestedreceivers);
        }
    }

    protected function process_new_message(immutable_message $message, array $receivers): array {
        $ephemeralreceivers = $this->receivermode === processing_style::RECEIVER_PREFERENCE
            ? $this->filter_ephemeral_receivers($receivers)
            : ($this->receivermode === processing_style::EPHEMERAL ? $receivers : []);

        $durablereceivers = $this->receivermode === processing_style::RECEIVER_PREFERENCE
            ? $this->filter_durable_receivers($receivers)
            : ($this->receivermode === processing_style::DURABLE ? $receivers : []);

        if (!empty($durablereceivers)) {
            try {
                $this->write_to_durable_storage($message);
                $persisted = true;
            } catch (Exception $e) {
                $persisted = false;
            }
        }

        $ephemeralresults = array_map(
            fn(message_receiver $receiver): array => ['style' => processing_style::EPHEMERAL, 'result' => $receiver->process_message($message)],
            $ephemeralreceivers
        );

        $durableresults = array_map(
            fn() => ['style' => processing_style::DURABLE, 'result' => $persisted],
            $durablereceivers
        );

        return $durableresults + $ephemeralresults;
    }

    /**
     * @param immutable_message $message
     * @param message_receiver[] $receivers
     * @return void
     */
    protected function process_stored_message(immutable_message $message, array $receivers) {
        foreach ($this->filter_durable_receivers($receivers) as $receiver) {
            $receiver->process_message($message);
        }
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
     * @param message_receiver[] $receivers
     * @return message_receiver[]
     */
    protected function filter_ephemeral_receivers(array $receivers): array {
        return array_filter(
            $receivers,
            fn(message_receiver $receiver): bool => $receiver->get_preferred_message_processing_method() === processing_style::EPHEMERAL
        );
    }

    /**
     * @param message_receiver[] $receivers
     * @return message_receiver[]
     */
    protected function filter_durable_receivers(array $receivers): array {
        return array_filter(
            $receivers,
            fn (message_receiver $receiver): bool => $receiver->get_preferred_message_processing_method() === processing_style::DURABLE
        );
    }

    /**
     * @param immutable_message $message
     * @return void
     */
    protected function write_to_durable_storage(immutable_message $message) {
        $this->dao->write_new_message($message);
    }

    /**
     * @param message_receiver[] $receivers
     * @return bool
     */
    protected function contains_durable_receiver(array $receivers): bool {
        $prefersdurable = fn(message_receiver $receiver): bool => $receiver->get_preferred_message_processing_method() === processing_style::DURABLE;
        return array_reduce(
            $receivers,
            fn(bool $gotdurable, message_receiver $receiver): bool => $gotdurable || $prefersdurable($receiver),
            false
        );
    }
}
