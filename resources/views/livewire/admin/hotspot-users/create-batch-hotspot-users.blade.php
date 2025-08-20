<div>
    <button type="button" class="btn btn-outline-primary btn-sm" wire:click="open">
        <i class="fas fa-layer-group"></i> Batch
    </button>

    @if($showModal)
        <div class="modal fade show d-block" style="background: rgba(0,0,0,.5);" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content" wire:click.stop>
                    <div class="modal-header">
                        <h5 class="modal-title">Create Batch Hotspot Users</h5>
                        <button type="button" class="close" wire:click="close"><span>&times;</span></button>
                    </div>
                    <div class="modal-body">

                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label>Owner *</label>
                                <select wire:model.live="owner_id" class="form-control form-control-sm">
                                    <option value="">-- choose --</option>
                                    @foreach($owners as $o)
                                        <option value="{{ $o['id'] }}">{{ $o['name'] }}</option>
                                    @endforeach
                                </select>
                                @error('owner_id') <small class="text-danger">{{ $message }}</small>@enderror
                            </div>
                            <div class="form-group col-md-4">
                                <label>Profile *</label>
                                <select wire:model.live="user_profile_id" class="form-control form-control-sm">
                                    <option value="">-- choose --</option>
                                    @foreach($profiles as $p)
                                        <option value="{{ $p['id'] }}">{{ $p['name'] }}</option>
                                    @endforeach
                                </select>
                                @error('user_profile_id') <small class="text-danger">{{ $message }}</small>@enderror
                            </div>
                            <div class="form-group col-md-4">
                                <label>Quantity *</label>
                                <input type="number" min="1" max="500" class="form-control form-control-sm" wire:model.live="quantity">
                                @error('quantity') <small class="text-danger">{{ $message }}</small>@enderror
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-3">
                                <label>Override Validity (min)</label>
                                <input type="number" min="1" class="form-control form-control-sm" wire:model.live="override_validity">
                                @error('override_validity') <small class="text-danger">{{ $message }}</small>@enderror
                            </div>
                            <div class="form-group col-md-3">
                                <label>Override Quota (MB)</label>
                                <input type="number" min="1" class="form-control form-control-sm" wire:model.live="override_quota_mb">
                                @error('override_quota_mb') <small class="text-danger">{{ $message }}</small>@enderror
                            </div>
                            <div class="form-group col-md-3">
                                <label>Username Prefix *</label>
                                <input type="text" class="form-control form-control-sm" wire:model.live="username_prefix">
                                @error('username_prefix') <small class="text-danger">{{ $message }}</small>@enderror
                            </div>
                            <div class="form-group col-md-3">
                                <label>Password Length *</label>
                                <input type="number" min="4" max="32" class="form-control form-control-sm" wire:model.live="password_length">
                                @error('password_length') <small class="text-danger">{{ $message }}</small>@enderror
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>Batch Ref (optionnel)</label>
                                <input type="text" class="form-control form-control-sm" wire:model.live="batch_ref" placeholder="Automatique si vide">
                                @error('batch_ref') <small class="text-danger">{{ $message }}</small>@enderror
                            </div>
                            <div class="form-group col-md-6 d-flex align-items-center">
                                <div class="form-check mt-4">
                                    <input type="checkbox" id="generate_pdf" class="form-check-input" wire:model.live="generate_pdf">
                                    <label for="generate_pdf" class="form-check-label">Ouvrir PDF après création</label>
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-info py-2">
                            Pour > 500 comptes, envisage un Job asynchrone (non implémenté ici).
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-secondary btn-sm" wire:click="close">Annuler</button>
                        <button class="btn btn-success btn-sm" wire:click="save">
                            <i class="fas fa-save"></i> Créer le lot
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-backdrop fade show" wire:click="close"></div>
    @endif
</div>