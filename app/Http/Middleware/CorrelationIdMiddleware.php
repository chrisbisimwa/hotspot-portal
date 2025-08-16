<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class CorrelationIdMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Generate or use existing correlation ID
        $correlationId = $request->header('X-Request-Id') ?? Str::uuid()->toString();
        
        // Store in request attributes for access throughout the request lifecycle
        $request->attributes->set('correlation_id', $correlationId);
        
        // Add to logging context
        logger()->withContext(['correlation_id' => $correlationId]);
        
        // Process the request
        $response = $next($request);
        
        // Add correlation ID to response headers
        $response->headers->set('X-Request-Id', $correlationId);
        
        return $response;
    }
}