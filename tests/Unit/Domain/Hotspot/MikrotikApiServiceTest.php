<?php

declare(strict_types=1);

use App\Domain\Hotspot\DTO\HotspotUserProvisionData;
use App\Domain\Hotspot\Services\MikrotikApiService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(Tests\TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    config(['mikrotik.fake' => true]);
});

test('fake mode create user returns result', function () {
    $service = new MikrotikApiService();
    
    $data = new HotspotUserProvisionData(
        username: 'test-user',
        password: 'test-pass',
        profileName: 'default',
        validityMinutes: 120,
        dataLimitMb: 500
    );

    $result = $service->createUser($data);

    expect($result->username)->toBe('test-user')
        ->and($result->mikrotik_id)->toStartWith('*')
        ->and($result->raw)->toHaveKey('fake_mode')
        ->and($result->raw['fake_mode'])->toBeTrue();
});

test('fake mode get ap interfaces load structure', function () {
    $service = new MikrotikApiService();
    
    $interfaces = $service->getApInterfacesLoad();

    expect($interfaces)->toBeArray()
        ->and($interfaces)->not->toBeEmpty();

    foreach ($interfaces as $interface) {
        expect($interface)->toHaveKeys(['interface', 'connected_users', 'last_sync_at'])
            ->and($interface['connected_users'])->toBeInt()
            ->and($interface['interface'])->toBeString();
    }
});

test('fake mode methods return expected types', function () {
    $service = new MikrotikApiService();

    expect($service->ping())->toBeTrue()
        ->and($service->getUsers())->toBeArray()
        ->and($service->getActiveSessions())->toBeArray()
        ->and($service->removeUser('test-user'))->toBeTrue()
        ->and($service->disconnectUser('test-user'))->toBeTrue()
        ->and($service->suspendUser('test-user'))->toBeTrue()
        ->and($service->resumeUser('test-user'))->toBeTrue();
});

test('connect does not throw in fake mode', function () {
    $service = new MikrotikApiService();
    
    expect(fn() => $service->connect())->not->toThrow(Exception::class);
});