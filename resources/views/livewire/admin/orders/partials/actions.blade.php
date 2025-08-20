<div class="btn-group btn-group-sm" role="group">
    <a href="{{ route('admin.orders.show', $order->id ?? $item->id) }}" class="btn btn-outline-primary" title="View">
        <i class="fas fa-eye"></i>
    </a>
    <a href="{{ route('admin.orders.edit', $order->id ?? $item->id) }}" class="btn btn-outline-secondary" title="Edit">
        <i class="fas fa-edit"></i>
    </a>
</div>