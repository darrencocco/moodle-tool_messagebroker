<?php
$functions = [
    'mbconnector_webservice_submit_message' => [
        'classname' => 'mbconnector_webservice\external\receive_message',
        'description' => 'Allows submitting messages to the Moodle Message Broker',
        'type' => 'write',
        'capabilities'  => 'local/messagebroker:submitmessage'
    ]
];
