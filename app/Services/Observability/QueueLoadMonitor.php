<?php

declare(strict_types=1);

namespace App\Services\Observability;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Support\StructuredLog;
use Carbon\Carbon;

class QueueLoadMonitor
{
    /**
     * Monitor queue load and health metrics
     */
    public function getMetrics(): array
    {
        return [
            'pending_jobs' => $this->getPendingJobsCount(),
            'failed_jobs' => $this->getFailedJobsCount(),
            'oldest_job_age' => $this->getOldestJobAge(),
            'queue_depth_by_queue' => $this->getQueueDepthByQueue(),
            'processing_rate' => $this->getProcessingRate(),
        ];
    }

    /**
     * Get total pending jobs count
     */
    public function getPendingJobsCount(): int
    {
        return DB::table('jobs')->count();
    }

    /**
     * Get failed jobs count
     */
    public function getFailedJobsCount(): int
    {
        return DB::table('failed_jobs')->count();
    }

    /**
     * Get age of oldest pending job in seconds
     */
    public function getOldestJobAge(): ?int
    {
        $oldestJob = DB::table('jobs')
            ->orderBy('created_at')
            ->first(['created_at']);

        if (!$oldestJob) {
            return null;
        }

        return Carbon::parse($oldestJob->created_at)->diffInSeconds(now());
    }

    /**
     * Get pending jobs count by queue
     */
    public function getQueueDepthByQueue(): array
    {
        $queues = DB::table('jobs')
            ->select('queue', DB::raw('count(*) as count'))
            ->groupBy('queue')
            ->get();

        $result = [];
        foreach ($queues as $queue) {
            $result[$queue->queue] = $queue->count;
        }

        return $result;
    }

    /**
     * Get job processing rate (jobs/minute)
     */
    public function getProcessingRate(): array
    {
        // Cache the calculation for 1 minute
        return Cache::remember('queue_processing_rate', 60, function () {
            $fiveMinutesAgo = Carbon::now()->subMinutes(5);
            
            // Count completed jobs in the last 5 minutes (approximation)
            $recentJobs = DB::table('jobs')
                ->where('created_at', '>=', $fiveMinutesAgo)
                ->count();

            return [
                'jobs_per_minute' => round($recentJobs / 5, 2),
                'window_minutes' => 5,
                'sample_size' => $recentJobs,
            ];
        });
    }

    /**
     * Check if queue is in critical state
     */
    public function isCriticalState(): bool
    {
        $criticalThreshold = config('rate_adaptive.thresholds.queue_critical_depth', 100);
        $criticalDepth = $this->getQueueDepthByQueue()['critical'] ?? 0;
        
        return $criticalDepth > $criticalThreshold;
    }

    /**
     * Check if oldest job exceeds age threshold
     */
    public function hasOldJobs(): bool
    {
        $maxAge = (int) env('QUEUE_CRITICAL_AGE_MAX', 60); // seconds
        $oldestAge = $this->getOldestJobAge();
        
        return $oldestAge && $oldestAge > $maxAge;
    }

    /**
     * Log queue monitoring event
     */
    public function logQueueHealth(): void
    {
        $metrics = $this->getMetrics();
        
        StructuredLog::info('queue_health_check', [
            'total_pending' => $metrics['pending_jobs'],
            'total_failed' => $metrics['failed_jobs'],
            'oldest_job_age_seconds' => $metrics['oldest_job_age'],
            'queue_depths' => $metrics['queue_depth_by_queue'],
            'processing_rate' => $metrics['processing_rate'],
            'is_critical' => $this->isCriticalState(),
            'has_old_jobs' => $this->hasOldJobs(),
        ]);

        // Alert if in critical state
        if ($this->isCriticalState() || $this->hasOldJobs()) {
            StructuredLog::warning('queue_critical_state', [
                'critical_queue_depth' => $metrics['queue_depth_by_queue']['critical'] ?? 0,
                'oldest_job_age' => $metrics['oldest_job_age'],
                'action_required' => true,
            ]);
        }
    }
}