<?php
namespace local_messagebroker;

use core\task\scheduled_task;
use local_messagebroker\message\durable_dao;

class durable_message_cleaner extends scheduled_task {

    public function get_name() {
        return "durable_message_cleaner";
    }

    public function execute() {
        $dao = new durable_dao();
        $dao->purge_completed_messages();
    }
}
