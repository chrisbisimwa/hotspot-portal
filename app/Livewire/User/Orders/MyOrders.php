<?php

declare(strict_types=1);

namespace App\Livewire\User\Orders;

use App\Livewire\Shared\DataTable;
use App\Models\Order;
use Illuminate\Database\Eloquent\Builder;

class MyOrders extends DataTable
{
    public string $statusFilter = '';

    protected $queryString = [
        'search'        => ['except' => ''],
        'sortField'     => ['except' => 'created_at'],
        'sortDirection' => ['except' => 'desc'],
        'perPage'       => ['except' => 15],
        'statusFilter'  => ['except' => ''],
    ];

    public function mount(
        array $columns = [],
        string $sortField = 'created_at',
        string $sortDirection = 'desc',
        int $perPage = 15,
        string $searchPlaceholder = 'Search my orders...'
    ): void {
        abort_unless(auth()->check(), 403);

        $defined = [
            ['field' => 'id',               'label' => 'Order #', 'sortable' => true],
            ['field' => 'userProfile.name', 'label' => 'Profile', 'sortable' => false],
            ['field' => 'quantity',         'label' => 'Quantity','sortable' => true],
            ['field' => 'total_amount',     'label' => 'Amount',  'sortable' => true, 'type' => 'currency'],
            ['field' => 'status',           'label' => 'Status',  'sortable' => true, 'type' => 'status', 'domain' => 'orders'],
            ['field' => 'created_at',       'label' => 'Date',    'sortable' => true, 'type' => 'date'],
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
        return Order::with(['userProfile'])
            ->where('user_id', auth()->id());
    }

    protected function applySearch(Builder $query): void
    {
        if ($this->search === '') {
            return;
        }
        $query->where(function ($q) {
            $q->where('id', 'like', "%{$this->search}%")
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
        return view('livewire.user.orders.my-orders', [
            'data' => $this->getData(),
        ])->layout('layouts.user');
    }
}