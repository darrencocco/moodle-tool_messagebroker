<?php
namespace messagebrokerdatastore_standarddb;

use local_messagebroker\message\durable_dao_interface;
use local_messagebroker\message\immutable_message;

class durable_dao implements durable_dao_interface {

    function write_new_message(immutable_message $message) {
        // TODO: Implement write_new_message() method.
    }

    function update_processing_time(int $id, int $processingtime) {
        // TODO: Implement update_processing_time() method.
    }

    function mark_message_as_processed(int $id) {
        // TODO: Implement mark_message_as_processed() method.
    }

    function get_upto_n_unprocessed_messages(int $n): array {
        // TODO: Implement get_upto_n_unprocessed_messages() method.
    }

    function purge_completed_messages() {

    }
}