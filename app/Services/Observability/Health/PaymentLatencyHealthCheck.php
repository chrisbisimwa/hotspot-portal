<?php

declare(strict_types=1);

namespace App\Services\Observability\Health;

use App\Domain\Monitoring\Services\SlaRecorder;

class PaymentLatencyHealthCheck
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
            // Get average payment processing time over last 24 hours
            $avgLatencyMs = $this->slaRecorder->getAverage('payment.processing_time_ms', '24h');
            $duration = round((microtime(true) - $start) * 1000, 2);
            
            // Health criteria
            $maxLatencyMs = 5000; // 5 seconds threshold
            $warningLatencyMs = 3000; // 3 seconds warning
            
            $healthy = true;
            $warning = false;
            $message = 'Payment processing latency is healthy';
            
            if ($avgLatencyMs === null) {
                // No recent payment data - could be normal for low-traffic periods
                $message = 'No recent payment data available';
            } elseif ($avgLatencyMs > $maxLatencyMs) {
                $healthy = false;
                $message = "Payment latency too high: {$avgLatencyMs}ms (max: {$maxLatencyMs}ms)";
            } elseif ($avgLatencyMs > $warningLatencyMs) {
                $warning = true;
                $message = "Payment latency elevated: {$avgLatencyMs}ms (warning: {$warningLatencyMs}ms)";
            } else {
                $message = "Payment latency healthy: {$avgLatencyMs}ms";
            }
            
            $result = [
                'healthy' => $healthy,
                'message' => $message,
                'duration_ms' => $duration,
                'metrics' => [
                    'avg_payment_latency_ms' => $avgLatencyMs,
                    'threshold_ms' => $maxLatencyMs,
                ],
            ];
            
            if ($warning) {
                $result['warning'] = true;
            }
            
            return $result;
            
        } catch (\Exception $e) {
            $duration = round((microtime(true) - $start) * 1000, 2);
            
            return [
                'healthy' => false,
                'message' => 'Payment latency health check failed: ' . $e->getMessage(),
                'duration_ms' => $duration,
            ];
        }
    }
}