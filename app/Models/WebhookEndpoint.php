<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WebhookEndpoint extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'url',
        'secret',
        'is_active',
        'event_types',
        'failure_count',
        'last_failed_at',
        'last_triggered_at',
        'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'event_types' => 'array',
        'failure_count' => 'integer',
        'last_failed_at' => 'datetime',
        'last_triggered_at' => 'datetime',
    ];

    public function attempts(): HasMany
    {
        return $this->hasMany(WebhookAttempt::class)->orderByDesc('created_at');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function supportsEvent(string $eventName): bool
    {
        return in_array($eventName, $this->event_types);
    }

    public function incrementFailureCount(): void
    {
        $this->increment('failure_count');
        $this->update(['last_failed_at' => now()]);
    }

    public function resetFailureCount(): void
    {
        $this->update([
            'failure_count' => 0,
            'last_triggered_at' => now(),
        ]);
    }
}