<?php
namespace tool_messagebroker;

use tool_messagebroker\message\durable_dao_interface;
use tool_messagebroker\message\immutable_message;
use tool_messagebroker\receiver\message_receiver;
use messagebrokerdatastore_standarddb\durable_dao_factory;

class message_processor {
    /**
     * @var message_receiver[]
     */
    private array $messagereceivers;

    private durable_dao_interface $dao;

    private static message_processor $instance;

    static function instance() {
        if (is_null(self::$instance)) {
            self::$instance = new message_processor();
        }
        return self::$instance;
    }

    protected function __construct() {
        $this->messagereceivers = $this->get_message_receivers();
        $durabledaoplugin = 'messagebrokerdatastore_standarddb';
        $this->dao = durable_dao_factory::make_durable_dao($durabledaoplugin);
    }

    function process_message(immutable_message $message) {
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
        if ($this->contains_durable_receiver($receivers)) {
            $this->write_to_durable_storage($message);
        }
        foreach ($this->filter_ephemeral_receivers($receivers) as $receiver) {
            $receiver->process_message($message);
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
        // TODO:
    }

    /**
     * @param immutable_message $message
     * @param message_receiver[] $receivers
     * @return message_receiver[]
     */
    protected function filter_interested_receivers(immutable_message $message, array $receivers): array {
        // TODO:
    }

    /**
     * @param message_receiver[] $receivers
     * @return message_receiver[]
     */
    protected function filter_ephemeral_receivers(array $receivers): array {
        // TODO:
    }

    /**
     * @param message_receiver[] $receivers
     * @return message_receiver[]
     */
    protected function filter_durable_receivers(array $receivers): array {
        // TODO:
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
        // TODO:
    }
}