<div class="p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Health & Incidents Overview</h1>
        <button wire:click="refresh" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
            Refresh
        </button>
    </div>

    <!-- Health Status Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- MikroTik Status -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">MikroTik Status</p>
                    <p class="text-2xl font-bold text-gray-900">
                        @if($mikrotikPing !== null)
                            {{ number_format($mikrotikPing, 0) }}ms
                        @else
                            Unknown
                        @endif
                    </p>
                </div>
                <div class="w-12 h-12 rounded-full flex items-center justify-center
                    @if($this->mikrotikStatus === 'healthy') bg-green-100 text-green-600
                    @elseif($this->mikrotikStatus === 'warning') bg-yellow-100 text-yellow-600
                    @elseif($this->mikrotikStatus === 'critical') bg-red-100 text-red-600
                    @else bg-gray-100 text-gray-600
                    @endif">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Payment Success Rate -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Payment Success (24h)</p>
                    <p class="text-2xl font-bold text-gray-900">
                        @if($paymentSuccessRate !== null)
                            {{ number_format($paymentSuccessRate, 1) }}%
                        @else
                            Unknown
                        @endif
                    </p>
                </div>
                <div class="w-12 h-12 rounded-full flex items-center justify-center
                    @if($this->paymentStatus === 'healthy') bg-green-100 text-green-600
                    @elseif($this->paymentStatus === 'warning') bg-yellow-100 text-yellow-600
                    @elseif($this->paymentStatus === 'critical') bg-red-100 text-red-600
                    @else bg-gray-100 text-gray-600
                    @endif">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Provisioning Error Rate -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Provisioning Errors</p>
                    <p class="text-2xl font-bold text-gray-900">
                        @if($provisioningErrorRate !== null)
                            {{ number_format($provisioningErrorRate * 100, 1) }}%
                        @else
                            Unknown
                        @endif
                    </p>
                </div>
                <div class="w-12 h-12 rounded-full flex items-center justify-center
                    @if($this->provisioningStatus === 'healthy') bg-green-100 text-green-600
                    @elseif($this->provisioningStatus === 'warning') bg-yellow-100 text-yellow-600
                    @elseif($this->provisioningStatus === 'critical') bg-red-100 text-red-600
                    @else bg-gray-100 text-gray-600
                    @endif">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Open Incidents -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Open Incidents</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $openIncidents }}</p>
                </div>
                <div class="w-12 h-12 rounded-full flex items-center justify-center
                    @if($openIncidents === 0) bg-green-100 text-green-600
                    @elseif($openIncidents <= 2) bg-yellow-100 text-yellow-600
                    @else bg-red-100 text-red-600
                    @endif">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.464 0L4.35 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Additional Stats -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- System Metrics -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">System Metrics</h3>
            <div class="space-y-4">
                <div class="flex justify-between">
                    <span class="text-gray-600">Total Users:</span>
                    <span class="font-semibold">{{ $metrics['total_users'] ?? 'N/A' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Active Sessions:</span>
                    <span class="font-semibold">{{ $metrics['active_sessions_count'] ?? 'N/A' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Orders (24h):</span>
                    <span class="font-semibold">{{ $metrics['orders_last_24h'] ?? 'N/A' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Revenue (24h):</span>
                    <span class="font-semibold">${{ number_format($metrics['revenue_last_24h'] ?? 0, 2) }}</span>
                </div>
            </div>
        </div>

        <!-- Webhook Status -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Webhook Status</h3>
            <div class="space-y-4">
                <div class="flex justify-between">
                    <span class="text-gray-600">Pending Webhooks:</span>
                    <span class="font-semibold">{{ $pendingWebhooks }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Notifications Queued:</span>
                    <span class="font-semibold">{{ $metrics['notifications_queued'] ?? 'N/A' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Payments Pending:</span>
                    <span class="font-semibold">{{ $metrics['payments_pending'] ?? 'N/A' }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="mt-8 flex space-x-4">
        <a href="{{ route('admin.incidents.index') }}" class="px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700">
            View Incidents
        </a>
        <a href="{{ route('admin.webhooks.index') }}" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700">
            Manage Webhooks
        </a>
    </div>
</div>