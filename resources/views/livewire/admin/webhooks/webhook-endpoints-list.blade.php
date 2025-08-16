<div class="p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Webhook Endpoints</h1>
        <div class="flex space-x-3">
            <a href="{{ route('admin.webhooks.attempts') }}" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700">
                View Attempts
            </a>
            <button wire:click="toggleCreateForm" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                {{ $showCreateForm ? 'Cancel' : 'Create Endpoint' }}
            </button>
        </div>
    </div>

    @if (session()->has('message'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
            {{ session('message') }}
        </div>
    @endif

    <!-- Create Form -->
    @if($showCreateForm)
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Create Webhook Endpoint</h3>
            
            <form wire:submit.prevent="createEndpoint">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
                        <input 
                            type="text" 
                            wire:model="name"
                            id="name"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            placeholder="My Webhook"
                            required
                        >
                        @error('name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="url" class="block text-sm font-medium text-gray-700">URL</label>
                        <input 
                            type="url" 
                            wire:model="url"
                            id="url"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            placeholder="https://example.com/webhook"
                            required
                        >
                        @error('url') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="mb-4">
                    <label for="secret" class="block text-sm font-medium text-gray-700">Secret (Optional)</label>
                    <input 
                        type="text" 
                        wire:model="secret"
                        id="secret"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        placeholder="Optional secret for signature verification"
                    >
                    @error('secret') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Event Types</label>
                    <div class="mt-2 space-y-2">
                        @foreach($availableEvents as $event)
                            <label class="inline-flex items-center mr-6">
                                <input 
                                    type="checkbox" 
                                    wire:model="eventTypes"
                                    value="{{ $event }}"
                                    class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                >
                                <span class="ml-2 text-sm text-gray-900">{{ $event }}</span>
                            </label>
                        @endforeach
                    </div>
                    @error('eventTypes') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div class="mb-6">
                    <label class="inline-flex items-center">
                        <input 
                            type="checkbox" 
                            wire:model="isActive"
                            class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        >
                        <span class="ml-2 text-sm text-gray-900">Active</span>
                    </label>
                </div>

                <div class="flex space-x-3">
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        Create Endpoint
                    </button>
                    <button type="button" wire:click="toggleCreateForm" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    @endif

    <!-- Search -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <div class="max-w-md">
            <label for="search" class="block text-sm font-medium text-gray-700">Search</label>
            <input 
                type="text" 
                wire:model.live.debounce.300ms="search"
                id="search"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                placeholder="Search endpoints..."
            >
        </div>
    </div>

    <!-- Endpoints Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">URL</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Events</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Failures</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($endpoints as $endpoint)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $endpoint->name }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900 truncate max-w-xs">{{ $endpoint->url }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex flex-wrap gap-1">
                                    @foreach($endpoint->event_types as $event)
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                            {{ $event }}
                                        </span>
                                    @endforeach
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $endpoint->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                    {{ $endpoint->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $endpoint->failure_count }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                <button 
                                    wire:click="toggleEndpoint({{ $endpoint->id }})"
                                    class="text-blue-600 hover:text-blue-900"
                                >
                                    {{ $endpoint->is_active ? 'Disable' : 'Enable' }}
                                </button>
                                <button 
                                    wire:click="deleteEndpoint({{ $endpoint->id }})"
                                    onclick="return confirm('Are you sure you want to delete this endpoint?')"
                                    class="text-red-600 hover:text-red-900"
                                >
                                    Delete
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                No webhook endpoints found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($endpoints->hasPages())
            <div class="px-6 py-3 border-t border-gray-200">
                {{ $endpoints->links() }}
            </div>
        @endif
    </div>
</div>