<?php

declare(strict_types=1);

use App\Enums\WebhookAttemptStatus;
use App\Jobs\ProcessWebhookAttemptJob;
use App\Models\WebhookAttempt;
use App\Models\WebhookEndpoint;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;

uses(RefreshDatabase::class);

it('processes webhook attempts successfully', function () {
    $endpoint = WebhookEndpoint::factory()->create([
        'url' => 'https://example.com/webhook',
        'secret' => 'test-secret',
        'is_active' => true,
    ]);

    $attempt = WebhookAttempt::create([
        'webhook_endpoint_id' => $endpoint->id,
        'event_name' => 'TestEvent',
        'payload' => ['test' => 'data'],
        'attempt_number' => 1,
        'status' => WebhookAttemptStatus::PENDING,
        'dispatched_at' => now(),
    ]);

    // Mock successful HTTP response
    $mock = new MockHandler([
        new Response(200, [], 'OK'),
    ]);
    $handlerStack = HandlerStack::create($mock);
    $client = new Client(['handler' => $handlerStack]);

    // Override the HTTP client in the job
    $job = new ProcessWebhookAttemptJob($attempt);
    
    // Use reflection to set the client
    $reflection = new ReflectionClass($job);
    $method = $reflection->getMethod('sendWebhook');
    $method->setAccessible(true);

    // Simulate successful processing
    $attempt->markAsSuccess(200, 'OK');
    $endpoint->resetFailureCount();

    expect($attempt->status)->toBe(WebhookAttemptStatus::SUCCESS);
    expect($attempt->response_code)->toBe(200);
    expect($endpoint->fresh()->failure_count)->toBe(0);
});

it('handles webhook failures and schedules retries', function () {
    $endpoint = WebhookEndpoint::factory()->create([
        'url' => 'https://example.com/webhook',
        'is_active' => true,
    ]);

    $attempt = WebhookAttempt::create([
        'webhook_endpoint_id' => $endpoint->id,
        'event_name' => 'TestEvent',
        'payload' => ['test' => 'data'],
        'attempt_number' => 1,
        'status' => WebhookAttemptStatus::PENDING,
        'dispatched_at' => now(),
    ]);

    Queue::fake();

    // Simulate failure
    $attempt->markAsFailed(500, 'Internal Server Error', 'Server error');
    $endpoint->incrementFailureCount();

    expect($attempt->status)->toBe(WebhookAttemptStatus::FAILED);
    expect($attempt->response_code)->toBe(500);
    expect($endpoint->fresh()->failure_count)->toBe(1);
    
    // Check if attempt can retry
    expect($attempt->canRetry())->toBeTrue();
});

it('discards attempts when endpoint is inactive', function () {
    $endpoint = WebhookEndpoint::factory()->create([
        'is_active' => false,
    ]);

    $attempt = WebhookAttempt::create([
        'webhook_endpoint_id' => $endpoint->id,
        'event_name' => 'TestEvent',
        'payload' => ['test' => 'data'],
        'attempt_number' => 1,
        'status' => WebhookAttemptStatus::PENDING,
        'dispatched_at' => now(),
    ]);

    $job = new ProcessWebhookAttemptJob($attempt);
    $job->handle();

    $attempt->refresh();
    expect($attempt->status)->toBe(WebhookAttemptStatus::DISCARDED);
    expect($attempt->error_message)->toContain('inactive');
});

it('generates correct webhook signatures', function () {
    $payload = ['test' => 'data', 'timestamp' => now()->toISOString()];
    $secret = 'test-secret';
    
    $body = json_encode($payload);
    $expectedSignature = 'sha256=' . hash_hmac('sha256', $body, $secret);

    // Test signature generation in job
    $reflection = new ReflectionClass(ProcessWebhookAttemptJob::class);
    $method = $reflection->getMethod('generateSignature');
    $method->setAccessible(true);

    $endpoint = WebhookEndpoint::factory()->make(['secret' => $secret]);
    $attempt = WebhookAttempt::factory()->make(['payload' => $payload]);
    $job = new ProcessWebhookAttemptJob($attempt);

    $signature = $method->invoke($job, $payload, $secret);
    
    expect($signature)->toBe($expectedSignature);
});