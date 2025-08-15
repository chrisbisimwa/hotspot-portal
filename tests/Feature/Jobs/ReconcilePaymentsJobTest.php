<?php

declare(strict_types=1);

use App\Domain\Billing\Services\PaymentService;
use App\Enums\PaymentStatus;
use App\Jobs\ReconcilePaymentsJob;
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

test('reconcile payments job processes pending payments', function () {
    $user = User::factory()->create();
    $userProfile = UserProfile::first();
    
    // Create order and payment
    $order = Order::factory()->create([
        'user_id' => $user->id,
        'user_profile_id' => $userProfile->id,
    ]);

    $pendingPayment = Payment::factory()->create([
        'order_id' => $order->id,
        'user_id' => $user->id,
        'status' => PaymentStatus::PENDING->value,
    ]);

    // Mock PaymentService to simulate successful verification
    $this->mock(PaymentService::class, function ($mock) use ($pendingPayment) {
        $mock->shouldReceive('verify')
            ->once()
            ->andReturn($pendingPayment);
    });

    // Run the job
    $job = new ReconcilePaymentsJob(1); // Small batch size for test
    $job->handle(app(PaymentService::class));

    // The payment service mock was called, indicating the job processed the payment
    // In a real implementation, the payment status would be updated by the service
    expect($pendingPayment)->not->toBeNull();
});

test('reconcile payments job respects batch size', function () {
    $user = User::factory()->create();
    $userProfile = UserProfile::first();
    
    // Create order
    $order = Order::factory()->create([
        'user_id' => $user->id,
        'user_profile_id' => $userProfile->id,
    ]);

    // Create multiple pending payments
    Payment::factory()->count(5)->create([
        'order_id' => $order->id,
        'user_id' => $user->id,
        'status' => PaymentStatus::PENDING->value,
    ]);

    // Mock PaymentService to track how many calls are made
    $callCount = 0;
    $this->mock(PaymentService::class, function ($mock) use (&$callCount) {
        $mock->shouldReceive('verify')
            ->times(3) // Should only process 3 due to batch size
            ->andReturnUsing(function ($payment) use (&$callCount) {
                $callCount++;
                return $payment; // Return the payment model
            });
    });

    // Run the job with batch size of 3
    $job = new ReconcilePaymentsJob(3);
    $job->handle(app(PaymentService::class));

    // Verify only 3 payments were processed
    expect($callCount)->toBe(3);
});

test('reconcile payments job handles verification failures gracefully', function () {
    $user = User::factory()->create();
    $userProfile = UserProfile::first();
    
    // Create order and payment
    $order = Order::factory()->create([
        'user_id' => $user->id,
        'user_profile_id' => $userProfile->id,
    ]);

    $pendingPayment = Payment::factory()->create([
        'order_id' => $order->id,
        'user_id' => $user->id,
        'status' => PaymentStatus::PENDING->value,
    ]);

    // Mock PaymentService to throw exception
    $this->mock(PaymentService::class, function ($mock) {
        $mock->shouldReceive('verify')
            ->once()
            ->andThrow(new \Exception('Gateway error'));
    });

    // Run the job - should not throw exception
    $job = new ReconcilePaymentsJob(1);
    
    // Should complete without throwing
    $job->handle(app(PaymentService::class));
    
    // Test passes if we reach this point
    expect(true)->toBeTrue();
});