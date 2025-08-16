<?php

declare(strict_types=1);

use App\Domain\Reporting\DTO\ExportRequestData;
use App\Domain\Reporting\Services\ExportService;
use App\Jobs\ProcessExportJob;
use App\Models\Export;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;

uses(RefreshDatabase::class);

it('can request export and creates job', function () {
    Queue::fake();
    
    $user = User::factory()->create();
    $exportService = app(ExportService::class);
    
    $export = $exportService->requestExport(
        'orders_summary',
        'csv',
        ['date_from' => '2024-01-01', 'date_to' => '2024-01-31'],
        $user
    );
    
    expect($export)->toBeInstanceOf(Export::class)
        ->and($export->report_key)->toBe('orders_summary')
        ->and($export->format)->toBe('csv')
        ->and($export->status)->toBe('queued')
        ->and($export->requested_by)->toBe($user->id);
    
    Queue::assertPushed(ProcessExportJob::class);
});

it('validates report exists when requesting export', function () {
    $user = User::factory()->create();
    $exportService = app(ExportService::class);
    
    expect(fn() => $exportService->requestExport(
        'unknown_report',
        'csv',
        [],
        $user
    ))->toThrow(\App\Domain\Reporting\Exceptions\ReportException::class);
});

it('validates format is allowed when requesting export', function () {
    $user = User::factory()->create();
    $exportService = app(ExportService::class);
    
    expect(fn() => $exportService->requestExport(
        'orders_summary',
        'xlsx', // Not allowed format
        [],
        $user
    ))->toThrow(InvalidArgumentException::class);
});

it('can check user access to export', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    
    $export = Export::factory()->create(['requested_by' => $user1->id]);
    $exportService = app(ExportService::class);
    
    expect($exportService->canUserAccessExport($export, $user1))->toBeTrue()
        ->and($exportService->canUserAccessExport($export, $user2))->toBeFalse()
        ->and($exportService->canUserAccessExport($export, $admin))->toBeTrue();
});