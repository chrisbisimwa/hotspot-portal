@section('page_title', 'Payment #'.$payment->id)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.payments.index') }}">Payments</a></li>
    <li class="breadcrumb-item active">#{{ $payment->id }}</li>
@endsection

<div>
    <div class="d-flex justify-content-between mb-3">
        <h4><i class="fas fa-credit-card mr-2"></i> Payment #{{ $payment->id }}</h4>
        <div class="btn-group btn-group-sm">
            <a href="{{ route('admin.payments.edit', $payment->id) }}" class="btn btn-secondary">
                <i class="fas fa-edit"></i> Edit
            </a>
            <a href="{{ route('admin.payments.index') }}" class="btn btn-light">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header"><strong>Details</strong></div>
        <div class="card-body p-0">
            <table class="table table-sm mb-0">
                <tr><th style="width:180px">ID</th><td>{{ $payment->id }}</td></tr>
                <tr><th>User</th><td>{{ $payment->user?->name }} ({{ $payment->user?->email }})</td></tr>
                <tr><th>Order</th><td>
                    @if($payment->order_id)
                        <a href="{{ route('admin.orders.edit', $payment->order_id) }}">#{{ $payment->order_id }}</a>
                    @else
                        -
                    @endif
                </td></tr>
                <tr><th>Provider</th><td>{{ $payment->provider }}</td></tr>
                <tr><th>Status</th><td>
                    <span class="badge badge-{{ $payment->status === 'success' ? 'success' : ($payment->status === 'failed' ? 'danger' : 'secondary') }}">
                        {{ strtoupper($payment->status) }}
                    </span>
                </td></tr>
                <tr><th>Transaction Ref</th><td>{{ $payment->transaction_ref }}</td></tr>
                <tr><th>Internal Ref</th><td>{{ $payment->internal_ref }}</td></tr>
                <tr><th>Amount</th><td>{{ number_format($payment->amount,2) }} {{ $payment->currency }}</td></tr>
                <tr><th>Fee</th><td>{{ $payment->fee_amount !== null ? number_format($payment->fee_amount,2) : '-' }}</td></tr>
                <tr><th>Net</th><td>{{ $payment->net_amount !== null ? number_format($payment->net_amount,2) : '-' }}</td></tr>
                <tr><th>Paid At</th><td>{{ $payment->paid_at?->format('Y-m-d H:i') ?? '-' }}</td></tr>
                <tr><th>Confirmed At</th><td>{{ $payment->confirmed_at?->format('Y-m-d H:i') ?? '-' }}</td></tr>
                <tr><th>Refunded At</th><td>{{ $payment->refunded_at?->format('Y-m-d H:i') ?? '-' }}</td></tr>
                <tr><th>Created</th><td>{{ $payment->created_at->format('Y-m-d H:i') }}</td></tr>
                <tr><th>Updated</th><td>{{ $payment->updated_at->format('Y-m-d H:i') }}</td></tr>
            </table>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between">
            <strong>Raw Data</strong>
            <button wire:click="toggleRaw" class="btn btn-sm btn-outline-secondary">
                {{ $showRaw ? 'Hide' : 'Show' }}
            </button>
        </div>
        @if($showRaw)
            <div class="card-body">
                <h6>raw_request</h6>
                <pre class="small bg-light p-2">{{ json_encode($payment->raw_request, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES) }}</pre>
                <h6>raw_response</h6>
                <pre class="small bg-light p-2">{{ json_encode($payment->raw_response, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES) }}</pre>
                <h6>callback_payload</h6>
                <pre class="small bg-light p-2">{{ json_encode($payment->callback_payload, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES) }}</pre>
                <h6>meta</h6>
                <pre class="small bg-light p-2">{{ json_encode($payment->meta, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES) }}</pre>
            </div>
        @endif
    </div>
</div>