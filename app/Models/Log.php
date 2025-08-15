<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\LogLevel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Log extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'actor_id',
        'loggable_type',
        'loggable_id',
        'action',
        'level',
        'message',
        'context',
        'ip_address',
        'user_agent',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'context' => 'array',
        ];
    }

    /**
     * Set level attribute to lowercase
     */
    public function setLevelAttribute(?string $value): void
    {
        $this->attributes['level'] = $value ? strtolower($value) : null;
    }

    /**
     * Get level as enum
     */
    public function getLevelEnumAttribute(): ?LogLevel
    {
        return $this->level ? LogLevel::tryFrom($this->level) : null;
    }

    /**
     * Scope by status
     */
    public function scopeWithStatus($query, $statusOrArray)
    {
        if (is_array($statusOrArray)) {
            return $query->whereIn('level', $statusOrArray);
        }

        return $query->where('level', $statusOrArray);
    }

    /**
     * Get the polymorphic loggable model
     */
    public function loggable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the actor (user) for this log
     */
    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
