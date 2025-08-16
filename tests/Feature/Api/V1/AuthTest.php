<?php

declare(strict_types=1);

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

test('user can login with email', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => bcrypt('password123'),
        'status' => 'active',
    ]);

    $response = $this->postJson('/api/v1/auth/login', [
        'identifier' => 'test@example.com',
        'password' => 'password123',
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'data' => [
                'token',
                'user' => [
                    'id',
                    'name',
                    'email',
                    'phone',
                    'user_type',
                    'status',
                    'roles'
                ]
            ],
            'meta',
            'errors'
        ])
        ->assertJson([
            'success' => true,
        ]);

    expect($response->json('data.user.email'))->toBe('test@example.com');
    expect($response->json('data.token'))->not->toBeNull();
});

test('user can login with phone', function () {
    $user = User::factory()->create([
        'phone' => '+243123456789',
        'password' => bcrypt('password123'),
        'status' => 'active',
    ]);

    $response = $this->postJson('/api/v1/auth/login', [
        'identifier' => '+243123456789',
        'password' => 'password123',
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
        ]);

    expect($response->json('data.user.phone'))->toBe('+243123456789');
});

test('login fails with invalid credentials', function () {
    $response = $this->postJson('/api/v1/auth/login', [
        'identifier' => 'nonexistent@example.com',
        'password' => 'wrongpassword',
    ]);

    $response->assertStatus(401)
        ->assertJson([
            'success' => false,
            'meta' => [
                'code' => 'INVALID_CREDENTIALS'
            ]
        ]);
});

test('login fails for inactive user', function () {
    $user = User::factory()->create([
        'email' => 'inactive@example.com',
        'password' => bcrypt('password123'),
        'status' => 'inactive',
    ]);

    $response = $this->postJson('/api/v1/auth/login', [
        'identifier' => 'inactive@example.com',
        'password' => 'password123',
    ]);

    $response->assertStatus(403)
        ->assertJson([
            'success' => false,
            'meta' => [
                'code' => 'ACCOUNT_INACTIVE'
            ]
        ]);
});

test('authenticated user can logout', function () {
    $user = User::factory()->create(['status' => 'active']);
    $token = $user->createToken('test-token')->plainTextToken;

    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $token,
    ])->postJson('/api/v1/auth/logout');

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
        ]);
});

test('authenticated user can get their profile', function () {
    $user = User::factory()->create(['status' => 'active']);
    $token = $user->createToken('test-token')->plainTextToken;

    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $token,
    ])->getJson('/api/v1/me');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'data' => [
                'id',
                'name',
                'email',
                'phone',
                'user_type',
                'status',
                'created_at',
                'updated_at'
            ],
            'meta',
            'errors'
        ])
        ->assertJson([
            'success' => true,
            'data' => [
                'id' => $user->id,
                'email' => $user->email,
            ]
        ]);
});