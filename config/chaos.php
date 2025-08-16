<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Chaos Engineering Configuration
    |--------------------------------------------------------------------------
    |
    | Chaos testing configuration for resilience testing in staging environment.
    | NEVER enable in production. Used to test system fault tolerance.
    |
    */

    'enabled' => env('CHAOS_ENABLED', false) && app()->environment('staging'),

    'probability' => [
        // Probability of chaos events (0.0 - 1.0)
        'error_rate' => (float) env('CHAOS_ERROR_RATE', 0.05), // 5% error injection
        'latency_rate' => (float) env('CHAOS_LATENCY_RATE', 0.10), // 10% latency injection
        'timeout_rate' => (float) env('CHAOS_TIMEOUT_RATE', 0.02), // 2% timeout simulation
    ],

    'latency' => [
        // Artificial latency injection (milliseconds)
        'min_ms' => (int) env('CHAOS_LATENCY_MIN_MS', 50),
        'max_ms' => (int) env('CHAOS_LATENCY_MAX_MS', 400),
    ],

    'errors' => [
        // Error types to inject randomly
        'http_codes' => [500, 502, 503, 504],
        'exceptions' => [
            'connection_timeout',
            'service_unavailable', 
            'internal_error',
        ],
    ],

    'exclusions' => [
        // Routes/endpoints that should never have chaos applied
        'routes' => [
            'health*',
            'internal/metrics',
            'admin/monitoring*',
        ],
        'user_roles' => [
            // Never apply chaos to admin users in staging
            'admin',
        ],
    ],

    'scheduling' => [
        // When chaos testing is active
        'enabled_hours' => [9, 10, 11, 14, 15, 16], // Business hours only
        'disabled_days' => [], // Days of week to disable (0 = Sunday)
        'timezone' => env('APP_TIMEZONE', 'UTC'),
    ],

    'targeting' => [
        // What types of operations to target
        'database_queries' => true,
        'external_apis' => true,
        'cache_operations' => false, // Don't break cache in staging
        'queue_jobs' => true,
    ],

    'monitoring' => [
        // Track chaos testing impact
        'log_events' => true,
        'metric_prefix' => 'chaos.',
        'alert_on_errors' => false, // Don't alert on intentional chaos
    ],
];