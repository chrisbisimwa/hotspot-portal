@section('page_title', 'Profile '.$userProfile->name)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.user-profiles.index') }}">User Profiles</a></li>
    <li class="breadcrumb-item active">{{ $userProfile->name }}</li>
@endsection

<div>
    <div class="d-flex justify-content-between mb-3">
        <h4><i class="fas fa-layer-group mr-2"></i> {{ $userProfile->name }}</h4>
        <div class="btn-group btn-group-sm">
            <a href="{{ route('admin.user-profiles.edit', $userProfile->id) }}" class="btn btn-secondary">
                <i class="fas fa-edit"></i> Edit
            </a>
            <a href="{{ route('admin.user-profiles.index') }}" class="btn btn-light">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success py-2">{{ session('success') }}</div>
    @endif

    <div class="card mb-3">
        <div class="card-header"><strong>Details</strong></div>
        <div class="card-body p-0">
            <table class="table table-sm mb-0">
                <tr><th style="width:180px">Name</th><td>{{ $userProfile->name }}</td></tr>
                <tr><th>Mikrotik Profile</th><td>{{ $userProfile->mikrotik_profile ?? '-' }}</td></tr>
                <tr><th>Price</th><td>${{ number_format((float)$userProfile->price, 2) }}</td></tr>
                <tr><th>Validity</th><td>{{ $userProfile->validity_minutes }} min (≈ {{ round($userProfile->validity_minutes/60,2) }} h)</td></tr>
                <tr><th>Data Limit</th><td>{{ $userProfile->data_limit_mb ? $userProfile->data_limit_mb.' MB' : 'Unlimited' }}</td></tr>
                <tr><th>Active</th><td>
                    @if($userProfile->is_active)
                        <span class="badge badge-success">Active</span>
                    @else
                        <span class="badge badge-secondary">Inactive</span>
                    @endif
                </td></tr>
                <tr><th>Description</th><td>{{ $userProfile->description ?? '-' }}</td></tr>
                <tr><th>Created</th><td>{{ $userProfile->created_at->format('Y-m-d H:i') }}</td></tr>
                <tr><th>Updated</th><td>{{ $userProfile->updated_at->format('Y-m-d H:i') }}</td></tr>
            </table>
        </div>
    </div>
</div>