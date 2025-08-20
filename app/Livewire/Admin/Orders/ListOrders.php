<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Orders;

use App\Livewire\Shared\DataTable;
use App\Models\Order;
use Illuminate\Database\Eloquent\Builder;

class ListOrders extends DataTable
{
    public string $statusFilter = '';

    protected $queryString = [
        'search'        => ['except' => ''],
        'sortField'     => ['except' => 'created_at'],
        'sortDirection' => ['except' => 'desc'],
        'perPage'       => ['except' => 15],
        'statusFilter'  => ['except' => ''],
    ];
    protected $listeners = [
        'order-created' => '$refresh',
    ];


    public function mount(
        array $columns = [],
        string $sortField = 'created_at',
        string $sortDirection = 'desc',
        int $perPage = 15,
        string $searchPlaceholder = 'Search orders...'
    ): void {
        abort_unless(auth()->user()?->hasRole('admin'), 403);

        $defined = [
            ['field' => 'id',               'label' => 'ID',      'sortable' => true],
            ['field' => 'user.name',        'label' => 'User',    'sortable' => true],
            ['field' => 'userProfile.name', 'label' => 'Profile', 'sortable' => false],
            ['field' => 'quantity',         'label' => 'Quantity','sortable' => true],
            ['field' => 'total_amount',     'label' => 'Amount',  'sortable' => true, 'type' => 'currency'],
            ['field' => 'status',           'label' => 'Status',  'sortable' => true, 'type' => 'status', 'domain' => 'orders'],
            ['field' => 'created_at',       'label' => 'Created', 'sortable' => true, 'type' => 'date'],
            ['type'  => 'actions',          'label' => 'Actions', 'sortable' => false],
        ];

        parent::mount(
            columns: $defined,
            sortField: $sortField,
            sortDirection: $sortDirection,
            perPage: $perPage,
            searchPlaceholder: $searchPlaceholder
        );

        $this->filters['statusFilter'] = &$this->statusFilter;
    }

    protected function getQuery(): Builder
    {
        return Order::with(['user', 'userProfile']);
    }

    protected function applySearch(Builder $query): void
    {
        if ($this->search === '') {
            return;
        }
        $query->where(function ($q) {
            $q->where('id', 'like', "%{$this->search}%")
              ->orWhereHas('user', function ($uq) {
                  $uq->where('name', 'like', "%{$this->search}%")
                     ->orWhere('email', 'like', "%{$this->search}%");
              })
              ->orWhereHas('userProfile', function ($pq) {
                  $pq->where('name', 'like', "%{$this->search}%");
              });
        });
    }

    protected function applyFilter(Builder $query, string $filter, $value): void
    {
        if ($filter === 'statusFilter' && $value !== '') {
            $query->where('status', $value);
        }
    }

    public function render()
    {
        // On wrappe le tableau dans la carte admin
        return view('livewire.admin.orders.list-orders', [
            'data' => $this->getData(),
        ])->layout('layouts.admin');
    }
}