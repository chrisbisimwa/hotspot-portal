<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Export;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class PurgeOldExportsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function handle(): void
    {
        $startTime = microtime(true);
        $retentionDays = config('exports.retention_days', 7);
        $cutoffDate = now()->subDays($retentionDays);
        
        Log::info('PurgeOldExportsJob: Starting purge process', [
            'retention_days' => $retentionDays,
            'cutoff_date' => $cutoffDate->toDateString(),
        ]);

        try {
            $disk = Storage::disk(config('exports.storage_disk', 'local'));
            
            // Get old exports
            $oldExports = Export::where('created_at', '<', $cutoffDate)->get();
            
            $filesDeleted = 0;
            $recordsDeleted = 0;

            foreach ($oldExports as $export) {
                // Delete file if it exists
                if ($export->file_path && $disk->exists($export->file_path)) {
                    $disk->delete($export->file_path);
                    $filesDeleted++;
                }

                // Delete the export record
                $export->delete();
                $recordsDeleted++;
            }

            $executionTime = microtime(true) - $startTime;
            Log::info('PurgeOldExportsJob: Purge completed successfully', [
                'execution_time_seconds' => round($executionTime, 3),
                'files_deleted' => $filesDeleted,
                'records_deleted' => $recordsDeleted,
                'cutoff_date' => $cutoffDate->toDateString(),
            ]);

        } catch (\Exception $e) {
            $executionTime = microtime(true) - $startTime;
            Log::error('PurgeOldExportsJob: Purge failed', [
                'error' => $e->getMessage(),
                'execution_time_seconds' => round($executionTime, 3),
            ]);
            
            throw $e;
        }
    }

    public function tags(): array
    {
        return ['purge', 'exports', 'cleanup'];
    }
}