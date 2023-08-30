<?php
namespace mbdatastore_standarddb;

use stdClass;
use tool_messagebroker\message\durable_dao_interface;
use tool_messagebroker\message\immutable_message;
use tool_messagebroker\message\message;

class durable_dao implements durable_dao_interface {

    const TABLE = 'mbdatastore_standarddb_msg';

    public function write_new_message(immutable_message $message) {
        global $DB;
        $now = time();

        $DB->insert_record(
            self::TABLE,
            (object)[
                'topic' => $message->get_topic(),
                'body' => json_encode($message->get_body()),
                'timecreated' => $now,
                'lastprocessed' => $now,
                'completed' => 0
            ]
        );
    }

    public function notify_processing_attempt(string $id) {
        global $DB;
        $now = time();

        $DB->update_record(
            self::TABLE,
            (object)[
                'id' => $id,
                'lastprocessed' => $now
            ]
        );
    }

    public function mark_message_as_processed(string $id) {
        global $DB;
        $now = time();

        $DB->update_record(
            self::TABLE,
            (object)[
                'id' => $id,
                'lastprocessed' => $now,
                'completed' => 1
            ]
        );
    }

    public function get_upto_n_unprocessed_messages(int $n): array {
        global $DB;
        $records = $DB->get_records_list(
            self::TABLE,
            'completed',
            ['0'],
            'id DESC',
            '*',
            0,
            $n
        );

        return array_map(
            fn(stdClass $record): immutable_message => (new message)
                ->set_id($record->id)
                ->set_topic($record->topic)
                ->set_body(json_decode($record->body))
                ->set_lastprocessed($record->lastprocessed)
                ->set_processingcomplete(false),
            $records
        );
    }

    public function purge_completed_messages() {
        global $DB;

        $DB->delete_records(self::TABLE, ['completed' => 1]);
    }
}
