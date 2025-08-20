<?php

return [
    // Cron expressions (utilisées dans routes/console.php)
    'snapshot_cron' => env('MONITORING_TS_SNAPSHOT_CRON', '*/5 * * * *'),
    'prune_cron' => env('MONITORING_TS_PRUNE_CRON', '25 3 * * *'),

    // Rétention (jours)
    'retention_days' => env('MONITORING_TS_RETENTION_DAYS', 14),

    // Clés par défaut si aucun param "keys" dans l’endpoint timeseries
    'default_keys' => [
        'global.active_sessions_count',
        'system.queue_pending',
        'global.revenue_last_24h',
    ],
];