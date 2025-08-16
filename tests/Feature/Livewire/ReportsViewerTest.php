<?php

declare(strict_types=1);

use App\Livewire\Admin\Reports\ReportViewer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('can render orders summary report', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    
    Livewire::actingAs($admin)
        ->test(ReportViewer::class, ['reportKey' => 'orders_summary'])
        ->assertSuccessful()
        ->assertSee('Orders Summary Report');
});

it('prevents non-admin access to reports', function () {
    $user = User::factory()->create();
    
    Livewire::actingAs($user)
        ->test(ReportViewer::class, ['reportKey' => 'orders_summary'])
        ->assertForbidden();
});

it('handles unknown report key', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    
    expect(fn() => Livewire::actingAs($admin)
        ->test(ReportViewer::class, ['reportKey' => 'unknown_report']))
        ->toThrow(Symfony\Component\HttpKernel\Exception\NotFoundHttpException::class);
});