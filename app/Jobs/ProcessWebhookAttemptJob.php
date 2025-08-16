<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Enums\WebhookAttemptStatus;
use App\Models\WebhookAttempt;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessWebhookAttemptJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1; // We handle retries manually
    public int $timeout = 30;

    public function __construct(
        private WebhookAttempt $attempt
    ) {}

    public function handle(): void
    {
        // Reload the attempt to ensure we have the latest data
        $this->attempt->refresh();

        if ($this->attempt->status !== WebhookAttemptStatus::PENDING) {
            Log::debug('Webhook attempt not in pending status, skipping', [
                'attempt_id' => $this->attempt->id,
                'status' => $this->attempt->status->value,
            ]);
            return;
        }

        $endpoint = $this->attempt->endpoint;

        if (!$endpoint || !$endpoint->is_active) {
            Log::warning('Webhook endpoint inactive or deleted, discarding attempt', [
                'attempt_id' => $this->attempt->id,
                'endpoint_id' => $endpoint?->id,
            ]);
            
            $this->attempt->update([
                'status' => WebhookAttemptStatus::DISCARDED,
                'error_message' => 'Endpoint inactive or deleted',
                'responded_at' => now(),
            ]);
            return;
        }

        $this->sendWebhook();
    }

    private function sendWebhook(): void
    {
        $endpoint = $this->attempt->endpoint;
        $payload = $this->attempt->payload;

        // Add signature if endpoint has a secret
        $headers = ['Content-Type' => 'application/json'];
        if ($endpoint->secret) {
            $signature = $this->generateSignature($payload, $endpoint->secret);
            $headers[config('webhooks.signature_header', 'X-Hub-Signature-Sha256')] = $signature;
        }

        $client = new Client([
            'timeout' => config('webhooks.timeout_seconds', 8),
            'connect_timeout' => 5,
        ]);

        try {
            Log::info('Sending webhook', [
                'attempt_id' => $this->attempt->id,
                'endpoint_url' => $endpoint->url,
                'attempt_number' => $this->attempt->attempt_number,
            ]);

            $response = $client->post($endpoint->url, [
                'json' => $payload,
                'headers' => $headers,
            ]);

            $statusCode = $response->getStatusCode();
            $responseBody = $response->getBody()->getContents();

            if ($statusCode >= 200 && $statusCode < 300) {
                $this->markAsSuccess($statusCode, $responseBody);
            } else {
                $this->markAsFailedAndScheduleRetry($statusCode, $responseBody, "HTTP {$statusCode}");
            }
        } catch (GuzzleException $e) {
            $this->markAsFailedAndScheduleRetry(null, null, $e->getMessage());
        }
    }

    private function markAsSuccess(int $statusCode, string $responseBody): void
    {
        $this->attempt->markAsSuccess($statusCode, $responseBody);
        $this->attempt->endpoint->resetFailureCount();

        Log::info('Webhook delivery successful', [
            'attempt_id' => $this->attempt->id,
            'endpoint_id' => $this->attempt->endpoint->id,
            'status_code' => $statusCode,
        ]);
    }

    private function markAsFailedAndScheduleRetry(?int $statusCode, ?string $responseBody, string $errorMessage): void
    {
        $this->attempt->markAsFailed($statusCode, $responseBody, $errorMessage);
        $this->attempt->endpoint->incrementFailureCount();

        Log::warning('Webhook delivery failed', [
            'attempt_id' => $this->attempt->id,
            'endpoint_id' => $this->attempt->endpoint->id,
            'attempt_number' => $this->attempt->attempt_number,
            'status_code' => $statusCode,
            'error' => $errorMessage,
        ]);

        // Schedule retry if we haven't exceeded max attempts
        if ($this->attempt->canRetry()) {
            $this->scheduleRetry();
        } else {
            Log::warning('Webhook attempt discarded after max retries', [
                'attempt_id' => $this->attempt->id,
                'endpoint_id' => $this->attempt->endpoint->id,
                'max_attempts' => config('webhooks.max_retries', 5),
            ]);

            $this->attempt->update(['status' => WebhookAttemptStatus::DISCARDED]);
        }
    }

    private function scheduleRetry(): void
    {
        $retrySchedule = config('webhooks.retry_schedule_minutes', [1, 5, 30, 120, 360]);
        $attemptIndex = $this->attempt->attempt_number - 1; // 0-based index

        if ($attemptIndex < count($retrySchedule)) {
            $delayMinutes = $retrySchedule[$attemptIndex];
            $nextRetryAt = now()->addMinutes($delayMinutes);

            // Create a new attempt for the retry
            $newAttempt = WebhookAttempt::create([
                'webhook_endpoint_id' => $this->attempt->webhook_endpoint_id,
                'event_name' => $this->attempt->event_name,
                'payload' => $this->attempt->payload,
                'attempt_number' => $this->attempt->attempt_number + 1,
                'status' => WebhookAttemptStatus::PENDING,
                'dispatched_at' => $nextRetryAt,
                'next_retry_at' => $nextRetryAt,
            ]);

            // Schedule the retry job
            ProcessWebhookAttemptJob::dispatch($newAttempt)->delay($nextRetryAt);

            Log::info('Webhook retry scheduled', [
                'original_attempt_id' => $this->attempt->id,
                'new_attempt_id' => $newAttempt->id,
                'retry_at' => $nextRetryAt->toISOString(),
                'delay_minutes' => $delayMinutes,
            ]);
        }
    }

    private function generateSignature(array $payload, string $secret): string
    {
        $algorithm = config('webhooks.signature_algorithm', 'sha256');
        $body = json_encode($payload);
        
        return $algorithm . '=' . hash_hmac($algorithm, $body, $secret);
    }
}