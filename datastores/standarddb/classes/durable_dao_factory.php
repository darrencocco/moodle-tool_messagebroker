<?php
namespace messagebrokerdatastore_standarddb;

use tool_messagebroker\message\durable_dao_factory_interface;
use tool_messagebroker\message\durable_dao_interface;

class durable_dao_factory implements durable_dao_factory_interface {

    static function make_durable_dao($variant): durable_dao_interface {
        // TODO: Implement make_durable_dao() method.
    }

    static function make_specific_durable_dao($variant, $variantdata): durable_dao_interface {
        // TODO: Implement make_specific_durable_dao() method.
    }
}
