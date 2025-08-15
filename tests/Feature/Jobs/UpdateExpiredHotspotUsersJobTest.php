<?php

declare(strict_types=1);

use App\Enums\HotspotUserStatus;
use App\Jobs\UpdateExpiredHotspotUsersJob;
use App\Models\HotspotUser;
use App\Models\UserProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Seed basic data
    $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
    $this->seed(\Database\Seeders\UserProfilesSeeder::class);
});

test('update expired hotspot users job marks expired users', function () {
    $userProfile = UserProfile::first();

    // Create expired hotspot user
    $expiredUser = HotspotUser::factory()->create([
        'user_profile_id' => $userProfile->id,
        'status' => HotspotUserStatus::ACTIVE->value,
        'expired_at' => now()->subHour(), // Expired 1 hour ago
    ]);

    // Create non-expired hotspot user
    $activeUser = HotspotUser::factory()->create([
        'user_profile_id' => $userProfile->id,
        'status' => HotspotUserStatus::ACTIVE->value,
        'expired_at' => now()->addHour(), // Expires in 1 hour
    ]);

    // Create already expired user (should not be touched)
    $alreadyExpiredUser = HotspotUser::factory()->create([
        'user_profile_id' => $userProfile->id,
        'status' => HotspotUserStatus::EXPIRED->value,
        'expired_at' => now()->subDay(), // Expired 1 day ago
    ]);

    // Run the job
    $job = new UpdateExpiredHotspotUsersJob();
    $job->handle();

    // Assert the expired user status was updated
    $expiredUser->refresh();
    expect($expiredUser->status)->toBe(HotspotUserStatus::EXPIRED->value);

    // Assert the active user was not changed
    $activeUser->refresh();
    expect($activeUser->status)->toBe(HotspotUserStatus::ACTIVE->value);

    // Assert the already expired user was not changed
    $alreadyExpiredUser->refresh();
    expect($alreadyExpiredUser->status)->toBe(HotspotUserStatus::EXPIRED->value);
});

test('update expired hotspot users job handles users without expiration date', function () {
    $userProfile = UserProfile::first();

    // Create user without expiration date
    $userWithoutExpiration = HotspotUser::factory()->create([
        'user_profile_id' => $userProfile->id,
        'status' => HotspotUserStatus::ACTIVE->value,
        'expired_at' => null,
    ]);

    // Run the job
    $job = new UpdateExpiredHotspotUsersJob();
    $job->handle();

    // Assert the user without expiration was not changed
    $userWithoutExpiration->refresh();
    expect($userWithoutExpiration->status)->toBe(HotspotUserStatus::ACTIVE->value);
});