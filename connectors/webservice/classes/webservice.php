<?php
namespace messagebrokerconnector_http;

use external_api;
use external_value;
use local_messagebroker\message\message;
use local_messagebroker\message\received_message;
use local_messagebroker\message_processor;

class webservice extends external_api {

    function receive_messages_parameters() {
        return new \external_function_parameters([
            'topic' => new external_value(PARAM_RAW_TRIMMED, 'Dot seperated topic identifier.', VALUE_REQUIRED, null, NULL_NOT_ALLOWED),
            'body' => new external_value(PARAM_RAW_TRIMMED, 'JSON encoded message contents.', VALUE_REQUIRED, null, NULL_NOT_ALLOWED)

        ]);
    }
    function receive_message($topic, $body) {
        // TODO: authorisation
        // TODO: sanitise data
        $messagebody = json_decode($body);
        $receivedmessage = (new received_message())
            ->set_topic($topic)
            ->set_body($messagebody);
        message_processor::instance()->process_message($receivedmessage);
    }

    function receive_messages_returns() {
        return new \external_single_structure([
            'success' => new external_value(PARAM_BOOL),
            'stored' => new external_value(PARAM_BOOL),
            'receivers' => new external_value(PARAM_INT)
        ]);
    }
}