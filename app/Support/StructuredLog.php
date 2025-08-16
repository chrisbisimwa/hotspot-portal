<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Support\Facades\Log;

class StructuredLog
{
    private static array $sensitiveKeys = [
        'password', 'secret', 'token', 'signature', 'api_key', 'private_key',
        'authorization', 'cookie', 'session_id', 'csrf_token'
    ];

    public static function info(string $event, array $context = []): void
    {
        Log::channel('structured')->info(self::formatMessage($event), self::sanitizeContext($context));
    }

    public static function warning(string $event, array $context = []): void
    {
        Log::channel('structured')->warning(self::formatMessage($event), self::sanitizeContext($context));
    }

    public static function error(string $event, array $context = []): void
    {
        Log::channel('structured')->error(self::formatMessage($event), self::sanitizeContext($context));
    }

    public static function debug(string $event, array $context = []): void
    {
        Log::channel('structured')->debug(self::formatMessage($event), self::sanitizeContext($context));
    }

    public static function critical(string $event, array $context = []): void
    {
        Log::channel('structured')->critical(self::formatMessage($event), self::sanitizeContext($context));
    }

    public static function alert(string $event, array $context = []): void
    {
        Log::channel('structured')->alert(self::formatMessage($event), self::sanitizeContext($context));
    }

    public static function emergency(string $event, array $context = []): void
    {
        Log::channel('structured')->emergency(self::formatMessage($event), self::sanitizeContext($context));
    }

    public static function notice(string $event, array $context = []): void
    {
        Log::channel('structured')->notice(self::formatMessage($event), self::sanitizeContext($context));
    }

    /**
     * Log slow queries with performance context
     */
    public static function slowQuery(string $sql, float $duration, array $bindings = []): void
    {
        self::warning('slow_query', [
            'sql' => $sql,
            'duration_ms' => round($duration, 2),
            'bindings_count' => count($bindings),
            'threshold_exceeded' => true,
        ]);
    }

    /**
     * Log trace information for correlation
     */
    public static function trace(string $event, array $context = []): void
    {
        $traceContext = self::getTraceContext();
        self::info($event, array_merge($context, $traceContext));
    }

    private static function formatMessage(string $event): string
    {
        return "[event={$event}]";
    }

    /**
     * Remove sensitive data from context
     */
    private static function sanitizeContext(array $context): array
    {
        return self::recursiveFilter($context);
    }

    private static function recursiveFilter(array $data): array
    {
        $filtered = [];
        
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $filtered[$key] = self::recursiveFilter($value);
            } elseif (is_string($key) && self::isSensitiveKey($key)) {
                $filtered[$key] = '[REDACTED]';
            } else {
                $filtered[$key] = $value;
            }
        }
        
        return $filtered;
    }

    private static function isSensitiveKey(string $key): bool
    {
        $lowercaseKey = strtolower($key);
        
        foreach (self::$sensitiveKeys as $sensitiveKey) {
            if (str_contains($lowercaseKey, $sensitiveKey)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Get trace context if available
     */
    private static function getTraceContext(): array
    {
        $context = [];
        
        // Add correlation ID if available in request
        if (request() && request()->attributes->has('correlation_id')) {
            $context['correlation_id'] = request()->attributes->get('correlation_id');
        }
        
        // Add user ID if authenticated
        if (auth()->check()) {
            $context['user_id'] = auth()->id();
        }
        
        // TODO: Add OpenTelemetry trace_id and span_id when available
        
        return $context;
    }
}