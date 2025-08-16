<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | SLA Metrics Storage
    |--------------------------------------------------------------------------
    |
    | Configure whether to store SLA metrics in the database.
    |
    */
    'store_metrics' => true,

    /*
    |--------------------------------------------------------------------------
    | Metric Prefix
    |--------------------------------------------------------------------------
    |
    | Prefix for metric keys to namespace them.
    |
    */
    'metric_prefix' => 'core',

    /*
    |--------------------------------------------------------------------------
    | Metric Retention
    |--------------------------------------------------------------------------
    |
    | How long to keep SLA metrics in the database.
    |
    */
    'retention_days' => 90,

    /*
    |--------------------------------------------------------------------------
    | Aggregation Settings
    |--------------------------------------------------------------------------
    |
    | Settings for metric aggregation and snapshots.
    |
    */
    'aggregation' => [
        'snapshot_intervals' => ['5m', '15m', '1h', '24h'],
        'default_window' => '15m',
    ],

    /*
    |--------------------------------------------------------------------------
    | Alert Thresholds
    |--------------------------------------------------------------------------
    |
    | Default threshold values for SLA-based alerting.
    |
    */
    'thresholds' => [
        'mikrotik.ping_ms' => 1000, // 1 second
        'payment.initiate_latency_ms' => 5000, // 5 seconds
        'provisioning.error_rate' => 0.1, // 10%
        'payment.failure_rate' => 0.4, // 40%
    ],
];