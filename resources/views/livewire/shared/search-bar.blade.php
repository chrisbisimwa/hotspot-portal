<div class="input-group input-group-sm">
    <input 
        type="text" 
        class="form-control" 
        placeholder="{{ $placeholder }}"
        wire:model.live.debounce.300ms="search"
        aria-label="{{ $placeholder }}"
    >
    <div class="input-group-append">
        <span class="input-group-text">
            <i class="fas fa-search"></i>
        </span>
    </div>
</div>