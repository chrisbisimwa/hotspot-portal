@section('page_title', 'Edit ' . $userProfile->name)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.user-profiles.index') }}">User Profiles</a></li>
    <li class="breadcrumb-item"><a
            href="{{ route('admin.user-profiles.show', $userProfile->id) }}">{{ $userProfile->name }}</a></li>
    <li class="breadcrumb-item active">Edit</li>
@endsection

<div>
    <div class="d-flex justify-content-between mb-3">
        <h4><i class="fas fa-edit mr-2"></i> Edit: {{ $userProfile->name }}</h4>
        <div class="btn-group btn-group-sm">
            <a href="{{ route('admin.user-profiles.show', $userProfile->id) }}" class="btn btn-light">
                <i class="fas fa-eye"></i> View
            </a>
            <a href="{{ route('admin.user-profiles.index') }}" class="btn btn-light">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success py-2">{{ session('success') }}</div>
    @endif

    <form wire:submit.prevent="save">
        <div class="card">
            <div class="card-header"><strong>Profile Data</strong></div>
            <div class="card-body">

                <div class="form-row">
                    <div class="form-group col-md-5">
                        <label>Name *</label>
                        <input type="text" class="form-control form-control-sm" wire:model.live="name">
                        @error('name')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                    <div class="form-group col-md-4">
                        <label>Mikrotik Profile</label>
                        <input type="text" class="form-control form-control-sm" wire:model.live="mikrotik_profile">
                        @error('mikrotik_profile')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                    <div class="form-group col-md-3">
                        <label>Price *</label>
                        <input type="number" step="0.01" min="0" class="form-control form-control-sm"
                            wire:model.live="price">
                        @error('price')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label>Validity (minutes) *</label>
                        <input type="number" min="1" class="form-control form-control-sm"
                            wire:model.live="validity_minutes">
                        @error('validity_minutes')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                    <div class="form-group col-md-4">
                        <label>Data Limit (MB)</label>
                        <input type="number" min="1" class="form-control form-control-sm"
                            wire:model.live="data_limit_mb">
                        @error('data_limit_mb')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                        <small class="text-muted">Empty = Unlimited</small>
                    </div>
                    <div class="form-group col-md-4">
                        <label>Active</label>
                        <div class="form-check mt-1">
                            <input type="checkbox" class="form-check-input" id="is_active_edit"
                                wire:model.live="is_active">
                            <label for="is_active_edit" class="form-check-label">Yes</label>
                        </div>
                        @error('is_active')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                </div>

                <div class="form-group">
                    <label>Description</label>
                    <textarea rows="3" class="form-control form-control-sm" wire:model.live="description"></textarea>
                    @error('description')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                <hr class="my-3">
                <h6 class="text-muted mb-2"><i class="fas fa-network-wired mr-1"></i> MikroTik Settings</h6>

                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label>Mikrotik Profile</label>
                        <input type="text" class="form-control form-control-sm" wire:model.live="mikrotik_profile"
                            placeholder="Auto if empty">
                        @error('mikrotik_profile')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                    <div class="form-group col-md-4">
                        <label>Rate Limit</label>
                        <input type="text" class="form-control form-control-sm" wire:model.live="rate_limit">
                        @error('rate_limit')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                    <div class="form-group col-md-4">
                        <label>Shared Users</label>
                        <input type="number" min="1" class="form-control form-control-sm"
                            wire:model.live="shared_users">
                        @error('shared_users')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label>Session Timeout</label>
                        <input type="text" class="form-control form-control-sm" wire:model.live="session_timeout">
                        @error('session_timeout')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                    <div class="form-group col-md-4">
                        <label>Idle Timeout</label>
                        <input type="text" class="form-control form-control-sm" wire:model.live="idle_timeout">
                        @error('idle_timeout')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                    <div class="form-group col-md-4">
                        <label>Keepalive Timeout</label>
                        <input type="text" class="form-control form-control-sm"
                            wire:model.live="keepalive_timeout">
                        @error('keepalive_timeout')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                </div>

                <div class="alert alert-info py-2 small">
                    Changer le nom Mikrotik recrée le profil (ancienne version supprimée si trouvé).
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
