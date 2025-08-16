<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\WebhookAttemptStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WebhookAttempt extends Model
{
    use HasFactory;

    protected $fillable = [
        'webhook_endpoint_id',
        'event_name',
        'payload',
        'response_code',
        'response_body',
        'attempt_number',
        'status',
        'error_message',
        'dispatched_at',
        'responded_at',
        'next_retry_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'status' => WebhookAttemptStatus::class,
        'dispatched_at' => 'datetime',
        'responded_at' => 'datetime',
        'next_retry_at' => 'datetime',
    ];

    public function endpoint(): BelongsTo
    {
        return $this->belongsTo(WebhookEndpoint::class, 'webhook_endpoint_id');
    }

    public function isSuccessful(): bool
    {
        return $this->status === WebhookAttemptStatus::SUCCESS;
    }

    public function hasFailed(): bool
    {
        return $this->status === WebhookAttemptStatus::FAILED;
    }

    public function canRetry(): bool
    {
        return $this->status === WebhookAttemptStatus::FAILED && $this->attempt_number < 5;
    }

    public function markAsSuccess(int $responseCode, ?string $responseBody = null): void
    {
        $this->update([
            'status' => WebhookAttemptStatus::SUCCESS,
            'response_code' => $responseCode,
            'response_body' => $responseBody,
            'responded_at' => now(),
            'next_retry_at' => null,
        ]);
    }

    public function markAsFailed(int $responseCode, ?string $responseBody = null, ?string $errorMessage = null): void
    {
        $this->update([
            'status' => WebhookAttemptStatus::FAILED,
            'response_code' => $responseCode,
            'response_body' => $responseBody,
            'error_message' => $errorMessage,
            'responded_at' => now(),
        ]);
    }
}