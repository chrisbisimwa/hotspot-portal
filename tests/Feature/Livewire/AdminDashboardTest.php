<?php

declare(strict_types=1);

use App\Livewire\Admin\Dashboard;
use App\Models\User;
use Livewire\Livewire;

it('can render admin dashboard for admin users', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $this->actingAs($admin);

    Livewire::test(Dashboard::class)
        ->assertSuccessful()
        ->assertSee('Admin Dashboard')
        ->assertSee('Total Users')
        ->assertSee('Active Users');
});

it('denies access to non-admin users', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    Livewire::test(Dashboard::class)
        ->assertForbidden();
});

it('displays metrics correctly', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $this->actingAs($admin);

    Livewire::test(Dashboard::class)
        ->assertSuccessful()
        ->assertViewHas('metrics')
        ->assertViewHas('systemMetrics');
});