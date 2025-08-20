@section('page_title', 'Hotspot Sessions')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Hotspot Sessions</li>
@endsection

<div>
    <div class="card mb-3">
        <div class="card-header d-flex justify-content-between flex-wrap align-items-center">
            <h3 class="card-title mb-2 mb-sm-0">
                <i class="fas fa-plug mr-1"></i> Sessions
            </h3>
            <div class="btn-group btn-group-sm">
                <button wire:click="resync" class="btn btn-outline-primary" title="Resync from Mikrotik">
                    <i class="fas fa-sync"></i> Sync
                </button>
                <button wire:click="clearFilters" class="btn btn-outline-secondary" title="Clear filters">
                    <i class="fas fa-filter"></i> Reset
                </button>
            </div>
        </div>
        <div class="card-body py-2">
            @if (session('success'))
                <div class="alert alert-success py-2 mb-2">{{ session('success') }}</div>
            @endif
            <div class="row text-center">
                <div class="col-6 col-md-3 mb-2">
                    <div class="border rounded p-2">
                        <div class="text-muted" style="font-size:11px;">Active</div>
                        <div style="font-size:18px;font-weight:600;">{{ $kpiActive }}</div>
                    </div>
                </div>
                <div class="col-6 col-md-3 mb-2">
                    <div class="border rounded p-2">
                        <div class="text-muted" style="font-size:11px;">Total Sessions</div>
                        <div style="font-size:18px;font-weight:600;">{{ $kpiTotalSessions }}</div>
                    </div>
                </div>
                <div class="col-6 col-md-3 mb-2">
                    <div class="border rounded p-2">
                        <div class="text-muted" style="font-size:11px;">Upload (MB)</div>
                        <div style="font-size:18px;font-weight:600;">{{ $kpiUploadMb }}</div>
                    </div>
                </div>
                <div class="col-6 col-md-3 mb-2">
                    <div class="border rounded p-2">
                        <div class="text-muted" style="font-size:11px;">Download (MB)</div>
                        <div style="font-size:18px;font-weight:600;">{{ $kpiDownloadMb }}</div>
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
                        <option value="active">Active</option>
                        <option value="closed">Closed</option>
                    </select>
                </div>
                <div class="col-md-2 mb-2">
                    <select wire:model.live="interfaceFilter" class="form-control form-control-sm">
                        <option value="">Interface: All</option>
                        @foreach($interfaces as $if)
                            <option value="{{ $if }}">{{ $if }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 mb-2">
                    <input type="date" wire:model.live="dateFrom" class="form-control form-control-sm" placeholder="From">
                </div>
                <div class="col-md-2 mb-2">
                    <input type="date" wire:model.live="dateTo" class="form-control form-control-sm" placeholder="To">
                </div>
                <div class="col-md-2 mb-2">
                    <input type="text" wire:model.debounce.500ms="search" class="form-control form-control-sm" placeholder="{{ $searchPlaceholder }}">
                </div>
                <div class="col-md-2 mb-2">
                    <input type="number" wire:model.live="userFilter" class="form-control form-control-sm" placeholder="User ID">
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