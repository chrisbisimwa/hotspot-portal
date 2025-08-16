<?php

declare(strict_types=1);

use App\Models\Order;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed([
        \Database\Seeders\RolesAndPermissionsSeeder::class,
        \Database\Seeders\UserProfilesSeeder::class,
    ]);
});

test('user can create order and initiate payment', function () {
    $user = User::factory()->create(['status' => 'active']);
    $profile = UserProfile::factory()->create([
        'name' => 'Test Package',
        'price' => 1000.00,
        'is_active' => true,
    ]);
    
    $token = $user->createToken('test-token')->plainTextToken;

    // Create order
    $orderResponse = $this->withHeaders([
        'Authorization' => 'Bearer ' . $token,
    ])->postJson('/api/v1/orders', [
        'user_profile_id' => $profile->id,
        'quantity' => 2,
    ]);

    $orderResponse->assertStatus(201)
        ->assertJsonStructure([
            'success',
            'data' => [
                'id',
                'user_id',
                'quantity',
                'unit_price',
                'total_amount',
                'status',
                'user_profile'
            ],
            'meta',
            'errors'
        ])
        ->assertJson([
            'success' => true,
            'data' => [
                'user_id' => $user->id,
                'quantity' => 2,
                'unit_price' => '1000.00',
                'total_amount' => '2000.00',
                'status' => 'pending',
            ]
        ]);

    $orderId = $orderResponse->json('data.id');

    // Initiate payment for the order
    $paymentResponse = $this->withHeaders([
        'Authorization' => 'Bearer ' . $token,
    ])->postJson("/api/v1/payments/{$orderId}/initiate", [
        'provider' => 'serdipay',
    ]);

    $paymentResponse->assertStatus(201)
        ->assertJsonStructure([
            'success',
            'data' => [
                'id',
                'order_id',
                'user_id',
                'provider',
                'status',
                'transaction_ref',
                'amount',
                'currency'
            ],
            'meta',
            'errors'
        ])
        ->assertJson([
            'success' => true,
            'data' => [
                'order_id' => $orderId,
                'user_id' => $user->id,
                'provider' => 'serdipay',
                'status' => 'initiated',
                'amount' => '2000.00',
                'currency' => 'CDF',
            ]
        ]);
});

test('user can list their orders', function () {
    $user = User::factory()->create(['status' => 'active']);
    $profile = UserProfile::factory()->create(['is_active' => true]);
    
    // Create some orders for the user
    Order::factory()->count(3)->create([
        'user_id' => $user->id,
        'user_profile_id' => $profile->id,
    ]);
    
    // Create orders for another user (should not be visible)
    $otherUser = User::factory()->create();
    Order::factory()->count(2)->create([
        'user_id' => $otherUser->id,
        'user_profile_id' => $profile->id,
    ]);
    
    $token = $user->createToken('test-token')->plainTextToken;

    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $token,
    ])->getJson('/api/v1/orders');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'data' => [
                '*' => [
                    'id',
                    'user_id',
                    'quantity',
                    'unit_price',
                    'total_amount',
                    'status',
                    'user_profile'
                ]
            ],
            'meta' => [
                'pagination'
            ],
            'errors'
        ])
        ->assertJson([
            'success' => true,
        ]);

    // Should only see own orders (3)
    expect($response->json('data'))->toHaveCount(3);
    
    // All orders should belong to the authenticated user
    foreach ($response->json('data') as $order) {
        expect($order['user_id'])->toBe($user->id);
    }
});

test('user cannot view another users order', function () {
    $user = User::factory()->create(['status' => 'active']);
    $otherUser = User::factory()->create(['status' => 'active']);
    $profile = UserProfile::factory()->create(['is_active' => true]);
    
    $otherOrder = Order::factory()->create([
        'user_id' => $otherUser->id,
        'user_profile_id' => $profile->id,
    ]);
    
    $token = $user->createToken('test-token')->plainTextToken;

    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $token,
    ])->getJson("/api/v1/orders/{$otherOrder->id}");

    $response->assertStatus(404)
        ->assertJson([
            'success' => false,
            'meta' => [
                'code' => 'ORDER_NOT_FOUND'
            ]
        ]);
});