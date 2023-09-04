<?php
namespace tool_messagebroker\task;

use core\task\scheduled_task;
use tool_messagebroker\message_processor;

class durable_message_processor extends scheduled_task {

    public function get_name() {
        return "durable_message_processor";
    }

    public function execute() {
        $mode = get_config('tool_messagebroker', 'receivemode');
        $maxmessagecount = (int)get_config('tool_messagebroker', 'messagespertask');
        $messageresults = message_processor::instance($mode)->process_upto_n_durable_messages($maxmessagecount);

        foreach ($messageresults as $messageid => $receiverresults) {
            $unfinishedreceivers = array_keys(array_filter($receiverresults, fn(array $result): bool => !$result['result']));

            if ($unfinishedreceivers) {
                mtrace("\tProcessing of message with ID $messageid is incomplete. The following receivers still require it:");
                mtrace(implode("\n", array_map(fn(string $receiver): string => "\t\t" . $receiver, $unfinishedreceivers)));
            }
        }
    }
}
