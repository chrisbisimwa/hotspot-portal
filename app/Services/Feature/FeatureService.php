<?php

declare(strict_types=1);

namespace App\Services\Feature;

use App\Models\FeatureFlag;
use Illuminate\Support\Facades\Cache;

class FeatureService
{
    /**
     * Check if a feature is enabled
     */
    public function enabled(string $key): bool
    {
        $ttl = config('cache_extensions.ttl.feature_flags', 60);
        
        return Cache::remember(
            "feature_flag:{$key}",
            $ttl,
            fn () => FeatureFlag::isEnabled($key)
        );
    }

    /**
     * Enable a feature flag
     */
    public function enable(string $key, array $meta = []): FeatureFlag
    {
        $flag = FeatureFlag::enable($key, $meta);
        
        // Clear cache
        $this->clearCache($key);
        
        return $flag;
    }

    /**
     * Disable a feature flag
     */
    public function disable(string $key): FeatureFlag
    {
        $flag = FeatureFlag::disable($key);
        
        // Clear cache
        $this->clearCache($key);
        
        return $flag;
    }

    /**
     * Get all feature flags
     */
    public function all(): array
    {
        $ttl = config('cache_extensions.ttl.feature_flags', 60);
        
        return Cache::remember(
            'feature_flags_all',
            $ttl,
            fn () => FeatureFlag::all()->keyBy('key')->toArray()
        );
    }

    /**
     * Get all enabled feature flags
     */
    public function getAllEnabled(): array
    {
        $ttl = config('cache_extensions.ttl.feature_flags', 60);
        
        return Cache::remember(
            'feature_flags_enabled',
            $ttl,
            fn () => FeatureFlag::getAllEnabled()
        );
    }

    /**
     * Clear cache for a specific feature flag
     */
    private function clearCache(?string $key = null): void
    {
        if ($key) {
            Cache::forget("feature_flag:{$key}");
        }
        
        // Clear all feature flag caches
        Cache::forget('feature_flags_all');
        Cache::forget('feature_flags_enabled');
    }

    /**
     * Bulk enable/disable feature flags
     */
    public function setBulk(array $flags): void
    {
        foreach ($flags as $key => $enabled) {
            if ($enabled) {
                FeatureFlag::enable($key);
            } else {
                FeatureFlag::disable($key);
            }
        }
        
        // Clear all cache
        $this->clearCache();
    }
}