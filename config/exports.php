<?php

declare(strict_types=1);

return [
    /**
     * Storage disk to use for export files
     */
    'storage_disk' => env('EXPORTS_DISK', 'local'),

    /**
     * Number of days to retain export files and records
     */
    'retention_days' => env('EXPORTS_RETENTION_DAYS', 7),

    /**
     * Maximum number of rows allowed per report
     */
    'max_rows' => env('EXPORTS_MAX_ROWS', 50000),
];