<?php
namespace tool_messagebroker;

use coding_exception;
use tool_messagebroker\message\durable_dao_interface;
use tool_messagebroker\message\immutable_message;
use tool_messagebroker\receiver\message_receiver;
use tool_messagebroker\receiver\processing_style;
use messagebrokerdatastore_standarddb\durable_dao_factory;

class message_processor {
    /**
     * @var message_receiver[]
     */
    private array $messagereceivers;

    private durable_dao_interface $dao;

    private string $receivermode;

    private static ?message_processor $instance = null;

    public static function instance(string $receivermode, ?durable_dao_interface $dao = null): self {
        if (is_null(self::$instance)) {
            self::$instance = new message_processor($receivermode, $dao);
        }
        return self::$instance;
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

    public function process_message(immutable_message $message) {
        $interestedreceivers = $this->filter_interested_receivers($message, $this->messagereceivers);

        if ($message->is_persisted()) {
            $this->process_stored_message($message, $interestedreceivers);
        } else {
            $this->process_new_message($message, $interestedreceivers);
        }
    }

    /**
     * @param immutable_message $message
     * @param message_receiver[] $receivers
     * @return void
     */
    protected function process_new_message(immutable_message $message, array $receivers) {

        if ($this->receivermode === processing_style::RECEIVER_PREFERENCE) {
            // Receivers are allowed to express their preference, and at least one of them
            // wants to process messages using the "durable" style. Persist the message, then
            // it will be processed via the message_processor task.
            if ($this->contains_durable_receiver($receivers)) {
                $this->write_to_durable_storage($message);
            }

            // Receivers are allowed to express their preference, hand the message off to any
            // receiver that wishes to be ephemeral. It will never be tried again after this.
            $ephemeralresults = array_map(
                fn(message_receiver $receiver): bool => $receiver->process_message($message),
                $this->filter_ephemeral_receivers($receivers)
            );
        }

        // All receivers must be treated as ephemeral.
        if ($this->receivermode === processing_style::EPHEMERAL) {
            $ephemeralresults = array_map(
                fn (message_receiver $receiver): bool => $receiver->process_message($message),
                $receivers
            );
        }

        // All receivers must be treated as durable. Persist the message.
        if ($this->receivermode === processing_style::DURABLE) {
            $this->write_to_durable_storage($message);
        }
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
        return array_merge(...array_values(array_map(
            fn(array $plugins): array => array_merge(...array_values(array_map('call_user_func', $plugins))),
            get_plugins_with_function('build_message_receivers', 'messagebroker.php')
        )));
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
