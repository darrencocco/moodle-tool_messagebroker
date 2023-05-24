<?php
$functions = [
    'messagebrokerconnector_http_submitmessage' => [
        'classname' => 'messagebrokerconnector_http\webservice',
        'methodname' => 'receive_message',
        'description' => 'Allows submitting messages to the Moodle Message Broker',
        'type' => 'write',
        'capabilities'  => 'local/messagebroker:submitmessage'
    ]
];