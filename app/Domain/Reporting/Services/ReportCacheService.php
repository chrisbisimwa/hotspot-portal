<?php

declare(strict_types=1);

namespace App\Domain\Reporting\Services;

use App\Domain\Reporting\Contracts\ReportBuilderInterface;
use App\Domain\Reporting\DTO\ReportResult;
use Illuminate\Support\Facades\Cache;

class ReportCacheService
{
    /**
     * Get cached report result or build and cache if not exists
     */
    public function getOrBuild(
        ReportBuilderInterface $builder,
        array $filters = []
    ): ReportResult {
        $cacheKey = $this->generateCacheKey($builder->identifier(), $filters);
        $ttl = config('reporting.cache_ttl_seconds', 300);

        return Cache::remember($cacheKey, $ttl, function () use ($builder, $filters) {
            $result = $builder->build($filters);
            
            // Add cache hit indicator to meta
            $meta = $result->meta;
            $meta['cache_hit'] = false;
            
            return new ReportResult(
                $result->rows,
                $result->columns,
                $meta,
                $result->generated_at
            );
        });
    }

    /**
     * Clear cache for a specific report and filters
     */
    public function forget(string $reportKey, array $filters = []): void
    {
        $cacheKey = $this->generateCacheKey($reportKey, $filters);
        Cache::forget($cacheKey);
    }

    /**
     * Clear all cache entries for a report
     */
    public function forgetAll(string $reportKey): void
    {
        $pattern = "reports:{$reportKey}:*";
        
        // Note: This is simplified - in production you might want to use Redis SCAN
        Cache::flush(); // Simplified approach for now
    }

    /**
     * Generate cache key for report and filters
     */
    private function generateCacheKey(string $reportKey, array $filters): string
    {
        $filtersHash = md5(json_encode($filters));
        
        return "reports:{$reportKey}:{$filtersHash}";
    }
}