<div>
    <button type="button" class="btn btn-primary btn-sm" wire:click="open">
        <i class="fas fa-plus"></i> New Order
    </button>

    @if($showModal)
        <div class="modal fade show d-block" style="background: rgba(0,0,0,.5);" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content" wire:click.stop>
                    <div class="modal-header">
                        <h5 class="modal-title">Create Order</h5>
                        <button type="button" class="close" wire:click="close"><span>&times;</span></button>
                    </div>
                    <div class="modal-body">

                        <div class="form-group">
                            <label>User</label>
                            <select class="form-control form-control-sm" wire:model.live="user_id">
                                <option value="">-- choose user --</option>
                                @foreach($users as $u)
                                    <option value="{{ $u['id'] }}">{{ $u['label'] }}</option>
                                @endforeach
                            </select>
                            @error('user_id') <small class="text-danger">{{ $message }}</small>@enderror
                        </div>

                        <div class="form-group">
                            <label>Profile</label>
                            <div class="d-flex align-items-center">
                                <select class="form-control form-control-sm" wire:model.live="user_profile_id" @disabled(!$user_id || $loadingProfiles)>
                                    <option value="">-- choose profile --</option>
                                    @foreach($profiles as $p)
                                        <option value="{{ $p['id'] }}">{{ $p['label'] }}</option>
                                    @endforeach
                                </select>
                                @if($loadingProfiles)
                                    <span class="spinner-border spinner-border-sm text-primary ml-2"></span>
                                @endif
                            </div>
                            @error('user_profile_id') <small class="text-danger">{{ $message }}</small>@enderror
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label>Quantity</label>
                                <input type="number" min="1" class="form-control form-control-sm" wire:model.live="quantity">
                                @error('quantity') <small class="text-danger">{{ $message }}</small>@enderror
                            </div>
                            <div class="form-group col-md-4">
                                <label>Unit Price</label>
                                <input type="number" min="0" step="0.01" class="form-control form-control-sm" wire:model.live="unit_price">
                                @error('unit_price') <small class="text-danger">{{ $message }}</small>@enderror
                            </div>
                            <div class="form-group col-md-4">
                                <label>Total (auto)</label>
                                <input type="text" class="form-control form-control-sm" value="{{ number_format($quantity * $unit_price, 2) }}" readonly>
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
        <!-- Backdrop clickable -->
        <div class="modal-backdrop fade show" wire:click="close"></div>
    @endif
</div>
