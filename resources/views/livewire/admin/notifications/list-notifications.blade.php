@section('page_title','Notifications')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Notifications</li>
@endsection

<div>
    <div class="card mb-3">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
            <h3 class="card-title mb-2 mb-sm-0">
                <i class="fas fa-bell mr-1"></i> Notifications
            </h3>
            <div class="btn-group btn-group-sm">
                <button wire:click="dispatchPending" class="btn btn-outline-primary">
                    <i class="fas fa-paper-plane"></i> Dispatch Pending
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
                <div class="col-6 col-md-2 mb-2">
                    <div class="border rounded p-2">
                        <div class="text-muted" style="font-size:11px;">Total</div>
                        <div style="font-size:18px;font-weight:600;">{{ $kpiTotal }}</div>
                    </div>
                </div>
                <div class="col-6 col-md-2 mb-2">
                    <div class="border rounded p-2">
                        <div class="text-muted" style="font-size:11px;">Sent</div>
                        <div style="font-size:18px;font-weight:600;">{{ $kpiSent }}</div>
                    </div>
                </div>
                <div class="col-6 col-md-2 mb-2">
                    <div class="border rounded p-2">
                        <div class="text-muted" style="font-size:11px;">Failed</div>
                        <div style="font-size:18px;font-weight:600;">{{ $kpiFailed }}</div>
                    </div>
                </div>
                <div class="col-6 col-md-2 mb-2">
                    <div class="border rounded p-2">
                        <div class="text-muted" style="font-size:11px;">Pending</div>
                        <div style="font-size:18px;font-weight:600;">{{ $kpiPending }}</div>
                    </div>
                </div>
                <div class="col-6 col-md-2 mb-2">
                    <div class="border rounded p-2">
                        <div class="text-muted" style="font-size:11px;">Success Rate</div>
                        <div style="font-size:18px;font-weight:600;">
                            {{ $kpiSuccessRate !== null ? $kpiSuccessRate.'%' : '—' }}
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
                        <option value="queued">Queued</option>
                        <option value="retrying">Retrying</option>
                        <option value="sent">Sent</option>
                        <option value="failed">Failed</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
                <div class="col-md-2 mb-2">
                    <select wire:model.live="channelFilter" class="form-control form-control-sm">
                        <option value="">Channel: All</option>
                        @foreach($channels as $ch)
                            <option value="{{ $ch }}">{{ strtoupper($ch) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 mb-2">
                    <input type="date" wire:model.live="dateFrom" class="form-control form-control-sm">
                </div>
                <div class="col-md-2 mb-2">
                    <input type="date" wire:model.live="dateTo" class="form-control form-control-sm">
                </div>
                <div class="col-md-2 mb-2">
                    <input type="text" wire:model.debounce.500ms="search" class="form-control form-control-sm"
                           placeholder="{{ $searchPlaceholder }}">
                </div>
                <div class="col-md-1 mb-2">
                    <input type="number" wire:model.live="userFilter" class="form-control form-control-sm" placeholder="User ID">
                </div>
                <div class="col-md-1 mb-2">
                    <input type="number" wire:model.live="orderFilter" class="form-control form-control-sm" placeholder="Order ID">
                </div>
                <div class="col-md-2 mb-2">
                    <input type="number" wire:model.live="hotspotUserFilter" class="form-control form-control-sm" placeholder="HS User ID">
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