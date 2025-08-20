<div wire:loading.class="opacity-50">
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
                    <span class="input-group-text"><i class="fas fa-search"></i></span>
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
        <div class="col-md-3"></div>
    </div>

    <div wire:loading.flex class="d-flex justify-content-center align-items-center p-3">
        <div class="spinner-border spinner-border-sm text-primary" role="status">
            <span class="sr-only">Loading...</span>
        </div>
        <span class="ml-2">Loading...</span>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered table-striped mb-2">
            <thead>
                <tr>
                    @foreach($columns as $column)
                        @php
                            $sortable = $column['sortable'] ?? false;
                            $field = $column['field'] ?? null;
                            $label = $column['label'] ?? ($field ?? '');
                            $type = $column['type'] ?? null;
                        @endphp
                        <th
                            @if($sortable && $field)
                                wire:click="sortBy('{{ $field }}')"
                                style="cursor:pointer"
                                class="user-select-none"
                            @endif
                        >
                            <span class="d-inline-flex align-items-center">
                                {{ $label }}
                                @if($sortable && $field)
                                    <i class="{{ $this->getSortIcon($field) }} ml-1"></i>
                                @endif
                            </span>
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse($data as $item)
                    <tr>
                        @foreach($columns as $column)
                            @php($type = $column['type'] ?? null)
                            <td>
                                @switch($type)
                                    @case('status')
                                        <livewire:shared.status-badge 
                                            :status="data_get($item, $column['field'])" 
                                            :domain="$column['domain'] ?? null" 
                                            :key="'status-' . $item->id . '-' . $column['field']"
                                        />
                                        @break

                                    @case('date')
                                        {{ data_get($item, $column['field'])?->format($column['format'] ?? 'Y-m-d H:i') ?? '-' }}
                                        @break

                                    @case('currency')
                                        ${{ number_format((float) data_get($item, $column['field'], 0), 2) }}
                                        @break
                                    @case('custom_validity')
                                        @php($minutes = data_get($item, $column['field']))
                                            {{ $minutes }} min (≈ {{ $minutes ? round($minutes/60,2) : 0 }} h)
                                        @break

                                    @case('custom_data')
                                        @php($mb = data_get($item, $column['field']))
                                            {{ $mb ? $mb.' MB' : 'Unlimited' }}
                                        @break

                                    
                                    @case('boolean')
                                        <span class="badge badge-{{ data_get($item, $column['field']) ? 'success' : 'secondary' }}">
                                            {{ data_get($item, $column['field']) ? 'Yes' : 'No' }}
                                        </span>
                                        @break

                                    @case('actions')
                                      @php($actionsView = $column['actions_view'] ?? null)
                                      @includeIf($actionsView, ['item' => $item])

                                    @break

                                    @default
                                        {{ data_get($item, $column['field'] ?? '', '-') ?? '-' }}
                                @endswitch
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

    <div class="d-flex justify-content-between align-items-center">
        <div class="text-muted small">
            Showing {{ $data->firstItem() ?? 0 }} to {{ $data->lastItem() ?? 0 }} of {{ $data->total() }} results
        </div>
        <div>
            {{ $data->links() }}
        </div>
    </div>
</div>