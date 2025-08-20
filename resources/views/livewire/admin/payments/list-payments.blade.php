@section('page_title','Payments')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Payments</li>
@endsection

<div>
    <div class="card mb-3">
        <div class="card-header d-flex justify-content-between flex-wrap align-items-center">
            <h3 class="card-title mb-2 mb-sm-0">
                <i class="fas fa-credit-card mr-1"></i> Payments
            </h3>
            <div class="btn-group btn-group-sm">
                <button wire:click="reconcileAll" class="btn btn-outline-primary">
                    <i class="fas fa-sync"></i> Reconcile
                </button>
                <button wire:click="clearFilters" class="btn btn-outline-secondary">
                    <i class="fas fa-filter"></i> Reset
                </button>
            </div>
        </div>
        <div class="card-body pb-2">
            @if(session('success'))
                <div class="alert alert-success py-2">{{ session('success') }}</div>
            @endif
            <div class="row text-center">
                <div class="col-6 col-md-3 mb-2">
                    <div class="border rounded p-2">
                        <div class="text-muted" style="font-size:11px;">Count</div>
                        <div style="font-size:18px;font-weight:600;">{{ $kpiCount }}</div>
                    </div>
                </div>
                <div class="col-6 col-md-3 mb-2">
                    <div class="border rounded p-2">
                        <div class="text-muted" style="font-size:11px;">Gross (Amount)</div>
                        <div style="font-size:18px;font-weight:600;">{{ number_format($kpiAmount,2) }}</div>
                    </div>
                </div>
                <div class="col-6 col-md-3 mb-2">
                    <div class="border rounded p-2">
                        <div class="text-muted" style="font-size:11px;">Net</div>
                        <div style="font-size:18px;font-weight:600;">{{ number_format($kpiNet,2) }}</div>
                    </div>
                </div>
                <div class="col-6 col-md-3 mb-2">
                    <div class="border rounded p-2">
                        <div class="text-muted" style="font-size:11px;">Success Rate</div>
                        <div style="font-size:18px;font-weight:600;">
                            {{ $kpiSuccessRate !== null ? ($kpiSuccessRate.'%') : '—' }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">

        <div class="card-body pb-0">
            <div class="row mb-3">
                <div class="col-md-2 mb-2">
                    <select wire:model.live="statusFilter" class="form-control form-control-sm">
                        <option value="">Status: All</option>
                        <option value="pending">Pending</option>
                        <option value="initiated">Initiated</option>
                        <option value="processing">Processing</option>
                        <option value="success">Success</option>
                        <option value="failed">Failed</option>
                        <option value="cancelled">Cancelled</option>
                        <option value="refunded">Refunded</option>
                    </select>
                </div>
                <div class="col-md-2 mb-2">
                    <select wire:model.live="providerFilter" class="form-control form-control-sm">
                        <option value="">Provider: All</option>
                        @foreach($providers as $pr)
                            <option value="{{ $pr }}">{{ ucfirst($pr) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 mb-2">
                    <input type="date" class="form-control form-control-sm" wire:model.live="dateFrom">
                </div>
                <div class="col-md-2 mb-2">
                    <input type="date" class="form-control form-control-sm" wire:model.live="dateTo">
                </div>
                <div class="col-md-2 mb-2">
                    <input type="text" class="form-control form-control-sm" wire:model.debounce.500ms="search" placeholder="{{ $searchPlaceholder }}">
                </div>
                <div class="col-md-1 mb-2">
                    <input type="number" step="0.01" class="form-control form-control-sm" wire:model.live="amountMin" placeholder="Min">
                </div>
                <div class="col-md-1 mb-2">
                    <input type="number" step="0.01" class="form-control form-control-sm" wire:model.live="amountMax" placeholder="Max">
                </div>
            </div>
        </div>

        <div class="card-body pt-0">
            @include('livewire.shared.data-table', [
                'data' => $data,
                'columns' => $columns,
                'searchPlaceholder' => $searchPlaceholder,
            ])
        </div>
    </div>
</div>