<?php

declare(strict_types=1);

use App\Models\UserProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('public user profiles endpoint returns active profiles', function () {
    // Create some profiles
    UserProfile::factory()->create([
        'name' => 'Active Profile',
        'is_active' => true,
    ]);
    
    UserProfile::factory()->create([
        'name' => 'Inactive Profile',
        'is_active' => false,
    ]);

    $response = $this->getJson('/api/v1/user-profiles');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'data' => [
                '*' => [
                    'id',
                    'name',
                    'description',
                    'price',
                    'validity_hours',
                    'is_active',
                    'created_at',
                    'updated_at'
                ]
            ],
            'meta',
            'errors'
        ])
        ->assertJson([
            'success' => true,
        ]);

    // Should only return active profiles
    expect($response->json('data'))->toHaveCount(1);
    expect($response->json('data.0.name'))->toBe('Active Profile');
    expect($response->json('data.0.is_active'))->toBe(true);
});

test('unauthenticated requests to protected endpoints return 401', function () {
    $response = $this->getJson('/api/v1/me');
    
    $response->assertStatus(401);
});

test('invalid json structure returns proper error format', function () {
    $response = $this->postJson('/api/v1/auth/login', [
        'identifier' => '', // empty identifier should fail validation
        'password' => '',
    ]);

    $response->assertStatus(422)
        ->assertJsonStructure([
            'message',
            'errors'
        ])
        ->assertJsonFragment([
            'identifier' => ['Email ou téléphone requis'],
            'password' => ['Mot de passe requis']
        ]);
});