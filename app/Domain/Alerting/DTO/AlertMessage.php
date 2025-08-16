<?php

declare(strict_types=1);

namespace App\Domain\Alerting\DTO;

use App\Enums\IncidentSeverity;
use Carbon\Carbon;

readonly class AlertMessage
{
    public function __construct(
        public string $code,
        public string $title,
        public IncidentSeverity $severity,
        public string $body,
        public array $context = [],
        public ?Carbon $occurredAt = null,
    ) {
        $this->occurredAt ??= now();
    }

    public function toArray(): array
    {
        return [
            'code' => $this->code,
            'title' => $this->title,
            'severity' => $this->severity->value,
            'body' => $this->body,
            'context' => $this->context,
            'occurred_at' => $this->occurredAt->toISOString(),
        ];
    }
}