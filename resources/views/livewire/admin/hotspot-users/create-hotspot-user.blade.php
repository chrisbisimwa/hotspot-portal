<div>
    <button type="button" class="btn btn-primary btn-sm" wire:click="open">
        <i class="fas fa-plus"></i> New Hotspot User
    </button>

    @if($showModal)
        <div class="modal fade show d-block" style="background: rgba(0,0,0,.5);" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content" wire:click.stop>
                    <div class="modal-header">
                        <h5 class="modal-title">Create Hotspot User</h5>
                        <button type="button" class="close" wire:click="close"><span>&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label>Owner *</label>
                                <select class="form-control form-control-sm" wire:model.live="owner_id">
                                    <option value="">-- choose --</option>
                                    @foreach($owners as $o)
                                        <option value="{{ $o['id'] }}">{{ $o['name'] }}</option>
                                    @endforeach
                                </select>
                                @error('owner_id') <small class="text-danger">{{ $message }}</small>@enderror
                            </div>
                            <div class="form-group col-md-4">
                                <label>Profile *</label>
                                <select class="form-control form-control-sm"
                                        wire:model.live="user_profile_id"
                                        wire:change="selectProfile($event.target.value)">
                                    <option value="">-- choose --</option>
                                    @foreach($profiles as $p)
                                        <option value="{{ $p['id'] }}">{{ $p['name'] }}</option>
                                    @endforeach
                                </select>
                                @error('user_profile_id') <small class="text-danger">{{ $message }}</small>@enderror
                            </div>
                            <div class="form-group col-md-4">
                                <label>Status *</label>
                                <select class="form-control form-control-sm" wire:model.live="status">
                                    <option value="active">Active</option>
                                    <option value="expired">Expired</option>
                                    <option value="suspended">Suspended</option>
                                </select>
                                @error('status') <small class="text-danger">{{ $message }}</small>@enderror
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label>Username *</label>
                                <div class="input-group input-group-sm">
                                    <input type="text" class="form-control" wire:model.live="username">
                                    <div class="input-group-append">
                                        <button class="btn btn-outline-secondary" type="button" wire:click="generateCredentials">
                                            <i class="fas fa-sync"></i>
                                        </button>
                                    </div>
                                </div>
                                @error('username') <small class="text-danger">{{ $message }}</small>@enderror
                            </div>
                            <div class="form-group col-md-4">
                                <label>Password *</label>
                                <input type="text" class="form-control form-control-sm" wire:model.live="password">
                                @error('password') <small class="text-danger">{{ $message }}</small>@enderror
                            </div>
                            <div class="form-group col-md-4">
                                <label>Validity (min) *</label>
                                <input type="number" class="form-control form-control-sm" wire:model.live="validity_minutes" min="1">
                                @error('validity_minutes') <small class="text-danger">{{ $message }}</small>@enderror
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label>Data Limit (MB)</label>
                                <input type="number" class="form-control form-control-sm" wire:model.live="data_limit_mb" min="1">
                                <small class="text-muted">Empty = unlimited</small>
                                @error('data_limit_mb') <small class="text-danger">{{ $message }}</small>@enderror
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-secondary btn-sm" wire:click="close">Cancel</button>
                        <button class="btn btn-success btn-sm" wire:click="save">
                            <i class="fas fa-save"></i> Save
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-backdrop fade show" wire:click="close"></div>
    @endif
</div>