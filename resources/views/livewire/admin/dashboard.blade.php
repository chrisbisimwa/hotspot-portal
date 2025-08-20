@section('page_title', 'Admin Dashboard')

@section('breadcrumb')
    <li class="breadcrumb-item active">Dashboard</li>
@endsection

<div>
    <!-- Metrics Cards Row -->
    <div class="row">
        <div class="col-lg-3 col-6">
            <livewire:shared.metric-card 
                title="Total Users" 
                :value="number_format($this->metrics['total_users'])" 
                icon="fas fa-users" 
                color="info"
                :key="'metric-total-users'"
            />
        </div>
        <div class="col-lg-3 col-6">
            <livewire:shared.metric-card 
                title="Active Users" 
                :value="number_format($this->metrics['active_users'])" 
                icon="fas fa-user-check" 
                color="success"
                :key="'metric-active-users'"
            />
        </div>
        <div class="col-lg-3 col-6">
            <livewire:shared.metric-card 
                title="Hotspot Users" 
                :value="number_format($this->metrics['hotspot_users'])" 
                icon="fas fa-wifi" 
                color="warning"
                :key="'metric-hotspot-users'"
            />
        </div>
        <div class="col-lg-3 col-6">
            <livewire:shared.metric-card 
                title="Active Hotspot Users" 
                :value="number_format($this->metrics['active_hotspot_users'])" 
                icon="fas fa-wifi" 
                color="success"
                :key="'metric-active-hotspot-users'"
            />
        </div>
    </div>

    <!-- Second Row -->
    <div class="row">
        <div class="col-lg-3 col-6">
            <livewire:shared.metric-card 
                title="Orders (24h)" 
                :value="number_format($this->metrics['orders_last_24h'])" 
                icon="fas fa-shopping-cart" 
                color="primary"
                :key="'metric-orders-24h'"
            />
        </div>
        <div class="col-lg-3 col-6">
            <livewire:shared.metric-card 
                title="Revenue (24h)" 
                :value="'$' . number_format($this->metrics['revenue_last_24h'], 2)" 
                icon="fas fa-dollar-sign" 
                color="success"
                :key="'metric-revenue-24h'"
            />
        </div>
        <div class="col-lg-3 col-6">
            <livewire:shared.metric-card 
                title="Pending Payments" 
                :value="number_format($this->metrics['payments_pending'])" 
                icon="fas fa-clock" 
                color="warning"
                :key="'metric-pending-payments'"
            />
        </div>
        <div class="col-lg-3 col-6">
            <livewire:shared.metric-card 
                title="Queued Notifications" 
                :value="number_format($this->metrics['notifications_queued'])" 
                icon="fas fa-bell" 
                color="info"
                :key="'metric-queued-notifications'"
            />
        </div>
    </div>

    <!-- Charts and Additional Info -->
    <div class="row mt-4">
        <!-- Chart Placeholder -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-line mr-1"></i>
                        Orders - Last 7 Days
                    </h3>
                </div>
                <div class="card-body">
                    <div class="chart-container" style="position: relative; height: 300px;">
                        <canvas id="ordersChart" style="height: 300px;"></canvas>
                        <!-- Placeholder for now -->
                        <div class="d-flex align-items-center justify-content-center h-100 text-muted">
                            <div class="text-center">
                                <i class="fas fa-chart-line fa-3x mb-3"></i>
                                <h5>Chart Placeholder</h5>
                                <p>Orders chart will be implemented with Chart.js</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- System Info -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-server mr-1"></i>
                        System Status
                    </h3>
                </div>
                <div class="card-body">
                    <div class="info-box">
                        <span class="info-box-icon bg-info">
                            <i class="fas fa-memory"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">Memory Usage</span>
                            <span class="info-box-number">{{ $this->systemMetrics['memory_usage']['formatted']['current'] }}</span>
                            <span class="info-box-text text-sm text-muted">Peak: {{ $this->systemMetrics['memory_usage']['formatted']['peak'] }}</span>
                        </div>
                    </div>

                    <div class="info-box">
                        <span class="info-box-icon bg-warning">
                            <i class="fas fa-tasks"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">Queue Jobs</span>
                            <span class="info-box-number">{{ $this->systemMetrics['queue_pending'] }}</span>
                            <span class="info-box-text text-sm text-muted">Pending</span>
                        </div>
                    </div>

                    <div class="info-box">
                        <span class="info-box-icon bg-success">
                            <i class="fas fa-wifi"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">Active Sessions</span>
                            <span class="info-box-number">{{ number_format($this->metrics['active_sessions_count']) }}</span>
                            <span class="info-box-text text-sm text-muted">Current</span>
                        </div>
                    </div>
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
                                <i class="fas fa-eye mr-1"></i> View Orders
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="#" class="btn btn-info btn-block">
                                <i class="fas fa-credit-card mr-1"></i> View Payments
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="#" class="btn btn-warning btn-block">
                                <i class="fas fa-wifi mr-1"></i> Hotspot Users
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const canvas = document.getElementById('ordersChart');
    if (!canvas || typeof Chart === 'undefined') {
        console.warn('Chart.js non chargé ou canvas introuvable');
        return;
    }

    fetch(@json(route('admin.orders.trends')), {
        headers: { 'Accept': 'application/json' }
    })
    .then(r => {
        if (!r.ok) throw new Error('HTTP ' + r.status);
        return r.json();
    })
    .then(payload => {
        new Chart(canvas.getContext('2d'), {
            type: 'line',
            data: {
                labels: payload.labels,
                datasets: payload.datasets
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { display: true },
                    tooltip: { enabled: true }
                },
                scales: {
                    x: { ticks: { autoSkip: true, maxTicksLimit: 7 } },
                    y: { beginAtZero: true, precision: 0 }
                }
            }
        });
    })
    .catch(e => {
        console.error('Erreur chargement trend commandes', e);
    });
});
</script>
@endpush