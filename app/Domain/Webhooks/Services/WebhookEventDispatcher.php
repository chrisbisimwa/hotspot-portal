<?php

declare(strict_types=1);

namespace App\Domain\Webhooks\Services;

use App\Enums\WebhookAttemptStatus;
use App\Jobs\ProcessWebhookAttemptJob;
use App\Models\WebhookAttempt;
use App\Models\WebhookEndpoint;
use Illuminate\Support\Facades\Log;

class WebhookEventDispatcher
{
    public function handle(string $eventName, array $payloadArray): void
    {
        $endpoints = $this->getActiveEndpointsForEvent($eventName);

        if ($endpoints->isEmpty()) {
            Log::debug('No active webhook endpoints for event', [
                'event_name' => $eventName,
            ]);
            return;
        }

        Log::info('Dispatching webhook event to endpoints', [
            'event_name' => $eventName,
            'endpoint_count' => $endpoints->count(),
        ]);

        foreach ($endpoints as $endpoint) {
            $this->createWebhookAttempt($endpoint, $eventName, $payloadArray);
        }
    }

    private function getActiveEndpointsForEvent(string $eventName): \Illuminate\Database\Eloquent\Collection
    {
        return WebhookEndpoint::where('is_active', true)
            ->whereJsonContains('event_types', $eventName)
            ->get();
    }

    private function createWebhookAttempt(WebhookEndpoint $endpoint, string $eventName, array $payloadArray): void
    {
        $filteredPayload = $this->filterSensitiveData($payloadArray);

        $attempt = WebhookAttempt::create([
            'webhook_endpoint_id' => $endpoint->id,
            'event_name' => $eventName,
            'payload' => [
                'event' => $eventName,
                'data' => $filteredPayload,
                'timestamp' => now()->toISOString(),
            ],
            'attempt_number' => 1,
            'status' => WebhookAttemptStatus::PENDING,
            'dispatched_at' => now(),
        ]);

        // Dispatch the job to process the webhook
        ProcessWebhookAttemptJob::dispatch($attempt);

        Log::debug('Webhook attempt created', [
            'attempt_id' => $attempt->id,
            'endpoint_id' => $endpoint->id,
            'event_name' => $eventName,
        ]);
    }

    private function filterSensitiveData(array $data): array
    {
        $filteredKeys = config('webhooks.filtered_keys', [
            'password',
            'secret',
            'token',
            'api_key',
            'private_key',
        ]);

        return $this->recursiveFilter($data, $filteredKeys);
    }

    private function recursiveFilter(array $data, array $filteredKeys): array
    {
        $filtered = [];

        foreach ($data as $key => $value) {
            if (in_array(strtolower($key), array_map('strtolower', $filteredKeys))) {
                $filtered[$key] = '[FILTERED]';
            } elseif (is_array($value)) {
                $filtered[$key] = $this->recursiveFilter($value, $filteredKeys);
            } else {
                $filtered[$key] = $value;
            }
        }

        return $filtered;
    }
}