<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Webhooks;

use App\Models\WebhookEndpoint;
use Livewire\Component;
use Livewire\WithPagination;

class WebhookEndpointsList extends Component
{
    use WithPagination;

    public string $search = '';
    public bool $showCreateForm = false;
    
    // Form fields
    public string $name = '';
    public string $url = '';
    public string $secret = '';
    public array $eventTypes = [];
    public bool $isActive = true;

    protected $queryString = [
        'search' => ['except' => ''],
    ];

    protected array $availableEvents = [
        'PaymentSucceeded',
        'HotspotUserProvisioned', 
        'OrderCompleted',
        'ExportCompleted',
        'IncidentStatusChanged',
    ];

    public function mount(): void
    {
        abort_unless(auth()->user()->can('manage', WebhookEndpoint::class), 403);
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function toggleCreateForm(): void
    {
        $this->showCreateForm = !$this->showCreateForm;
        if (!$this->showCreateForm) {
            $this->resetForm();
        }
    }

    public function createEndpoint(): void
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'url' => 'required|url|max:255',
            'secret' => 'nullable|string|max:255',
            'eventTypes' => 'required|array|min:1',
            'eventTypes.*' => 'in:' . implode(',', $this->availableEvents),
        ]);

        WebhookEndpoint::create([
            'name' => $this->name,
            'url' => $this->url,
            'secret' => $this->secret ?: null,
            'event_types' => $this->eventTypes,
            'is_active' => $this->isActive,
            'created_by' => auth()->id(),
        ]);

        $this->resetForm();
        $this->showCreateForm = false;
        session()->flash('message', 'Webhook endpoint created successfully.');
    }

    public function toggleEndpoint(WebhookEndpoint $endpoint): void
    {
        $endpoint->update(['is_active' => !$endpoint->is_active]);
        session()->flash('message', 'Webhook endpoint status updated.');
    }

    public function deleteEndpoint(WebhookEndpoint $endpoint): void
    {
        $endpoint->delete();
        session()->flash('message', 'Webhook endpoint deleted successfully.');
    }

    private function resetForm(): void
    {
        $this->name = '';
        $this->url = '';
        $this->secret = '';
        $this->eventTypes = [];
        $this->isActive = true;
    }

    public function getEndpointsProperty()
    {
        return WebhookEndpoint::with(['creator'])
            ->when($this->search, function ($query) {
                return $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('url', 'like', '%' . $this->search . '%');
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(15);
    }

    public function render()
    {
        return view('livewire.admin.webhooks.webhook-endpoints-list', [
            'endpoints' => $this->endpoints,
            'availableEvents' => $this->availableEvents,
        ])->layout('layouts.admin');
    }
}