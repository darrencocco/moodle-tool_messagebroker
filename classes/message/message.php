<?php
namespace tool_messagebroker\message;

use stdClass;

class message extends immutable_message {
    /**
     * @param string $id
     * @return self
     */
    public function set_id(string $id): self {
        $this->id = $id;
        return $this;
    }

    /**
     * @param string $topic
     * @return self
     */
    public function set_topic(string $topic): self {
        $this->topic = $topic;
        return $this;
    }

    /**
     * @param stdClass $body
     * @return self
     */
    public function set_body(stdClass $body): self {
        $this->body = $body;
        return $this;
    }

    /**
     * @param bool $processingcomplete
     * @return self
     */
    public function set_processingcomplete(bool $processingcomplete): self {
        $this->processingcomplete = $processingcomplete;
        return $this;
    }

    /**
     * @param int $lastprocessed
     * @return self
     */
    public function set_lastprocessed(int $lastprocessed): self {
        $this->lastprocessed = $lastprocessed;
        return $this;
    }
}
