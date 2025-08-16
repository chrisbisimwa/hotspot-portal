<?php

declare(strict_types=1);

use App\Livewire\User\Dashboard;
use App\Models\User;
use Livewire\Livewire;

it('can render user dashboard for authenticated users', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    Livewire::test(Dashboard::class)
        ->assertSuccessful()
        ->assertSee('Welcome back, ' . $user->name)
        ->assertSee('Total Orders')
        ->assertSee('Recent Orders');
});

it('denies access to unauthenticated users', function () {
    Livewire::test(Dashboard::class)
        ->assertForbidden();
});

it('displays user metrics correctly', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    Livewire::test(Dashboard::class)
        ->assertSuccessful()
        ->assertViewHas('userMetrics')
        ->assertViewHas('recentOrders')
        ->assertViewHas('recentNotifications');
});