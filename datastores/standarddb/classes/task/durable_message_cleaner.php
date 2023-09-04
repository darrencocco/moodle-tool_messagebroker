<?php
namespace mbdatastore_standarddb\task;

use core\task\scheduled_task;
use mbdatastore_standarddb\durable_dao_factory;

class durable_message_cleaner extends scheduled_task {

    public function get_name() {
        return "durable_message_cleaner";
    }

    public function execute() {
        $dao = durable_dao_factory::make_durable_dao('mbdatastore_standarddb');
        $dao->purge_completed_messages();
    }
}
