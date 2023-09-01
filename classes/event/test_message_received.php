<?php
namespace tool_messagebroker\event;

defined('MOODLE_INTERNAL') || die();

/**
 * Dummy event for testing message broker receiving.
 *
 * It's not advised to log every message as an event
 * at the message broker level because it may add
 * significant load to the system.
 *
 * If you want to do this then feel free to add an
 * all topics receiver to your plugin and have it
 * emit events.
 *
 * @package   tool_messagebroker
 * @copyright 2023 Monash University
 * @author    Darren Cocco <darren.cocco@monash.edu>
 */
class test_message_received extends \core\event\base {

    protected function init() {
        $this->data['crud'] = 'r';
        $this->data['edulevel'] = self::LEVEL_OTHER;
    }

    public static function get_name() {
        return get_string('eventtestmessagereceived', 'tool_messagebroker');
    }

    public function get_description() {
        return "The user with id '$this->userid' has sent a {$this->data['other']['type']} type message with the content:\n" .
            "Topic: {$this->data['other']['topic']}\n".
            "Body:\n" . json_encode($this->data['other']['body'], JSON_PRETTY_PRINT);
    }
}