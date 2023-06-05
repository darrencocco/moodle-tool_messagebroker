<?php
namespace messagebrokerdatastore_standarddb;

use core\task\scheduled_task;

class durable_message_cleaner extends scheduled_task {

    public function get_name() {
        return "durable_message_cleaner";
    }

    public function execute() {
        $dao = durable_dao_factory::make_durable_dao('messagebrokerdatastore_standarddb');
        $dao->purge_completed_messages();
    }
}
