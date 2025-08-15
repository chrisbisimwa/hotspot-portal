<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\NotificationStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Notification extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'order_id',
        'hotspot_user_id',
        'channel',
        'to',
        'subject',
        'message',
        'status',
        'sent_at',
        'provider_response',
        'meta',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'sent_at' => 'datetime',
            'provider_response' => 'array',
            'meta' => 'array',
        ];
    }

    /**
     * Set status attribute to lowercase
     */
    public function setStatusAttribute(?string $value): void
    {
        $this->attributes['status'] = $value ? strtolower($value) : null;
    }

    /**
     * Get status as enum
     */
    public function getStatusEnumAttribute(): ?NotificationStatus
    {
        return $this->status ? NotificationStatus::tryFrom($this->status) : null;
    }

    /**
     * Scope by status
     */
    public function scopeWithStatus($query, $statusOrArray)
    {
        if (is_array($statusOrArray)) {
            return $query->whereIn('status', $statusOrArray);
        }

        return $query->where('status', $statusOrArray);
    }

    /**
     * Get the user for this notification
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the order for this notification
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the hotspot user for this notification
     */
    public function hotspotUser(): BelongsTo
    {
        return $this->belongsTo(HotspotUser::class);
    }
}
