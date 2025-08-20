<div class="btn-group btn-group-sm">
    <a href="{{ route('admin.payments.show', $item['id']) }}" class="btn btn-outline-primary" title="View">
        <i class="fas fa-eye"></i>
    </a>
    <a href="{{ route('admin.payments.edit', $item['id']) }}" class="btn btn-outline-secondary" title="Edit">
        <i class="fas fa-edit"></i>
    </a>
    {{-- Placeholder reconcile single (si tu crées une route ou Livewire action dédiée) --}}
</div>