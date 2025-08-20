@section('page_title', 'Hotspot User ' . $hotspotUser->username)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.hotspot-users.index') }}">Hotspot Users</a></li>
    <li class="breadcrumb-item active">{{ $hotspotUser->username }}</li>
@endsection

<div>
    <div class="d-flex justify-content-between mb-3">
        <h4><i class="fas fa-user-lock mr-2"></i> {{ $hotspotUser->username }}</h4>
        <div class="btn-group btn-group-sm">
            <a href="{{ route('admin.hotspot-users.edit', $hotspotUser->id) }}" class="btn btn-secondary">
                <i class="fas fa-edit"></i> Edit
            </a>
            <a href="{{ route('admin.hotspot-users.index') }}" class="btn btn-light">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success py-2">{{ session('success') }}</div>
    @endif

    <div class="card mb-3">
        <div class="card-header d-flex justify-content-between">
            <span><strong>Details</strong></span>
            <div class="btn-group btn-group-sm">
                <button wire:click="resetPassword" class="btn btn-outline-warning" title="Reset password">
                    <i class="fas fa-key"></i>
                </button>
                <button wire:click="forceExpire" class="btn btn-outline-danger" title="Force expire">
                    <i class="fas fa-ban"></i>
                </button>
                <button wire:click="markRead" class="btn btn-outline-secondary" title="Mark read flag">
                    <i class="fas fa-check"></i>
                </button>
                <a href="{{ route('admin.hotspot-users.ticket.pdf', $hotspotUser->id) }}" target="_blank"
                    class="btn btn-outline-primary" title="Ticket PDF">
                    <i class="fas fa-print"></i>
                </a>
            </div>
        </div>
        <div class="card-body p-0">
            <table class="table table-sm mb-0">
                <tr>
                    <th style="width:180px">Username</th>
                    <td>{{ $hotspotUser->username }}</td>
                </tr>
                <tr>
                    <th>Password</th>
                    <td>{{ $hotspotUser->password }}</td>
                </tr>
                <tr>
                    <th>Owner</th>
                    <td>{{ $hotspotUser->owner?->name }} ({{ $hotspotUser->owner?->email }})</td>
                </tr>
                <tr>
                    <th>Profile</th>
                    <td>{{ $hotspotUser->userProfile?->name }}</td>
                </tr>
                <tr>
                    <th>Status</th>
                    <td>
                        @php $st = $hotspotUser->status; @endphp
                        <span
                            class="badge badge-{{ $st === 'active' ? 'success' : ($st === 'expired' ? 'secondary' : 'warning') }}">
                            {{ ucfirst($st) }}
                        </span>
                    </td>
                </tr>
                <tr>
                    <th>Validity</th>
                    <td>{{ $hotspotUser->validity_minutes }} min (≈ {{ round($hotspotUser->validity_minutes / 60, 2) }}
                        h)</td>
                </tr>
                <tr>
                    <th>Data Limit</th>
                    <td>{{ $hotspotUser->data_limit_mb ? $hotspotUser->data_limit_mb . ' MB' : 'Unlimited' }}</td>
                </tr>
                <tr>
                    <th>Expired At</th>
                    <td>{{ $hotspotUser->expired_at?->format('Y-m-d H:i') ?? '-' }}</td>
                </tr>
                <tr>
                    <th>Last Login</th>
                    <td>{{ $hotspotUser->last_login_at?->format('Y-m-d H:i') ?? '-' }}</td>
                </tr>
                <tr>
                    <th>Read At</th>
                    <td>{{ $hotspotUser->read_at?->format('Y-m-d H:i') ?? '-' }}</td>
                </tr>
                <tr>
                    <th>Created</th>
                    <td>{{ $hotspotUser->created_at->format('Y-m-d H:i') }}</td>
                </tr>
                <tr>
                    <th>Updated</th>
                    <td>{{ $hotspotUser->updated_at->format('Y-m-d H:i') }}</td>
                </tr>
            </table>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><strong>Recent Sessions (last 25)</strong></div>
        <div class="card-body p-0">
            <table class="table table-sm table-striped mb-0">
                <thead>
                    <tr>
                        <th>Start</th>
                        <th>Stop</th>
                        <th>Upload (MB)</th>
                        <th>Download (MB)</th>
                        <th>IP</th>
                        <th>MAC</th>
                        <th>Interface</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($hotspotUser->hotspotSessions as $s)
                        <tr>
                            <td>{{ $s->start_time?->format('Y-m-d H:i') }}</td>
                            <td>{{ $s->stop_time?->format('Y-m-d H:i') ?? '—' }}</td>
                            <td>{{ $s->upload_mb }}</td>
                            <td>{{ $s->download_mb }}</td>
                            <td>{{ $s->ip_address }}</td>
                            <td>{{ $s->mac_address }}</td>
                            <td>{{ $s->interface }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted">No sessions</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
