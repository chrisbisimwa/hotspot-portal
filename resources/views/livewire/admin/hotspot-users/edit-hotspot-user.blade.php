@section('page_title', 'Edit '.$hotspotUser->username)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.hotspot-users.index') }}">Hotspot Users</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.hotspot-users.show', $hotspotUser->id) }}">{{ $hotspotUser->username }}</a></li>
    <li class="breadcrumb-item active">Edit</li>
@endsection

<div>
    @if (session('success'))
        <div class="alert alert-success py-2">{{ session('success') }}</div>
    @endif

    <form wire:submit.prevent="save">
        <div class="card">
            <div class="card-header">
                <strong>Hotspot User Data</strong>
            </div>
            <div class="card-body">
                <div class="form-row">
                    <div class="form-group col-md-3">
                        <label>Status *</label>
                        <select class="form-control form-control-sm" wire:model.live="status">
                            <option value="active">Active</option>
                            <option value="expired">Expired</option>
                            <option value="suspended">Suspended</option>
                        </select>
                        @error('status') <small class="text-danger">{{ $message }}</small>@enderror
                    </div>
                    <div class="form-group col-md-3">
                        <label>Profile *</label>
                        <select class="form-control form-control-sm" wire:model.live="user_profile_id">
                            @foreach($profiles as $p)
                                <option value="{{ $p['id'] }}">{{ $p['name'] }}</option>
                            @endforeach
                        </select>
                        @error('user_profile_id') <small class="text-danger">{{ $message }}</small>@enderror
                    </div>
                    <div class="form-group col-md-3">
                        <label>Validity (minutes) *</label>
                        <input type="number" min="1" class="form-control form-control-sm" wire:model.live="validity_minutes">
                        @error('validity_minutes') <small class="text-danger">{{ $message }}</small>@enderror
                    </div>
                    <div class="form-group col-md-3">
                        <label>Data Limit (MB)</label>
                        <input type="number" min="1" class="form-control form-control-sm" wire:model.live="data_limit_mb">
                        <small class="text-muted">Empty = unlimited</small>
                        @error('data_limit_mb') <small class="text-danger">{{ $message }}</small>@enderror
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