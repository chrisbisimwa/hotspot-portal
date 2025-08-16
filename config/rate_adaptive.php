<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Adaptive Rate Limiting Configuration
    |--------------------------------------------------------------------------
    |
    | Dynamic rate limiting based on system load, queue depth, and user patterns.
    | Adapts limits based on real-time conditions to maintain performance.
    |
    */

    'enabled' => env('ADAPTIVE_RATE_ENABLED', true),

    'base_limits' => [
        // Base requests per minute by role
        'user' => (int) env('ADAPTIVE_RATE_BASE_USER', 120),
        'agent' => (int) env('ADAPTIVE_RATE_BASE_AGENT', 300),
        'admin' => (int) env('ADAPTIVE_RATE_BASE_ADMIN', 600),
        'guest' => (int) env('ADAPTIVE_RATE_BASE_GUEST', 60),
    ],

    'multipliers' => [
        // Load-based adjustment factors
        'low_load' => 1.5,    // 50% increase when system is underloaded
        'normal_load' => 1.0, // No change under normal conditions
        'high_load' => 0.7,   // 30% reduction under high load
        'critical_load' => 0.3, // 70% reduction under critical load
    ],

    'thresholds' => [
        // System load thresholds for adaptive scaling
        'queue_critical_depth' => (int) env('QUEUE_CRITICAL_MAX', 100),
        'cpu_usage_high' => 70, // TODO: Implement real CPU monitoring
        'memory_usage_high' => 85,
        'response_time_high' => 500, // milliseconds
    ],

    'calculation' => [
        // How to calculate adaptive limits
        'window_minutes' => 5, // Time window for load analysis
        'min_requests_sample' => 10, // Minimum requests for meaningful stats
        'max_multiplier' => 2.0, // Maximum increase multiplier
        'min_multiplier' => 0.1, // Minimum decrease multiplier
    ],

    'endpoints' => [
        // Endpoint-specific configurations
        'auth' => [
            'base_limit' => 30, // auth endpoints get special treatment
            'multiplier_enabled' => false, // don't adapt auth limits
        ],
        'api' => [
            'include_in_adaptive' => true,
            'priority' => 'normal',
        ],
        'admin' => [
            'include_in_adaptive' => true,
            'priority' => 'high',
        ],
    ],

    'retry_after' => [
        // Retry-After header values (seconds)
        'default' => 60,
        'high_load' => 120,
        'critical_load' => 300,
    ],

    'bypass' => [
        // IPs that bypass rate limiting
        'whitelist' => array_filter(explode(',', env('RATE_LIMIT_WHITELIST_IPS', ''))),
        'admin_override' => env('RATE_LIMIT_ADMIN_OVERRIDE', true),
    ],
];