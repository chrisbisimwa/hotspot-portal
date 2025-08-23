import Chart from 'chart.js/auto';

let charts = {};
let auto = true;
let intervalId = null;

function buildLine(ctx, label, color) {
    return new Chart(ctx, {
        type: 'line',
        data: { labels: [], datasets: [{ label, data: [], borderColor: color, tension: .1, pointRadius: 0 }] },
        options: {
            animation: false,
            responsive: true,
            scales: {
                x: { ticks: { maxRotation: 0 }, grid: { display: false } },
                y: { beginAtZero: true }
            }
        }
    });
}

function updateChart(chart, series) {
    chart.data.labels = series.map(p => new Date(p.t).toLocaleTimeString());
    chart.data.datasets[0].data = series.map(p => p.v);
    chart.update('none');
}

async function fetchTimeseries() {
    const res = await fetch(`${window.MonitoringConfig.routeTimeseries}?range=${window.MonitoringConfig.range}`);
    return await res.json();
}

async function fetchInterfaces() {
    const res = await fetch(`${window.MonitoringConfig.routeInterfaces}?range=1h`);
    return await res.json();
}

async function refreshAll() {
    try {
        const ts = await fetchTimeseries();
        const data = ts.data || {};

        if (charts.sessions && data['global.active_sessions_count']) {
            updateChart(charts.sessions, data['global.active_sessions_count']);
        }
        if (charts.queue && data['system.queue_pending']) {
            updateChart(charts.queue, data['system.queue_pending']);
        }
        if (charts.revenue && data['global.revenue_last_24h']) {
            updateChart(charts.revenue, data['global.revenue_last_24h']);
        }

        // Payments success/failed ratio placeholder (if future keys)
        if (charts.payments) {
            const succ = data['payments.success'] || [];
            updateChart(charts.payments, succ);
        }

        if (charts.interfaces) {
            const ifData = await fetchInterfaces();
            // Aggregate sum RX for example
            const aggregated = aggregateInterfaces(ifData.data);
            updateChart(charts.interfaces, aggregated);
        }
    } catch (e) {
        console.error('Monitoring refresh error', e);
    }
}

function aggregateInterfaces(map) {
    // map: {key: [{t,v}, ...]}
    // Return combined per timestamp (sum)
    const bucket = {};
    Object.values(map).forEach(series => {
        series.forEach(p => {
            bucket[p.t] = (bucket[p.t] || 0) + (p.v || 0);
        });
    });
    return Object.entries(bucket)
        .sort((a,b) => a[0].localeCompare(b[0]))
        .map(([t,v]) => ({ t, v }));
}

function init() {
    const sessions = document.getElementById('chartSessions');
    if (sessions) charts.sessions = buildLine(sessions, 'Active Sessions', '#007bff');
    const queue = document.getElementById('chartQueue');
    if (queue) charts.queue = buildLine(queue, 'Queue Pending', '#6f42c1');
    const revenue = document.getElementById('chartRevenue');
    if (revenue) charts.revenue = buildLine(revenue, 'Revenue (24h metric snapshot)', '#28a745');
    const payments = document.getElementById('chartPayments');
    if (payments) charts.payments = buildLine(payments, 'Payments Success (placeholder)', '#ffc107');
    const interfaces = document.getElementById('chartInterfaces');
    if (interfaces) charts.interfaces = buildLine(interfaces, 'Interfaces RX+TX (sum kbps)', '#17a2b8');
    const revenue2 = document.getElementById('chartRevenue2');
    if (revenue2 && !charts.revenue2) charts.revenue2 = buildLine(revenue2, 'Revenue', '#20c997');

    refreshAll();
    auto = true;
    intervalId = setInterval(() => {
        if (auto) refreshAll();
    }, window.MonitoringConfig.refreshMs || 30000);

    window.addEventListener('monitoring-range-changed', e => {
        window.MonitoringConfig.range = e.detail.range;
        refreshAll();
    });
    window.addEventListener('monitoring-refreshed', () => refreshAll());
}

document.addEventListener('DOMContentLoaded', init);