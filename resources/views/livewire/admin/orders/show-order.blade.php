@section('page_title', 'Order #'.$order->id)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}/orders">Orders</a></li>
    <li class="breadcrumb-item active">Order #{{ $order->id }}</li>
@endsection

<div>
    <div class="d-flex justify-content-between mb-3">
        <h4 class="mb-0">
            <i class="fas fa-receipt mr-2"></i> Order #{{ $order->id }}
        </h4>
        <div class="btn-group btn-group-sm">
            <a href="{{ route('admin.orders.edit', $order->id) }}" class="btn btn-secondary">
                <i class="fas fa-edit"></i> Edit
            </a>
            <a href="{{ url()->previous() }}" class="btn btn-light">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success py-2">{{ session('success') }}</div>
    @endif

    <div class="row">
        <div class="col-lg-7">
            <div class="card mb-3">
                <div class="card-header">
                    <strong>Summary</strong>
                    <button class="btn btn-link btn-sm float-right" wire:click="refreshData">
                        <i class="fas fa-sync"></i>
                    </button>
                </div>
                <div class="card-body p-0">
                    <table class="table mb-0 table-sm">
                        <tr>
                            <th style="width:160px">ID</th>
                            <td>{{ $order->id }}</td>
                        </tr>
                        <tr>
                            <th>User</th>
                            <td>
                                {{ $order->user?->name }}<br>
                                <small class="text-muted">{{ $order->user?->email }}</small>
                            </td>
                        </tr>
                        <tr>
                            <th>Profile</th>
                            <td>{{ $order->userProfile?->name ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Quantity</th>
                            <td>{{ $order->quantity }}</td>
                        </tr>
                        <tr>
                            <th>Unit Price</th>
                            <td>${{ number_format((float)$order->unit_price, 2) }}</td>
                        </tr>
                        <tr>
                            <th>Total Amount</th>
                            <td><strong>${{ number_format((float)$order->total_amount, 2) }}</strong></td>
                        </tr>
                        <tr>
                            <th>Status</th>
                            <td>
                                <livewire:shared.status-badge
                                    :status="$order->status"
                                    domain="orders"
                                    :key="'show-status-'.$order->id"
                                />
                            </td>
                        </tr>
                        <tr>
                            <th>Payment Ref</th>
                            <td>{{ $order->payment_reference ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Requested At</th>
                            <td>{{ $order->requested_at?->format('Y-m-d H:i') ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Paid At</th>
                            <td>{{ $order->paid_at?->format('Y-m-d H:i') ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Completed At</th>
                            <td>{{ $order->completed_at?->format('Y-m-d H:i') ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Cancelled At</th>
                            <td>{{ $order->cancelled_at?->format('Y-m-d H:i') ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Expires At</th>
                            <td>{{ $order->expires_at?->format('Y-m-d H:i') ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Created</th>
                            <td>{{ $order->created_at->format('Y-m-d H:i') }}</td>
                        </tr>
                        <tr>
                            <th>Updated</th>
                            <td>{{ $order->updated_at->format('Y-m-d H:i') }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            {{-- Placeholder pour paiements si tu veux détailler plus tard --}}
            @if($order->payments->count())
                <div class="card mb-3">
                    <div class="card-header">
                        <strong>Payments ({{ $order->payments->count() }})</strong>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-sm mb-0">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Provider</th>
                                    <th>Status</th>
                                    <th>Amount</th>
                                    <th>Paid At</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($order->payments as $pay)
                                    <tr>
                                        <td>{{ $pay->id }}</td>
                                        <td>{{ $pay->provider }}</td>
                                        <td>{{ $pay->status }}</td>
                                        <td>${{ number_format((float)$pay->amount, 2) }}</td>
                                        <td>{{ $pay->paid_at?->format('Y-m-d H:i') ?? '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>

        <div class="col-lg-5">
            <div class="card mb-3">
                <div class="card-header">
                    <strong>Meta</strong>
                </div>
                <div class="card-body">
                    @php $meta = $order->meta; @endphp
                    @if(empty($meta))
                        <em class="text-muted">No meta</em>
                    @else
                        <pre class="small mb-0">{{ json_encode($meta, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE) }}</pre>
                    @endif
                </div>
            </div>

            {{-- Placeholder si tu veux ajouter logs / timeline plus tard --}}
            <div class="card">
                <div class="card-header">
                    <strong>Timeline (placeholder)</strong>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled small mb-0">
                        <li><i class="far fa-clock text-muted"></i> Requested: {{ $order->requested_at?->diffForHumans() ?? '-' }}</li>
                        @if($order->paid_at)
                            <li><i class="far fa-clock text-success"></i> Paid: {{ $order->paid_at->diffForHumans() }}</li>
                        @endif
                        @if($order->completed_at)
                            <li><i class="far fa-check-circle text-primary"></i> Completed: {{ $order->completed_at->diffForHumans() }}</li>
                        @endif
                        @if($order->cancelled_at)
                            <li><i class="far fa-times-circle text-danger"></i> Cancelled: {{ $order->cancelled_at->diffForHumans() }}</li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>