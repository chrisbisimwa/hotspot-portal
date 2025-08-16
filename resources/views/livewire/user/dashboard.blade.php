@section('page_title', 'Dashboard')

@section('breadcrumb')
    <li class="breadcrumb-item active">Dashboard</li>
@endsection

<div>
    <!-- Welcome Section -->
    <div class="row">
        <div class="col-12">
            <div class="callout callout-info">
                <h5><i class="fas fa-info"></i> Welcome back, {{ auth()->user()->name }}!</h5>
                Here's an overview of your account activity and recent transactions.
            </div>
        </div>
    </div>

    <!-- User Metrics Cards -->
    <div class="row">
        <div class="col-lg-3 col-6">
            <livewire:shared.metric-card 
                title="Total Orders" 
                :value="number_format($this->userMetrics['orders_count'])" 
                icon="fas fa-shopping-cart" 
                color="primary"
                :key="'user-metric-orders'"
            />
        </div>
        <div class="col-lg-3 col-6">
            <livewire:shared.metric-card 
                title="Total Paid" 
                :value="'$' . number_format($this->userMetrics['orders_total_paid'], 2)" 
                icon="fas fa-dollar-sign" 
                color="success"
                :key="'user-metric-paid'"
            />
        </div>
        <div class="col-lg-3 col-6">
            <livewire:shared.metric-card 
                title="Active Hotspot Users" 
                :value="number_format($this->userMetrics['hotspot_users_active'])" 
                icon="fas fa-wifi" 
                color="warning"
                :key="'user-metric-hotspot-users'"
            />
        </div>
        <div class="col-lg-3 col-6">
            <livewire:shared.metric-card 
                title="Active Sessions" 
                :value="number_format($this->userMetrics['sessions_active'])" 
                icon="fas fa-clock" 
                color="info"
                :key="'user-metric-sessions'"
            />
        </div>
    </div>

    <!-- Content Row -->
    <div class="row mt-4">
        <!-- Recent Orders -->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-shopping-cart mr-1"></i>
                        Recent Orders
                    </h3>
                    <div class="card-tools">
                        <a href="#" class="btn btn-tool btn-sm">
                            <i class="fas fa-external-link-alt"></i> View All
                        </a>
                    </div>
                </div>
                <div class="card-body p-0">
                    @if($this->recentOrders->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Order</th>
                                        <th>Profile</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($this->recentOrders as $order)
                                        <tr>
                                            <td>
                                                <strong>#{{ $order->id }}</strong>
                                                <br>
                                                <small class="text-muted">{{ $order->quantity }} users</small>
                                            </td>
                                            <td>{{ $order->userProfile->name ?? 'N/A' }}</td>
                                            <td>${{ number_format($order->total_amount, 2) }}</td>
                                            <td>
                                                <livewire:shared.status-badge 
                                                    :status="$order->status" 
                                                    domain="orders"
                                                    :key="'order-status-' . $order->id"
                                                />
                                            </td>
                                            <td>
                                                <small>{{ $order->created_at->format('M d, Y') }}</small>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="p-4 text-center text-muted">
                            <i class="fas fa-inbox fa-2x mb-2"></i>
                            <p>No orders yet</p>
                            <a href="#" class="btn btn-primary btn-sm">
                                <i class="fas fa-plus mr-1"></i> Create First Order
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Recent Notifications -->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-bell mr-1"></i>
                        Recent Notifications
                        @if($this->userMetrics['notifications_unread'] > 0)
                            <span class="badge badge-warning">{{ $this->userMetrics['notifications_unread'] }}</span>
                        @endif
                    </h3>
                    <div class="card-tools">
                        <a href="#" class="btn btn-tool btn-sm">
                            <i class="fas fa-external-link-alt"></i> View All
                        </a>
                    </div>
                </div>
                <div class="card-body p-0">
                    @if($this->recentNotifications->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($this->recentNotifications as $notification)
                                <div class="list-group-item {{ $notification->read_at ? '' : 'bg-light' }}">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1">{{ $notification->title }}</h6>
                                        <small>{{ $notification->created_at->diffForHumans() }}</small>
                                    </div>
                                    <p class="mb-1">{{ Str::limit($notification->body, 80) }}</p>
                                    @if($notification->read_at)
                                        <small class="text-success">
                                            <i class="fas fa-check"></i> Read
                                        </small>
                                    @else
                                        <small class="text-warning">
                                            <i class="fas fa-circle"></i> Unread
                                        </small>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="p-4 text-center text-muted">
                            <i class="fas fa-bell-slash fa-2x mb-2"></i>
                            <p>No notifications</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-bolt mr-1"></i>
                        Quick Actions
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <a href="#" class="btn btn-primary btn-block">
                                <i class="fas fa-plus mr-1"></i> New Order
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="#" class="btn btn-success btn-block">
                                <i class="fas fa-eye mr-1"></i> My Orders
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="#" class="btn btn-info btn-block">
                                <i class="fas fa-wifi mr-1"></i> My Hotspot Users
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="{{ route('settings.profile') }}" class="btn btn-warning btn-block">
                                <i class="fas fa-cog mr-1"></i> Settings
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>