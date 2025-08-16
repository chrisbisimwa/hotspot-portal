<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Support\StructuredLog;
use Carbon\Carbon;

class ChaosMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only apply chaos in staging environment
        if (!config('chaos.enabled', false) || !app()->environment('staging')) {
            return $next($request);
        }
        
        // Check if chaos should be applied to this request
        if (!$this->shouldApplyChaos($request)) {
            return $next($request);
        }
        
        // Apply latency chaos
        if ($this->shouldInjectLatency()) {
            $this->injectLatency();
        }
        
        // Apply error chaos
        if ($this->shouldInjectError()) {
            return $this->injectError($request);
        }
        
        return $next($request);
    }
    
    /**
     * Determine if chaos should be applied to this request
     */
    private function shouldApplyChaos(Request $request): bool
    {
        // Check excluded routes
        $excludedRoutes = config('chaos.exclusions.routes', []);
        $currentRoute = $request->path();
        
        foreach ($excludedRoutes as $pattern) {
            if (str($currentRoute)->is($pattern)) {
                return false;
            }
        }
        
        // Check excluded user roles
        if ($user = $request->user()) {
            $userRoles = $user->getRoleNames()->toArray();
            $excludedRoles = config('chaos.exclusions.user_roles', []);
            
            if (array_intersect($userRoles, $excludedRoles)) {
                return false;
            }
        }
        
        // Check if within allowed hours
        if (!$this->isWithinAllowedHours()) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Check if current time is within allowed chaos hours
     */
    private function isWithinAllowedHours(): bool
    {
        $allowedHours = config('chaos.scheduling.enabled_hours', []);
        
        if (empty($allowedHours)) {
            return true; // If no restriction, allow all hours
        }
        
        $currentHour = Carbon::now(config('chaos.scheduling.timezone', 'UTC'))->hour;
        
        return in_array($currentHour, $allowedHours);
    }
    
    /**
     * Determine if latency should be injected
     */
    private function shouldInjectLatency(): bool
    {
        $latencyRate = config('chaos.probability.latency_rate', 0.10);
        return mt_rand() / mt_getrandmax() < $latencyRate;
    }
    
    /**
     * Determine if error should be injected
     */
    private function shouldInjectError(): bool
    {
        $errorRate = config('chaos.probability.error_rate', 0.05);
        return mt_rand() / mt_getrandmax() < $errorRate;
    }
    
    /**
     * Inject artificial latency
     */
    private function injectLatency(): void
    {
        $minMs = config('chaos.latency.min_ms', 50);
        $maxMs = config('chaos.latency.max_ms', 400);
        
        $latencyMs = mt_rand($minMs, $maxMs);
        
        // Convert to microseconds and sleep
        usleep($latencyMs * 1000);
        
        if (config('chaos.monitoring.log_events', true)) {
            StructuredLog::info('chaos_latency_injected', [
                'latency_ms' => $latencyMs,
                'chaos_type' => 'latency',
            ]);
        }
    }
    
    /**
     * Inject error response
     */
    private function injectError(Request $request): Response
    {
        $httpCodes = config('chaos.errors.http_codes', [500, 502, 503, 504]);
        $errorCode = $httpCodes[array_rand($httpCodes)];
        
        if (config('chaos.monitoring.log_events', true)) {
            StructuredLog::warning('chaos_error_injected', [
                'http_code' => $errorCode,
                'chaos_type' => 'error',
                'request_path' => $request->path(),
            ]);
        }
        
        return response()->json([
            'error' => 'Chaos Engineering: Simulated Error',
            'code' => $errorCode,
            'message' => 'This is an intentional error for resilience testing',
        ], $errorCode);
    }
}