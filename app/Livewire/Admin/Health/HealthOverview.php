<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Health;

use App\Domain\Monitoring\Services\MetricsService;
use App\Domain\Monitoring\Services\SlaRecorder;
use App\Enums\IncidentStatus;
use App\Enums\WebhookAttemptStatus;
use App\Models\Incident;
use App\Models\WebhookAttempt;
use Livewire\Component;

class HealthOverview extends Component
{
    public array $metrics = [];
    public array $slaMetrics = [];
    public int $openIncidents = 0;
    public int $pendingWebhooks = 0;
    public ?float $mikrotikPing = null;
    public ?float $paymentSuccessRate = null;
    public ?float $provisioningErrorRate = null;

    private MetricsService $metricsService;
    private SlaRecorder $slaRecorder;

    public function boot(MetricsService $metricsService, SlaRecorder $slaRecorder): void
    {
        $this->metricsService = $metricsService;
        $this->slaRecorder = $slaRecorder;
    }

    public function mount(): void
    {
        abort_unless(auth()->user()->hasRole('admin'), 403, 'Unauthorized access to health overview');
        
        $this->loadHealthData();
    }

    public function loadHealthData(): void
    {
        // Get basic metrics
        $this->metrics = $this->metricsService->global();
        
        // Get SLA metrics
        $this->loadSlaMetrics();
        
        // Get incidents count
        $this->openIncidents = Incident::whereIn('status', [
            IncidentStatus::OPEN,
            IncidentStatus::MONITORING,
        ])->count();
        
        // Get pending webhooks count
        $this->pendingWebhooks = WebhookAttempt::where('status', WebhookAttemptStatus::PENDING)
            ->count();
    }

    private function loadSlaMetrics(): void
    {
        // MikroTik ping status (last value)
        $this->mikrotikPing = $this->slaRecorder->getAverage('mikrotik.ping_ms', '5m');
        
        // Payment success rate (last 24 hours)
        $this->paymentSuccessRate = $this->calculatePaymentSuccessRate();
        
        // Provisioning error rate
        $this->provisioningErrorRate = $this->slaRecorder->getAverage('provisioning.error_rate', '1h');
    }

    private function calculatePaymentSuccessRate(): ?float
    {
        $successCount = $this->slaRecorder->getCount('payment.success', '24h');
        $failureCount = $this->slaRecorder->getCount('payment.failure', '24h');
        
        $total = $successCount + $failureCount;
        
        if ($total === 0) {
            return null;
        }
        
        return ($successCount / $total) * 100;
    }

    public function refresh(): void
    {
        $this->loadHealthData();
        $this->dispatch('$refresh');
    }

    public function getMikrotikStatusProperty(): string
    {
        if ($this->mikrotikPing === null) {
            return 'unknown';
        }
        
        if ($this->mikrotikPing > 1000) {
            return 'critical';
        }
        
        if ($this->mikrotikPing > 500) {
            return 'warning';
        }
        
        return 'healthy';
    }

    public function getPaymentStatusProperty(): string
    {
        if ($this->paymentSuccessRate === null) {
            return 'unknown';
        }
        
        if ($this->paymentSuccessRate < 60) {
            return 'critical';
        }
        
        if ($this->paymentSuccessRate < 80) {
            return 'warning';
        }
        
        return 'healthy';
    }

    public function getProvisioningStatusProperty(): string
    {
        if ($this->provisioningErrorRate === null) {
            return 'unknown';
        }
        
        if ($this->provisioningErrorRate > 0.2) { // 20%
            return 'critical';
        }
        
        if ($this->provisioningErrorRate > 0.1) { // 10%
            return 'warning';
        }
        
        return 'healthy';
    }

    public function render()
    {
        return view('livewire.admin.health.health-overview')
            ->layout('layouts.admin');
    }
}