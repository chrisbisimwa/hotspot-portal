<?php

declare(strict_types=1);

namespace App\Services\Observability;

use Illuminate\Support\Facades\Cache;
use App\Services\Observability\QueueLoadMonitor;

class AdaptiveRateLimiter
{
    private QueueLoadMonitor $queueMonitor;

    public function __construct(QueueLoadMonitor $queueMonitor)
    {
        $this->queueMonitor = $queueMonitor;
    }

    /**
     * Calculate adaptive rate limit for a user role
     */
    public function calculateLimit(string $role, ?string $userId = null): int
    {
        if (!config('rate_adaptive.enabled', true)) {
            return $this->getBaseLimit($role);
        }

        $baseLimit = $this->getBaseLimit($role);
        $loadFactor = $this->calculateLoadFactor();
        
        $adaptiveLimit = (int) round($baseLimit * $loadFactor);
        
        // Apply boundaries
        $maxMultiplier = config('rate_adaptive.calculation.max_multiplier', 2.0);
        $minMultiplier = config('rate_adaptive.calculation.min_multiplier', 0.1);
        
        $maxLimit = (int) round($baseLimit * $maxMultiplier);
        $minLimit = (int) round($baseLimit * $minMultiplier);
        
        return max($minLimit, min($maxLimit, $adaptiveLimit));
    }

    /**
     * Get base limit for role
     */
    public function getBaseLimit(string $role): int
    {
        $baseLimits = config('rate_adaptive.base_limits', []);
        
        return $baseLimits[$role] ?? $baseLimits['user'] ?? 120;
    }

    /**
     * Calculate system load factor
     */
    public function calculateLoadFactor(): float
    {
        return Cache::remember('adaptive_rate_load_factor', 30, function () {
            $factors = [];
            
            // Queue depth factor
            $factors['queue'] = $this->calculateQueueLoadFactor();
            
            // TODO: Add CPU usage factor when real monitoring is available
            $factors['cpu'] = 1.0;
            
            // TODO: Add memory usage factor
            $factors['memory'] = 1.0;
            
            // TODO: Add response time factor
            $factors['response_time'] = 1.0;
            
            // Use the most restrictive factor
            return min($factors);
        });
    }

    /**
     * Calculate queue load factor
     */
    private function calculateQueueLoadFactor(): float
    {
        $queueDepths = $this->queueMonitor->getQueueDepthByQueue();
        $criticalQueueDepth = $queueDepths['critical'] ?? 0;
        $criticalThreshold = config('rate_adaptive.thresholds.queue_critical_depth', 100);
        
        if ($criticalQueueDepth == 0) {
            return config('rate_adaptive.multipliers.low_load', 1.5);
        }
        
        if ($criticalQueueDepth > $criticalThreshold) {
            return config('rate_adaptive.multipliers.critical_load', 0.3);
        }
        
        if ($criticalQueueDepth > ($criticalThreshold * 0.7)) {
            return config('rate_adaptive.multipliers.high_load', 0.7);
        }
        
        return config('rate_adaptive.multipliers.normal_load', 1.0);
    }

    /**
     * Get retry-after value based on current load
     */
    public function getRetryAfter(): int
    {
        $loadFactor = $this->calculateLoadFactor();
        
        if ($loadFactor <= 0.3) {
            return config('rate_adaptive.retry_after.critical_load', 300);
        }
        
        if ($loadFactor <= 0.7) {
            return config('rate_adaptive.retry_after.high_load', 120);
        }
        
        return config('rate_adaptive.retry_after.default', 60);
    }

    /**
     * Check if IP should bypass rate limiting
     */
    public function shouldBypass(string $ip, ?string $userRole = null): bool
    {
        $whitelist = config('rate_adaptive.bypass.whitelist', []);
        
        if (in_array($ip, $whitelist)) {
            return true;
        }
        
        if ($userRole === 'admin' && config('rate_adaptive.bypass.admin_override', true)) {
            return true;
        }
        
        return false;
    }

    /**
     * Get current system load summary
     */
    public function getLoadSummary(): array
    {
        return [
            'load_factor' => $this->calculateLoadFactor(),
            'queue_metrics' => $this->queueMonitor->getMetrics(),
            'is_critical_state' => $this->queueMonitor->isCriticalState(),
            'retry_after_seconds' => $this->getRetryAfter(),
        ];
    }
}