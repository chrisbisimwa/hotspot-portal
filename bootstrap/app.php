<?php

use App\Http\Middleware\ResolveRoleRateLimiter;
use App\Http\Middleware\CorrelationIdMiddleware;
use App\Http\Middleware\SecurityHeadersMiddleware;
use App\Http\Middleware\ApiCacheMiddleware;
use App\Http\Middleware\AdaptiveThrottle;
use App\Http\Middleware\ChaosMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Global middleware
        $middleware->append([
            CorrelationIdMiddleware::class,
            SecurityHeadersMiddleware::class,
        ]);
        
        // Middleware aliases
        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
            'resolve-rate-limiter' => ResolveRoleRateLimiter::class,
            'api-cache' => ApiCacheMiddleware::class,
            'adaptive-throttle' => AdaptiveThrottle::class,
            'chaos' => ChaosMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Custom exception handler is registered via service provider
    })->create();
