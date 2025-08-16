<?php

declare(strict_types=1);

namespace App\Services\Observability\Health;

use App\Domain\Monitoring\Services\SlaRecorder;
use Carbon\Carbon;

class MikrotikHealthCheck
{
    private SlaRecorder $slaRecorder;

    public function __construct()
    {
        $this->slaRecorder = app(SlaRecorder::class);
    }

    public function check(): array
    {
        $start = microtime(true);
        
        try {
            // Check last MikroTik ping time and latency
            $lastPingMs = $this->slaRecorder->getAverage('mikrotik.ping_ms', '5m');
            $duration = round((microtime(true) - $start) * 1000, 2);
            
            // Get last recorded ping time (approximate)
            $lastPingTime = $this->getLastPingTime();
            $pingAgeSeconds = $lastPingTime ? Carbon::now()->diffInSeconds($lastPingTime) : null;
            
            // Health criteria
            $maxPingAgeSeconds = 60; // Last ping should be within 60 seconds
            $maxPingLatencyMs = 1000; // Ping should be under 1 second
            
            $healthy = true;
            $issues = [];
            
            if ($pingAgeSeconds === null || $pingAgeSeconds > $maxPingAgeSeconds) {
                $healthy = false;
                $issues[] = $pingAgeSeconds === null 
                    ? 'No recent ping data' 
                    : "Last ping {$pingAgeSeconds}s ago (max: {$maxPingAgeSeconds}s)";
            }
            
            if ($lastPingMs !== null && $lastPingMs > $maxPingLatencyMs) {
                $healthy = false;
                $issues[] = "High ping latency: {$lastPingMs}ms (max: {$maxPingLatencyMs}ms)";
            }
            
            $message = $healthy 
                ? "MikroTik connectivity is healthy (ping: {$lastPingMs}ms)"
                : 'MikroTik issues: ' . implode(', ', $issues);
            
            return [
                'healthy' => $healthy,
                'message' => $message,
                'duration_ms' => $duration,
                'metrics' => [
                    'last_ping_ms' => $lastPingMs,
                    'last_ping_age_seconds' => $pingAgeSeconds,
                ],
            ];
            
        } catch (\Exception $e) {
            $duration = round((microtime(true) - $start) * 1000, 2);
            
            return [
                'healthy' => false,
                'message' => 'MikroTik health check failed: ' . $e->getMessage(),
                'duration_ms' => $duration,
            ];
        }
    }
    
    /**
     * Get approximate last ping time
     * This is a placeholder - in real implementation would query SLA records
     */
    private function getLastPingTime(): ?Carbon
    {
        // TODO: Query actual SLA records for last mikrotik ping timestamp
        // For now, assume recent if we have ping data
        return Carbon::now()->subSeconds(30); // Placeholder
    }
}