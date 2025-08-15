<?php

declare(strict_types=1);

use App\Domain\Hotspot\Contracts\MikrotikApiInterface;
use App\Jobs\SyncActiveSessionsJob;
use App\Models\HotspotSession;
use App\Models\HotspotUser;
use App\Models\UserProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Seed basic data
    $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
    $this->seed(\Database\Seeders\UserProfilesSeeder::class);
});

test('sync active sessions job creates and updates sessions', function () {
    // Create a hotspot user
    $userProfile = UserProfile::first();
    $hotspotUser = HotspotUser::factory()->create([
        'username' => 'testuser1',
        'user_profile_id' => $userProfile->id,
    ]);

    // Mock Mikrotik API to return active sessions
    $this->mock(MikrotikApiInterface::class, function ($mock) {
        $mock->shouldReceive('getActiveSessions')->andReturn([
            [
                '.id' => 'session1',
                'user' => 'testuser1',
                'address' => '192.168.1.100',
                'mac-address' => '00:11:22:33:44:55',
                'interface' => 'wlan1',
                'bytes-in' => 1048576,  // 1MB
                'bytes-out' => 2097152, // 2MB
            ],
            [
                '.id' => 'session2',
                'user' => 'testuser1',
                'address' => '192.168.1.101',
                'mac-address' => '00:11:22:33:44:56',
                'interface' => 'wlan2',
                'bytes-in' => 5242880,  // 5MB
                'bytes-out' => 3145728, // 3MB
            ]
        ]);
    });

    // Run the job
    $job = new SyncActiveSessionsJob();
    $job->handle(app(MikrotikApiInterface::class));

    // Assert sessions were created
    expect(HotspotSession::count())->toBe(2);

    $session1 = HotspotSession::where('mikrotik_session_id', 'session1')->first();
    
    expect($session1)->not->toBeNull()
        ->and($session1->hotspot_user_id)->toBe($hotspotUser->id)
        ->and($session1->ip_address)->toBe('192.168.1.100')
        ->and($session1->mac_address)->toBe('00:11:22:33:44:55')
        ->and($session1->interface)->toBe('wlan1')
        ->and($session1->download_mb)->toBe(1)
        ->and($session1->upload_mb)->toBe(2);
        
    // Check that stop_time is null (session is active)
    expect($session1->stop_time)->toBeNull();
});

test('sync active sessions job closes inactive sessions', function () {
    // Create a hotspot user and existing session
    $userProfile = UserProfile::first();
    $hotspotUser = HotspotUser::factory()->create([
        'username' => 'testuser1',
        'user_profile_id' => $userProfile->id,
    ]);

    $existingSession = HotspotSession::factory()->create([
        'hotspot_user_id' => $hotspotUser->id,
        'mikrotik_session_id' => 'old_session',
        'stop_time' => null, // Active session
    ]);

    // Mock Mikrotik API to return no active sessions
    $this->mock(MikrotikApiInterface::class, function ($mock) {
        $mock->shouldReceive('getActiveSessions')->andReturn([]);
    });

    // Run the job
    $job = new SyncActiveSessionsJob();
    $job->handle(app(MikrotikApiInterface::class));

    // Assert the existing session was closed
    $existingSession->refresh();
    expect($existingSession->stop_time)->not->toBeNull();
});