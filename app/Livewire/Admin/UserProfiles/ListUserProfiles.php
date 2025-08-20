<?php

declare(strict_types=1);

namespace App\Livewire\Admin\UserProfiles;

use App\Livewire\Shared\DataTable;
use App\Models\UserProfile;
use Illuminate\Database\Eloquent\Builder;

class ListUserProfiles extends DataTable
{
    public string $publishedFilter = '';

    protected $queryString = [
        'search'        => ['except' => ''],
        'sortField'     => ['except' => 'created_at'],
        'sortDirection' => ['except' => 'desc'],
        'perPage'       => ['except' => 15],
        'statusFilter'  => ['except' => ''],
    ];

    protected $listeners = ['user-profile-created' => '$refresh'];

    public function mount(
        array $columns = [],
        string $sortField = 'created_at',
        string $sortDirection = 'desc',
        int $perPage = 15,
        string $searchPlaceholder = 'Search profiles...'
    ): void {
        abort_unless(auth()->user()?->hasRole('admin'), 403);

        $defined = [
            ['field' => 'id',               'label' => 'ID',      'sortable' => true],
            ['field' => 'name',             'label' => 'Name',    'sortable' => true],
            ['field' => 'mikrotik_profile', 'label' => 'Mikrotik Profile', 'sortable' => false],
            ['field' => 'price',            'label' => 'Price',   'sortable' => false],
            ['field' => 'validity_minutes', 'label' => 'Validity (min)', 'sortable' => true],
            ['field' => 'data_limit_mb',    'label' => 'Data Limit (MB)', 'sortable' => true],
            ['field' => 'is_active',        'label' => 'Active',   'sortable' => true, 'type' => 'inline_active'],
            ['field' => 'created_at',       'label' => 'Created', 'sortable' => true, 'type' => 'date'],
            ['type'  => 'actions',          'label' => 'Actions',  'sortable' => false, 'actions_view' => 'livewire.admin.user-profiles.partials.actions'],
        ];

        parent::mount(
            columns: $defined,
            sortField: $sortField,
            sortDirection: $sortDirection,
            perPage: $perPage,
            searchPlaceholder: $searchPlaceholder
        );

        $this->filters['publishedFilter'] = &$this->publishedFilter;
    }

    protected function getQuery(): Builder
    {
        return UserProfile::with(['hotspotUsers']);
    }

    protected function applySearch(Builder $query): void
    {
        if ($this->search === '') {
            return;
        }

        $query->where(function (Builder $q) {
            $q->where('name', 'like', '%' . $this->search . '%')
                ->orWhere('mikrotik_profile', 'like', '%' . $this->search . '%')
                ->orWhere('description', 'like', '%' . $this->search . '%');
        });
    }

    protected function applySort(Builder $query): void
    {
        if ($this->sortField && $this->sortDirection) {
            $query->orderBy($this->sortField, $this->sortDirection);
        }
    }

    public function render()
    {
        return view('livewire.admin.user-profiles.list-user-profiles', [
            'data' => $this->getData(),
        ])->layout('layouts.admin');
    }
}