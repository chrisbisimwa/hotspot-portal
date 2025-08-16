<?php

declare(strict_types=1);

namespace App\Domain\Monitoring\Services;

use App\Domain\Alerting\DTO\AlertMessage;
use App\Domain\Alerting\Services\AlertDispatcher;
use App\Enums\IncidentSeverity;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class AnomalyDetector
{
    private AlertDispatcher $alertDispatcher;
    private SlaRecorder $slaRecorder;

    public function __construct(AlertDispatcher $alertDispatcher, SlaRecorder $slaRecorder)
    {
        $this->alertDispatcher = $alertDispatcher;
        $this->slaRecorder = $slaRecorder;
    }

    public function updateMetric(string $metricKey, float $value, array $meta = []): void
    {
        // Record the metric
        $this->slaRecorder->record($metricKey, $value, $meta);

        // Check for anomalies
        $this->checkRules($metricKey, $value, $meta);
    }

    public function checkRules(string $metricKey, float $value, array $meta = []): void
    {
        $rules = $this->getRulesForMetric($metricKey);

        foreach ($rules as $rule) {
            $this->evaluateRule($rule, $metricKey, $value, $meta);
        }
    }

    private function getRulesForMetric(string $metricKey): array
    {
        return match ($metricKey) {
            'payment.failure_rate' => [
                [
                    'type' => 'threshold',
                    'threshold' => 0.4, // 40%
                    'window' => '10m',
                    'min_samples' => 10,
                    'alert_code' => 'payment_failure_rate',
                    'severity' => IncidentSeverity::HIGH,
                    'title' => 'High Payment Failure Rate',
                ],
            ],
            'mikrotik.ping_ms' => [
                [
                    'type' => 'consecutive_failures',
                    'threshold' => 1000, // 1 second
                    'consecutive_count' => 3,
                    'alert_code' => 'mikrotik_unreachable',
                    'severity' => IncidentSeverity::CRITICAL,
                    'title' => 'MikroTik Unreachable',
                ],
            ],
            'provisioning.partial_failure' => [
                [
                    'type' => 'count_threshold',
                    'threshold' => 5,
                    'window' => '15m',
                    'alert_code' => 'provisioning_partial_spike',
                    'severity' => IncidentSeverity::MEDIUM,
                    'title' => 'Provisioning Partial Failures Spike',
                ],
            ],
            default => [],
        };
    }

    private function evaluateRule(array $rule, string $metricKey, float $value, array $meta): void
    {
        $ruleType = $rule['type'];

        switch ($ruleType) {
            case 'threshold':
                $this->evaluateThresholdRule($rule, $metricKey, $value, $meta);
                break;
            case 'consecutive_failures':
                $this->evaluateConsecutiveFailuresRule($rule, $metricKey, $value, $meta);
                break;
            case 'count_threshold':
                $this->evaluateCountThresholdRule($rule, $metricKey, $value, $meta);
                break;
        }
    }

    private function evaluateThresholdRule(array $rule, string $metricKey, float $value, array $meta): void
    {
        if ($value <= $rule['threshold']) {
            return;
        }

        $window = $rule['window'] ?? '15m';
        $minSamples = $rule['min_samples'] ?? 1;
        
        $recentCount = $this->slaRecorder->getCount($metricKey, $window);
        
        if ($recentCount < $minSamples) {
            return;
        }

        $recentAverage = $this->slaRecorder->getAverage($metricKey, $window);
        
        if ($recentAverage > $rule['threshold']) {
            $this->triggerAlert($rule, $metricKey, [
                'current_value' => $value,
                'average_value' => $recentAverage,
                'threshold' => $rule['threshold'],
                'sample_count' => $recentCount,
                'window' => $window,
            ] + $meta);
        }
    }

    private function evaluateConsecutiveFailuresRule(array $rule, string $metricKey, float $value, array $meta): void
    {
        $cacheKey = "anomaly_detector.consecutive.{$metricKey}";
        $threshold = $rule['threshold'];
        $requiredCount = $rule['consecutive_count'];

        if ($value > $threshold) {
            // Failure detected
            $consecutiveCount = Cache::get($cacheKey, 0) + 1;
            Cache::put($cacheKey, $consecutiveCount, now()->addMinutes(10));

            if ($consecutiveCount >= $requiredCount) {
                $this->triggerAlert($rule, $metricKey, [
                    'current_value' => $value,
                    'threshold' => $threshold,
                    'consecutive_failures' => $consecutiveCount,
                    'required_count' => $requiredCount,
                ] + $meta);
                
                // Reset counter after alerting
                Cache::forget($cacheKey);
            }
        } else {
            // Success, reset counter
            Cache::forget($cacheKey);
        }
    }

    private function evaluateCountThresholdRule(array $rule, string $metricKey, float $value, array $meta): void
    {
        $window = $rule['window'] ?? '15m';
        $threshold = $rule['threshold'];
        
        $recentCount = $this->slaRecorder->getCount($metricKey, $window);
        
        if ($recentCount > $threshold) {
            $this->triggerAlert($rule, $metricKey, [
                'current_count' => $recentCount,
                'threshold' => $threshold,
                'window' => $window,
            ] + $meta);
        }
    }

    private function triggerAlert(array $rule, string $metricKey, array $context): void
    {
        $alertCode = $rule['alert_code'];
        $severity = $rule['severity'];
        $title = $rule['title'];

        // Prevent alert spam - only alert once every 30 minutes for the same code
        $alertCacheKey = "alert_dispatched.{$alertCode}";
        
        if (Cache::has($alertCacheKey)) {
            Log::debug('Alert suppressed due to recent dispatch', [
                'alert_code' => $alertCode,
                'metric_key' => $metricKey,
            ]);
            return;
        }

        $body = "Anomaly detected in metric '{$metricKey}'. " . 
                "See context for details.";

        $alert = new AlertMessage(
            code: $alertCode,
            title: $title,
            severity: $severity,
            body: $body,
            context: array_merge(['metric_key' => $metricKey], $context),
        );

        $this->alertDispatcher->dispatch($alert);

        // Set cache to prevent spam
        Cache::put($alertCacheKey, true, now()->addMinutes(30));

        Log::info('Anomaly alert triggered', [
            'alert_code' => $alertCode,
            'metric_key' => $metricKey,
            'severity' => $severity->value,
        ]);
    }
}