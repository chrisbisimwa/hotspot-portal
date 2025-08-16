<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | OpenTelemetry Configuration
    |--------------------------------------------------------------------------
    |
    | This file configures OpenTelemetry instrumentation for observability.
    | Basic traces and metrics collection with configurable sampling.
    |
    */

    'enabled' => env('OTEL_TRACES_ENABLED', false),

    'service_name' => env('OTEL_SERVICE_NAME', 'hotspot-portal'),

    'traces' => [
        'exporter' => env('OTEL_TRACES_EXPORTER', 'console'),
        'endpoint' => env('OTEL_EXPORTER_OTLP_TRACES_ENDPOINT', 'http://localhost:4318/v1/traces'),
        'sampler' => [
            'type' => env('OTEL_TRACES_SAMPLER', 'traceidratio'),
            'ratio' => (float) env('OTEL_TRACES_SAMPLER_RATIO', 0.2),
        ],
    ],

    'metrics' => [
        'exporter' => env('OTEL_METRICS_EXPORTER', 'console'),
        'endpoint' => env('OTEL_EXPORTER_OTLP_METRICS_ENDPOINT', 'http://localhost:4318/v1/metrics'),
        'interval' => (int) env('OTEL_METRIC_EXPORT_INTERVAL', 60000), // milliseconds
    ],

    'resource' => [
        'attributes' => [
            'service.name' => env('OTEL_SERVICE_NAME', 'hotspot-portal'),
            'service.version' => env('OTEL_SERVICE_VERSION', '1.0.0'),
            'deployment.environment' => env('OTEL_RESOURCE_ATTRIBUTES_DEPLOYMENT_ENVIRONMENT', env('APP_ENV', 'local')),
        ],
    ],

    'headers' => [
        // Custom headers for OTLP exporter
        'api-key' => env('OTEL_EXPORTER_OTLP_HEADERS_API_KEY'),
    ],

    // TODO: Implement full OpenTelemetry SDK integration
    // TODO: Add automatic instrumentation for HTTP requests
    // TODO: Add database query instrumentation
    // TODO: Add queue job instrumentation middleware
];