<?php
namespace mbdatastore_standarddb;

use tool_messagebroker\message\durable_dao_factory_interface;
use tool_messagebroker\message\durable_dao_interface;

class durable_dao_factory implements durable_dao_factory_interface {

    public static function make_durable_dao($variant): durable_dao_interface {
        return new durable_dao;
    }

    public static function make_specific_durable_dao($variant, $variantdata): durable_dao_interface {
        return new durable_dao;
    }
}
