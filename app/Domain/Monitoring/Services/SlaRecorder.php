<?php

declare(strict_types=1);

namespace App\Domain\Monitoring\Services;

use App\Models\SlaMetric;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class SlaRecorder
{
    public function record(string $metricKey, float $value, array $meta = []): void
    {
        if (!config('sla.store_metrics', true)) {
            return;
        }

        $prefixedKey = $this->prefixMetricKey($metricKey);

        try {
            SlaMetric::create([
                'metric_key' => $prefixedKey,
                'value' => $value,
                'captured_at' => now(),
                'meta' => $meta,
            ]);

            Log::debug('SLA metric recorded', [
                'metric_key' => $prefixedKey,
                'value' => $value,
                'meta' => $meta,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to record SLA metric', [
                'metric_key' => $prefixedKey,
                'value' => $value,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function getAverage(string $metricKey, string $range = '15m'): ?float
    {
        $prefixedKey = $this->prefixMetricKey($metricKey);
        $since = $this->parseTimeRange($range);

        $average = SlaMetric::where('metric_key', $prefixedKey)
            ->where('captured_at', '>=', $since)
            ->avg('value');

        return $average ? (float) $average : null;
    }

    public function getCount(string $metricKey, string $range = '15m'): int
    {
        $prefixedKey = $this->prefixMetricKey($metricKey);
        $since = $this->parseTimeRange($range);

        return SlaMetric::where('metric_key', $prefixedKey)
            ->where('captured_at', '>=', $since)
            ->count();
    }

    public function snapshotAverages(array $metricKeys, string $range = '15m'): array
    {
        $snapshots = [];

        foreach ($metricKeys as $metricKey) {
            $snapshots[$metricKey] = [
                'average' => $this->getAverage($metricKey, $range),
                'count' => $this->getCount($metricKey, $range),
                'range' => $range,
                'timestamp' => now()->toISOString(),
            ];
        }

        return $snapshots;
    }

    private function prefixMetricKey(string $metricKey): string
    {
        $prefix = config('sla.metric_prefix', 'core');
        
        if (str_starts_with($metricKey, $prefix . '.')) {
            return $metricKey;
        }

        return $prefix . '.' . $metricKey;
    }

    private function parseTimeRange(string $range): Carbon
    {
        $matches = [];
        
        if (preg_match('/^(\d+)([mhd])$/', $range, $matches)) {
            $value = (int) $matches[1];
            $unit = $matches[2];

            return match ($unit) {
                'm' => now()->subMinutes($value),
                'h' => now()->subHours($value),
                'd' => now()->subDays($value),
                default => now()->subMinutes(15),
            };
        }

        return now()->subMinutes(15); // Default to 15 minutes
    }
}