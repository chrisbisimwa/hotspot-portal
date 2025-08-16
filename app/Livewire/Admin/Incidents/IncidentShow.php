<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Incidents;

use App\Domain\Incidents\Services\IncidentService;
use App\Enums\IncidentStatus;
use App\Models\Incident;
use Livewire\Component;

class IncidentShow extends Component
{
    public Incident $incident;
    public string $newUpdate = '';
    public string $selectedStatus = '';

    private IncidentService $incidentService;

    public function boot(IncidentService $incidentService): void
    {
        $this->incidentService = $incidentService;
    }

    public function mount(Incident $incident): void
    {
        abort_unless(auth()->user()->can('view', $incident), 403);
        
        $this->incident = $incident;
        $this->selectedStatus = $incident->status->value;
    }

    public function addUpdate(): void
    {
        $this->validate([
            'newUpdate' => 'required|string|min:5|max:1000',
        ]);

        $this->incidentService->addUpdate(
            $this->incident,
            $this->newUpdate,
            auth()->user()
        );

        $this->newUpdate = '';
        $this->incident->refresh();
        $this->incident->load('updates.user');

        session()->flash('message', 'Update added successfully.');
    }

    public function updateStatus(): void
    {
        $this->validate([
            'selectedStatus' => 'required|in:' . implode(',', array_column(IncidentStatus::cases(), 'value')),
        ]);

        $newStatus = IncidentStatus::from($this->selectedStatus);
        
        $success = $this->incidentService->transition(
            $this->incident,
            $newStatus,
            null,
            auth()->user()
        );

        if ($success) {
            $this->incident->refresh();
            $this->incident->load('updates.user');
            session()->flash('message', 'Incident status updated successfully.');
        } else {
            session()->flash('error', 'No status change was made.');
        }
    }

    public function getStatusOptionsProperty(): array
    {
        return collect(IncidentStatus::cases())
            ->mapWithKeys(fn($status) => [$status->value => $status->label()])
            ->toArray();
    }

    public function render()
    {
        $this->incident->load(['updates.user', 'creator', 'updater']);
        
        return view('livewire.admin.incidents.incident-show', [
            'statusOptions' => $this->statusOptions,
        ])->layout('layouts.admin');
    }
}