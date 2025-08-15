<?php

declare(strict_types=1);

namespace App\Domain\Monitoring\Services;

use App\Domain\Hotspot\Contracts\MikrotikApiInterface;
use App\Enums\HotspotUserStatus;
use App\Enums\NotificationStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\UserStatus;
use App\Models\HotspotSession;
use App\Models\HotspotUser;
use App\Models\Notification;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class MetricsService
{
    private MikrotikApiInterface $mikrotikApi;

    public function __construct(MikrotikApiInterface $mikrotikApi)
    {
        $this->mikrotikApi = $mikrotikApi;
    }

    /**
     * Get global system metrics
     */
    public function global(): array
    {
        return [
            'total_users' => User::count(),
            'active_users' => User::where('status', UserStatus::ACTIVE->value)->count(),
            'hotspot_users' => HotspotUser::count(),
            'active_hotspot_users' => HotspotUser::where('status', HotspotUserStatus::ACTIVE->value)->count(),
            'user_profiles_active' => UserProfile::where('is_active', true)->count(),
            'orders_last_24h' => Order::where('created_at', '>=', now()->subDay())->count(),
            'revenue_last_24h' => Payment::where('status', PaymentStatus::SUCCESS->value)
                ->where('confirmed_at', '>=', now()->subDay())
                ->sum('net_amount'),
            'active_sessions_count' => HotspotSession::whereNull('stop_time')->count(),
            'payments_pending' => Payment::whereIn('status', [
                PaymentStatus::PENDING->value,
                PaymentStatus::INITIATED->value,
                PaymentStatus::PROCESSING->value
            ])->count(),
            'notifications_queued' => Notification::where('status', NotificationStatus::QUEUED->value)->count(),
        ];
    }

    /**
     * Get Mikrotik interfaces load (cached for 30 seconds)
     */
    public function interfacesLoad(): array
    {
        return Cache::remember('mikrotik_interfaces_load', 30, function () {
            try {
                return $this->mikrotikApi->getApInterfacesLoad();
            } catch (\Exception $e) {
                return [
                    'error' => 'Failed to retrieve interfaces load',
                    'message' => $e->getMessage()
                ];
            }
        });
    }

    /**
     * Get system metrics (server resources and queue status)
     */
    public function system(): array
    {
        $metrics = [
            'memory_usage' => [
                'current' => memory_get_usage(true),
                'peak' => memory_get_peak_usage(true),
                'formatted' => [
                    'current' => $this->formatBytes(memory_get_usage(true)),
                    'peak' => $this->formatBytes(memory_get_peak_usage(true)),
                ]
            ]
        ];

        // Get queue pending count if using database queue
        try {
            $queuePending = DB::table('jobs')->count();
            $metrics['queue_pending'] = $queuePending;
        } catch (\Exception $e) {
            $metrics['queue_pending'] = 'unavailable';
        }

        // TODO: Add server load metrics in the future
        $metrics['server_load'] = 'TODO: implement server load monitoring';

        return $metrics;
    }

    /**
     * Format bytes into human readable format
     */
    private function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }
}