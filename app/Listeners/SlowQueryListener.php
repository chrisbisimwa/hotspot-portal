<?php

declare(strict_types=1);

namespace App\Listeners;

use Illuminate\Database\Events\QueryExecuted;
use App\Support\StructuredLog;

class SlowQueryListener
{
    /**
     * Handle the database query executed event.
     */
    public function handle(QueryExecuted $event): void
    {
        $threshold = (float) env('DB_SLOW_MS', 120); // Default 120ms
        
        if ($event->time >= $threshold) {
            StructuredLog::slowQuery(
                $event->sql,
                $event->time,
                $event->bindings
            );
            
            // Also increment a metric if available
            // TODO: Increment slow query counter metric
        }
    }
}