@section('page_title', 'Reports')

@section('breadcrumb')
    <li class="breadcrumb-item active">Reports</li>
@endsection

<div>
    <!-- Page Header -->
    <div class="row mb-3">
        <div class="col-md-6">
            <h4 class="mb-0">Available Reports</h4>
            <p class="text-muted mb-0">Generate and export system reports</p>
        </div>
    </div>

    <!-- Reports Grid -->
    <div class="row">
        @foreach($this->reports as $reportKey => $report)
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-chart-bar text-primary me-2"></i>
                        {{ $report['title'] }}
                    </h5>
                </div>
                <div class="card-body">
                    <p class="card-text text-muted">{{ $report['description'] }}</p>
                    
                    <!-- Formats -->
                    <div class="mb-3">
                        <small class="text-muted">Available formats:</small>
                        <div>
                            @foreach($report['allowed_formats'] as $format)
                                <span class="badge bg-light text-dark me-1">{{ strtoupper($format) }}</span>
                            @endforeach
                        </div>
                    </div>

                    <!-- Filters Info -->
                    @if(count($report['filters_schema']) > 0)
                    <div class="mb-3">
                        <small class="text-muted">Filters:</small>
                        <div>
                            @foreach($report['filters_schema'] as $filterKey => $filterType)
                                <span class="badge bg-secondary me-1">{{ ucfirst(str_replace('_', ' ', $filterKey)) }}</span>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>
                <div class="card-footer">
                    <a href="{{ route('admin.reports.viewer', $reportKey) }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-eye me-1"></i>
                        View Report
                    </a>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    @if(count($this->reports) === 0)
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="fas fa-chart-bar fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No Reports Available</h5>
                    <p class="text-muted">No report builders have been registered in the system.</p>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>