<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Domain\Monitoring\Services\MetricsService;
use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;

class AdminMetricsController extends Controller
{
    use ApiResponse;

    private MetricsService $metricsService;

    public function __construct(MetricsService $metricsService)
    {
        $this->metricsService = $metricsService;
    }

    /**
     * Get system metrics (admin only)
     */
    public function index(): JsonResponse
    {
        return $this->success([
            'global' => $this->metricsService->global(),
            'system' => $this->metricsService->system(),
            'interfaces' => $this->metricsService->interfacesLoad(),
            'timestamp' => now()->toISOString(),
        ]);
    }
}