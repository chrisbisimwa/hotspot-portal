<?php

declare(strict_types=1);

namespace App\Support;

class StatusColor
{
    /**
     * Color mapping for different domains and their statuses
     */
    private static array $colorMap = [
        'orders' => [
            'pending' => 'secondary',
            'payment_received' => 'info',
            'processing' => 'warning',
            'completed' => 'success',
            'cancelled' => 'dark',
            'expired' => 'danger',
        ],
        'payments' => [
            'pending' => 'secondary',
            'initiated' => 'info',
            'processing' => 'warning',
            'success' => 'success',
            'failed' => 'danger',
            'cancelled' => 'dark',
            'refunded' => 'primary',
        ],
        'hotspot_users' => [
            'active' => 'success',
            'suspended' => 'warning',
            'expired' => 'danger',
            'disabled' => 'dark',
        ],
        'notifications' => [
            'queued' => 'secondary',
            'sending' => 'info',
            'sent' => 'success',
            'failed' => 'danger',
        ],
        'users' => [
            'active' => 'success',
            'inactive' => 'warning',
            'banned' => 'danger',
        ],
        'sessions' => [
            'active' => 'success',
            'terminated' => 'secondary',
        ],
    ];

    /**
     * Get Bootstrap badge color class for a given domain and status
     */
    public static function color(string $domain, string $status): string
    {
        $status = strtolower($status);
        $domain = strtolower($domain);

        return self::$colorMap[$domain][$status] ?? 'secondary';
    }

    /**
     * Get all available color mappings
     */
    public static function getColorMap(): array
    {
        return self::$colorMap;
    }

    /**
     * Check if a domain exists in the color map
     */
    public static function hasDomain(string $domain): bool
    {
        return isset(self::$colorMap[strtolower($domain)]);
    }

    /**
     * Get all statuses for a domain
     */
    public static function getStatusesForDomain(string $domain): array
    {
        $domain = strtolower($domain);
        return array_keys(self::$colorMap[$domain] ?? []);
    }
}