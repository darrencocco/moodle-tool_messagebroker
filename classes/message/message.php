<?php
namespace local_messagebroker\message;

use stdClass;

class message extends immutable_message {
    /**
     * @param int $id
     * @return immutable_message
     */
    public function set_id(int $id): immutable_message {
        $this->id = $id;
        return $this;
    }

    /**
     * @param string $topic
     * @return immutable_message
     */
    public function set_topic(string $topic): immutable_message {
        $this->topic = $topic;
        return $this;
    }

    /**
     * @param stdClass $body
     * @return immutable_message
     */
    public function set_body(stdClass $body): immutable_message {
        $this->body = $body;
        return $this;
    }

    /**
     * @param bool $processingcomplete
     * @return immutable_message
     */
    public function set_processingcomplete(bool $processingcomplete): immutable_message {
        $this->processingcomplete = $processingcomplete;
        return $this;
    }

    /**
     * @param int $lastprocessed
     * @return immutable_message
     */
    public function set_lastprocessed(int $lastprocessed): immutable_message {
        $this->lastprocessed = $lastprocessed;
        return $this;
    }
}