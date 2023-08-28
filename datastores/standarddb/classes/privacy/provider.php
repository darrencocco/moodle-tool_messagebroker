<?php

declare(strict_types=1);

namespace mbdatastore_standarddb\privacy;

use core_privacy\local\metadata\null_provider;

/**
 * Privacy provider.
 *
 * @package   mbdatastore_standarddb
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
