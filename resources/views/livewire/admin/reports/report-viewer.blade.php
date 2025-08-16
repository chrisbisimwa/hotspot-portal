@section('page_title', $reportMetadata['title'] ?? 'Report Viewer')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.reports.index') }}">Reports</a></li>
    <li class="breadcrumb-item active">{{ $reportMetadata['title'] ?? 'Report' }}</li>
@endsection

<div>
    <!-- Page Header -->
    <div class="row mb-3">
        <div class="col-md-8">
            <h4 class="mb-0">{{ $reportMetadata['title'] ?? 'Report' }}</h4>
            <p class="text-muted mb-0">{{ $reportMetadata['description'] ?? '' }}</p>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('admin.reports.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i>
                Back to Reports
            </a>
        </div>
    </div>

    <!-- Filters Form -->
    @if(isset($reportMetadata['filters_schema']) && count($reportMetadata['filters_schema']) > 0)
    <div class="card mb-4">
        <div class="card-header">
            <h6 class="card-title mb-0">
                <i class="fas fa-filter me-2"></i>
                Report Filters
            </h6>
        </div>
        <div class="card-body">
            <form wire:submit.prevent="loadReport">
                <div class="row">
                    @foreach($reportMetadata['filters_schema'] as $filterKey => $filterType)
                    <div class="col-md-{{ $filterType === 'date' ? '3' : '4' }} mb-3">
                        <label for="filter_{{ $filterKey }}" class="form-label">
                            {{ ucfirst(str_replace('_', ' ', $filterKey)) }}
                        </label>
                        @if($filterType === 'date')
                            <input type="date" 
                                   id="filter_{{ $filterKey }}"
                                   class="form-control" 
                                   wire:model.defer="filters.{{ $filterKey }}">
                        @else
                            <input type="text" 
                                   id="filter_{{ $filterKey }}"
                                   class="form-control" 
                                   wire:model.defer="filters.{{ $filterKey }}">
                        @endif
                    </div>
                    @endforeach
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                            <span wire:loading.remove>
                                <i class="fas fa-sync me-1"></i>
                                Refresh Report
                            </span>
                            <span wire:loading>
                                <i class="fas fa-spinner fa-spin me-1"></i>
                                Loading...
                            </span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    @endif

    <!-- Report Results -->
    @if($error)
    <div class="alert alert-danger">
        <i class="fas fa-exclamation-triangle me-2"></i>
        {{ $error }}
    </div>
    @endif

    @if($result)
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h6 class="card-title mb-0">
                    <i class="fas fa-table me-2"></i>
                    Report Results
                </h6>
                <small class="text-muted">
                    {{ $result['meta']['total_rows'] ?? 0 }} rows
                    @if(isset($result['meta']['cache_hit']) && $result['meta']['cache_hit'])
                        <span class="badge bg-success ms-1">Cached</span>
                    @endif
                    @if(isset($result['meta']['truncated']) && $result['meta']['truncated'])
                        <span class="badge bg-warning ms-1">Truncated</span>
                    @endif
                </small>
            </div>
            <div class="btn-group">
                @foreach($reportMetadata['allowed_formats'] ?? [] as $format)
                <button type="button" 
                        class="btn btn-outline-primary btn-sm" 
                        wire:click="exportReport('{{ $format }}')">
                    <i class="fas fa-download me-1"></i>
                    Export {{ strtoupper($format) }}
                </button>
                @endforeach
            </div>
        </div>
        <div class="card-body p-0">
            @if(count($result['rows']) > 0)
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0">
                    <thead class="table-dark">
                        <tr>
                            @foreach($result['columns'] as $column)
                            <th class="{{ in_array($column['type'], ['currency', 'integer', 'number']) ? 'text-end' : '' }}">
                                {{ $column['label'] }}
                            </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($result['rows'] as $row)
                        <tr>
                            @foreach($result['columns'] as $column)
                            <td class="{{ in_array($column['type'], ['currency', 'integer', 'number']) ? 'text-end' : '' }}{{ $column['type'] === 'date' ? 'text-center' : '' }}">
                                {{ $row[$column['key']] ?? '' }}
                            </td>
                            @endforeach
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="text-center py-5">
                <i class="fas fa-search fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No Data Found</h5>
                <p class="text-muted">No data matches the selected criteria.</p>
            </div>
            @endif
        </div>
        @if(isset($result['meta']) && count($result['meta']) > 0)
        <div class="card-footer">
            <small class="text-muted">
                Generated: {{ $result['generated_at'] ?? 'Unknown' }}
                @if(isset($result['meta']['date_range']))
                | Date Range: {{ $result['meta']['date_range']['from'] }} to {{ $result['meta']['date_range']['to'] }}
                @endif
            </small>
        </div>
        @endif
    </div>
    @endif

    @if($loading)
    <div class="card">
        <div class="card-body text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <h5 class="mt-3 text-muted">Loading Report...</h5>
            <p class="text-muted">Please wait while we generate your report.</p>
        </div>
    </div>
    @endif
</div>