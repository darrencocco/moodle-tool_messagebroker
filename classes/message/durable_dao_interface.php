<?php
namespace tool_messagebroker\message;

interface durable_dao_interface {
    function write_new_message(immutable_message $message);

    function notify_processing_attempt(string $id);

    function mark_message_as_processed(string $id) ;

    /**
     * @param int $n
     * @return immutable_message[]
     */
    function get_upto_n_unprocessed_messages(int $n): array;
}
