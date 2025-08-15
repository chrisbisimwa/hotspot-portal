<?php

declare(strict_types=1);

use App\Domain\Hotspot\Exceptions\ProvisioningException;
use App\Domain\Hotspot\Services\HotspotProvisioningService;
use App\Enums\OrderStatus;
use App\Models\HotspotUser;
use App\Models\Order;
use App\Models\User;
use App\Models\UserProfile;

test('provision order creates expected number of hotspot users', function () {
    // Arrange
    $user = User::factory()->create();
    $profile = UserProfile::factory()->create();
    $order = Order::factory()->create([
        'user_id' => $user->id,
        'user_profile_id' => $profile->id,
        'quantity' => 3,
        'status' => OrderStatus::PAYMENT_RECEIVED->value,
    ]);

    $provisioningService = app(HotspotProvisioningService::class);

    // Act
    $result = $provisioningService->provisionOrder($order);

    // Assert
    expect($result)->toHaveCount(3);
    expect(HotspotUser::count())->toBe(3);
    
    $order->refresh();
    expect($order->status)->toBe(OrderStatus::COMPLETED->value);
    expect($order->completed_at)->not->toBeNull();
    
    // Check that each hotspot user has correct attributes
    $result->each(function (HotspotUser $hotspotUser) use ($profile, $user) {
        expect($hotspotUser->user_profile_id)->toBe($profile->id);
        expect($hotspotUser->owner_id)->toBe($user->id);
        expect($hotspotUser->validity_minutes)->toBe($profile->validity_minutes);
        expect($hotspotUser->data_limit_mb)->toBe($profile->data_limit_mb);
        expect($hotspotUser->username)->toMatch('/^HS\d{14}\d{3}$/'); // Pattern: HS + timestamp + 3 digits
        expect($hotspotUser->password)->toHaveLength(10);
    });
});

test('provision order requires payment_received status', function () {
    // Arrange
    $user = User::factory()->create();
    $profile = UserProfile::factory()->create();
    $order = Order::factory()->create([
        'user_id' => $user->id,
        'user_profile_id' => $profile->id,
        'status' => OrderStatus::PENDING->value, // Wrong status
    ]);

    $provisioningService = app(HotspotProvisioningService::class);

    // Act & Assert
    expect(fn() => $provisioningService->provisionOrder($order))
        ->toThrow(ProvisioningException::class, 'Cannot provision order');
});

test('provision single creates hotspot user with correct credentials', function () {
    // Arrange
    $user = User::factory()->create();
    $profile = UserProfile::factory()->create([
        'validity_minutes' => 120,
        'data_limit_mb' => 1024,
    ]);
    $order = Order::factory()->create([
        'user_id' => $user->id,
        'user_profile_id' => $profile->id,
        'status' => OrderStatus::PAYMENT_RECEIVED->value,
    ]);

    $provisioningService = app(HotspotProvisioningService::class);

    // Act
    $hotspotUser = $provisioningService->provisionSingle($order, $profile, 1);

    // Assert
    expect($hotspotUser)->toBeInstanceOf(HotspotUser::class);
    expect($hotspotUser->username)->toMatch('/^HS\d{14}001$/'); // Should end with 001 for index 1
    expect($hotspotUser->password)->toHaveLength(10);
    expect($hotspotUser->user_profile_id)->toBe($profile->id);
    expect($hotspotUser->owner_id)->toBe($user->id);
    expect($hotspotUser->validity_minutes)->toBe(120);
    expect($hotspotUser->data_limit_mb)->toBe(1024);
    expect($hotspotUser->status)->toBe('active');
});

test('generate username creates correct pattern with index', function () {
    $provisioningService = app(HotspotProvisioningService::class);
    
    $username1 = $provisioningService->generateUsername(1);
    $username2 = $provisioningService->generateUsername(999);
    
    expect($username1)->toMatch('/^HS\d{14}001$/');
    expect($username2)->toMatch('/^HS\d{14}999$/');
});

test('generate username creates correct pattern without index', function () {
    $provisioningService = app(HotspotProvisioningService::class);
    
    $username = $provisioningService->generateUsername();
    
    expect($username)->toMatch('/^HS\d{14}[A-Z0-9]{3}$/');
});

test('generate password creates correct length password', function () {
    $provisioningService = app(HotspotProvisioningService::class);
    
    $password = $provisioningService->generatePassword();
    
    expect($password)->toHaveLength(10);
    expect($password)->toMatch('/^[A-HJ-NP-Za-hj-np-z2-9]+$/'); // Should only contain allowed characters
});