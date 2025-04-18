<?php
namespace tool_messagebroker\receiver;

defined('MOODLE_INTERNAL') || die();

use core_context\system;
use tool_messagebroker\event\test_message_received;
use tool_messagebroker\message\immutable_message;

/**
 * Dummy durable receiver for testing
 *
 * @package   tool_messagebroker
 * @copyright 2023 Monash University
 * @author    Darren Cocco <darren.cocco@monash.edu>
 */
class dummy_durable implements message_receiver {

    public static function instance(): message_receiver {
        return new self;
    }

    public function process_message(immutable_message $message): bool {
        global $USER;
        test_message_received::create([
            'userid' => $USER->id,
            'context' => system::instance(),
            'other' => [
                'type' => $this->get_preferred_message_processing_method(),
                'topic' => $message->get_topic(),
                'body' => $message->get_body()
            ]
        ])->trigger();
        return true;
    }

    public function get_preferred_message_processing_method(): string {
        return processing_style::DURABLE;
    }

    public function get_registered_topic(): string {
        return '/^Dummy\.Durable/';
    }
}
