<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class ResolveRoleRateLimiter
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        
        // Determine rate limiter based on user role
        $limiterKey = 'api-user'; // default
        
        if ($user) {
            if ($user->hasRole('admin')) {
                $limiterKey = 'api-admin';
            } elseif ($user->hasRole('agent')) {
                $limiterKey = 'api-agent';
            } else {
                $limiterKey = 'api-user';
            }
        }
        
        // Apply the appropriate rate limiter
        $key = $limiterKey . ':' . ($user?->id ?? $request->ip());
        
        $response = RateLimiter::attempt($key, $this->getMaxAttempts($limiterKey), function () use ($next, $request) {
            return $next($request);
        }, $this->getDecayMinutes());
        
        if (!$response) {
            return response()->json([
                'success' => false,
                'message' => 'Rate limit exceeded. Please try again later.',
                'errors' => null,
                'data' => null,
                'meta' => [
                    'code' => 'RATE_LIMIT_EXCEEDED'
                ]
            ], 429);
        }
        
        return $response;
    }
    
    /**
     * Get max attempts for limiter type
     */
    private function getMaxAttempts(string $limiterKey): int
    {
        return match ($limiterKey) {
            'api-admin' => 600,
            'api-agent' => 300,
            'api-user' => 120,
            'api-auth' => 30,
            default => 120,
        };
    }
    
    /**
     * Get decay minutes (always 1 minute for all limiters)
     */
    private function getDecayMinutes(): int
    {
        return 1;
    }
}