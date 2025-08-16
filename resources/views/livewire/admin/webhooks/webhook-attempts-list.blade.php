<div class="p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Webhook Attempts</h1>
        <a href="{{ route('admin.webhooks.endpoints') }}" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700">
            Manage Endpoints
        </a>
    </div>

    @if (session()->has('message'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
            {{ session('message') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
            {{ session('error') }}
        </div>
    @endif

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <!-- Search -->
            <div>
                <label for="search" class="block text-sm font-medium text-gray-700">Search</label>
                <input 
                    type="text" 
                    wire:model.live.debounce.300ms="search"
                    id="search"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    placeholder="Search attempts..."
                >
            </div>

            <!-- Status Filter -->
            <div>
                <label for="status-filter" class="block text-sm font-medium text-gray-700">Status</label>
                <select 
                    wire:model.live="statusFilter"
                    id="status-filter"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                >
                    <option value="">All Statuses</option>
                    @foreach($statusOptions as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Endpoint Filter -->
            <div>
                <label for="endpoint-filter" class="block text-sm font-medium text-gray-700">Endpoint</label>
                <select 
                    wire:model.live="endpointFilter"
                    id="endpoint-filter"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                >
                    <option value="">All Endpoints</option>
                    @foreach($endpoints as $endpoint)
                        <option value="{{ $endpoint->id }}">{{ $endpoint->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Event Filter -->
            <div>
                <label for="event-filter" class="block text-sm font-medium text-gray-700">Event</label>
                <select 
                    wire:model.live="eventFilter"
                    id="event-filter"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                >
                    <option value="">All Events</option>
                    @foreach($uniqueEvents as $event)
                        <option value="{{ $event }}">{{ $event }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Clear Filters -->
            <div class="flex items-end">
                <button 
                    wire:click="clearFilters"
                    class="w-full px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700"
                >
                    Clear Filters
                </button>
            </div>
        </div>
    </div>

    <!-- Attempts Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Event</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Endpoint</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Attempt</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Response</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dispatched</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($attempts as $attempt)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $attempt->event_name }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">
                                    {{ $attempt->endpoint ? $attempt->endpoint->name : 'Deleted' }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                                    @if($attempt->status->value === 'pending') bg-yellow-100 text-yellow-800
                                    @elseif($attempt->status->value === 'success') bg-green-100 text-green-800
                                    @elseif($attempt->status->value === 'failed') bg-red-100 text-red-800
                                    @else bg-gray-100 text-gray-800
                                    @endif">
                                    {{ $attempt->status->label() }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $attempt->attempt_number }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                @if($attempt->response_code)
                                    <span class="font-medium {{ $attempt->response_code >= 200 && $attempt->response_code < 300 ? 'text-green-600' : 'text-red-600' }}">
                                        {{ $attempt->response_code }}
                                    </span>
                                @else
                                    -
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $attempt->dispatched_at->format('M j, H:i') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                @if($attempt->status === \App\Enums\WebhookAttemptStatus::FAILED && $attempt->canRetry())
                                    <button 
                                        wire:click="retryAttempt({{ $attempt->id }})"
                                        class="text-blue-600 hover:text-blue-900"
                                    >
                                        Retry
                                    </button>
                                @endif
                                
                                @if($attempt->error_message)
                                    <button 
                                        onclick="alert('{{ addslashes($attempt->error_message) }}')"
                                        class="text-orange-600 hover:text-orange-900 ml-2"
                                        title="View Error"
                                    >
                                        Error
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                No webhook attempts found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($attempts->hasPages())
            <div class="px-6 py-3 border-t border-gray-200">
                {{ $attempts->links() }}
            </div>
        @endif
    </div>

    <!-- Stats Cards -->
    <div class="mt-6 grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-lg shadow p-4">
            <div class="text-sm text-gray-600">Total Attempts</div>
            <div class="text-2xl font-bold text-gray-900">{{ $attempts->total() }}</div>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <div class="text-sm text-gray-600">Success Rate</div>
            <div class="text-2xl font-bold text-green-600">
                @php
                    $total = $attempts->total();
                    $successful = \App\Models\WebhookAttempt::where('status', 'success')->count();
                    $rate = $total > 0 ? ($successful / $total * 100) : 0;
                @endphp
                {{ number_format($rate, 1) }}%
            </div>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <div class="text-sm text-gray-600">Failed Attempts</div>
            <div class="text-2xl font-bold text-red-600">
                {{ \App\Models\WebhookAttempt::where('status', 'failed')->count() }}
            </div>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <div class="text-sm text-gray-600">Pending Attempts</div>
            <div class="text-2xl font-bold text-yellow-600">
                {{ \App\Models\WebhookAttempt::where('status', 'pending')->count() }}
            </div>
        </div>
    </div>
</div>