<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Log;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log as LaravelLog;

class PruneOldLogsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    private int $pruneAfterDays;

    public function __construct(?int $pruneAfterDays = null)
    {
        $this->pruneAfterDays = $pruneAfterDays ?? config('logging_extra.prune_after_days', 30);
    }

    public function handle(): void
    {
        $startTime = microtime(true);
        
        LaravelLog::info('PruneOldLogsJob: Starting log pruning process', [
            'prune_after_days' => $this->pruneAfterDays
        ]);

        try {
            $cutoffDate = now()->subDays($this->pruneAfterDays);
            
            // Count logs to be deleted
            $logsToDeleteCount = Log::where('created_at', '<', $cutoffDate)->count();
            
            LaravelLog::info('PruneOldLogsJob: Found old logs to prune', [
                'count' => $logsToDeleteCount,
                'cutoff_date' => $cutoffDate->toISOString()
            ]);

            if ($logsToDeleteCount > 0) {
                // Delete old logs (force delete since we want to permanently remove them)
                $deletedCount = Log::where('created_at', '<', $cutoffDate)->delete();
                
                LaravelLog::info('PruneOldLogsJob: Logs pruned successfully', [
                    'deleted_count' => $deletedCount,
                    'cutoff_date' => $cutoffDate->toISOString()
                ]);
            } else {
                LaravelLog::info('PruneOldLogsJob: No logs to prune');
            }

            $executionTime = microtime(true) - $startTime;
            LaravelLog::info('PruneOldLogsJob: Pruning completed', [
                'execution_time_seconds' => round($executionTime, 3),
                'logs_deleted' => $logsToDeleteCount
            ]);

        } catch (\Exception $e) {
            $executionTime = microtime(true) - $startTime;
            LaravelLog::error('PruneOldLogsJob: Pruning failed', [
                'error' => $e->getMessage(),
                'execution_time_seconds' => round($executionTime, 3)
            ]);
            
            throw $e;
        }
    }

    public function tags(): array
    {
        return ['logs', 'cleanup', 'maintenance'];
    }
}