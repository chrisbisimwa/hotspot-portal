<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\MetricTimeseries;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class PruneOldTimeseriesMetricsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        $days = (int) config('monitoring_timeseries.retention_days', 14);
        $cutoff = now()->subDays($days);

        try {
            $deleted = MetricTimeseries::where('captured_at', '<', $cutoff)->delete();
            Log::info('PruneOldTimeseriesMetricsJob done', [
                'deleted' => $deleted,
                'cutoff' => $cutoff->toDateTimeString(),
            ]);
        } catch (\Throwable $e) {
            Log::error('PruneOldTimeseriesMetricsJob failed', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function tags(): array
    {
        return ['metrics','prune'];
    }
}