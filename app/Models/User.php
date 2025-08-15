<?php

declare(strict_types=1);

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\UserStatus;
use App\Enums\UserType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, HasRoles, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'user_type',
        'status',
        'last_login_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Set user_type attribute to lowercase
     */
    public function setUserTypeAttribute(?string $value): void
    {
        $this->attributes['user_type'] = $value ? strtolower($value) : null;
    }

    /**
     * Set status attribute to lowercase
     */
    public function setStatusAttribute(?string $value): void
    {
        $this->attributes['status'] = $value ? strtolower($value) : null;
    }

    /**
     * Get user_type as enum
     */
    public function getUserTypeEnumAttribute(): ?UserType
    {
        return $this->user_type ? UserType::tryFrom($this->user_type) : null;
    }

    /**
     * Get status as enum
     */
    public function getStatusEnumAttribute(): ?UserStatus
    {
        return $this->status ? UserStatus::tryFrom($this->status) : null;
    }

    /**
     * Scope by status
     */
    public function scopeStatus($query, $status)
    {
        if (is_array($status)) {
            return $query->whereIn('status', $status);
        }
        
        return $query->where('status', $status);
    }

    /**
     * Scope by user type
     */
    public function scopeType($query, $type)
    {
        if (is_array($type)) {
            return $query->whereIn('user_type', $type);
        }
        
        return $query->where('user_type', $type);
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn ($word) => Str::substr($word, 0, 1))
            ->implode('');
    }

    /**
     * Get orders for this user
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Get payments for this user
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Get notifications for this user
     */
    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    /**
     * Get hotspot users owned by this user
     */
    public function hotspotUsers(): HasMany
    {
        return $this->hasMany(HotspotUser::class, 'owner_id');
    }
}
