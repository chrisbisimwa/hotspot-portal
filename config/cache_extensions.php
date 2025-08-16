<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Extended Cache Configuration
    |--------------------------------------------------------------------------
    |
    | Define specific TTL values for different cache categories and tags.
    | Used by cache warming and invalidation strategies.
    |
    */

    'ttl' => [
        // User data caching
        'profiles_list' => 300, // 5 minutes - user profiles listing
        'user_profile' => 600, // 10 minutes - individual user profile
        'user_sessions' => 120, // 2 minutes - active user sessions

        // System metrics and monitoring
        'metrics_global' => 60, // 1 minute - global system metrics
        'metrics_interfaces' => 30, // 30 seconds - MikroTik interfaces
        'queue_status' => 30, // 30 seconds - queue status metrics

        // Configuration data
        'feature_flags' => 60, // 1 minute - feature flags
        'sla_metrics' => 300, // 5 minutes - SLA calculations
        
        // Registry and reports  
        'registry_reports' => 900, // 15 minutes - registry reports
        'export_data' => 1800, // 30 minutes - export data preparation

        // API responses
        'api_user_profiles' => 300, // 5 minutes - public API user profiles
        'api_metrics' => 60, // 1 minute - API metrics responses
    ],

    'tags' => [
        // Cache tags for organized invalidation
        'user_profiles' => 'user_profiles',
        'metrics' => 'metrics',
        'feature_flags' => 'feature_flags',
        'sla_data' => 'sla_data',
        'api_responses' => 'api_responses',
        'exports' => 'exports',
    ],

    'warm' => [
        // Cache warming configuration
        'enabled' => env('CACHE_WARM_ENABLED', true),
        'batch_size' => 50, // Number of items to warm per batch
        'retry_attempts' => 3,
        
        // What to warm on startup/schedule
        'startup' => [
            'active_user_profiles',
            'feature_flags',
            'global_metrics',
        ],
    ],

    'invalidation' => [
        // Automatic cache invalidation rules
        'user_profile_updated' => ['user_profiles', 'api_responses'],
        'feature_flag_changed' => ['feature_flags'],
        'metrics_updated' => ['metrics'],
        'sla_data_changed' => ['sla_data'],
    ],
];