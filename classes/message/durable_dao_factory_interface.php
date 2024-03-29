<?php
namespace tool_messagebroker\message;

interface durable_dao_factory_interface {
    public static function make_durable_dao($variant): durable_dao_interface;

    public static function make_specific_durable_dao($variant, $variantdata): durable_dao_interface;
}
