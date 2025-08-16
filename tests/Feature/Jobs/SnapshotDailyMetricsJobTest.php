<?php

declare(strict_types=1);

use App\Jobs\SnapshotDailyMetricsJob;
use App\Models\MetricSnapshot;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('snapshot creates metric records', function () {
    $job = new SnapshotDailyMetricsJob('2024-01-01');
    
    // Mock metrics service to return test data
    $this->mock(\App\Domain\Monitoring\Services\MetricsService::class, function ($mock) {
        $mock->shouldReceive('global')->andReturn([
            'total_users' => 100,
            'active_users' => 80,
        ]);
        $mock->shouldReceive('system')->andReturn([
            'memory_usage' => ['current' => 1000000],
        ]);
    });
    
    $job->handle(app(\App\Domain\Monitoring\Services\MetricsService::class));
    
    expect(MetricSnapshot::count())->toBeGreaterThan(0);
    
    $snapshot = MetricSnapshot::where('metric_key', 'global.total_users')->first();
    expect($snapshot)->not->toBeNull()
        ->and($snapshot->value)->toBe(['numeric' => 100]);
});

it('can retrieve snapshot for specific date', function () {
    MetricSnapshot::create([
        'snapshot_date' => '2024-01-01',
        'metric_key' => 'test.metric',
        'value' => ['value' => 'test'],
        'created_at' => now(),
    ]);
    
    $snapshots = MetricSnapshot::forDate('2024-01-01')->get();
    
    expect($snapshots)->toHaveCount(1);
});