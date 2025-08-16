<?php

declare(strict_types=1);

use App\Domain\Reporting\Builders\OrdersSummaryReportBuilder;
use App\Domain\Reporting\Services\ReportRegistry;

it('can register report builders', function () {
    $registry = new ReportRegistry();
    $builder = new OrdersSummaryReportBuilder();
    
    $registry->register($builder);
    
    expect($registry->has('orders_summary'))->toBeTrue()
        ->and($registry->get('orders_summary'))->toBe($builder);
});

it('throws exception for unknown report', function () {
    $registry = new ReportRegistry();
    
    expect(fn() => $registry->get('unknown_report'))
        ->toThrow(\App\Domain\Reporting\Exceptions\ReportException::class, 'Unknown report: unknown_report');
});

it('registry lists registered reports', function () {
    $registry = new ReportRegistry();
    $builder = new OrdersSummaryReportBuilder();
    
    $registry->register($builder);
    
    $metadata = $registry->metadata();
    
    expect($metadata)->toHaveKey('orders_summary')
        ->and($metadata['orders_summary'])->toHaveKeys([
            'identifier', 'title', 'description', 'default_format', 'allowed_formats', 'filters_schema'
        ]);
});