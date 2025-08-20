<div class="btn-group btn-group-sm">
    <a href="{{ route('admin.user-profiles.show', $profile->id ?? $item->id) }}" class="btn btn-outline-primary" title="View">
        <i class="fas fa-eye"></i>
    </a>
    <a href="{{ route('admin.user-profiles.edit', $profile->id ?? $item->id) }}" class="btn btn-outline-secondary" title="Edit">
        <i class="fas fa-edit"></i>
    </a>
</div>