<?php

declare(strict_types=1);

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Services\Observability\QueueLoadMonitor;
use App\Support\StructuredLog;

class MonitorQueuesJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        // Run this job on the high priority queue
        $this->onQueue('high');
    }

    /**
     * Execute the job.
     */
    public function handle(QueueLoadMonitor $queueMonitor): void
    {
        try {
            // Log current queue health
            $queueMonitor->logQueueHealth();
            
            // Check if we need to alert
            $metrics = $queueMonitor->getMetrics();
            
            if ($queueMonitor->isCriticalState()) {
                $this->alertCriticalQueueState($metrics);
            }
            
            if ($queueMonitor->hasOldJobs()) {
                $this->alertOldJobs($metrics);
            }
            
        } catch (\Exception $e) {
            StructuredLog::error('queue_monitoring_failed', [
                'error' => $e->getMessage(),
                'job' => self::class,
            ]);
        }
    }

    /**
     * Alert when queue is in critical state
     */
    private function alertCriticalQueueState(array $metrics): void
    {
        $criticalDepth = $metrics['queue_depth_by_queue']['critical'] ?? 0;
        $threshold = config('rate_adaptive.thresholds.queue_critical_depth', 100);
        
        StructuredLog::warning('queue_critical_backlog', [
            'critical_queue_depth' => $criticalDepth,
            'threshold' => $threshold,
            'total_pending' => $metrics['pending_jobs'],
            'alert_type' => 'critical_backlog',
            'action_required' => true,
        ]);
        
        // TODO: Send alert to notification channels (Slack, email, etc.)
    }

    /**
     * Alert when jobs are too old
     */
    private function alertOldJobs(array $metrics): void
    {
        $oldestAge = $metrics['oldest_job_age'];
        $maxAge = (int) env('QUEUE_CRITICAL_AGE_MAX', 60);
        
        StructuredLog::warning('queue_old_jobs', [
            'oldest_job_age_seconds' => $oldestAge,
            'max_age_seconds' => $maxAge,
            'alert_type' => 'old_jobs',
            'action_required' => true,
        ]);
        
        // TODO: Send alert to notification channels
    }
}
