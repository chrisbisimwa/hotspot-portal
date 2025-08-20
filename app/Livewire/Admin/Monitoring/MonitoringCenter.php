<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Monitoring;

use App\Domain\Monitoring\Services\MetricsService;
use Livewire\Component;

class MonitoringCenter extends Component
{
    public string $tab = 'overview';
    public string $timeseriesRange = '24h';
    public bool $autoRefresh = true;
    public int $refreshIntervalMs = 30000;

    public array $global = [];
    public array $system = [];
    public array $interfaces = [];

    private MetricsService $metricsService;

    public function boot(MetricsService $metricsService): void
    {
        $this->metricsService = $metricsService;
    }

    public function mount(): void
    {
        abort_unless(auth()->user()?->hasRole('admin'), 403);
        $this->loadData();
    }

    public function updatedTimeseriesRange(): void
    {
        $this->dispatch('monitoring-range-changed', range: $this->timeseriesRange);
    }

    public function toggleAuto(): void
    {
        $this->autoRefresh = !$this->autoRefresh;
    }

    public function switchTab(string $tab): void
    {
        $this->tab = $tab;
        if ($tab === 'mikrotik') {
            $this->interfaces = $this->metricsService->interfacesLoad();
        }
    }

    public function refreshNow(): void
    {
        $this->loadData();
        $this->dispatch('monitoring-refreshed');
    }

    private function loadData(): void
    {
        $this->global = $this->metricsService->global();
        $this->system = $this->metricsService->system();
        if ($this->tab === 'mikrotik') {
            $this->interfaces = $this->metricsService->interfacesLoad();
        }
    }

    public function render()
    {
        return view('livewire.admin.monitoring.monitoring-center')
            ->layout('layouts.admin');
    }
}