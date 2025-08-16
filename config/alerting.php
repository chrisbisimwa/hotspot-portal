<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Alert Channels
    |--------------------------------------------------------------------------
    |
    | Configure which alert channels are active in the system.
    | Available channels: 'slack', 'email'
    |
    */
    'channels' => [
        'slack',
        'email',
    ],

    /*
    |--------------------------------------------------------------------------
    | Severity Filtering
    |--------------------------------------------------------------------------
    |
    | Configure minimum severity levels for each channel to filter noise.
    | Available severities: 'critical', 'high', 'medium', 'low'
    |
    */
    'severity_email_min' => 'high',
    'severity_slack_min' => 'medium',

    /*
    |--------------------------------------------------------------------------
    | Slack Configuration
    |--------------------------------------------------------------------------
    |
    | Webhook URL for Slack alerts. If empty, slack alerts will be disabled.
    |
    */
    'slack_webhook_url' => env('SLACK_ALERT_WEBHOOK'),

    /*
    |--------------------------------------------------------------------------
    | Email Configuration
    |--------------------------------------------------------------------------
    |
    | Email settings for alert notifications.
    |
    */
    'email' => [
        'to' => env('ALERT_EMAIL_TO', 'ops@example.com'),
        'from' => env('ALERT_EMAIL_FROM', 'alerts@example.com'),
        'subject_prefix' => env('ALERT_EMAIL_SUBJECT_PREFIX', '[ALERT]'),
    ],
];