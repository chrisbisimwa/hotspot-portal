<?php

declare(strict_types=1);

namespace App\Services\Observability\Health;

use App\Services\Observability\QueueLoadMonitor;

class QueueHealthCheck
{
    private QueueLoadMonitor $queueMonitor;

    public function __construct()
    {
        $this->queueMonitor = app(QueueLoadMonitor::class);
    }

    public function check(): array
    {
        $start = microtime(true);
        
        try {
            $metrics = $this->queueMonitor->getMetrics();
            $duration = round((microtime(true) - $start) * 1000, 2);
            
            // Check if queue is in critical state
            $isCritical = $this->queueMonitor->isCriticalState();
            $hasOldJobs = $this->queueMonitor->hasOldJobs();
            
            $healthy = !$isCritical && !$hasOldJobs;
            
            $message = $healthy 
                ? 'Queue system is healthy'
                : $this->buildUnhealthyMessage($isCritical, $hasOldJobs, $metrics);
            
            return [
                'healthy' => $healthy,
                'message' => $message,
                'duration_ms' => $duration,
                'metrics' => [
                    'pending_jobs' => $metrics['pending_jobs'],
                    'failed_jobs' => $metrics['failed_jobs'],
                    'oldest_job_age_seconds' => $metrics['oldest_job_age'],
                    'critical_queue_depth' => $metrics['queue_depth_by_queue']['critical'] ?? 0,
                ],
            ];
            
        } catch (\Exception $e) {
            $duration = round((microtime(true) - $start) * 1000, 2);
            
            return [
                'healthy' => false,
                'message' => 'Queue health check failed: ' . $e->getMessage(),
                'duration_ms' => $duration,
            ];
        }
    }
    
    private function buildUnhealthyMessage(bool $isCritical, bool $hasOldJobs, array $metrics): string
    {
        $issues = [];
        
        if ($isCritical) {
            $criticalDepth = $metrics['queue_depth_by_queue']['critical'] ?? 0;
            $issues[] = "Critical queue backlog: {$criticalDepth} jobs";
        }
        
        if ($hasOldJobs) {
            $oldestAge = $metrics['oldest_job_age'];
            $issues[] = "Oldest job age: {$oldestAge} seconds";
        }
        
        return 'Queue issues: ' . implode(', ', $issues);
    }
}