<?php

declare(strict_types=1);

use App\Domain\Reporting\Builders\OrdersSummaryReportBuilder;
use App\Domain\Reporting\Builders\PaymentsStatusBreakdownReportBuilder;
use App\Domain\Reporting\Builders\HotspotUsageReportBuilder;
use App\Domain\Reporting\Builders\UserGrowthReportBuilder;

it('can create orders summary report builder', function () {
    $builder = new OrdersSummaryReportBuilder();
    
    expect($builder->identifier())->toBe('orders_summary')
        ->and($builder->title())->toContain('Orders Summary')
        ->and($builder->allowedFormats())->toContain('csv', 'pdf')
        ->and($builder::filtersSchema())->toHaveKeys(['date_from', 'date_to']);
});

it('can create payments status breakdown report builder', function () {
    $builder = new PaymentsStatusBreakdownReportBuilder();
    
    expect($builder->identifier())->toBe('payments_status_breakdown')
        ->and($builder->title())->toContain('Payments Status')
        ->and($builder->allowedFormats())->toContain('csv', 'pdf')
        ->and($builder::filtersSchema())->toHaveKeys(['date_from', 'date_to']);
});

it('can create hotspot usage report builder', function () {
    $builder = new HotspotUsageReportBuilder();
    
    expect($builder->identifier())->toBe('hotspot_usage')
        ->and($builder->title())->toContain('Hotspot Usage')
        ->and($builder->allowedFormats())->toContain('csv', 'pdf')
        ->and($builder::filtersSchema())->toHaveKeys(['date_from', 'date_to']);
});

it('can create user growth report builder', function () {
    $builder = new UserGrowthReportBuilder();
    
    expect($builder->identifier())->toBe('user_growth')
        ->and($builder->title())->toContain('User Growth')
        ->and($builder->allowedFormats())->toContain('csv', 'pdf')
        ->and($builder::filtersSchema())->toHaveKeys(['date_from', 'date_to']);
});

it('orders summary builder returns expected columns', function () {
    $builder = new OrdersSummaryReportBuilder();
    $result = $builder->build();
    
    expect($result->columns)->toHaveCount(4);
    
    $expectedKeys = ['date', 'orders_count', 'total_amount', 'avg_amount'];
    $actualKeys = collect($result->columns)->pluck('key')->all();
    
    expect($actualKeys)->toBe($expectedKeys);
});