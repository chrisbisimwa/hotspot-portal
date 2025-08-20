<div class="btn-group btn-group-sm">
    <a href="{{ route('admin.notifications.show', $item['id']) }}" class="btn btn-outline-primary" title="View">
        <i class="fas fa-eye"></i>
    </a>
    <a href="{{ route('admin.notifications.edit', $item['id']) }}" class="btn btn-outline-secondary" title="Edit">
        <i class="fas fa-edit"></i>
    </a>
</div>