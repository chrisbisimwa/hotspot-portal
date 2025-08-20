<div class="btn-group btn-group-sm">
    @if(isset($item['hotspotUser']))
        <a href="{{ route('admin.hotspot-users.show', $item['hotspotUser']->id) }}"
           class="btn btn-outline-primary"
           title="View User">
            <i class="fas fa-user"></i>
        </a>
    @endif
</div>