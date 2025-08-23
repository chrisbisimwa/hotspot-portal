<?php

declare(strict_types=1);

it('returns trends json for admin', function () {
    $admin = \App\Models\User::factory()->create();
    $admin->assignRole('admin'); // selon ton système de rôles

    actingAs($admin)
        ->get(route('admin.orders.trends'))
        ->assertOk()
        ->assertJsonStructure([
            'labels',
            'datasets' => [
                ['label', 'data']
            ],
            'total',
            'range_days',
        ]);
});

it('rejects non admin', function () {
    $user = \App\Models\User::factory()->create();
    actingAs($user)
        ->get(route('admin.orders.trends'))
        ->assertStatus(403); // ou redirection si middleware diffère
});