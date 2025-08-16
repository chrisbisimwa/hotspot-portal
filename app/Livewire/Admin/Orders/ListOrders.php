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
        'search' => ['except' => ''],
        'sortField' => ['except' => 'id'],
        'sortDirection' => ['except' => 'desc'],
        'perPage' => ['except' => 15],
        'statusFilter' => ['except' => ''],
    ];

    public function mount(): void
    {
        // Check authorization
        abort_unless(auth()->user()->hasRole('admin'), 403, 'Unauthorized access to admin orders');

        parent::mount(
            columns: [
                ['field' => 'id', 'label' => 'ID', 'sortable' => true],
                ['field' => 'user.name', 'label' => 'User', 'sortable' => true],
                ['field' => 'userProfile.name', 'label' => 'Profile', 'sortable' => false],
                ['field' => 'quantity', 'label' => 'Quantity', 'sortable' => true],
                ['field' => 'total_amount', 'label' => 'Amount', 'sortable' => true, 'type' => 'currency'],
                ['field' => 'status', 'label' => 'Status', 'sortable' => true, 'type' => 'status', 'domain' => 'orders'],
                ['field' => 'created_at', 'label' => 'Created', 'sortable' => true, 'type' => 'date'],
            ],
            sortField: 'created_at',
            sortDirection: 'desc',
            searchPlaceholder: 'Search orders...'
        );
    }

    protected function getQuery(): Builder
    {
        return Order::with(['user', 'userProfile']);
    }

    protected function applySearch(Builder $query): void
    {
        $query->where(function ($q) {
            $q->where('id', 'like', '%' . $this->search . '%')
              ->orWhereHas('user', function ($userQuery) {
                  $userQuery->where('name', 'like', '%' . $this->search . '%')
                           ->orWhere('email', 'like', '%' . $this->search . '%');
              })
              ->orWhereHas('userProfile', function ($profileQuery) {
                  $profileQuery->where('name', 'like', '%' . $this->search . '%');
              });
        });
    }

    protected function applyFilter(Builder $query, string $filter, $value): void
    {
        if ($filter === 'statusFilter' && !empty($value)) {
            $query->where('status', $value);
        }
    }

    public function render()
    {
        return view('livewire.admin.orders.list-orders')
            ->layout('layouts.admin');
    }
}