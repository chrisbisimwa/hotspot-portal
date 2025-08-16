<div wire:loading.class="opacity-50">
    <!-- Search and filters -->
    <div class="row mb-3">
        <div class="col-md-6">
            <div class="input-group input-group-sm">
                <input 
                    type="text" 
                    class="form-control" 
                    placeholder="{{ $searchPlaceholder }}"
                    wire:model.live.debounce.300ms="search"
                    aria-label="{{ $searchPlaceholder }}"
                >
                <div class="input-group-append">
                    <span class="input-group-text">
                        <i class="fas fa-search"></i>
                    </span>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <select wire:model.live="perPage" class="form-control form-control-sm">
                <option value="10">10 per page</option>
                <option value="15">15 per page</option>
                <option value="25">25 per page</option>
                <option value="50">50 per page</option>
            </select>
        </div>
        <div class="col-md-3">
            <!-- Custom filters slot -->
            {{ $filters ?? '' }}
        </div>
    </div>

    <!-- Loading indicator -->
    <div wire:loading.flex class="d-flex justify-content-center align-items-center p-3">
        <div class="spinner-border spinner-border-sm text-primary" role="status">
            <span class="sr-only">Loading...</span>
        </div>
        <span class="ml-2">Loading...</span>
    </div>

    <!-- Data table -->
    <div class="table-responsive">
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    @foreach($columns as $column)
                        <th 
                            @if($column['sortable'] ?? false)
                                wire:click="sortBy('{{ $column['field'] }}')" 
                                style="cursor: pointer;"
                                class="user-select-none"
                            @endif
                        >
                            {{ $column['label'] }}
                            @if($column['sortable'] ?? false)
                                <i class="{{ $this->getSortIcon($column['field']) }}"></i>
                            @endif
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse($data as $item)
                    <tr>
                        @foreach($columns as $column)
                            <td>
                                @if($column['type'] === 'slot')
                                    {{ $slot }}
                                @elseif($column['type'] === 'status')
                                    <livewire:shared.status-badge 
                                        :status="data_get($item, $column['field'])" 
                                        :domain="$column['domain']" 
                                        :key="'status-' . $item->id . '-' . $column['field']"
                                    />
                                @elseif($column['type'] === 'date')
                                    {{ data_get($item, $column['field'])?->format($column['format'] ?? 'Y-m-d H:i') ?? '-' }}
                                @elseif($column['type'] === 'currency')
                                    ${{ number_format(data_get($item, $column['field'], 0), 2) }}
                                @elseif($column['type'] === 'boolean')
                                    <span class="badge badge-{{ data_get($item, $column['field']) ? 'success' : 'secondary' }}">
                                        {{ data_get($item, $column['field']) ? 'Yes' : 'No' }}
                                    </span>
                                @else
                                    {{ data_get($item, $column['field']) ?? '-' }}
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ count($columns) }}" class="text-center text-muted py-4">
                            <i class="fas fa-inbox fa-2x d-block mb-2"></i>
                            No records found
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="d-flex justify-content-between align-items-center">
        <div class="text-muted small">
            Showing {{ $data->firstItem() ?? 0 }} to {{ $data->lastItem() ?? 0 }} of {{ $data->total() }} results
        </div>
        <div>
            {{ $data->links() }}
        </div>
    </div>
</div>