@section('page_title', 'Orders')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Orders</li>
@endsection

<div>
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
            <h3 class="card-title mb-2 mb-sm-0">
                <i class="fas fa-shopping-cart mr-1"></i>
                All Orders
            </h3>
            <div class="btn-toolbar mb-2 mb-sm-0">
                <div class="btn-group mr-2">
                    <button wire:click="exportCsv" class="btn btn-outline-info btn-sm">
                        <i class="fas fa-file-csv"></i> CSV
                    </button>
                    <button wire:click="exportExcel" class="btn btn-outline-success btn-sm">
                        <i class="fas fa-file-excel"></i> Excel
                    </button>
                </div>
                <livewire:admin.orders.create-order :key="'create-order-component'"/>
            </div>
        </div>
        <div class="card-body">

            <div class="row mb-2">
                <div class="col-md-3">
                    <select wire:model.live="statusFilter" class="form-control form-control-sm">
                        <option value="">All Statuses</option>
                        <option value="pending">Pending</option>
                        <option value="payment_received">Payment Received</option>
                        <option value="processing">Processing</option>
                        <option value="completed">Completed</option>
                        <option value="cancelled">Cancelled</option>
                        <option value="expired">Expired</option>
                    </select>
                </div>
            </div>

            @include('livewire.shared.data-table', [
                'data' => $data,
                'columns' => $columns,
                'searchPlaceholder' => $searchPlaceholder,
            ])
        </div>
    </div>
</div>