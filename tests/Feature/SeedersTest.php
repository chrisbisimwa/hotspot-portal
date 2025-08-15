<?php

declare(strict_types=1);

use App\Models\User;
use App\Models\UserProfile;
use Database\Seeders\AdminUserSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\UserProfilesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

test('admin user seeded', function () {
    // Run the seeders
    $this->seed([
        RolesAndPermissionsSeeder::class,
        AdminUserSeeder::class,
    ]);

    // Check admin user exists
    $admin = User::where('email', 'admin@demo.test')->first();

    expect($admin)->not->toBeNull()
        ->and($admin->name)->toBe('Super Admin')
        ->and($admin->phone)->toBe('+00000000000')
        ->and($admin->user_type)->toBe('admin')
        ->and($admin->status)->toBe('active')
        ->and($admin->hasRole('admin'))->toBeTrue();
});

test('roles exist', function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    expect(Role::where('name', 'admin')->exists())->toBeTrue()
        ->and(Role::where('name', 'agent')->exists())->toBeTrue()
        ->and(Role::where('name', 'user')->exists())->toBeTrue();
});

test('profiles seeded', function () {
    $this->seed(UserProfilesSeeder::class);

    $profiles = UserProfile::all();

    expect($profiles->count())->toBeGreaterThanOrEqual(3);

    // Check specific profiles exist
    expect(UserProfile::where('name', '2H')->exists())->toBeTrue()
        ->and(UserProfile::where('name', '1DAY')->exists())->toBeTrue()
        ->and(UserProfile::where('name', '1WEEK')->exists())->toBeTrue();

    // Check 2H profile details
    $twoHourProfile = UserProfile::where('name', '2H')->first();
    expect($twoHourProfile->validity_minutes)->toBe(120)
        ->and($twoHourProfile->price)->toBe('1.50')
        ->and($twoHourProfile->is_active)->toBeTrue();
});

test('database seeder runs without errors', function () {
    // This should run all seeders without throwing exceptions
    $this->seed();

    // Verify key data exists
    expect(User::where('email', 'admin@demo.test')->exists())->toBeTrue()
        ->and(Role::count())->toBeGreaterThanOrEqual(3)
        ->and(UserProfile::count())->toBeGreaterThanOrEqual(3);
});
