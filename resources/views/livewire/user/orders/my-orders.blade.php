@section('page_title', 'My Orders')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">My Orders</li>
@endsection

<div>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-shopping-cart mr-1"></i>
                My Orders
            </h3>
        </div>
        <div class="card-body">
            <div class="mb-3" style="max-width:200px;">
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

            @include('livewire.shared.data-table', [
                'data' => $data,
                'columns' => $columns,
                'searchPlaceholder' => $searchPlaceholder,
            ])
        </div>
    </div>
</div>