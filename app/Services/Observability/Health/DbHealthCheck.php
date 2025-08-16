<?php

declare(strict_types=1);

namespace App\Services\Observability\Health;

use Illuminate\Support\Facades\DB;

class DbHealthCheck
{
    public function check(): array
    {
        $start = microtime(true);
        
        try {
            // Simple query to test database connectivity
            DB::select('SELECT 1');
            
            $duration = round((microtime(true) - $start) * 1000, 2);
            
            return [
                'healthy' => true,
                'message' => 'Database connection is working',
                'duration_ms' => $duration,
            ];
            
        } catch (\Exception $e) {
            $duration = round((microtime(true) - $start) * 1000, 2);
            
            return [
                'healthy' => false,
                'message' => 'Database connection failed: ' . $e->getMessage(),
                'duration_ms' => $duration,
            ];
        }
    }
}