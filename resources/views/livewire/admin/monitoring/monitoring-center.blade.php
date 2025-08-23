@section('page_title', 'Monitoring')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Monitoring</li>
@endsection

<div x-data="monitoringCenter()">
    <div class="d-flex justify-content-between flex-wrap mb-3">
        <h4 class="mb-2"><i class="fas fa-chart-line mr-2"></i> Monitoring Center</h4>
        <div class="btn-group btn-group-sm">
            <button wire:click="refreshNow" class="btn btn-outline-primary">
                <i class="fas fa-sync"></i>
            </button>
            <button wire:click="toggleAuto" class="btn btn-outline-secondary" x-text="autoLabel()"></button>
        </div>
    </div>

    <ul class="nav nav-tabs mb-3">
        @foreach (['overview' => 'Overview', 'metrics' => 'Metrics', 'mikrotik' => 'MikroTik', 'queue' => 'Queue', 'payments' => 'Payments'] as $code => $label)
            <li class="nav-item">
                <a href="#" wire:click.prevent="switchTab('{{ $code }}')"
                    class="nav-link @if ($tab === $code) active @endif">{{ $label }}</a>
            </li>
        @endforeach
    </ul>

    @if ($tab === 'overview')
        <div class="row">
            @foreach ([
        'total_users' => 'Total Users',
        'active_users' => 'Active Users',
        'hotspot_users' => 'Hotspot Users',
        'active_hotspot_users' => 'Active HS Users',
        'orders_last_24h' => 'Orders 24h',
        'revenue_last_24h' => 'Revenue 24h',
        'active_sessions_count' => 'Active Sessions',
        'payments_pending' => 'Payments Pending',
        'notifications_queued' => 'Notifications Queued',
    ] as $k => $label)
                <div class="col-6 col-md-3 mb-3">
                    <div class="p-2 border rounded h-100">
                        <div class="text-muted small">{{ $label }}</div>
                        <div class="h5 mb-0">{{ $global[$k] ?? '—' }}</div>
                    </div>
                </div>
            @endforeach
        </div>
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong>Key Timeseries</strong>
                <div class="d-flex align-items-center">
                    <select wire:model.live="timeseriesRange" class="form-control form-control-sm mr-2"
                        style="width:auto;">
                        <option value="1h">1h</option>
                        <option value="6h">6h</option>
                        <option value="24h">24h</option>
                        <option value="7d">7d</option>
                    </select>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <canvas id="chartSessions"></canvas>
                    </div>
                    <div class="col-md-6 mb-3">
                        <canvas id="chartQueue"></canvas>
                    </div>
                    <div class="col-md-6 mb-3">
                        <canvas id="chartRevenue"></canvas>
                    </div>
                    <div class="col-md-6 mb-3">
                        <canvas id="chartPayments"></canvas>
                    </div>
                </div>
            </div>
        </div>
    @elseif($tab === 'mikrotik')
        <div class="card">
            <div class="card-header"><strong>MikroTik Interfaces</strong></div>
            <div class="card-body">
                @if (isset($interfaces['error']))
                    <div class="alert alert-danger">{{ $interfaces['message'] ?? $interfaces['error'] }}</div>
                @else
                    <div class="table-responsive">
                        <table class="min-w-full text-sm">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="px-3 py-2 text-left font-semibold">Name</th>
                                    <th class="px-3 py-2 text-right font-semibold">RX (kbit/s)</th>
                                    <th class="px-3 py-2 text-right font-semibold">TX (kbit/s)</th>
                                    <th class="px-3 py-2 text-left font-semibold">Type / State</th>
                                    <th class="px-3 py-2 text-left font-semibold">Source</th>
                                </tr>
                            </thead>
                            <tbody id="mikrotik-interfaces-body" class="divide-y divide-gray-200">
                                {{-- Contenu injecté dynamiquement via JS fetch JSON --}}
                            </tbody>
                        </table>
                    </div>
                    <canvas id="chartInterfaces" height="140"></canvas>
                @endif
            </div>
        </div>
    @elseif($tab === 'queue')
        <div class="card">
            <div class="card-header"><strong>Queue / System</strong></div>
            <div class="card-body">
                <ul class="list-unstyled small mb-0">
                    <li>Queue Pending: <strong>{{ $system['queue_pending'] ?? '—' }}</strong></li>
                    <li>Memory Usage: <strong>{{ $system['memory_usage']['formatted']['current'] ?? '—' }}</strong></li>
                    <li>Memory Peak: <strong>{{ $system['memory_usage']['formatted']['peak'] ?? '—' }}</strong></li>
                    <li>Server Load: <strong>{{ $system['server_load'] ?? '—' }}</strong></li>
                </ul>
                <div class="mt-3">
                    <canvas id="chartQueueDepth"></canvas>
                </div>
            </div>
        </div>
    @elseif($tab === 'payments')
        <div class="card">
            <div class="card-header"><strong>Payments & Revenue</strong></div>
            <div class="card-body">
                <div class="mb-3">Revenue last 24h:
                    <strong>{{ number_format($global['revenue_last_24h'] ?? 0, 2) }}</strong>
                </div>
                <canvas id="chartRevenue2"></canvas>
            </div>
        </div>
    @elseif($tab === 'metrics')
        <div class="card">
            <div class="card-header"><strong>Current Raw Metrics</strong></div>
            <div class="card-body">
                <div class="row">
                    @foreach ($global as $k => $v)
                        <div class="col-md-3 mb-2">
                            <div class="border rounded p-2 h-100">
                                <div class="small text-muted">{{ $k }}</div>
                                <div class="font-weight-bold">{{ is_numeric($v) ? $v : json_encode($v) }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
</div>

@push('scripts')
    {{-- @vite('resources/js/monitoring-charts.js') --}}
    {{-- import resources/js/monitoring-charts.js --}}
    <script src="{{ asset('dist/js/monitoring-charts.js') }}"></script>

    <script>
        function monitoringCenter() {
            return {
                autoLabel() {
                    return @entangle('autoRefresh') ? 'Auto: ON' : 'Auto: OFF';
                }
            }
        }
        window.MonitoringConfig = {
            range: @json($timeseriesRange),
            refreshMs: @json($refreshIntervalMs),
            routeTimeseries: @json(route('admin.monitoring.timeseries')),
            routeInterfaces: @json(route('admin.monitoring.interfaces')),
        };




        document.addEventListener('DOMContentLoaded', () => {
            const body = document.getElementById('mikrotik-interfaces-body');
            const endpoint = "{{ route('admin.monitoring.interfaces.live') }}";
            const refreshMs = {{ (int) config('mikrotik.interfaces_poll_interval_seconds', 10) * 1000 }};
            let timer = null;

            async function load() {
                try {
                    const r = await fetch(endpoint, {
                        credentials: 'same-origin'
                    });
                    if (!r.ok) throw new Error('HTTP ' + r.status);
                    const data = await r.json();
                    render(data.data || []);
                } catch (e) {
                    console.error('Load interfaces failed', e);
                }
            }

            function render(rows) {
                body.innerHTML = '';
                rows.forEach(row => {
                    const running = (row.running || '') === 'true';
                    const tr = document.createElement('tr');
                    if (!running) tr.classList.add('bg-gray-50');

                    const rx = (row.rx_kbps ?? row['rx-kbps']);
                    const tx = (row.tx_kbps ?? row['tx-kbps']);

                    tr.innerHTML = `
                <td class="px-3 py-1 font-medium">${row.name ?? '-'}</td>
                <td class="px-3 py-1 text-right">${Number.isFinite(rx) ? rx.toFixed(1) : '—'}</td>
                <td class="px-3 py-1 text-right">${Number.isFinite(tx) ? tx.toFixed(1) : '—'}</td>
                <td class="px-3 py-1">
                    <span class="inline-flex items-center gap-1 text-xs text-gray-700">
                        <span class="h-2 w-2 rounded-full ${running ? 'bg-green-500' : 'bg-gray-400'}"></span>
                        ${row.type ?? '-'} ${running ? '(up)' : '(down)'}
                    </span>
                </td>
                <td class="px-3 py-1 text-xs text-gray-500">${row.source ?? '-'}</td>
            `;
                    body.appendChild(tr);
                });
            }

            load();
            timer = setInterval(load, refreshMs);
        });
    </script>
@endpush
