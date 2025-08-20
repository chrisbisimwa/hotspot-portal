<div>
    <button type="button" class="btn btn-primary btn-sm" wire:click="open">
        <i class="fas fa-plus"></i> New Profile
    </button>

    @if ($showModal)
        <div class="modal fade show d-block" style="background: rgba(0,0,0,.5);" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content" wire:click.stop>
                    <div class="modal-header">
                        <h5 class="modal-title">Create User Profile</h5>
                        <button type="button" class="close" wire:click="close"><span>&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Name *</label>
                            <input type="text" class="form-control form-control-sm" wire:model.live="name">
                            @error('name')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>Mikrotik Profile (override)</label>
                                <input type="text" class="form-control form-control-sm"
                                    wire:model.live="mikrotik_profile" placeholder="Auto si vide">
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
                            <div class="form-group col-md-3">
                                <label>Validity (min) *</label>
                                <input type="number" min="1" class="form-control form-control-sm"
                                    wire:model.live="validity_minutes">
                                @error('validity_minutes')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label>Rate Limit</label>
                                <input type="text" class="form-control form-control-sm" wire:model.live="rate_limit"
                                    placeholder="ex: 2M/2M">
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
                            <div class="form-group col-md-4">
                                <label>Data (MB)</label>
                                <input type="number" min="1" class="form-control form-control-sm"
                                    wire:model.live="data_limit_mb">
                                @error('data_limit_mb')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label>Session Timeout</label>
                                <input type="text" class="form-control form-control-sm"
                                    wire:model.live="session_timeout" placeholder="ex: 1h">
                                @error('session_timeout')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                            <div class="form-group col-md-4">
                                <label>Idle Timeout</label>
                                <input type="text" class="form-control form-control-sm"
                                    wire:model.live="idle_timeout" placeholder="ex: 5m">
                                @error('idle_timeout')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                            <div class="form-group col-md-4">
                                <label>Keepalive Timeout</label>
                                <input type="text" class="form-control form-control-sm"
                                    wire:model.live="keepalive_timeout" placeholder="ex: 10m">
                                @error('keepalive_timeout')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Description</label>
                            <textarea class="form-control form-control-sm" rows="2" wire:model.live="description"></textarea>
                            @error('description')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="is_active_create"
                                wire:model.live="is_active">
                            <label for="is_active_create" class="form-check-label">Active (sync allowed)</label>
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
