<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Domain\Monitoring\Services\MetricsService;
use Illuminate\Console\Command;

class MonitoringPrintMetricsCommand extends Command
{
    protected $signature = 'monitoring:print-metrics';

    protected $description = 'Print system metrics in JSON format';

    public function handle(MetricsService $metricsService): int
    {
        $this->info('Collecting system metrics...');
        
        try {
            $metrics = [
                'global' => $metricsService->global(),
                'system' => $metricsService->system(),
                'interfaces' => $metricsService->interfacesLoad(),
                'timestamp' => now()->toISOString()
            ];
            
            $this->line(json_encode($metrics, JSON_PRETTY_PRINT));
            
            return Command::SUCCESS;
            
        } catch (\Exception $e) {
            $this->error('Failed to collect metrics: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}