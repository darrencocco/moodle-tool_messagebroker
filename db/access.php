<?php

/**
 * Capability definitions for tool_messagebroker.
 *
 * @package   tool_messagebroker
 * @copyright 2023 Monash University
 * @author    Cameron Ball <cameronball@catalyst-au.net>
 */

declare(strict_types=1);

defined('MOODLE_INTERNAL') || die();

$capabilities = [
    'tool/messagebroker:submitmessage' => [
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => []
    ]
];
