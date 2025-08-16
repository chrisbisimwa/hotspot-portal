<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FeatureFlag extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'enabled',
        'meta',
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'meta' => 'array',
    ];

    /**
     * Check if a feature is enabled by key
     */
    public static function isEnabled(string $key): bool
    {
        $flag = static::where('key', $key)->first();
        
        return $flag ? $flag->enabled : false;
    }

    /**
     * Enable a feature flag
     */
    public static function enable(string $key, array $meta = []): self
    {
        return static::updateOrCreate(
            ['key' => $key],
            ['enabled' => true, 'meta' => $meta]
        );
    }

    /**
     * Disable a feature flag
     */
    public static function disable(string $key): self
    {
        return static::updateOrCreate(
            ['key' => $key],
            ['enabled' => false]
        );
    }

    /**
     * Get all enabled feature flags as a key-value array
     */
    public static function getAllEnabled(): array
    {
        return static::where('enabled', true)
            ->pluck('enabled', 'key')
            ->toArray();
    }
}
