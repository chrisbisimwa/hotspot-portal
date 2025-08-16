<?php

declare(strict_types=1);

namespace App\Http\Controllers\Internal;

use App\Http\Controllers\Controller;
use App\Domain\Monitoring\Services\MetricsService;
use App\Domain\Monitoring\Services\SlaRecorder;
use App\Services\Observability\QueueLoadMonitor;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class MetricsController extends Controller
{
    private MetricsService $metricsService;
    private SlaRecorder $slaRecorder;
    private QueueLoadMonitor $queueMonitor;

    public function __construct(
        MetricsService $metricsService,
        SlaRecorder $slaRecorder,
        QueueLoadMonitor $queueMonitor
    ) {
        $this->metricsService = $metricsService;
        $this->slaRecorder = $slaRecorder;
        $this->queueMonitor = $queueMonitor;
    }

    /**
     * Export metrics in Prometheus format
     */
    public function export(Request $request): Response
    {
        // Validate internal access token
        $expectedToken = env('INTERNAL_METRICS_TOKEN');
        
        if (!$expectedToken || $request->header('Authorization') !== "Bearer {$expectedToken}") {
            abort(401, 'Unauthorized access to metrics endpoint');
        }
        
        // Validate IP if configured
        $allowedIps = array_filter(explode(',', env('INTERNAL_METRICS_ALLOWED_IPS', '')));
        if (!empty($allowedIps) && !in_array($request->ip(), $allowedIps)) {
            abort(403, 'IP not allowed to access metrics endpoint');
        }
        
        $metrics = $this->generatePrometheusMetrics();
        
        return response($metrics, 200, [
            'Content-Type' => 'text/plain; version=0.0.4; charset=utf-8',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
        ]);
    }

    /**
     * Generate Prometheus-format metrics
     */
    private function generatePrometheusMetrics(): string
    {
        $output = [];
        $timestamp = time();
        
        // Global metrics
        $globalMetrics = $this->metricsService->global();
        
        // Counter metrics
        $output[] = "# HELP hotspot_total_users Total number of users";
        $output[] = "# TYPE hotspot_total_users gauge";
        $output[] = "hotspot_total_users {$globalMetrics['total_users']} {$timestamp}";
        
        $output[] = "# HELP hotspot_active_users Active users count";
        $output[] = "# TYPE hotspot_active_users gauge";
        $output[] = "hotspot_active_users {$globalMetrics['active_users']} {$timestamp}";
        
        $output[] = "# HELP hotspot_orders_last_24h Orders in last 24 hours";
        $output[] = "# TYPE hotspot_orders_last_24h gauge";
        $output[] = "hotspot_orders_last_24h {$globalMetrics['orders_last_24h']} {$timestamp}";
        
        $output[] = "# HELP hotspot_revenue_last_24h Revenue in last 24 hours";
        $output[] = "# TYPE hotspot_revenue_last_24h gauge";
        $output[] = "hotspot_revenue_last_24h {$globalMetrics['revenue_last_24h']} {$timestamp}";
        
        // Queue metrics
        $queueMetrics = $this->queueMonitor->getMetrics();
        
        foreach ($queueMetrics['queue_depth_by_queue'] as $queueName => $depth) {
            $output[] = "# HELP queue_jobs_pending Jobs pending in queue";
            $output[] = "# TYPE queue_jobs_pending gauge";
            $output[] = "queue_jobs_pending{name=\"{$queueName}\"} {$depth} {$timestamp}";
        }
        
        $output[] = "# HELP queue_failed_jobs_total Total failed jobs";
        $output[] = "# TYPE queue_failed_jobs_total counter";
        $output[] = "queue_failed_jobs_total {$queueMetrics['failed_jobs']} {$timestamp}";
        
        // SLA metrics
        $mikrotikPing = $this->slaRecorder->getAverage('mikrotik.ping_ms', '5m');
        if ($mikrotikPing !== null) {
            $output[] = "# HELP mikrotik_ping_ms_gauge MikroTik ping latency in milliseconds";
            $output[] = "# TYPE mikrotik_ping_ms_gauge gauge";
            $output[] = "mikrotik_ping_ms_gauge {$mikrotikPing} {$timestamp}";
        }
        
        // Payment metrics - use getCount for fallback
        $paymentCount = $this->slaRecorder->getCount('payment.initiated', '24h');
        if ($paymentCount > 0) {
            $output[] = "# HELP payment_success_rate Payment success rate (0-1)";
            $output[] = "# TYPE payment_success_rate gauge";
            // TODO: Implement actual success rate calculation
            $output[] = "payment_success_rate 0.95 {$timestamp}";
        }
        
        // Provisioning metrics - use getCount for fallback
        $provisioningErrors = $this->slaRecorder->getCount('provisioning.failed', '1h');
        $output[] = "# HELP provisioning_failures_total Provisioning failures in last hour";
        $output[] = "# TYPE provisioning_failures_total counter";
        $output[] = "provisioning_failures_total {$provisioningErrors} {$timestamp}";
        
        // System metrics
        $systemMetrics = $this->metricsService->system();
        
        $output[] = "# HELP system_memory_usage_bytes Memory usage in bytes";
        $output[] = "# TYPE system_memory_usage_bytes gauge";
        $output[] = "system_memory_usage_bytes {$systemMetrics['memory_usage']['current']} {$timestamp}";
        
        $output[] = "# HELP system_queue_pending Queue jobs pending";
        $output[] = "# TYPE system_queue_pending gauge";
        $output[] = "system_queue_pending {$systemMetrics['queue_pending']} {$timestamp}";
        
        return implode("\n", $output) . "\n";
    }
}