<?php
namespace tool_messagebroker\message;

interface durable_dao_interface {
    function write_new_message(immutable_message $message);

    function update_processing_time(int $id, int $processingtime);

    function mark_message_as_processed(int $id) ;

    /**
     * @param int $n
     * @return immutable_message[]
     */
    function get_upto_n_unprocessed_messages(int $n): array;
}
