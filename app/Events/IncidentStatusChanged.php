<?php

declare(strict_types=1);

namespace App\Events;

use App\Enums\IncidentStatus;
use App\Models\Incident;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class IncidentStatusChanged
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Incident $incident,
        public IncidentStatus $fromStatus,
        public IncidentStatus $toStatus
    ) {}

    public function toWebhookPayload(): array
    {
        return [
            'incident' => [
                'id' => $this->incident->id,
                'slug' => $this->incident->slug,
                'title' => $this->incident->title,
                'status' => $this->toStatus->value,
                'severity' => $this->incident->severity->value,
                'started_at' => $this->incident->started_at->toISOString(),
                'meta' => $this->incident->meta,
            ],
            'status_change' => [
                'from' => $this->fromStatus->value,
                'to' => $this->toStatus->value,
                'changed_at' => now()->toISOString(),
            ],
        ];
    }
}