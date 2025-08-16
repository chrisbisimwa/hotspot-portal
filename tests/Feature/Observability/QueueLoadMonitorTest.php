<?php

declare(strict_types=1);

use App\Services\Observability\QueueLoadMonitor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

test('queue load monitor returns basic metrics', function () {
    $monitor = app(QueueLoadMonitor::class);
    $metrics = $monitor->getMetrics();

    expect($metrics)->toHaveKeys([
        'pending_jobs',
        'failed_jobs',
        'oldest_job_age',
        'queue_depth_by_queue',
        'processing_rate'
    ]);
});

test('queue load monitor detects no jobs state', function () {
    $monitor = app(QueueLoadMonitor::class);
    
    expect($monitor->getPendingJobsCount())->toBe(0)
        ->and($monitor->getFailedJobsCount())->toBe(0)
        ->and($monitor->getOldestJobAge())->toBeNull()
        ->and($monitor->isCriticalState())->toBeFalse()
        ->and($monitor->hasOldJobs())->toBeFalse();
});

test('queue load monitor measures job depth by queue', function () {
    // Simulate jobs in different queues
    DB::table('jobs')->insert([
        ['queue' => 'critical', 'payload' => 'test', 'attempts' => 0, 'reserved_at' => null, 'available_at' => now()->timestamp, 'created_at' => now()->timestamp],
        ['queue' => 'critical', 'payload' => 'test', 'attempts' => 0, 'reserved_at' => null, 'available_at' => now()->timestamp, 'created_at' => now()->timestamp],
        ['queue' => 'default', 'payload' => 'test', 'attempts' => 0, 'reserved_at' => null, 'available_at' => now()->timestamp, 'created_at' => now()->timestamp],
    ]);
    
    $monitor = app(QueueLoadMonitor::class);
    $depths = $monitor->getQueueDepthByQueue();
    
    expect($depths)->toHaveKey('critical')
        ->and($depths['critical'])->toBe(2)
        ->and($depths)->toHaveKey('default')
        ->and($depths['default'])->toBe(1);
});

test('queue load monitor detects critical state with many jobs', function () {
    // Set a low threshold for testing
    config(['rate_adaptive.thresholds.queue_critical_depth' => 2]);
    
    // Insert jobs in critical queue to trigger threshold
    for ($i = 0; $i < 3; $i++) {
        DB::table('jobs')->insert([
            'queue' => 'critical',
            'payload' => 'test',
            'attempts' => 0,
            'reserved_at' => null,
            'available_at' => now()->timestamp,
            'created_at' => now()->timestamp
        ]);
    }
    
    $monitor = app(QueueLoadMonitor::class);
    
    expect($monitor->isCriticalState())->toBeTrue();
});

test('queue load monitor calculates processing rate', function () {
    $monitor = app(QueueLoadMonitor::class);
    $rate = $monitor->getProcessingRate();
    
    expect($rate)->toHaveKeys([
        'jobs_per_minute',
        'window_minutes',
        'sample_size'
    ])
        ->and($rate['jobs_per_minute'])->toBeFloat()
        ->and($rate['window_minutes'])->toBe(5);
});

test('queue load monitor logs health check without errors', function () {
    $monitor = app(QueueLoadMonitor::class);
    
    // This should not throw an exception
    $monitor->logQueueHealth();
    
    // If we get here, no exception was thrown
    expect(true)->toBeTrue();
});