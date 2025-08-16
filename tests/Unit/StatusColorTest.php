<?php

declare(strict_types=1);

use App\Support\StatusColor;

it('returns correct colors for order statuses', function () {
    expect(StatusColor::color('orders', 'pending'))->toBe('secondary');
    expect(StatusColor::color('orders', 'completed'))->toBe('success');
    expect(StatusColor::color('orders', 'cancelled'))->toBe('dark');
    expect(StatusColor::color('orders', 'expired'))->toBe('danger');
});

it('returns correct colors for payment statuses', function () {
    expect(StatusColor::color('payments', 'pending'))->toBe('secondary');
    expect(StatusColor::color('payments', 'success'))->toBe('success');
    expect(StatusColor::color('payments', 'failed'))->toBe('danger');
    expect(StatusColor::color('payments', 'refunded'))->toBe('primary');
});

it('returns default color for unknown status', function () {
    expect(StatusColor::color('orders', 'unknown_status'))->toBe('secondary');
    expect(StatusColor::color('unknown_domain', 'any_status'))->toBe('secondary');
});

it('handles case insensitive input', function () {
    expect(StatusColor::color('ORDERS', 'PENDING'))->toBe('secondary');
    expect(StatusColor::color('Orders', 'Pending'))->toBe('secondary');
});

it('can check if domain exists', function () {
    expect(StatusColor::hasDomain('orders'))->toBeTrue();
    expect(StatusColor::hasDomain('payments'))->toBeTrue();
    expect(StatusColor::hasDomain('unknown'))->toBeFalse();
});

it('can get statuses for domain', function () {
    $orderStatuses = StatusColor::getStatusesForDomain('orders');
    
    expect($orderStatuses)->toContain('pending', 'completed', 'cancelled', 'expired');
    expect(StatusColor::getStatusesForDomain('unknown'))->toBe([]);
});