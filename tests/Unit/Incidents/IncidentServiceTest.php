<?php

declare(strict_types=1);

use App\Domain\Incidents\Services\IncidentService;
use App\Enums\IncidentSeverity;
use App\Enums\IncidentStatus;
use App\Models\Incident;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('opens new incidents correctly', function () {
    $service = app(IncidentService::class);
    
    $incident = $service->open(
        'test_code',
        'Test Incident',
        IncidentSeverity::HIGH,
        ['context' => 'test']
    );

    expect($incident)->toBeInstanceOf(Incident::class);
    expect($incident->status)->toBe(IncidentStatus::OPEN);
    expect($incident->severity)->toBe(IncidentSeverity::HIGH);
    expect($incident->title)->toBe('Test Incident');
    expect($incident->slug)->not->toBeEmpty();
    expect($incident->meta['code'])->toBe('test_code');
    expect($incident->started_at)->not->toBeNull();
    expect($incident->detected_at)->not->toBeNull();
});

it('transitions incident status correctly', function () {
    $user = User::factory()->create();
    $service = app(IncidentService::class);
    
    $incident = $service->open(
        'test_code',
        'Test Incident',
        IncidentSeverity::HIGH
    );

    // Transition to monitoring
    $success = $service->transition($incident, IncidentStatus::MONITORING, null, $user);
    
    expect($success)->toBeTrue();
    $incident->refresh();
    expect($incident->status)->toBe(IncidentStatus::MONITORING);
    expect($incident->updated_by)->toBe($user->id);

    // Transition to resolved
    $success = $service->transition($incident, IncidentStatus::RESOLVED, null, $user);
    
    expect($success)->toBeTrue();
    $incident->refresh();
    expect($incident->status)->toBe(IncidentStatus::RESOLVED);
    expect($incident->resolved_at)->not->toBeNull();
    expect($incident->closed_at)->not->toBeNull();
});

it('does not transition to same status', function () {
    $service = app(IncidentService::class);
    
    $incident = $service->open(
        'test_code',
        'Test Incident',
        IncidentSeverity::HIGH
    );

    $success = $service->transition($incident, IncidentStatus::OPEN);
    
    expect($success)->toBeFalse();
});

it('adds updates to incidents', function () {
    $user = User::factory()->create();
    $service = app(IncidentService::class);
    
    $incident = $service->open(
        'test_code',
        'Test Incident',
        IncidentSeverity::HIGH
    );

    $update = $service->addUpdate($incident, 'Test update message', $user);
    
    expect($update->message)->toBe('Test update message');
    expect($update->user_id)->toBe($user->id);
    expect($update->incident_id)->toBe($incident->id);
});

it('prevents duplicate incident creation within window', function () {
    $service = app(IncidentService::class);
    
    // Create first incident
    $incident1 = $service->open(
        'duplicate_code',
        'First Incident',
        IncidentSeverity::HIGH
    );

    // Try to create second incident with same code
    $alertMessage = new \App\Domain\Alerting\DTO\AlertMessage(
        'duplicate_code',
        'Second Incident',
        IncidentSeverity::HIGH,
        'Test body'
    );

    $incident2 = $service->autoOpenFromAlert($alertMessage);
    
    expect($incident2)->toBeNull();
    
    // Should have added update to existing incident
    $incident1->refresh();
    expect($incident1->updates)->toHaveCount(2); // Initial + new update
});