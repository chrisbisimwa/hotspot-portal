<?php

declare(strict_types=1);

namespace App\Livewire\Admin\HotspotUsers;

use App\Livewire\Shared\DataTable;
use App\Models\HotspotUser;
use Illuminate\Database\Eloquent\Builder;

class ListHotspotUsers extends DataTable
{
    public string $statusFilter = ''; // '', active, expired, suspended, etc.
    public string $profileFilter = '';
    public ?int $ownerFilter = null;

    protected $queryString = [
        'search'        => ['except' => ''],
        'sortField'     => ['except' => 'created_at'],
        'sortDirection' => ['except' => 'desc'],
        'perPage'       => ['except' => 15],
        'statusFilter'  => ['except' => ''],
        'profileFilter' => ['except' => ''],
        'ownerFilter'   => ['except' => null],
    ];

    protected $listeners = [
        'hotspot-user-created' => '$refresh',
        'hotspot-user-updated' => '$refresh',
        'hotspot-user-password-reset' => '$refresh',
    ];

    public function mount(...$params): void
    {
        abort_unless(auth()->user()?->hasRole('admin'), 403);

        parent::mount(
            columns: [
                ['field' => 'username',                'label' => 'Username',  'sortable' => true],
                ['field' => 'owner.name',              'label' => 'Owner',     'sortable' => true],
                ['field' => 'userProfile.name',        'label' => 'Profile',   'sortable' => true],
                ['field' => 'status',                  'label' => 'Status',    'sortable' => true, 'type' => 'status', 'domain' => 'hotspot_users'],
                ['field' => 'validity_minutes',        'label' => 'Validity',  'sortable' => true, 'type' => 'custom_validity'],
                ['field' => 'data_limit_mb',           'label' => 'Quota',     'sortable' => true, 'type' => 'custom_quota'],
                ['field' => 'expired_at',              'label' => 'Expires',   'sortable' => true, 'type' => 'date'],
                ['field' => 'created_at',              'label' => 'Created',   'sortable' => true, 'type' => 'date'],
                ['type'  => 'actions',                 'label' => 'Actions',   'sortable' => false,
                    'actions_view' => 'livewire.admin.hotspot-users.partials.actions'],
            ],
            sortField: 'created_at',
            sortDirection: 'desc',
            perPage: 15,
            searchPlaceholder: 'Search username / owner...'
        );

        $this->filters['statusFilter']  = &$this->statusFilter;
        $this->filters['profileFilter'] = &$this->profileFilter;
        $this->filters['ownerFilter']   = &$this->ownerFilter;
    }

    protected function getQuery(): Builder
    {
        return HotspotUser::query()
            ->with(['owner:id,name,email,phone','userProfile:id,name']);
    }

    protected function applySearch(Builder $query): void
    {
        if ($this->search === '') {
            return;
        }

        $s = $this->search;
        $query->where(function ($q) use ($s) {
            $q->where('username', 'like', "%$s%")
              ->orWhereHas('owner', function ($qo) use ($s) {
                  $qo->where('name','like',"%$s%")
                     ->orWhere('email','like',"%$s%")
                     ->orWhere('phone','like',"%$s%");
              })
              ->orWhereHas('userProfile', function ($qp) use ($s) {
                  $qp->where('name','like',"%$s%");
              });
        });
    }

    protected function applyFilter(Builder $query, string $filter, $value): void
    {
        if ($filter === 'statusFilter' && $value !== '') {
            $query->where('status', $value);
        }

        if ($filter === 'profileFilter' && $value !== '') {
            $query->whereHas('userProfile', fn($p) => $p->where('id', $value)->orWhere('name', $value));
        }

        if ($filter === 'ownerFilter' && $value) {
            $query->where('owner_id', (int) $value);
        }
    }

    public function render()
    {
        return view('livewire.admin.hotspot-users.list-hotspot-users', [
            'data' => $this->getData(),
        ])->layout('layouts.admin');
    }
}