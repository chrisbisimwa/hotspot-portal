<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeadersMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        
        // Content Security Policy (baseline)
        $response->headers->set('Content-Security-Policy', $this->getContentSecurityPolicy());
        
        // Prevent embedding in frames
        $response->headers->set('X-Frame-Options', 'DENY');
        
        // Prevent MIME type sniffing
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        
        // Referrer Policy
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        
        // Permissions Policy (baseline)
        $response->headers->set('Permissions-Policy', $this->getPermissionsPolicy());
        
        // HSTS (if HTTPS)
        if ($request->isSecure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }
        
        // Prevent XSS attacks
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        
        return $response;
    }
    
    /**
     * Generate Content Security Policy
     */
    private function getContentSecurityPolicy(): string
    {
        $policies = [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline' 'unsafe-eval'",
            "style-src 'self' 'unsafe-inline'",
            "img-src 'self' data: https:",
            "font-src 'self' data:",
            "connect-src 'self'",
            "media-src 'self'",
            "object-src 'none'",
            "frame-ancestors 'none'",
            "base-uri 'self'",
            "form-action 'self'",
        ];
        
        return implode('; ', $policies);
    }
    
    /**
     * Generate Permissions Policy
     */
    private function getPermissionsPolicy(): string
    {
        $policies = [
            'camera=()',
            'microphone=()',
            'geolocation=()',
            'payment=()',
            'usb=()',
            'magnetometer=()',
            'gyroscope=()',
            'accelerometer=()',
        ];
        
        return implode(', ', $policies);
    }
}