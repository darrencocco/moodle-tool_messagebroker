<?php
namespace local_messagebroker\message;

use stdClass;

class received_message extends immutable_message {
    /**
     * @param string $topic
     * @return received_message
     */
    public function set_topic(string $topic): immutable_message {
        $this->topic = $topic;
        return $this;
    }

    /**
     * @param stdClass $body
     * @return received_message
     */
    public function set_body(stdClass $body): immutable_message {
        $this->body = $body;
        return $this;
    }
}
