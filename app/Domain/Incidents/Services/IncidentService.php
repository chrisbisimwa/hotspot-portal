<?php

declare(strict_types=1);

namespace App\Domain\Incidents\Services;

use App\Domain\Alerting\DTO\AlertMessage;
use App\Enums\IncidentSeverity;
use App\Enums\IncidentStatus;
use App\Events\IncidentStatusChanged;
use App\Models\Incident;
use App\Models\IncidentUpdate;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class IncidentService
{
    public function open(
        string $code,
        string $title,
        IncidentSeverity $severity,
        array $context = [],
        ?string $detectionSource = null
    ): Incident {
        $incident = Incident::create([
            'title' => $title,
            'status' => IncidentStatus::OPEN,
            'severity' => $severity,
            'started_at' => now(),
            'detected_at' => now(),
            'detection_source' => $detectionSource,
            'summary' => $this->generateSummaryFromContext($context),
            'meta' => [
                'code' => $code,
                'context' => $context,
            ],
        ]);

        $this->addUpdate(
            $incident,
            "Incident opened automatically. Severity: {$severity->label()}",
            null
        );

        Log::info('Incident opened', [
            'incident_id' => $incident->id,
            'code' => $code,
            'severity' => $severity->value,
            'title' => $title,
        ]);

        return $incident;
    }

    public function transition(
        Incident $incident,
        IncidentStatus $newStatus,
        ?array $meta = null,
        ?User $user = null
    ): bool {
        $oldStatus = $incident->status;

        if ($oldStatus === $newStatus) {
            return false; // No change needed
        }

        $updates = ['status' => $newStatus];

        // Set timestamps based on status
        match ($newStatus) {
            IncidentStatus::MONITORING => $updates['detected_at'] = now(),
            IncidentStatus::MITIGATED => $updates['mitigated_at'] = now(),
            IncidentStatus::RESOLVED, IncidentStatus::FALSE_POSITIVE => [
                $updates['resolved_at'] = now(),
                $updates['closed_at'] = now(),
            ],
            default => null,
        };

        if ($meta) {
            $existingMeta = $incident->meta ?? [];
            $updates['meta'] = array_merge($existingMeta, $meta);
        }

        if ($user) {
            $updates['updated_by'] = $user->id;
        }

        $incident->update($updates);

        // Add update entry
        $this->addUpdate(
            $incident,
            "Status changed from {$oldStatus->label()} to {$newStatus->label()}",
            $user
        );

        // Fire event for webhooks and other listeners
        IncidentStatusChanged::dispatch($incident, $oldStatus, $newStatus);

        Log::info('Incident status changed', [
            'incident_id' => $incident->id,
            'from_status' => $oldStatus->value,
            'to_status' => $newStatus->value,
            'user_id' => $user?->id,
        ]);

        return true;
    }

    public function addUpdate(Incident $incident, string $message, ?User $user = null): IncidentUpdate
    {
        $update = IncidentUpdate::create([
            'incident_id' => $incident->id,
            'user_id' => $user?->id,
            'message' => $message,
            'created_at' => now(),
        ]);

        Log::debug('Incident update added', [
            'incident_id' => $incident->id,
            'update_id' => $update->id,
            'user_id' => $user?->id,
        ]);

        return $update;
    }

    public function autoOpenFromAlert(AlertMessage $alert): ?Incident
    {
        // Check if there's already an open incident for this alert code in the last 30 minutes
        $existingIncident = Incident::where('meta->code', $alert->code)
            ->whereIn('status', [IncidentStatus::OPEN, IncidentStatus::MONITORING])
            ->where('created_at', '>=', now()->subMinutes(30))
            ->first();

        if ($existingIncident) {
            Log::debug('Incident already exists for alert code, skipping auto-creation', [
                'alert_code' => $alert->code,
                'existing_incident_id' => $existingIncident->id,
            ]);

            // Add an update to the existing incident
            $this->addUpdate(
                $existingIncident,
                "Related alert triggered again: {$alert->body}",
                null
            );

            return null;
        }

        // Only auto-create incidents for HIGH and CRITICAL alerts
        if ($alert->severity->priority() < IncidentSeverity::HIGH->priority()) {
            Log::debug('Alert severity too low for auto-incident creation', [
                'alert_code' => $alert->code,
                'severity' => $alert->severity->value,
            ]);
            return null;
        }

        $incident = $this->open(
            code: $alert->code,
            title: $alert->title,
            severity: $alert->severity,
            context: $alert->context,
            detectionSource: "alert:{$alert->code}"
        );

        Log::info('Incident auto-created from alert', [
            'incident_id' => $incident->id,
            'alert_code' => $alert->code,
            'severity' => $alert->severity->value,
        ]);

        return $incident;
    }

    private function generateSummaryFromContext(array $context): ?string
    {
        if (empty($context)) {
            return null;
        }

        $summary = [];
        foreach ($context as $key => $value) {
            if (is_scalar($value)) {
                $summary[] = "{$key}: {$value}";
            }
        }

        return implode(', ', $summary);
    }
}