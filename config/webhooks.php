<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Webhook Retry Configuration
    |--------------------------------------------------------------------------
    |
    | Configure how webhook delivery attempts are retried.
    |
    */
    'max_retries' => 5,
    'retry_schedule_minutes' => [1, 5, 30, 120, 360], // 1m, 5m, 30m, 2h, 6h
    'timeout_seconds' => 8,

    /*
    |--------------------------------------------------------------------------
    | Security
    |--------------------------------------------------------------------------
    |
    | Security settings for webhook payloads.
    |
    */
    'signature_header' => 'X-Hub-Signature-Sha256',
    'signature_algorithm' => 'sha256',

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    |
    | Rate limiting for webhook endpoints to prevent overwhelming targets.
    |
    */
    'rate_limit_per_minute' => 60,

    /*
    |--------------------------------------------------------------------------
    | Payload Filtering
    |--------------------------------------------------------------------------
    |
    | Keys to filter out from webhook payloads for security.
    |
    */
    'filtered_keys' => [
        'password',
        'secret',
        'token',
        'api_key',
        'private_key',
    ],
];