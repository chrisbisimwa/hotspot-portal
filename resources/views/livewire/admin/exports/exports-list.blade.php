@section('page_title', 'Exports')

@section('breadcrumb')
    <li class="breadcrumb-item active">Exports</li>
@endsection

<div>
    <!-- Page Header -->
    <div class="row mb-3">
        <div class="col-md-6">
            <h4 class="mb-0">Export History</h4>
            <p class="text-muted mb-0">Manage and download report exports</p>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="search" class="form-label">Search</label>
                    <input type="text" 
                           id="search"
                           class="form-control" 
                           placeholder="Search by report key..."
                           wire:model.debounce.300ms="search">
                </div>
                <div class="col-md-6 mb-3">
                    <label for="status-filter" class="form-label">Status</label>
                    <select id="status-filter" class="form-select" wire:model="statusFilter">
                        @foreach($this->statusOptions as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Exports Table -->
    <div class="card">
        <div class="card-body p-0">
            @if($this->exports->count() > 0)
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Report</th>
                            <th>Format</th>
                            <th>Status</th>
                            <th>Requested By</th>
                            <th>Created</th>
                            <th>Completed</th>
                            <th>Rows</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($this->exports as $export)
                        <tr>
                            <td>
                                <span class="badge bg-light text-dark">#{{ $export->id }}</span>
                            </td>
                            <td>
                                <strong>{{ ucwords(str_replace('_', ' ', $export->report_key)) }}</strong>
                            </td>
                            <td>
                                <span class="badge bg-secondary">{{ strtoupper($export->format) }}</span>
                            </td>
                            <td>
                                @switch($export->status)
                                    @case('queued')
                                        <span class="badge bg-secondary">Queued</span>
                                        @break
                                    @case('processing')
                                        <span class="badge bg-primary">Processing</span>
                                        @break
                                    @case('completed')
                                        <span class="badge bg-success">Completed</span>
                                        @break
                                    @case('failed')
                                        <span class="badge bg-danger">Failed</span>
                                        @break
                                    @default
                                        <span class="badge bg-light text-dark">{{ ucfirst($export->status) }}</span>
                                @endswitch
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar avatar-sm me-2">
                                        <span class="avatar-initial bg-primary">
                                            {{ substr($export->requestedBy->name, 0, 1) }}
                                        </span>
                                    </div>
                                    <div>
                                        <div class="fw-semibold">{{ $export->requestedBy->name }}</div>
                                        <small class="text-muted">{{ $export->requestedBy->email }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div>{{ $export->created_at->format('M j, Y') }}</div>
                                <small class="text-muted">{{ $export->created_at->format('g:i A') }}</small>
                            </td>
                            <td>
                                @if($export->finished_at)
                                <div>{{ $export->finished_at->format('M j, Y') }}</div>
                                <small class="text-muted">{{ $export->finished_at->format('g:i A') }}</small>
                                @else
                                <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($export->total_rows)
                                <span class="badge bg-info">{{ number_format($export->total_rows) }}</span>
                                @else
                                <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    @if($export->isCompleted() && $export->file_path)
                                    <a href="{{ route('admin.exports.download', $export) }}" 
                                       class="btn btn-outline-primary" 
                                       title="Download">
                                        <i class="fas fa-download"></i>
                                    </a>
                                    @endif
                                    
                                    @if($export->isFailed() && $export->error_message)
                                    <button type="button" 
                                            class="btn btn-outline-danger" 
                                            title="View Error"
                                            data-bs-toggle="tooltip"
                                            data-bs-title="{{ $export->error_message }}">
                                        <i class="fas fa-exclamation-triangle"></i>
                                    </button>
                                    @endif
                                    
                                    <button type="button" 
                                            class="btn btn-outline-danger" 
                                            wire:click="deleteExport({{ $export->id }})"
                                            wire:confirm="Are you sure you want to delete this export?"
                                            title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="text-center py-5">
                <i class="fas fa-file-export fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No Exports Found</h5>
                <p class="text-muted">
                    @if($search || $statusFilter)
                        No exports match your search criteria.
                    @else
                        No exports have been created yet.
                    @endif
                </p>
            </div>
            @endif
        </div>
        
        @if($this->exports->hasPages())
        <div class="card-footer">
            {{ $this->exports->links() }}
        </div>
        @endif
    </div>
</div>