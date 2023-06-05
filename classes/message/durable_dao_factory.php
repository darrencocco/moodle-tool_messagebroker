<?php
namespace local_messagebroker\message;

use messagebrokerdatastore_standarddb\durable_dao;

class durable_dao_factory implements durable_dao_factory_interface {
    static function make_durable_dao($variant): durable_dao {

    }

    static function make_specific_durable_dao($variant, $variantdata): durable_dao {

    }
}