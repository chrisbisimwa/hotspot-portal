<?php

declare(strict_types=1);

use App\Models\Order;
use App\Models\Payment;
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

test('user cannot view another users payment', function () {
    $user = User::factory()->create(['status' => 'active']);
    $otherUser = User::factory()->create(['status' => 'active']);
    
    $otherPayment = Payment::factory()->create([
        'user_id' => $otherUser->id,
    ]);
    
    $token = $user->createToken('test-token')->plainTextToken;

    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $token,
    ])->getJson("/api/v1/payments/{$otherPayment->id}");

    $response->assertStatus(404)
        ->assertJson([
            'success' => false,
            'meta' => [
                'code' => 'PAYMENT_NOT_FOUND'
            ]
        ]);
});

test('admin can access admin endpoints', function () {
    $admin = User::factory()->create(['status' => 'active']);
    $admin->assignRole('admin');
    
    $token = $admin->createToken('test-token', ['admin'])->plainTextToken;

    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $token,
    ])->getJson('/api/v1/admin/metrics');

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
        ])
        ->assertJsonStructure([
            'success',
            'data' => [
                'global',
                'system',
                'interfaces',
                'timestamp'
            ]
        ]);
});

test('regular user cannot access admin endpoints', function () {
    $user = User::factory()->create(['status' => 'active']);
    $user->assignRole('user');
    
    $token = $user->createToken('test-token', ['user'])->plainTextToken;

    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $token,
    ])->getJson('/api/v1/admin/metrics');

    $response->assertStatus(403);
});

test('unauthenticated user cannot access protected endpoints', function () {
    $response = $this->getJson('/api/v1/me');
    $response->assertStatus(401);
    
    $response = $this->getJson('/api/v1/orders');
    $response->assertStatus(401);
    
    $response = $this->getJson('/api/v1/admin/metrics');
    $response->assertStatus(401);
});