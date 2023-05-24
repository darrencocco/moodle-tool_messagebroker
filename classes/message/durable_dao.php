<?php
namespace local_messagebroker\message;

class durable_dao {
    CONST table = 'local_messagebroker_durable';

    function write_new_message(immutable_message $message) {
        // TODO:
    }

    function purge_message(int $id) {
        // TODO:
    }

    function update_processing_time(int $id, int $processingtime) {
        // TODO:
    }

    function mark_message_as_processed(int $id) {
        // TODO:
    }

    /**
     * @param int $n
     * @return immutable_message[]
     */
    function get_upto_n_unprocessed_messages(int $n): array {
        // TODO:
    }

    function purge_completed_messages() {
        // TODO:
    }
}
