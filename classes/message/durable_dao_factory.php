<?php
namespace tool_messagebroker\message;

use coding_exception;

class durable_dao_factory implements durable_dao_factory_interface {

    public static function make_durable_dao($variant): durable_dao_interface {
        $classname = 'mbdatastore_' . $variant . '\durable_dao_factory';
        if (!class_exists($classname)) {
            throw new coding_exception('Durable DAO factory not implemented for ' . $variant);
        }

        return $classname::make_durable_dao($variant);
    }

    public static function make_specific_durable_dao($variant, $variantdata): durable_dao_interface {
        $classname = 'mbdatastore_' . $variant . '\durable_dao_factory';
        if (!class_exists($classname)) {
            throw new coding_exception('Durable DAO factory not implemented for ' . $variant);
        }

        return $classname::make_specific_durable_dao($variant, $variantdata);
    }
}
