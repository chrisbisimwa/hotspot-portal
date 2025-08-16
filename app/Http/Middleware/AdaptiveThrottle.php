<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;
use App\Services\Observability\AdaptiveRateLimiter;
use App\Support\StructuredLog;

class AdaptiveThrottle
{
    private AdaptiveRateLimiter $adaptiveLimiter;

    public function __construct(AdaptiveRateLimiter $adaptiveLimiter)
    {
        $this->adaptiveLimiter = $adaptiveLimiter;
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $endpoint = 'api'): Response
    {
        $ip = $request->ip();
        $user = $request->user();
        $userRole = $user?->getRoleNames()?->first() ?? 'guest';
        $userId = $user?->id;

        // Check if should bypass rate limiting
        if ($this->adaptiveLimiter->shouldBypass($ip, $userRole)) {
            return $next($request);
        }

        // Get adaptive limit for user role
        $limit = $this->adaptiveLimiter->calculateLimit($userRole, $userId);
        
        // Create rate limiter key
        $key = $this->getRateLimiterKey($request, $endpoint, $userId);
        
        // Check rate limit
        if (RateLimiter::tooManyAttempts($key, $limit)) {
            $retryAfter = $this->adaptiveLimiter->getRetryAfter();
            
            // Log rate limit hit
            StructuredLog::warning('rate_limit_exceeded', [
                'ip' => $ip,
                'user_id' => $userId,
                'user_role' => $userRole,
                'endpoint' => $endpoint,
                'limit' => $limit,
                'retry_after' => $retryAfter,
                'adaptive_limit_applied' => true,
            ]);
            
            return response()->json([
                'message' => 'Too Many Requests',
                'retry_after' => $retryAfter,
                'limit' => $limit,
            ], 429, [
                'Retry-After' => $retryAfter,
                'X-RateLimit-Limit' => $limit,
                'X-RateLimit-Remaining' => 0,
            ]);
        }
        
        // Hit the rate limiter
        RateLimiter::hit($key, 60); // 1 minute window
        
        $response = $next($request);
        
        // Add rate limit headers
        $remaining = RateLimiter::remaining($key, $limit);
        $response->headers->set('X-RateLimit-Limit', $limit);
        $response->headers->set('X-RateLimit-Remaining', max(0, $remaining));
        
        return $response;
    }

    /**
     * Generate rate limiter key
     */
    private function getRateLimiterKey(Request $request, string $endpoint, ?int $userId): string
    {
        $ip = $request->ip();
        
        if ($userId) {
            return "adaptive_throttle:{$endpoint}:user:{$userId}";
        }
        
        return "adaptive_throttle:{$endpoint}:ip:{$ip}";
    }
}