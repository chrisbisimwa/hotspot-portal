<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\Observability\HealthCheckRunner;
use Illuminate\Http\JsonResponse;

class HealthController extends Controller
{
    private HealthCheckRunner $healthRunner;

    public function __construct(HealthCheckRunner $healthRunner)
    {
        $this->healthRunner = $healthRunner;
    }

    /**
     * Liveness probe - basic application health
     */
    public function live(): JsonResponse
    {
        $result = $this->healthRunner->runLiveness();
        
        return response()->json($result, 200);
    }

    /**
     * Readiness probe - checks if app can handle traffic
     */
    public function ready(): JsonResponse
    {
        $result = $this->healthRunner->runReadiness();
        
        $statusCode = $result['status'] === 'ready' ? 200 : 503;
        
        return response()->json($result, $statusCode);
    }

    /**
     * Full health check - detailed health information
     */
    public function health(): JsonResponse
    {
        $result = $this->healthRunner->runAll();
        
        $statusCode = $result['status'] === 'healthy' ? 200 : 503;
        
        return response()->json($result, $statusCode);
    }

    /**
     * Health summary - quick overview
     */
    public function summary(): JsonResponse
    {
        $result = $this->healthRunner->getSummary();
        
        $statusCode = $result['overall_status'] === 'healthy' ? 200 : 503;
        
        return response()->json($result, $statusCode);
    }
}