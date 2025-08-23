<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Domain\Hotspot\Services\MikrotikMetricsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Cache;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Bus\Dispatchable;

class RefreshMikrotikInterfacesMetricsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 1;

    public function handle(MikrotikMetricsService $metrics): void
    {
        $interval = (int) config('mikrotik.interfaces_poll_interval_seconds', 10);

        // Verrou simple pour éviter plusieurs chaînes simultanées.
        if (!Cache::add('mikrotik:if:poller:lock', 1, $interval + 5)) {
            // Un autre job est en train de tourner ou a déjà planifié la suite.
            return;
        }

        $metrics->refreshCached();

        // Re-dispatch (auto-chain)
        static::dispatch()->delay(now()->addSeconds($interval));
    }
}