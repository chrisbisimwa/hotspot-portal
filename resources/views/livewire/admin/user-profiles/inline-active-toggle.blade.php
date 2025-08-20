<div class="d-inline-flex align-items-center">
    <button
        type="button"
        class="btn btn-sm {{ $isActive ? 'btn-success' : 'btn-outline-secondary' }}"
        wire:click="toggle"
        wire:loading.attr="disabled"
        title="{{ $isActive ? 'Click to deactivate' : 'Click to activate' }}"
        style="min-width: 38px;"
    >
        @if($loading)
            <span class="spinner-border spinner-border-sm"></span>
        @else
            @if($isActive)
                <i class="fas fa-toggle-on"></i>
            @else
                <i class="fas fa-toggle-off"></i>
            @endif
        @endif
    </button>

    @if($errorMessage)
        <span class="text-danger small ml-1" title="{{ $errorMessage }}">!</span>
    @endif
</div>