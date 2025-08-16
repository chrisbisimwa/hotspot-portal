<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Domain\Monitoring\Services\MetricsService;
use Livewire\Component;

class Dashboard extends Component
{
    private MetricsService $metricsService;

    public function boot(MetricsService $metricsService): void
    {
        $this->metricsService = $metricsService;
    }

    public function mount(): void
    {
        // Check authorization
        abort_unless(auth()->user()->hasRole('admin'), 403, 'Unauthorized access to admin dashboard');
    }

    public function getMetricsProperty(): array
    {
        return $this->metricsService->global();
    }

    public function getSystemMetricsProperty(): array
    {
        return $this->metricsService->system();
    }

    public function render()
    {
        return view('livewire.admin.dashboard')
            ->layout('layouts.admin');
    }
}