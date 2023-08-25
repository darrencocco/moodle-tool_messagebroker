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
$string['receivemode_help'] = 'Specifies how receivers will be treated when a message enters the system. When a value is selected, all receivers will be sent messages through the selected process (ephemeral/durable). When set to "Receiver preference" then the receivers preferred method will be used.<ul><li>Ephemeral: Receiver will receive and be required to process the message the before the message is confirmed as received by Moodle (Moodle doesn\'t retry these receivers).</li><li>Durable: Receiver will receive and process the message in the background (Moodle will continue to retry the message internally until the receiver gives a positive response).</li></ul>';
