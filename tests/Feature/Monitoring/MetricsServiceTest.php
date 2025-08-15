<?php

declare(strict_types=1);

use App\Domain\Monitoring\Services\MetricsService;
use App\Models\HotspotSession;
use App\Models\HotspotUser;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Seed basic data
    $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
    $this->seed(\Database\Seeders\UserProfilesSeeder::class);
});

test('metrics service global returns expected keys', function () {
    $metricsService = app(MetricsService::class);
    
    $metrics = $metricsService->global();
    
    expect($metrics)->toHaveKeys([
        'total_users',
        'active_users', 
        'hotspot_users',
        'active_hotspot_users',
        'user_profiles_active',
        'orders_last_24h',
        'revenue_last_24h',
        'active_sessions_count',
        'payments_pending',
        'notifications_queued'
    ]);
});

test('metrics service global counts correctly with seeded data', function () {
    // Create test data
    $users = User::factory()->count(3)->create();
    $userProfile = UserProfile::first();
    
    $hotspotUsers = HotspotUser::factory()->count(2)->create([
        'user_profile_id' => $userProfile->id,
    ]);
    
    $orders = Order::factory()->count(2)->create([
        'user_id' => $users->first()->id,
        'user_profile_id' => $userProfile->id,
        'created_at' => now()->subHours(2), // Within last 24h
    ]);
    
    $payments = Payment::factory()->count(1)->create([
        'order_id' => $orders->first()->id,
        'user_id' => $users->first()->id,
        'status' => 'success',
        'confirmed_at' => now()->subHours(1),
        'net_amount' => 100.00,
    ]);
    
    $sessions = HotspotSession::factory()->count(3)->create([
        'hotspot_user_id' => $hotspotUsers->first()->id,
        'stop_time' => null, // Active sessions
    ]);
    
    $metricsService = app(MetricsService::class);
    $metrics = $metricsService->global();
    
    // Admin user + 3 created users = 4 total
    expect($metrics['total_users'])->toBe(4)
        ->and($metrics['hotspot_users'])->toBe(2)
        ->and($metrics['orders_last_24h'])->toBe(2)
        ->and($metrics['revenue_last_24h'])->toBe(100.00)
        ->and($metrics['active_sessions_count'])->toBe(3);
});

test('metrics service system returns memory and queue info', function () {
    $metricsService = app(MetricsService::class);
    
    $metrics = $metricsService->system();
    
    expect($metrics)->toHaveKeys([
        'memory_usage',
        'queue_pending',
        'server_load'
    ])
        ->and($metrics['memory_usage'])->toHaveKeys([
            'current',
            'peak',
            'formatted'
        ])
        ->and($metrics['memory_usage']['formatted'])->toHaveKeys([
            'current',
            'peak'
        ])
        ->and($metrics['queue_pending'])->toBeInt()
        ->and($metrics['server_load'])->toBeString();
});

test('metrics service interfaces load returns array', function () {
    $metricsService = app(MetricsService::class);
    
    $interfaces = $metricsService->interfacesLoad();
    
    expect($interfaces)->toBeArray();
    
    // In fake mode, should return mock data
    if (config('mikrotik.fake')) {
        expect($interfaces)->not->toBeEmpty()
            ->and($interfaces[0])->toHaveKeys([
                'interface',
                'connected_users',
                'last_sync_at'
            ]);
    }
});