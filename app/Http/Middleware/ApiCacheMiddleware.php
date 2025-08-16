<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class ApiCacheMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only cache GET requests
        if ($request->method() !== 'GET') {
            return $next($request);
        }
        
        // Only cache specific cacheable endpoints
        if (!$this->isCacheable($request)) {
            return $next($request);
        }
        
        // Generate cache key and ETag
        $cacheKey = $this->generateCacheKey($request);
        $etag = md5($cacheKey);
        
        // Check If-None-Match header for conditional requests
        if ($request->header('If-None-Match') === '"' . $etag . '"') {
            return response('', 304)->header('ETag', '"' . $etag . '"');
        }
        
        // Try to get cached response
        $cachedResponse = Cache::get($cacheKey);
        
        if ($cachedResponse) {
            return response($cachedResponse['content'], $cachedResponse['status'])
                ->withHeaders($cachedResponse['headers'])
                ->header('ETag', '"' . $etag . '"')
                ->header('Cache-Control', 'public, max-age=' . config('cache_extensions.ttl.api_user_profiles', 300));
        }
        
        // Process the request
        $response = $next($request);
        
        // Cache successful responses
        if ($response->getStatusCode() === 200) {
            $this->cacheResponse($cacheKey, $response, $etag);
        }
        
        return $response->header('ETag', '"' . $etag . '"')
                       ->header('Cache-Control', 'public, max-age=' . config('cache_extensions.ttl.api_user_profiles', 300));
    }
    
    /**
     * Determine if the request should be cached
     */
    private function isCacheable(Request $request): bool
    {
        $path = $request->path();
        
        // Only cache specific public API endpoints
        $cacheableRoutes = [
            'api/v1/user-profiles',
        ];
        
        // Don't cache requests with query parameters (for now)
        if ($request->query()) {
            return false;
        }
        
        return in_array($path, $cacheableRoutes);
    }
    
    /**
     * Generate cache key for the request
     */
    private function generateCacheKey(Request $request): string
    {
        return 'api_cache:' . md5($request->fullUrl());
    }
    
    /**
     * Cache the response data
     */
    private function cacheResponse(string $cacheKey, Response $response, string $etag): void
    {
        $ttl = config('cache_extensions.ttl.api_user_profiles', 300);
        
        Cache::put($cacheKey, [
            'content' => $response->getContent(),
            'status' => $response->getStatusCode(),
            'headers' => $response->headers->all(),
            'etag' => $etag,
        ], $ttl);
    }
}