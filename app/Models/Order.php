<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'user_profile_id',
        'quantity',
        'unit_price',
        'total_amount',
        'status',
        'payment_reference',
        'requested_at',
        'paid_at',
        'completed_at',
        'cancelled_at',
        'expires_at',
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
            'quantity' => 'integer',
            'unit_price' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'requested_at' => 'datetime',
            'paid_at' => 'datetime',
            'completed_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'expires_at' => 'datetime',
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
    public function getStatusEnumAttribute(): ?OrderStatus
    {
        return $this->status ? OrderStatus::tryFrom($this->status) : null;
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
     * Get the user for this order
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the user profile for this order
     */
    public function userProfile(): BelongsTo
    {
        return $this->belongsTo(UserProfile::class);
    }

    /**
     * Get payments for this order
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Get notifications for this order
     */
    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }
}
