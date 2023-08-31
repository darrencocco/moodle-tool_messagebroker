<?php

declare(strict_types=1);

namespace mbconnector_webservice\privacy;

use core_privacy\local\metadata\null_provider;

/**
 * Privacy provider.
 *
 * @package   mbconnector_webservice
 * @copyright 2023 Monash University
 * @author    Cameron Ball <cameronball@catalyst-au.net>
 */
class provider implements null_provider {

    /**
     * Constructor.
     */
    public static function get_reason(): string {
        return 'privacy:metadata';
    }
}
