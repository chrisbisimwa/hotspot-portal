@section('page_title','Notification #'.$notification->id)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.notifications.index') }}">Notifications</a></li>
    <li class="breadcrumb-item active">#{{ $notification->id }}</li>
@endsection

<div>
    <div class="d-flex justify-content-between mb-3">
        <h4><i class="fas fa-bell mr-2"></i> Notification #{{ $notification->id }}</h4>
        <div class="btn-group btn-group-sm">
            <a href="{{ route('admin.notifications.edit', $notification->id) }}" class="btn btn-secondary">
                <i class="fas fa-edit"></i> Edit
            </a>
            <button wire:click="resend" class="btn btn-outline-primary" title="Resend">
                <i class="fas fa-sync"></i>
            </button>
            <button wire:click="markRead" class="btn btn-outline-success" title="Mark Read">
                <i class="fas fa-check"></i>
            </button>
            <button wire:click="cancel" class="btn btn-outline-danger" title="Cancel">
                <i class="fas fa-ban"></i>
            </button>
            <button wire:click="toggleRaw" class="btn btn-outline-dark" title="Raw">
                <i class="fas fa-code"></i>
            </button>
            <a href="{{ route('admin.notifications.index') }}" class="btn btn-light">
                <i class="fas fa-arrow-left"></i>
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success py-2">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger py-2">{{ session('error') }}</div>
    @endif

    <div class="card mb-3">
        <div class="card-header"><strong>Details</strong></div>
        <div class="card-body p-0">
            <table class="table table-sm mb-0">
                <tr><th style="width:180px">User</th><td>{{ $notification->user?->name }} ({{ $notification->user?->email }})</td></tr>
                <tr><th>Order</th><td>{{ $notification->order_id ?? '-' }}</td></tr>
                <tr><th>Hotspot User</th><td>{{ $notification->hotspot_user_id ?? '-' }}</td></tr>
                <tr><th>Channel</th><td>{{ strtoupper($notification->channel) }}</td></tr>
                <tr><th>Status</th><td>
                    <span class="badge badge-{{ $notification->status === 'sent' ? 'success' : ($notification->status === 'failed' ? 'danger':'secondary') }}">
                        {{ strtoupper($notification->status) }}
                    </span>
                </td></tr>
                <tr><th>To</th><td>{{ $notification->to }}</td></tr>
                <tr><th>Subject</th><td>{{ $notification->subject ?? '—' }}</td></tr>
                <tr><th>Message</th><td><pre class="mb-0" style="white-space:pre-wrap;font-size:12px;">{{ $notification->message }}</pre></td></tr>
                <tr><th>Sent At</th><td>{{ $notification->sent_at?->format('Y-m-d H:i') ?? '—' }}</td></tr>
                <tr><th>Read At</th><td>{{ $notification->read_at?->format('Y-m-d H:i') ?? '—' }}</td></tr>
                <tr><th>Created</th><td>{{ $notification->created_at->format('Y-m-d H:i') }}</td></tr>
                <tr><th>Updated</th><td>{{ $notification->updated_at->format('Y-m-d H:i') }}</td></tr>
            </table>
        </div>
    </div>

    @if($showRaw)
    <div class="card">
        <div class="card-header"><strong>Raw / Provider</strong></div>
        <div class="card-body">
            <h6>Provider Response</h6>
            <pre class="small bg-light p-2">{{ json_encode($notification->provider_response, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES) }}</pre>
            <h6>Meta</h6>
            <pre class="small bg-light p-2">{{ json_encode($notification->meta, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES) }}</pre>
        </div>
    </div>
    @endif
</div>