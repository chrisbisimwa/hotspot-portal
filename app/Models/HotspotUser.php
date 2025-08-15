<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\HotspotUserStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class HotspotUser extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'username',
        'password',
        'user_profile_id',
        'owner_id',
        'status',
        'mikrotik_id',
        'validity_minutes',
        'data_limit_mb',
        'expired_at',
        'last_login_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'validity_minutes' => 'integer',
            'data_limit_mb' => 'integer',
            'expired_at' => 'datetime',
            'last_login_at' => 'datetime',
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
    public function getStatusEnumAttribute(): ?HotspotUserStatus
    {
        return $this->status ? HotspotUserStatus::tryFrom($this->status) : null;
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
     * Get the user profile for this hotspot user
     */
    public function userProfile(): BelongsTo
    {
        return $this->belongsTo(UserProfile::class);
    }

    /**
     * Get the owner of this hotspot user
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * Get sessions for this hotspot user
     */
    public function hotspotSessions(): HasMany
    {
        return $this->hasMany(HotspotSession::class);
    }
}
