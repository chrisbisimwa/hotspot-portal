<?php

declare(strict_types=1);

use App\Domain\Billing\Events\PaymentSucceeded;
use App\Domain\Hotspot\Events\HotspotUserProvisioned;
use App\Domain\Hotspot\Events\OrderCompleted;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\HotspotUser;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Support\Facades\Event;

test('payment success triggers provisioning flow', function () {
    Event::fake();

    // Arrange
    $user = User::factory()->create();
    $profile = UserProfile::factory()->create();
    $order = Order::factory()->create([
        'user_id' => $user->id,
        'user_profile_id' => $profile->id,
        'quantity' => 2,
        'status' => OrderStatus::PAYMENT_RECEIVED->value,
    ]);
    $payment = Payment::factory()->create([
        'order_id' => $order->id,
        'user_id' => $user->id,
        'status' => PaymentStatus::SUCCESS->value,
    ]);

    // Act - simulate PaymentSucceeded event
    event(new PaymentSucceeded($payment));

    // Assert
    Event::assertDispatched(PaymentSucceeded::class);
    
    // Check that hotspot users were created
    expect(HotspotUser::count())->toBe(2);
    
    // Check order status
    $order->refresh();
    expect($order->status)->toBe(OrderStatus::COMPLETED->value);
    
    // Check that HotspotUserProvisioned events were dispatched
    Event::assertDispatched(HotspotUserProvisioned::class, 2);
    
    // Check that OrderCompleted event was dispatched
    Event::assertDispatched(OrderCompleted::class);
});

test('provisioning with quantity greater than one creates multiple users', function () {
    // Arrange
    $user = User::factory()->create();
    $profile = UserProfile::factory()->create([
        'validity_minutes' => 60,
        'data_limit_mb' => 500,
    ]);
    $order = Order::factory()->create([
        'user_id' => $user->id,
        'user_profile_id' => $profile->id,
        'quantity' => 5,
        'status' => OrderStatus::PAYMENT_RECEIVED->value,
    ]);

    $provisioningService = app(\App\Domain\Hotspot\Services\HotspotProvisioningService::class);

    // Act
    $result = $provisioningService->provisionOrder($order);

    // Assert
    expect($result)->toHaveCount(5);
    expect(HotspotUser::count())->toBe(5);

    // Check that all users have unique usernames
    $usernames = $result->pluck('username')->toArray();
    expect($usernames)->toHaveCount(5);
    expect(array_unique($usernames))->toHaveCount(5);

    // Check order completion
    $order->refresh();
    expect($order->status)->toBe(OrderStatus::COMPLETED->value);
    expect($order->completed_at)->not->toBeNull();
});

test('provisioning updates order status progression correctly', function () {
    // Arrange
    $user = User::factory()->create();
    $profile = UserProfile::factory()->create();
    $order = Order::factory()->create([
        'user_id' => $user->id,
        'user_profile_id' => $profile->id,
        'quantity' => 1,
        'status' => OrderStatus::PAYMENT_RECEIVED->value,
    ]);

    $provisioningService = app(\App\Domain\Hotspot\Services\HotspotProvisioningService::class);

    // Assert initial status
    expect($order->status)->toBe(OrderStatus::PAYMENT_RECEIVED->value);

    // Act
    $result = $provisioningService->provisionOrder($order);

    // Assert final status
    $order->refresh();
    expect($order->status)->toBe(OrderStatus::COMPLETED->value);
    expect($order->completed_at)->not->toBeNull();
    expect($result)->toHaveCount(1);
});