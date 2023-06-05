<?php
namespace local_messagebroker;

use core\task\scheduled_task;
use local_messagebroker\message\durable_dao_factory;
use local_messagebroker\message\durable_dao_interface;

class durable_message_processor extends scheduled_task {

    public function get_name() {
        return "durable_message_processor";
    }

    public function execute() {
        $daovariant = 'messagebrokerdatastore_standarddb'; // TODO: replace with setting.
        $dao = durable_dao_factory::make_durable_dao($daovariant);
        $processor = message_processor::instance();
        $maxmessagecount = 10; // TODO: replace with setting.
        $messages = $dao->get_upto_n_unprocessed_messages($maxmessagecount);
        foreach ($messages as $message) {
            $dao->update_processing_time($message->get_id(), time());
            $processor->process_message($message);
            $dao->mark_message_as_processed($message->get_id());
        }
    }
}
