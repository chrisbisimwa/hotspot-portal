@section('page_title', 'Orders')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Orders</li>
@endsection

<div>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-shopping-cart mr-1"></i>
                All Orders
            </h3>
            <div class="card-tools">
                <a href="#" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> New Order
                </a>
            </div>
        </div>
        <div class="card-body">
            <livewire:shared.data-table 
                :columns="$columns"
                sort-field="created_at"
                sort-direction="desc"
                search-placeholder="Search orders..."
                :key="'admin-orders-table'"
            >
                <x-slot name="filters">
                    <select wire:model.live="statusFilter" class="form-control form-control-sm">
                        <option value="">All Statuses</option>
                        <option value="pending">Pending</option>
                        <option value="payment_received">Payment Received</option>
                        <option value="processing">Processing</option>
                        <option value="completed">Completed</option>
                        <option value="cancelled">Cancelled</option>
                        <option value="expired">Expired</option>
                    </select>
                </x-slot>
            </livewire:shared.data-table>
        </div>
    </div>
</div>