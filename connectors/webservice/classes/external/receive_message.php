<?php

namespace mbconnector_webservice\external;

use core\context\system;
use core_external\external_api;
use core_external\external_value;
use core_external\external_description;
use core_external\external_single_structure;
use core_external\external_function_parameters;
use moodle_exception;
use tool_messagebroker\message\received_message;
use tool_messagebroker\message_processor;
use tool_messagebroker\receiver\processing_style;

class receive_message extends external_api {

    public static function execute(string $topic, string $body): array {
        global $USER;

        require_capability('tool/messagebroker:submitmessage', system::instance(), $USER);

        $messagebody = json_decode($body);

        if ($messagebody === null && json_last_error() !== JSON_ERROR_NONE) {
            throw new moodle_exception('JSON data provided is invalid.');
        }

        $receivedmessage = (new received_message())
            ->set_topic($topic)
            ->set_body($messagebody);

        $mode = get_config('tool_messagebroker', 'receivemode');
        $results = message_processor::instance($mode)->process_message($receivedmessage);
        $durableresults = array_filter($results, fn (array $result): bool => $result['style'] === processing_style::DURABLE);
        $everythingsucceeds = fn(bool $everything, array $result): bool => $everything && $result['result'];

        return [
            'success' => array_reduce($results, $everythingsucceeds, true),
            'stored' => !empty($durableresults) && array_reduce($durableresults, $everythingsucceeds, true),
            'receivers' => count($results)
        ];
    }

    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'topic' => new external_value(PARAM_RAW_TRIMMED, 'Dot seperated topic identifier.', VALUE_REQUIRED, null, NULL_NOT_ALLOWED),
            'body' => new external_value(PARAM_RAW_TRIMMED, 'JSON encoded message contents.', VALUE_REQUIRED, null, NULL_NOT_ALLOWED)

        ]);
    }

    public static function execute_returns(): external_description {
        return new external_single_structure([
            'success' => new external_value(PARAM_BOOL),
            'stored' => new external_value(PARAM_BOOL),
            'receivers' => new external_value(PARAM_INT)
        ]);
    }
}
