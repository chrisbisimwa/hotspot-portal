<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Webhooks;

use App\Enums\WebhookAttemptStatus;
use App\Jobs\ProcessWebhookAttemptJob;
use App\Models\WebhookAttempt;
use App\Models\WebhookEndpoint;
use Livewire\Component;
use Livewire\WithPagination;

class WebhookAttemptsList extends Component
{
    use WithPagination;

    public string $search = '';
    public string $statusFilter = '';
    public string $endpointFilter = '';
    public string $eventFilter = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => ''],
        'endpointFilter' => ['except' => ''],
        'eventFilter' => ['except' => ''],
    ];

    public function mount(): void
    {
        abort_unless(auth()->user()->can('manage', WebhookEndpoint::class), 403);
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatingEndpointFilter(): void
    {
        $this->resetPage();
    }

    public function updatingEventFilter(): void
    {
        $this->resetPage();
    }

    public function retryAttempt(WebhookAttempt $attempt): void
    {
        if (!$attempt->endpoint || !$attempt->endpoint->is_active) {
            session()->flash('error', 'Cannot retry - endpoint is inactive or deleted.');
            return;
        }

        // Create a new retry attempt
        $newAttempt = WebhookAttempt::create([
            'webhook_endpoint_id' => $attempt->webhook_endpoint_id,
            'event_name' => $attempt->event_name,
            'payload' => $attempt->payload,
            'attempt_number' => $attempt->attempt_number + 1,
            'status' => WebhookAttemptStatus::PENDING,
            'dispatched_at' => now(),
        ]);

        ProcessWebhookAttemptJob::dispatch($newAttempt);
        
        session()->flash('message', 'Webhook attempt queued for retry.');
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->statusFilter = '';
        $this->endpointFilter = '';
        $this->eventFilter = '';
        $this->resetPage();
    }

    public function getAttemptsProperty()
    {
        return WebhookAttempt::with(['endpoint'])
            ->when($this->search, function ($query) {
                return $query->where(function ($q) {
                    $q->where('event_name', 'like', '%' . $this->search . '%')
                      ->orWhere('error_message', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->statusFilter, function ($query) {
                return $query->where('status', $this->statusFilter);
            })
            ->when($this->endpointFilter, function ($query) {
                return $query->where('webhook_endpoint_id', $this->endpointFilter);
            })
            ->when($this->eventFilter, function ($query) {
                return $query->where('event_name', $this->eventFilter);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20);
    }

    public function getEndpointsProperty()
    {
        return WebhookEndpoint::orderBy('name')->get();
    }

    public function getStatusOptionsProperty(): array
    {
        return collect(WebhookAttemptStatus::cases())
            ->mapWithKeys(fn($status) => [$status->value => $status->label()])
            ->toArray();
    }

    public function getUniqueEventsProperty(): array
    {
        return WebhookAttempt::distinct()->pluck('event_name')->sort()->toArray();
    }

    public function render()
    {
        return view('livewire.admin.webhooks.webhook-attempts-list', [
            'attempts' => $this->attempts,
            'endpoints' => $this->endpoints,
            'statusOptions' => $this->statusOptions,
            'uniqueEvents' => $this->uniqueEvents,
        ])->layout('layouts.admin');
    }
}