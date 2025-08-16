<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use App\Models\UserProfile;
use App\Models\FeatureFlag;
use App\Domain\Monitoring\Services\MetricsService;
use App\Support\StructuredLog;

class CacheWarmCommand extends Command
{
    protected $signature = 'cache:warm {--force : Force refresh of existing cache}';

    protected $description = 'Pre-warm application caches with frequently accessed data';

    public function handle(MetricsService $metricsService): int
    {
        if (!config('cache_extensions.warm.enabled', true)) {
            $this->info('Cache warming is disabled');
            return Command::SUCCESS;
        }

        $this->info('Starting cache warming...');
        $startTime = microtime(true);
        $warmedCount = 0;

        try {
            // Warm active user profiles
            $warmedCount += $this->warmUserProfiles();
            
            // Warm feature flags
            $warmedCount += $this->warmFeatureFlags();
            
            // Warm global metrics
            $warmedCount += $this->warmGlobalMetrics($metricsService);
            
            // Warm registry reports (if applicable)
            $warmedCount += $this->warmRegistryReports();

            $duration = round((microtime(true) - $startTime) * 1000, 2);
            
            $this->info("Cache warming completed!");
            $this->info("Items warmed: {$warmedCount}");
            $this->info("Duration: {$duration}ms");
            
            StructuredLog::info('cache_warm_completed', [
                'items_warmed' => $warmedCount,
                'duration_ms' => $duration,
                'forced' => $this->option('force'),
            ]);

            return Command::SUCCESS;
            
        } catch (\Exception $e) {
            $this->error("Cache warming failed: {$e->getMessage()}");
            
            StructuredLog::error('cache_warm_failed', [
                'error' => $e->getMessage(),
                'items_warmed' => $warmedCount,
            ]);
            
            return Command::FAILURE;
        }
    }

    /**
     * Warm user profiles cache
     */
    private function warmUserProfiles(): int
    {
        $this->info('Warming user profiles...');
        
        $ttl = config('cache_extensions.ttl.profiles_list', 300);
        $batchSize = config('cache_extensions.warm.batch_size', 50);
        $force = $this->option('force');
        
        $cacheKey = 'user_profiles_active';
        
        if (!$force && Cache::has($cacheKey)) {
            $this->info('User profiles already cached, skipping...');
            return 0;
        }

        $activeProfiles = UserProfile::where('is_active', true)
            ->with(['user'])
            ->take($batchSize)
            ->get();

        Cache::put($cacheKey, $activeProfiles, $ttl);
        
        $this->info("Cached {$activeProfiles->count()} active user profiles");
        return 1;
    }

    /**
     * Warm feature flags cache
     */
    private function warmFeatureFlags(): int
    {
        $this->info('Warming feature flags...');
        
        $ttl = config('cache_extensions.ttl.feature_flags', 60);
        $force = $this->option('force');
        
        $cacheKey = 'feature_flags_all';
        
        if (!$force && Cache::has($cacheKey)) {
            $this->info('Feature flags already cached, skipping...');
            return 0;
        }

        // Check if FeatureFlag model exists (will be created)
        if (class_exists(\App\Models\FeatureFlag::class)) {
            $flags = FeatureFlag::all()->keyBy('key');
            Cache::put($cacheKey, $flags, $ttl);
            $this->info("Cached {$flags->count()} feature flags");
            return 1;
        }
        
        $this->info('FeatureFlag model not available, skipping...');
        return 0;
    }

    /**
     * Warm global metrics cache
     */
    private function warmGlobalMetrics(MetricsService $metricsService): int
    {
        $this->info('Warming global metrics...');
        
        $ttl = config('cache_extensions.ttl.metrics_global', 60);
        $force = $this->option('force');
        
        $cacheKey = 'metrics_global_cached';
        
        if (!$force && Cache::has($cacheKey)) {
            $this->info('Global metrics already cached, skipping...');
            return 0;
        }

        $metrics = $metricsService->global();
        Cache::put($cacheKey, $metrics, $ttl);
        
        $this->info('Cached global metrics');
        return 1;
    }

    /**
     * Warm registry reports cache
     */
    private function warmRegistryReports(): int
    {
        $this->info('Warming registry reports...');
        
        $ttl = config('cache_extensions.ttl.registry_reports', 900);
        $force = $this->option('force');
        
        $cacheKey = 'registry_reports_summary';
        
        if (!$force && Cache::has($cacheKey)) {
            $this->info('Registry reports already cached, skipping...');
            return 0;
        }

        // Placeholder for registry reports caching
        $reports = ['placeholder' => 'registry_reports_will_be_implemented'];
        Cache::put($cacheKey, $reports, $ttl);
        
        $this->info('Cached registry reports summary');
        return 1;
    }
}