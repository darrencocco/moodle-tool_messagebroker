<?php
/**
 * Dummy receiver provider for testing.
 *
 * @package   tool_messagebroker
 * @copyright 2023 Monash University
 * @author    Darren Cocco <darren.cocco@monash.edu>
 */

defined('MOODLE_INTERNAL') || die();

/**
 * @return tool_messagebroker\receiver\message_receiver[]
 */
function tool_messagebroker_build_message_receivers(): array {
    return [
        \tool_messagebroker\receiver\dummy_durable::instance(),
        \tool_messagebroker\receiver\dummy_ephemeral::instance()
    ];
}