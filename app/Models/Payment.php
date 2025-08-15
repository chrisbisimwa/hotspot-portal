<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'order_id',
        'user_id',
        'provider',
        'status',
        'transaction_ref',
        'internal_ref',
        'amount',
        'currency',
        'fee_amount',
        'net_amount',
        'paid_at',
        'confirmed_at',
        'refunded_at',
        'raw_request',
        'raw_response',
        'callback_payload',
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
            'amount' => 'decimal:2',
            'fee_amount' => 'decimal:2',
            'net_amount' => 'decimal:2',
            'paid_at' => 'datetime',
            'confirmed_at' => 'datetime',
            'refunded_at' => 'datetime',
            'raw_request' => 'array',
            'raw_response' => 'array',
            'callback_payload' => 'array',
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
    public function getStatusEnumAttribute(): ?PaymentStatus
    {
        return $this->status ? PaymentStatus::tryFrom($this->status) : null;
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
     * Scope by provider
     */
    public function scopeWithProvider($query, string $provider)
    {
        return $query->where('provider', $provider);
    }

    /**
     * Scope recent payments
     */
    public function scopeRecent($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    /**
     * Get the order for this payment
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the user for this payment
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
