@section('page_title', 'Hotspot Users')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Hotspot Users</li>
@endsection

<div>
    <div class="card">
        <div class="card-header d-flex justify-content-between flex-wrap align-items-center">
            <h3 class="card-title mb-2 mb-sm-0">
                <i class="fas fa-wifi mr-1"></i> Hotspot Users
            </h3>
            <div>
                <livewire:admin.hotspot-users.create-hotspot-user :key="'create-hsu-component'" />
                
            </div>
            <div>
                <livewire:admin.hotspot-users.create-batch-hotspot-users :key="'create-bhu-component'" />
            </div>
        </div>
        <div class="card-body">

            <div class="row mb-3">
                <div class="col-md-3">
                    <select wire:model.live="statusFilter" class="form-control form-control-sm">
                        <option value="">All Statuses</option>
                        <option value="active">Active</option>
                        <option value="expired">Expired</option>
                        <option value="suspended">Suspended</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select wire:model.live="profileFilter" class="form-control form-control-sm">
                        <option value="">All Profiles</option>
                        @foreach(\App\Models\UserProfile::orderBy('name')->get(['id','name']) as $p)
                            <option value="{{ $p->id }}">{{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <input type="text"
                           wire:model.debounce.500ms="search"
                           class="form-control form-control-sm"
                           placeholder="{{ $searchPlaceholder }}">
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