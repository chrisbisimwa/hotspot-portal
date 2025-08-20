<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Domain\Monitoring\Services\MetricsService;
use App\Models\MetricTimeseries;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SnapshotHighFreqMetricsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;

    public function handle(MetricsService $metricsService): void
    {
        $now = now();
        $rows = [];

        try {
            $global = $metricsService->global();
            $system = $metricsService->system();
            $interfaces = $metricsService->interfacesLoad();

            foreach ($global as $k => $v) {
                $rows[] = $this->row("global.$k", $v, $now);
            }

            foreach ($system as $k => $v) {
                if ($k === 'memory_usage') {
                    $rows[] = $this->row('system.memory_usage.current', $v['current'] ?? null, $now, $v);
                } elseif ($k === 'queue_pending') {
                    $rows[] = $this->row('system.queue_pending', is_numeric($v) ? $v : null, $now);
                }
            }

            if (is_array($interfaces) && !isset($interfaces['error'])) {
                foreach ($interfaces as $iface) {
                    if (!is_array($iface)) continue;
                    $name = $iface['name'] ?? null;
                    if (!$name) continue;
                    $rx = $iface['rx-kbps'] ?? $iface['rx'] ?? null;
                    $tx = $iface['tx-kbps'] ?? $iface['tx'] ?? null;
                    if ($rx !== null)
                        $rows[] = $this->row("mikrotik.if.$name.rx_kbps", $rx, $now, $iface);
                    if ($tx !== null)
                        $rows[] = $this->row("mikrotik.if.$name.tx_kbps", $tx, $now, $iface);
                }
            }

            // Filtrer lignes vides
            $rows = array_filter($rows, fn($r) => $r !== null);

            if (!empty($rows)) {
                MetricTimeseries::insert($rows);
            }

        } catch (\Throwable $e) {
            Log::error('SnapshotHighFreqMetricsJob failed', [
                'message' => $e->getMessage(),
                'trace' => substr($e->getTraceAsString(), 0, 800),
            ]);
        }
    }

    private function row(string $key, $value, $ts, $meta = null): ?array
    {
        return [
            'metric_key' => $key,
            'value' => is_numeric($value) ? (float)$value : null,
            'meta' => $meta && !is_numeric($meta) ? json_encode($meta) : null,
            'captured_at' => $ts,
            'created_at' => $ts,
        ];
    }

    public function tags(): array
    {
        return ['metrics','snapshot','highfreq'];
    }
}