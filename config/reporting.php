<?php

declare(strict_types=1);

return [
    /**
     * Cache TTL for report results in seconds
     */
    'cache_ttl_seconds' => env('REPORTING_CACHE_TTL', 300),

    /**
     * Time to run daily snapshot job (HH:MM format)
     */
    'snapshot_time' => '01:05',
];