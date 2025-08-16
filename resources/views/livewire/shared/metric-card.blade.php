<div class="small-box bg-{{ $color }}">
    <div class="inner">
        <h3>{{ $value }}</h3>
        <p>{{ $title }}</p>
        @if($diff)
            <p class="small">
                @if($diff['direction'] === 'up')
                    <i class="fas fa-arrow-up"></i> +{{ $diff['value'] }}
                @elseif($diff['direction'] === 'down')
                    <i class="fas fa-arrow-down"></i> -{{ $diff['value'] }}
                @else
                    <i class="fas fa-minus"></i> {{ $diff['value'] }}
                @endif
                {{ $diff['label'] ?? 'vs last period' }}
            </p>
        @endif
    </div>
    @if($icon)
        <div class="icon">
            <i class="{{ $icon }}"></i>
        </div>
    @endif
    <div class="small-box-footer">
        <span aria-label="{{ $title }}: {{ $value }}"></span>
    </div>
</div>