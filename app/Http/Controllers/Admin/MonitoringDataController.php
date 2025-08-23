<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MetricTimeseries;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Domain\Hotspot\Services\MikrotikMetricsService;

class MonitoringDataController extends Controller
{
    public function timeseries(Request $request): JsonResponse
    {
        $this->authorize('viewAdminMonitoring');

        $range = $request->get('range', '24h');
        $since = match ($range) {
            '1h' => now()->subHour(),
            '6h' => now()->subHours(6),
            '24h' => now()->subDay(),
            '7d' => now()->subDays(7),
            default => now()->subDay(),
        };

        $keys = $request->get('keys', config('monitoring_timeseries.default_keys'));

        $data = [];
        MetricTimeseries::query()
            ->whereIn('metric_key', $keys)
            ->where('captured_at', '>=', $since)
            ->orderBy('captured_at')
            ->chunk(1000, function ($chunk) use (&$data) {
                foreach ($chunk as $row) {
                    $data[$row->metric_key][] = [
                        't' => $row->captured_at->toISOString(),
                        'v' => $row->value,
                    ];
                }
            });

        return response()->json([
            'range' => $range,
            'since' => $since->toISOString(),
            'data' => $data,
        ]);
    }

    public function interfaces(Request $request): JsonResponse
    {
        $this->authorize('viewAdminMonitoring');

        $range = $request->get('range', '1h');
        $since = match ($range) {
            '1h' => now()->subHour(),
            '6h' => now()->subHours(6),
            '24h' => now()->subDay(),
            '7d' => now()->subDays(7),
            default => now()->subHours(2),
        };

        $pattern = 'mikrotik.if.%';
        $rows = MetricTimeseries::query()
            ->where('metric_key', 'like', $pattern)
            ->where('captured_at', '>=', $since)
            ->orderBy('captured_at')
            ->get()
            ->groupBy('metric_key')
            ->map(fn($group) => $group->map(fn($r) => [
                't' => $r->captured_at->toISOString(),
                'v' => $r->value,
            ])->values());

        return response()->json([
            'range' => $range,
            'since' => $since->toISOString(),
            'data' => $rows,
        ]);
    }

    public function interfacesLive(MikrotikMetricsService $metrics): JsonResponse
    {
        return response()->json([
            'success' => true,
            'updated_at' => now()->toISOString(),
            'data' => $metrics->getCachedInterfaces(autoRefresh: true),
        ]);
    }
}