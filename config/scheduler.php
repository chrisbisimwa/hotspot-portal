<?php

return [
    'sync_users_cron' => env('CRON_SYNC_USERS', '*/10 * * * *'),
    'sync_sessions_cron' => env('CRON_SYNC_SESSIONS', '*/2 * * * *'),
    'expire_users_cron' => env('CRON_EXPIRE_USERS', '*/15 * * * *'),
    'reconcile_payments_cron' => env('CRON_RECONCILE_PAYMENTS', '*/5 * * * *'),
    'dispatch_notifications_cron' => env('CRON_DISPATCH_NOTIFICATIONS', '* * * * *'),
    'prune_logs_cron' => env('CRON_PRUNE_LOGS', '0 2 * * *'),
];