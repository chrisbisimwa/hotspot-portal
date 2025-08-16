<?php

declare(strict_types=1);

namespace App\Livewire\User;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\HotspotSession;
use App\Models\HotspotUser;
use App\Models\Notification;
use App\Models\Order;
use App\Models\Payment;
use Livewire\Component;

class Dashboard extends Component
{
    public function mount(): void
    {
        // Basic auth check - already handled by middleware
        abort_unless(auth()->check(), 403, 'Unauthorized access');
    }

    public function getUserMetricsProperty(): array
    {
        $userId = auth()->id();

        return [
            'orders_count' => Order::where('user_id', $userId)->count(),
            'orders_total_paid' => Payment::whereHas('order', function ($query) use ($userId) {
                    $query->where('user_id', $userId);
                })
                ->where('status', PaymentStatus::SUCCESS->value)
                ->sum('net_amount'),
            'hotspot_users_active' => HotspotUser::where('owner_id', $userId)
                ->where('status', 'active')
                ->count(),
            'sessions_active' => HotspotSession::whereNull('stop_time')
                ->whereHas('hotspotUser', function ($q) use ($userId) {
                    $q->where('owner_id', $userId);
                })
                ->count(),
            'notifications_unread' => Notification::where('user_id', $userId)
                ->where('read_at', null)
                ->count(),
        ];
    }

    public function getRecentOrdersProperty()
    {
        return Order::with(['userProfile', 'payments'])
            ->where('user_id', auth()->id())
            ->latest()
            ->take(5)
            ->get();
    }

    public function getRecentNotificationsProperty()
    {
        return Notification::where('user_id', auth()->id())
            ->latest()
            ->take(5)
            ->get();
    }

    public function render()
    {
        return view('livewire.user.dashboard')
            ->layout('layouts.user');
    }
}