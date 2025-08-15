<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Seed basic data
    $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
    $this->seed(\Database\Seeders\AdminUserSeeder::class);
    $this->seed(\Database\Seeders\UserProfilesSeeder::class);
});

test('admin can access monitoring metrics endpoint', function () {
    $admin = User::where('email', 'admin@demo.test')->first();
    
    $response = $this->actingAs($admin)->get('/admin/monitoring/metrics');
    
    $response->assertStatus(200)
        ->assertJsonStructure([
            'global' => [
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
            ],
            'system' => [
                'memory_usage',
                'queue_pending',
                'server_load'
            ],
            'timestamp'
        ]);
    
    $data = $response->json();
    expect($data['global']['total_users'])->toBeInt()
        ->and($data['timestamp'])->toBeString();
});

test('admin can access monitoring interfaces endpoint', function () {
    $admin = User::where('email', 'admin@demo.test')->first();
    
    $response = $this->actingAs($admin)->get('/admin/monitoring/interfaces');
    
    $response->assertStatus(200)
        ->assertJsonStructure([
            'interfaces',
            'timestamp'
        ]);
    
    $data = $response->json();
    expect($data['interfaces'])->toBeArray()
        ->and($data['timestamp'])->toBeString();
});

test('non-admin users cannot access monitoring endpoints', function () {
    $user = User::factory()->create();
    
    $response = $this->actingAs($user)->get('/admin/monitoring/metrics');
    $response->assertStatus(403);
    
    $response = $this->actingAs($user)->get('/admin/monitoring/interfaces');
    $response->assertStatus(403);
});

test('guest users cannot access monitoring endpoints', function () {
    $response = $this->get('/admin/monitoring/metrics');
    $response->assertRedirect('/login');
    
    $response = $this->get('/admin/monitoring/interfaces');
    $response->assertRedirect('/login');
});

test('monitoring metrics endpoint contains expected total_users count', function () {
    $admin = User::where('email', 'admin@demo.test')->first();
    
    // Create additional users
    User::factory()->count(2)->create();
    
    $response = $this->actingAs($admin)->get('/admin/monitoring/metrics');
    
    $response->assertStatus(200);
    
    $data = $response->json();
    // Should have admin + 2 created users = 3 total
    expect($data['global']['total_users'])->toBe(3);
});