<?php

declare(strict_types=1);

namespace App\Services\Observability;

use App\Services\Observability\Health\DbHealthCheck;
use App\Services\Observability\Health\RedisHealthCheck;
use App\Services\Observability\Health\QueueHealthCheck;
use App\Services\Observability\Health\MikrotikHealthCheck;
use App\Services\Observability\Health\PaymentLatencyHealthCheck;

class HealthCheckRunner
{
    private array $checks = [];

    public function __construct()
    {
        $this->checks = [
            'database' => new DbHealthCheck(),
            'redis' => new RedisHealthCheck(),
            'queue' => new QueueHealthCheck(),
            'mikrotik' => new MikrotikHealthCheck(),
            'payment_latency' => new PaymentLatencyHealthCheck(),
        ];
    }

    /**
     * Run all health checks
     */
    public function runAll(): array
    {
        $results = [];
        $overallHealthy = true;

        foreach ($this->checks as $name => $check) {
            try {
                $result = $check->check();
                $results[$name] = $result;
                
                if (!$result['healthy']) {
                    $overallHealthy = false;
                }
            } catch (\Exception $e) {
                $results[$name] = [
                    'healthy' => false,
                    'message' => 'Health check failed: ' . $e->getMessage(),
                    'duration_ms' => 0,
                ];
                $overallHealthy = false;
            }
        }

        return [
            'status' => $overallHealthy ? 'healthy' : 'unhealthy',
            'checks' => $results,
            'timestamp' => now()->toISOString(),
        ];
    }

    /**
     * Run liveness check (basic checks only)
     */
    public function runLiveness(): array
    {
        return [
            'status' => 'ok',
            'timestamp' => now()->toISOString(),
        ];
    }

    /**
     * Run readiness check (all critical services)
     */
    public function runReadiness(): array
    {
        $criticalChecks = ['database', 'queue', 'mikrotik'];
        $results = [];
        $ready = true;

        foreach ($criticalChecks as $checkName) {
            if (isset($this->checks[$checkName])) {
                try {
                    $result = $this->checks[$checkName]->check();
                    $results[$checkName] = $result;
                    
                    if (!$result['healthy']) {
                        $ready = false;
                    }
                } catch (\Exception $e) {
                    $results[$checkName] = [
                        'healthy' => false,
                        'message' => 'Check failed: ' . $e->getMessage(),
                        'duration_ms' => 0,
                    ];
                    $ready = false;
                }
            }
        }

        return [
            'status' => $ready ? 'ready' : 'not_ready',
            'checks' => $results,
            'timestamp' => now()->toISOString(),
        ];
    }

    /**
     * Get health summary
     */
    public function getSummary(): array
    {
        $all = $this->runAll();
        
        return [
            'overall_status' => $all['status'],
            'healthy_checks' => count(array_filter($all['checks'], fn($check) => $check['healthy'])),
            'total_checks' => count($all['checks']),
            'timestamp' => $all['timestamp'],
        ];
    }
}