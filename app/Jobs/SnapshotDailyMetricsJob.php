<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Domain\Monitoring\Services\MetricsService;
use App\Domain\Reporting\Events\MetricsUpdated;
use App\Models\MetricSnapshot;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SnapshotDailyMetricsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(
        public ?string $snapshotDate = null
    ) {}

    public function handle(MetricsService $metricsService): void
    {
        $startTime = microtime(true);
        $date = $this->snapshotDate ?? now()->format('Y-m-d');
        
        Log::info('SnapshotDailyMetricsJob: Starting snapshot process', [
            'snapshot_date' => $date,
        ]);

        try {
            // Get current metrics
            $globalMetrics = $metricsService->global();
            $systemMetrics = $metricsService->system();
            
            $snapshotsCreated = 0;

            // Store global metrics
            foreach ($globalMetrics as $key => $value) {
                MetricSnapshot::updateOrCreate(
                    [
                        'snapshot_date' => $date,
                        'metric_key' => "global.{$key}",
                    ],
                    [
                        'value' => is_numeric($value) ? ['numeric' => $value] : ['value' => $value],
                        'created_at' => now(),
                    ]
                );
                $snapshotsCreated++;
            }

            // Store system metrics
            foreach ($systemMetrics as $key => $value) {
                MetricSnapshot::updateOrCreate(
                    [
                        'snapshot_date' => $date,
                        'metric_key' => "system.{$key}",
                    ],
                    [
                        'value' => is_array($value) ? $value : ['value' => $value],
                        'created_at' => now(),
                    ]
                );
                $snapshotsCreated++;
            }

            $executionTime = microtime(true) - $startTime;
            Log::info('SnapshotDailyMetricsJob: Snapshot completed successfully', [
                'snapshot_date' => $date,
                'execution_time_seconds' => round($executionTime, 3),
                'snapshots_created' => $snapshotsCreated,
            ]);

            // Broadcast metrics update
            $allMetrics = array_merge(
                array_map(fn($k, $v) => ['key' => "global.{$k}", 'value' => $v], array_keys($globalMetrics), $globalMetrics),
                array_map(fn($k, $v) => ['key' => "system.{$k}", 'value' => $v], array_keys($systemMetrics), $systemMetrics)
            );
            
            event(new MetricsUpdated([
                'type' => 'daily_snapshot',
                'date' => $date,
                'metrics' => $allMetrics,
            ]));

        } catch (\Exception $e) {
            $executionTime = microtime(true) - $startTime;
            Log::error('SnapshotDailyMetricsJob: Snapshot failed', [
                'snapshot_date' => $date,
                'error' => $e->getMessage(),
                'execution_time_seconds' => round($executionTime, 3),
            ]);
            
            throw $e;
        }
    }

    public function tags(): array
    {
        return ['snapshot', 'metrics', 'daily'];
    }
}