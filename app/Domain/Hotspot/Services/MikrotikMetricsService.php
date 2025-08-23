<?php

declare(strict_types=1);

namespace App\Domain\Hotspot\Services;

use Illuminate\Support\Facades\Cache;

/**
 * Calcule les débits interfaces préférentiellement via delta rx-byte/tx-byte
 * sinon fallback sur monitor-traffic.
 *
 * Cache:
 *  - Snapshot brut: mikrotik:if:last_raw
 *  - Résultat métriques: mikrotik:if:metrics
 */
class MikrotikMetricsService
{
    public function __construct(private readonly MikrotikApiService $api) {}

    /**
     * Rafraîchit le cache des métriques. Retourne les données calculées.
     *
     * @return array<int,array<string,mixed>>
     */
    public function refreshCached(): array
    {
        $raw = $this->api->listInterfacesRaw();
        $now = microtime(true);

        $previous = Cache::get('mikrotik:if:last_raw', []);
        $intervalSeconds = config('mikrotik.interfaces_poll_interval_seconds', 10);

        $result = [];
        $newSnapshot = [];

        foreach ($raw as $row) {
            $name = $row['name'] ?? null;
            if (!$name) {
                continue;
            }
            $running = ($row['running'] ?? 'false') === 'true';
            $type = $row['type'] ?? null;

            $rxKbps = null;
            $txKbps = null;
            $source = 'none';

            $rxBytes = $row['rx-byte'] ?? $row['rx-bytes'] ?? null;
            $txBytes = $row['tx-byte'] ?? $row['tx-bytes'] ?? null;

            if ($running && is_numeric($rxBytes) && is_numeric($txBytes) && isset($previous[$name])) {
                $dt = $now - ($previous[$name]['ts'] ?? $now);
                if ($dt > 0 && $dt <= ($intervalSeconds * 5)) {
                    $drx = (int)$rxBytes - (int)$previous[$name]['rx'];
                    $dtx = (int)$txBytes - (int)$previous[$name]['tx'];
                    if ($drx >= 0) {
                        $rxKbps = round(($drx * 8) / 1000 / $dt, 1);
                    }
                    if ($dtx >= 0) {
                        $txKbps = round(($dtx * 8) / 1000 / $dt, 1);
                    }
                    $source = 'bytes';
                }
            }

            if ($running && ($rxKbps === null || $txKbps === null)) {
                // Fallback monitor uniquement si bytes non exploitables
                [$rxMon, $txMon, $src] = $this->api->monitorInterfaceTraffic($name);
                if ($rxKbps === null) {
                    $rxKbps = $rxMon;
                }
                if ($txKbps === null) {
                    $txKbps = $txMon;
                }
                if ($source !== 'bytes') {
                    $source = $src;
                }
            }

            $result[] = [
                'name'     => $name,
                'type'     => $type,
                'running'  => $running ? 'true' : 'false',
                'rx_kbps'  => $rxKbps,
                'tx_kbps'  => $txKbps,
                'source'   => $source,
                'ts'       => $now,
            ];

            // Snapshot pour prochain delta
            $newSnapshot[$name] = [
                'rx' => is_numeric($rxBytes) ? (int)$rxBytes : 0,
                'tx' => is_numeric($txBytes) ? (int)$txBytes : 0,
                'ts' => $now,
            ];
        }

        Cache::put('mikrotik:if:last_raw', $newSnapshot, 3600);
        Cache::put('mikrotik:if:metrics', [
            'updated_at' => $now,
            'data'       => $result,
        ], (int) max($intervalSeconds * 2, 30));

        return $result;
    }

    /**
     * Récupère les métriques mises en cache (optionnellement rafraîchit si périmé).
     */
    public function getCachedInterfaces(bool $autoRefresh = true): array
    {
        $interval = (int) config('mikrotik.interfaces_poll_interval_seconds', 10);
        $payload = Cache::get('mikrotik:if:metrics');

        if (!$payload && $autoRefresh) {
            return $this->refreshCached();
        }

        if ($payload) {
            $age = microtime(true) - ($payload['updated_at'] ?? 0);
            if ($autoRefresh && $age > ($interval * 2)) {
                return $this->refreshCached();
            }
            return $payload['data'] ?? [];
        }

        return [];
    }
}