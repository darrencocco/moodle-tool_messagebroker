<?php
namespace tool_messagebroker;

use core\task\scheduled_task;
use tool_messagebroker\message\durable_dao_factory;
use tool_messagebroker\message\durable_dao_interface;
use tool_messagebroker\receiver\processing_style;

class durable_message_processor extends scheduled_task {

    public function get_name() {
        return "durable_message_processor";
    }

    public function execute() {
        $daovariant = 'standarddb'; // TODO: replace with setting.
        $dao = durable_dao_factory::make_durable_dao($daovariant);
        $processor = message_processor::instance(processing_style::RECEIVER_PREFERENCE, $dao);
        $maxmessagecount = 10; // TODO: replace with setting.
        $messages = $dao->get_upto_n_unprocessed_messages($maxmessagecount);
        foreach ($messages as $message) {
            $dao->notify_processing_attempt($message->get_id());
            $processor->process_message($message);
            $dao->mark_message_as_processed($message->get_id());
        }
    }
}
