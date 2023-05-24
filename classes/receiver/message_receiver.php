<?php
namespace local_messagebroker\receiver;

use local_messagebroker\message\immutable_message;

/**
 * Interface that message receivers must implement.
 */
interface message_receiver {
    static function instance(): message_receiver;
    function process_message(immutable_message $message): bool;

    /**
     * @return string a processing style from \local_messagebroker\receiver\processing_style
     */
    function get_preferred_message_processing_method(): string;

    function get_registered_topic(): string;
}
