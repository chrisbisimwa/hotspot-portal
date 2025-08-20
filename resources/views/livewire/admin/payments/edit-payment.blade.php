@section('page_title', 'Edit Payment #'.$payment->id)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.payments.index') }}">Payments</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.payments.show', $payment->id) }}">#{{ $payment->id }}</a></li>
    <li class="breadcrumb-item active">Edit</li>
@endsection

<div>
    @if(session('success'))
        <div class="alert alert-success py-2">{{ session('success') }}</div>
    @endif

    <form wire:submit.prevent="save">
        <div class="card">
            <div class="card-header"><strong>Payment Data</strong></div>
            <div class="card-body">
                <div class="form-row">
                    <div class="form-group col-md-3">
                        <label>Status *</label>
                        <select wire:model.live="status" class="form-control form-control-sm">
                            @foreach($availableStatuses as $s)
                                <option value="{{ $s }}">{{ ucfirst($s) }}</option>
                            @endforeach
                        </select>
                        @error('status') <small class="text-danger">{{ $message }}</small>@enderror
                    </div>
                    <div class="form-group col-md-3">
                        <label>Provider *</label>
                        <select wire:model.live="provider" class="form-control form-control-sm">
                            @foreach($availableProviders as $p)
                                <option value="{{ $p }}">{{ ucfirst($p) }}</option>
                            @endforeach
                        </select>
                        @error('provider') <small class="text-danger">{{ $message }}</small>@enderror
                    </div>
                    <div class="form-group col-md-3">
                        <label>Transaction Ref</label>
                        <input type="text" class="form-control form-control-sm" wire:model.live="transaction_ref">
                        @error('transaction_ref') <small class="text-danger">{{ $message }}</small>@enderror
                    </div>
                    <div class="form-group col-md-3">
                        <label>Internal Ref</label>
                        <input type="text" class="form-control form-control-sm" wire:model.live="internal_ref">
                        @error('internal_ref') <small class="text-danger">{{ $message }}</small>@enderror
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-2">
                        <label>Fee</label>
                        <input type="number" step="0.01" class="form-control form-control-sm" wire:model.live="fee_amount">
                        @error('fee_amount') <small class="text-danger">{{ $message }}</small>@enderror
                    </div>
                    <div class="form-group col-md-2">
                        <label>Net</label>
                        <input type="number" step="0.01" class="form-control form-control-sm" wire:model.live="net_amount">
                        @error('net_amount') <small class="text-danger">{{ $message }}</small>@enderror
                    </div>
                    <div class="form-group col-md-2">
                        <label>Paid At</label>
                        <input type="text" class="form-control form-control-sm" placeholder="YYYY-MM-DD HH:MM" wire:model.live="paid_at">
                        @error('paid_at') <small class="text-danger">{{ $message }}</small>@enderror
                    </div>
                    <div class="form-group col-md-2">
                        <label>Confirmed At</label>
                        <input type="text" class="form-control form-control-sm" wire:model.live="confirmed_at">
                        @error('confirmed_at') <small class="text-danger">{{ $message }}</small>@enderror
                    </div>
                    <div class="form-group col-md-2">
                        <label>Refunded At</label>
                        <input type="text" class="form-control form-control-sm" wire:model.live="refunded_at">
                        @error('refunded_at') <small class="text-danger">{{ $message }}</small>@enderror
                    </div>
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