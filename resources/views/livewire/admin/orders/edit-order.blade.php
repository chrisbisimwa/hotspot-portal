@section('page_title', 'Edit Order #'.$order->id)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}/orders">Orders</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.orders.show', $order->id) }}">Order #{{ $order->id }}</a></li>
    <li class="breadcrumb-item active">Edit</li>
@endsection

<div>
    <div class="d-flex justify-content-between mb-3">
        <h4 class="mb-0">
            <i class="fas fa-edit mr-2"></i> Edit Order #{{ $order->id }}
        </h4>
        <div class="btn-group btn-group-sm">
            <a href="{{ route('admin.orders.show', $order->id) }}" class="btn btn-light">
                <i class="fas fa-eye"></i> View
            </a>
            <a href="{{ url()->previous() }}" class="btn btn-light">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success py-2">{{ session('success') }}</div>
    @endif

    <form wire:submit.prevent="save">
        <div class="card mb-3">
            <div class="card-header">
                <strong>Order Data</strong>
            </div>
            <div class="card-body">

                <div class="form-row">
                    <div class="form-group col-md-3">
                        <label>Quantity</label>
                        <input type="number" class="form-control form-control-sm" min="1" wire:model.live="quantity">
                        @error('quantity') <small class="text-danger">{{ $message }}</small>@enderror
                    </div>
                    <div class="form-group col-md-3">
                        <label>Unit Price</label>
                        <input type="number" step="0.01" min="0" class="form-control form-control-sm" wire:model.live="unit_price">
                        @error('unit_price') <small class="text-danger">{{ $message }}</small>@enderror
                    </div>
                    <div class="form-group col-md-3">
                        <label>Total</label>
                        <input type="text" class="form-control form-control-sm" value="{{ number_format($quantity * $unit_price, 2) }}" readonly>
                    </div>
                    <div class="form-group col-md-3">
                        <label>Status</label>
                        <select class="form-control form-control-sm" wire:model.live="status">
                            @foreach($availableStatuses as $s)
                                <option value="{{ $s }}">{{ ucfirst(str_replace('_',' ',$s)) }}</option>
                            @endforeach
                        </select>
                        @error('status') <small class="text-danger">{{ $message }}</small>@enderror
                    </div>
                </div>

                <div class="form-group">
                    <label>User Profile ID</label>
                    <input type="number" class="form-control form-control-sm" wire:model.live="user_profile_id">
                    @error('user_profile_id') <small class="text-danger">{{ $message }}</small>@enderror
                    <small class="text-muted">Pour changer plus proprement, prévoir un select filtré par user si nécessaire.</small>
                </div>

                <div class="form-group">
                    <label>Payment Reference</label>
                    <input type="text" class="form-control form-control-sm" wire:model.live="payment_reference">
                    @error('payment_reference') <small class="text-danger">{{ $message }}</small>@enderror
                </div>

                <div class="alert alert-info py-2 small">
                    Timestamps mis à jour automatiquement si statut devient payment_received / completed / cancelled.
                </div>
            </div>
            <div class="card-footer text-right">
                <button class="btn btn-success btn-sm">
                    <i class="fas fa-save"></i> Save
                </button>
            </div>
        </div>
    </form>
</div>