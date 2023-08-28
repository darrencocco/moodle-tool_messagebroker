<?php

/**
 * Settings for tool_messagebroker.
 *
 * @package   tool_messagebroker
 * @copyright 2023 Monash University
 * @author    Cameron Ball <cameronball@catalyst-au.net>
 */

declare(strict_types=1);

use tool_messagebroker\receiver\processing_style;

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $settingspage = new admin_settingpage('tool_messagebroker', get_string('pluginname', 'tool_messagebroker'));
    $ADMIN->add('tools', $settingspage);


    if ($ADMIN->fulltree) {
        $datastoreplugins = array_keys(core_plugin_manager::instance()->get_installed_plugins('mbdatastore'));
        $datastoreselections = array_map(
            fn(string $pluginname): string => get_string('pluginname', 'mbdatastore_' . $pluginname),
            array_combine($datastoreplugins, $datastoreplugins)
        );

        $settingspage->add(new admin_setting_configselect(
            'tool_messagebroker/datastore',
            get_string('datastore', 'tool_messagebroker'),
            get_string('datastore_help', 'tool_messagebroker'),
            'standarddb',
            $datastoreselections
        ));

        $receivemodeselections = array_map(
            fn(string $style): string => get_string('processingstyle:' . strtolower($style), 'tool_messagebroker'),
            array_flip((new \ReflectionClass(processing_style::class))->getConstants())
        );

        $settingspage->add( new admin_setting_configselect(
            'tool_messagebroker/receivemode',
            get_string('receivemode', 'tool_messagebroker'),
            get_string('receivemode_help', 'tool_messagebroker'),
            processing_style::RECEIVER_PREFERENCE,
            $receivemodeselections
        ));

        $settingspage->add(new admin_setting_configtext(
            'tool_messagebroker/messagespertask',
            get_string('messagespertask', 'tool_messagebroker'),
            get_string('messagespertask_help', 'tool_messagebroker'),
            1,
            PARAM_INT
        ));
    }
}


