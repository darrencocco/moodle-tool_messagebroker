<?php

/**
 * Strings for tool_messagebroker.
 *
 * @package   tool_messagebroker
 * @copyright 2023 Monash University
 * @author    Cameron Ball <cameronball@catalyst-au.net>
 */

declare(strict_types=1);

defined('MOODLE_INTERNAL') || die();

$string['datastore'] = 'Datastore plugin';
$string['datastore_help'] = 'The selected datastore plugin will be used for persisting data associated with messages.';
$string['messagespertask'] = 'Messages per task';
$string['messagespertask_help'] = 'The maximum number of messages which will be processed per execution of the Durable Message Processor task.';
$string['pluginname'] = 'Message Broker';
$string['privacy:metadata'] = 'The Message Broker plugin does not store any personal data';
$string['processingstyle:durable'] = 'Durable';
$string['processingstyle:ephemeral'] = 'Ephemeral';
$string['processingstyle:receiver_preference'] = 'Receiver preference';
$string['receivemode'] = 'Message receiving mode';
$string['receivemode_help'] = 'Specifies how receivers will be treated when a message enters the system. When a value is selected, all receivers will be treated as if they interested in that type of message. When set to "Receiver preference" are given the option to ignore messages they have not expressed preference for.';
