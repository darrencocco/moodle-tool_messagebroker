<?php
namespace tool_messagebroker\message;

use stdClass;

class immutable_message {
    protected int $id = 0;
    protected string $topic;
    protected stdClass $body;
    protected bool $processingcomplete;
    protected int $lastprocessattempt;

    public function is_persisted(): bool {
        return $this->id > 0;
    }

    /**
     * @return int
     */
    public function get_id(): int {
        return $this->id;
    }

    /**
     * @return string
     */
    public function get_topic(): string {
        return $this->topic;
    }

    /**
     * @return string
     */
    public function get_body(): stdClass {
        return $this->body;
    }

    /**
     * @return bool
     */
    public function is_processingcomplete(): bool {
        return $this->processingcomplete;
    }

    /**
     * @return int
     */
    public function get_lastprocessed(): int {
        return $this->lastprocessed;
    }
}