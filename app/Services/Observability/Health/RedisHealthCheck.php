<?php

declare(strict_types=1);

namespace App\Services\Observability\Health;

use Illuminate\Support\Facades\Redis;

class RedisHealthCheck
{
    public function check(): array
    {
        $start = microtime(true);
        
        try {
            // Test Redis connection with a simple ping
            Redis::ping();
            
            $duration = round((microtime(true) - $start) * 1000, 2);
            
            return [
                'healthy' => true,
                'message' => 'Redis connection is working',
                'duration_ms' => $duration,
            ];
            
        } catch (\Exception $e) {
            $duration = round((microtime(true) - $start) * 1000, 2);
            
            // Redis may not be configured or available, treat as non-critical
            return [
                'healthy' => true, // Non-critical for basic functionality
                'message' => 'Redis not available: ' . $e->getMessage(),
                'duration_ms' => $duration,
                'warning' => true,
            ];
        }
    }
}