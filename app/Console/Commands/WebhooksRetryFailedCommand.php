<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\WebhookAttemptStatus;
use App\Jobs\ProcessWebhookAttemptJob;
use App\Models\WebhookAttempt;
use Illuminate\Console\Command;

class WebhooksRetryFailedCommand extends Command
{
    protected $signature = 'webhooks:retry-failed 
                            {--limit=50 : Maximum number of attempts to retry}
                            {--endpoint= : Specific endpoint ID to retry}
                            {--event= : Specific event name to retry}';

    protected $description = 'Retry failed webhook attempts that haven\'t exceeded maximum retries';

    public function handle(): int
    {
        $limit = (int) $this->option('limit');
        $endpointId = $this->option('endpoint');
        $eventName = $this->option('event');

        $query = WebhookAttempt::where('status', WebhookAttemptStatus::FAILED)
            ->where('attempt_number', '<', config('webhooks.max_retries', 5))
            ->orderBy('created_at');

        if ($endpointId) {
            $query->where('webhook_endpoint_id', $endpointId);
        }

        if ($eventName) {
            $query->where('event_name', $eventName);
        }

        $failedAttempts = $query->limit($limit)->get();

        if ($failedAttempts->isEmpty()) {
            $this->info('No failed webhook attempts found to retry.');
            return self::SUCCESS;
        }

        $this->info("Found {$failedAttempts->count()} failed attempts to retry.");

        $retried = 0;
        foreach ($failedAttempts as $attempt) {
            if (!$attempt->endpoint || !$attempt->endpoint->is_active) {
                $this->warn("Skipping attempt {$attempt->id} - endpoint inactive or deleted");
                continue;
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
            
            $retried++;
            $this->line("Queued retry for attempt {$attempt->id} -> new attempt {$newAttempt->id}");
        }

        $this->info("Successfully queued {$retried} webhook retries.");

        return self::SUCCESS;
    }
}