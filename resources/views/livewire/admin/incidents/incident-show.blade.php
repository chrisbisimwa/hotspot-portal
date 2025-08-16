<div class="p-6">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">{{ $incident->title }}</h1>
            <p class="text-gray-600 mt-1">{{ $incident->slug }}</p>
        </div>
        <a href="{{ route('admin.incidents.index') }}" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
            Back to Incidents
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

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Incident Details -->
        <div class="lg:col-span-2">
            <!-- Basic Info -->
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Incident Details</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-600">Status</label>
                        <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full mt-1
                            @if($incident->status->value === 'open') bg-red-100 text-red-800
                            @elseif($incident->status->value === 'monitoring') bg-yellow-100 text-yellow-800
                            @elseif($incident->status->value === 'mitigated') bg-blue-100 text-blue-800
                            @elseif($incident->status->value === 'resolved') bg-green-100 text-green-800
                            @else bg-gray-100 text-gray-800
                            @endif">
                            {{ $incident->status->label() }}
                        </span>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-600">Severity</label>
                        <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full mt-1
                            @if($incident->severity->value === 'critical') bg-red-100 text-red-800
                            @elseif($incident->severity->value === 'high') bg-orange-100 text-orange-800
                            @elseif($incident->severity->value === 'medium') bg-yellow-100 text-yellow-800
                            @else bg-blue-100 text-blue-800
                            @endif">
                            {{ $incident->severity->label() }}
                        </span>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-600">Started At</label>
                        <p class="text-gray-900 mt-1">{{ $incident->started_at->format('M j, Y H:i T') }}</p>
                    </div>

                    @if($incident->detected_at)
                    <div>
                        <label class="block text-sm font-medium text-gray-600">Detected At</label>
                        <p class="text-gray-900 mt-1">{{ $incident->detected_at->format('M j, Y H:i T') }}</p>
                    </div>
                    @endif

                    @if($incident->mitigated_at)
                    <div>
                        <label class="block text-sm font-medium text-gray-600">Mitigated At</label>
                        <p class="text-gray-900 mt-1">{{ $incident->mitigated_at->format('M j, Y H:i T') }}</p>
                    </div>
                    @endif

                    @if($incident->resolved_at)
                    <div>
                        <label class="block text-sm font-medium text-gray-600">Resolved At</label>
                        <p class="text-gray-900 mt-1">{{ $incident->resolved_at->format('M j, Y H:i T') }}</p>
                    </div>
                    @endif

                    @if($incident->detection_source)
                    <div>
                        <label class="block text-sm font-medium text-gray-600">Detection Source</label>
                        <p class="text-gray-900 mt-1">{{ $incident->detection_source }}</p>
                    </div>
                    @endif
                </div>

                @if($incident->summary)
                <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-600">Summary</label>
                    <p class="text-gray-900 mt-1">{{ $incident->summary }}</p>
                </div>
                @endif

                @if($incident->root_cause)
                <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-600">Root Cause</label>
                    <p class="text-gray-900 mt-1">{{ $incident->root_cause }}</p>
                </div>
                @endif

                @if($incident->impact)
                <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-600">Impact</label>
                    <p class="text-gray-900 mt-1">{{ $incident->impact }}</p>
                </div>
                @endif
            </div>

            <!-- Updates Timeline -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Timeline</h3>
                
                <div class="space-y-4">
                    @forelse($incident->updates as $update)
                        <div class="flex space-x-3">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                    <svg class="w-4 h-4 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="flex-1">
                                <div class="text-sm text-gray-900">{{ $update->message }}</div>
                                <div class="text-xs text-gray-500 mt-1">
                                    {{ $update->created_at->format('M j, Y H:i T') }}
                                    @if($update->user)
                                        by {{ $update->user->name }}
                                    @else
                                        (System)
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="text-gray-500">No updates yet.</p>
                    @endforelse
                </div>

                <!-- Add Update Form -->
                <form wire:submit.prevent="addUpdate" class="mt-6 pt-6 border-t border-gray-200">
                    <div class="mb-4">
                        <label for="newUpdate" class="block text-sm font-medium text-gray-700">Add Update</label>
                        <textarea 
                            wire:model="newUpdate"
                            id="newUpdate"
                            rows="3"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            placeholder="Add a new update to this incident..."
                        ></textarea>
                        @error('newUpdate') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        Add Update
                    </button>
                </form>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Status Update -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Update Status</h3>
                
                <form wire:submit.prevent="updateStatus">
                    <div class="mb-4">
                        <label for="selectedStatus" class="block text-sm font-medium text-gray-700">New Status</label>
                        <select 
                            wire:model="selectedStatus"
                            id="selectedStatus"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        >
                            @foreach($statusOptions as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('selectedStatus') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                    <button type="submit" class="w-full px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700">
                        Update Status
                    </button>
                </form>
            </div>

            <!-- Metadata -->
            @if($incident->meta)
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Metadata</h3>
                <div class="space-y-2">
                    @foreach($incident->meta as $key => $value)
                        <div>
                            <span class="text-sm font-medium text-gray-600">{{ ucfirst(str_replace('_', ' ', $key)) }}:</span>
                            <span class="text-sm text-gray-900 ml-2">
                                @if(is_array($value))
                                    {{ json_encode($value) }}
                                @else
                                    {{ $value }}
                                @endif
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>
</div>