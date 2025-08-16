<?php

declare(strict_types=1);

use App\Models\FeatureFlag;
use App\Facades\Feature;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('feature flag model can be created and retrieved', function () {
    $flag = FeatureFlag::create([
        'key' => 'test_feature',
        'enabled' => true,
        'meta' => ['description' => 'Test feature']
    ]);

    expect($flag->key)->toBe('test_feature')
        ->and($flag->enabled)->toBeTrue()
        ->and($flag->meta)->toBe(['description' => 'Test feature']);
});

test('feature flag isEnabled method works correctly', function () {
    FeatureFlag::create(['key' => 'enabled_feature', 'enabled' => true]);
    FeatureFlag::create(['key' => 'disabled_feature', 'enabled' => false]);

    expect(FeatureFlag::isEnabled('enabled_feature'))->toBeTrue()
        ->and(FeatureFlag::isEnabled('disabled_feature'))->toBeFalse()
        ->and(FeatureFlag::isEnabled('nonexistent_feature'))->toBeFalse();
});

test('feature flag enable and disable methods work', function () {
    $flag = FeatureFlag::enable('dynamic_feature', ['version' => '1.0']);
    
    expect($flag->key)->toBe('dynamic_feature')
        ->and($flag->enabled)->toBeTrue()
        ->and($flag->meta)->toBe(['version' => '1.0']);

    $flag = FeatureFlag::disable('dynamic_feature');
    
    expect($flag->enabled)->toBeFalse();
});

test('feature service facade works correctly', function () {
    FeatureFlag::create(['key' => 'facade_test', 'enabled' => true]);

    expect(Feature::enabled('facade_test'))->toBeTrue()
        ->and(Feature::enabled('nonexistent'))->toBeFalse();
});

test('feature service can enable and disable flags', function () {
    $flag = Feature::enable('service_test', ['meta' => 'data']);
    
    expect($flag->key)->toBe('service_test')
        ->and($flag->enabled)->toBeTrue();
    
    expect(Feature::enabled('service_test'))->toBeTrue();
    
    Feature::disable('service_test');
    
    expect(Feature::enabled('service_test'))->toBeFalse();
});

test('feature service returns all flags', function () {
    FeatureFlag::create(['key' => 'flag1', 'enabled' => true]);
    FeatureFlag::create(['key' => 'flag2', 'enabled' => false]);

    $all = Feature::all();
    
    expect($all)->toHaveCount(2)
        ->and($all)->toHaveKey('flag1')
        ->and($all)->toHaveKey('flag2');
});

test('feature service returns enabled flags only', function () {
    FeatureFlag::create(['key' => 'enabled1', 'enabled' => true]);
    FeatureFlag::create(['key' => 'enabled2', 'enabled' => true]);
    FeatureFlag::create(['key' => 'disabled1', 'enabled' => false]);

    $enabled = Feature::getAllEnabled();
    
    expect($enabled)->toHaveCount(2)
        ->and($enabled)->toHaveKey('enabled1')
        ->and($enabled)->toHaveKey('enabled2')
        ->and($enabled)->not->toHaveKey('disabled1');
});

test('feature service bulk operations work', function () {
    Feature::setBulk([
        'bulk1' => true,
        'bulk2' => false,
        'bulk3' => true
    ]);

    expect(Feature::enabled('bulk1'))->toBeTrue()
        ->and(Feature::enabled('bulk2'))->toBeFalse()
        ->and(Feature::enabled('bulk3'))->toBeTrue();
});