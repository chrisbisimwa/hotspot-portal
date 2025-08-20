<div class="btn-group btn-group-sm">
    <a href="{{ route('admin.hotspot-users.show', $item->id) }}" class="btn btn-outline-primary" title="View">
        <i class="fas fa-eye"></i>
    </a>
    <a href="{{ route('admin.hotspot-users.edit', $item->id) }}" class="btn btn-outline-secondary" title="Edit">
        <i class="fas fa-edit"></i>
    </a>
    <a href="{{ route('admin.hotspot-users.ticket.pdf', $item->id) }}" target="_blank" class="btn btn-outline-primary"
        title="Ticket">
        <i class="fas fa-ticket-alt"></i>
    </a>
</div>
