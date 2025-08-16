<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Domain\Alerting\DTO\AlertMessage;
use App\Domain\Incidents\Services\IncidentService;

class OnAlertMessageDispatched
{
    public function __construct(
        private IncidentService $incidentService
    ) {}

    public function handle(AlertMessage $alert): void
    {
        $this->incidentService->autoOpenFromAlert($alert);
    }
}