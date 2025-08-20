<?php

use App\Jobs\DispatchPendingNotificationsJob;
use App\Jobs\PruneOldExportsJob;
use App\Jobs\PruneOldLogsJob;
use App\Jobs\ReconcilePaymentsJob;
use App\Jobs\SnapshotDailyMetricsJob;
use App\Jobs\SyncActiveSessionsJob;
use App\Jobs\SyncMikrotikUsersJob;
use App\Jobs\UpdateExpiredHotspotUsersJob;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Jobs\SnapshotHighFreqMetricsJob;
use App\Jobs\PruneOldTimeseriesMetricsJob;


Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule jobs using configuration
Schedule::job(new SyncMikrotikUsersJob())
    ->cron(config('scheduler.sync_users_cron'))
    ->withoutOverlapping();

Schedule::job(new SyncActiveSessionsJob())
    ->cron(config('scheduler.sync_sessions_cron'))
    ->withoutOverlapping();

Schedule::job(new UpdateExpiredHotspotUsersJob())
    ->cron(config('scheduler.expire_users_cron'))
    ->withoutOverlapping();

Schedule::job(new ReconcilePaymentsJob())
    ->cron(config('scheduler.reconcile_payments_cron'))
    ->withoutOverlapping();

Schedule::job(new DispatchPendingNotificationsJob())
    ->cron(config('scheduler.dispatch_notifications_cron'))
    ->withoutOverlapping();

Schedule::job(new PruneOldLogsJob())
    ->cron(config('scheduler.prune_logs_cron'))
    ->withoutOverlapping();

Schedule::job(new SnapshotHighFreqMetricsJob())
    ->cron(config('monitoring_timeseries.snapshot_cron'))
    ->withoutOverlapping();

Schedule::job(new PruneOldTimeseriesMetricsJob())
    ->cron(config('monitoring_timeseries.prune_cron'))
    ->withoutOverlapping();

// Reporting jobs
// TODO: Re-enable after migration is run
// Schedule::job(new SnapshotDailyMetricsJob())
//     ->dailyAt(config('reporting.snapshot_time'))
//     ->withoutOverlapping();

// Schedule::job(new PurgeOldExportsJob())
//     ->dailyAt('02:15')
//     ->withoutOverlapping();
