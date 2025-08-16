<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Incidents;

use App\Enums\IncidentSeverity;
use App\Enums\IncidentStatus;
use App\Models\Incident;
use Livewire\Component;
use Livewire\WithPagination;

class IncidentsList extends Component
{
    use WithPagination;

    public string $search = '';
    public string $statusFilter = '';
    public string $severityFilter = '';
    public string $sortBy = 'created_at';
    public string $sortDirection = 'desc';

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => ''],
        'severityFilter' => ['except' => ''],
        'sortBy' => ['except' => 'created_at'],
        'sortDirection' => ['except' => 'desc'],
    ];

    public function mount(): void
    {
        abort_unless(auth()->user()->can('viewAny', Incident::class), 403);
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatingSeverityFilter(): void
    {
        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->statusFilter = '';
        $this->severityFilter = '';
        $this->resetPage();
    }

    public function getIncidentsProperty()
    {
        $query = Incident::with(['creator', 'updater'])
            ->when($this->search, function ($query) {
                return $query->where(function ($q) {
                    $q->where('title', 'like', '%' . $this->search . '%')
                      ->orWhere('slug', 'like', '%' . $this->search . '%')
                      ->orWhere('detection_source', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->statusFilter, function ($query) {
                return $query->where('status', $this->statusFilter);
            })
            ->when($this->severityFilter, function ($query) {
                return $query->where('severity', $this->severityFilter);
            })
            ->orderBy($this->sortBy, $this->sortDirection);

        return $query->paginate(20);
    }

    public function getStatusOptionsProperty(): array
    {
        return collect(IncidentStatus::cases())
            ->mapWithKeys(fn($status) => [$status->value => $status->label()])
            ->toArray();
    }

    public function getSeverityOptionsProperty(): array
    {
        return collect(IncidentSeverity::cases())
            ->mapWithKeys(fn($severity) => [$severity->value => $severity->label()])
            ->toArray();
    }

    public function render()
    {
        return view('livewire.admin.incidents.incidents-list', [
            'incidents' => $this->incidents,
            'statusOptions' => $this->statusOptions,
            'severityOptions' => $this->severityOptions,
        ])->layout('layouts.admin');
    }
}