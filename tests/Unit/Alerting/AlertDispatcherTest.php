<?php

declare(strict_types=1);

use App\Domain\Alerting\Channels\EmailAlertChannel;
use App\Domain\Alerting\Channels\SlackAlertChannel;
use App\Domain\Alerting\DTO\AlertMessage;
use App\Domain\Alerting\Services\AlertDispatcher;
use App\Enums\IncidentSeverity;

it('dispatches alerts to configured channels based on severity', function () {
    config([
        'alerting.channels' => ['slack', 'email'],
        'alerting.severity_slack_min' => 'medium',
        'alerting.severity_email_min' => 'high',
    ]);

    $dispatcher = new AlertDispatcher();
    
    // Mock channels
    $slackChannel = Mockery::mock(SlackAlertChannel::class);
    $emailChannel = Mockery::mock(EmailAlertChannel::class);
    
    $dispatcher->addChannel('slack', $slackChannel);
    $dispatcher->addChannel('email', $emailChannel);

    // Test CRITICAL alert - should go to both channels
    $criticalAlert = new AlertMessage(
        'test_critical',
        'Critical Test Alert',
        IncidentSeverity::CRITICAL,
        'This is a critical test alert'
    );

    $slackChannel->shouldReceive('send')->once()->with($criticalAlert)->andReturn(true);
    $emailChannel->shouldReceive('send')->once()->with($criticalAlert)->andReturn(true);

    $dispatcher->dispatch($criticalAlert);

    // Test MEDIUM alert - should only go to Slack
    $mediumAlert = new AlertMessage(
        'test_medium',
        'Medium Test Alert', 
        IncidentSeverity::MEDIUM,
        'This is a medium test alert'
    );

    $slackChannel->shouldReceive('send')->once()->with($mediumAlert)->andReturn(true);
    // Email channel should NOT receive this alert

    $dispatcher->dispatch($mediumAlert);

    // Test LOW alert - should not go to any channel
    $lowAlert = new AlertMessage(
        'test_low',
        'Low Test Alert',
        IncidentSeverity::LOW, 
        'This is a low test alert'
    );

    // Neither channel should receive this alert
    $dispatcher->dispatch($lowAlert);
});

it('gracefully handles channel failures', function () {
    config([
        'alerting.channels' => ['slack'],
        'alerting.severity_slack_min' => 'low',
    ]);

    $dispatcher = new AlertDispatcher();
    
    $slackChannel = Mockery::mock(SlackAlertChannel::class);
    $dispatcher->addChannel('slack', $slackChannel);

    $alert = new AlertMessage(
        'test_alert',
        'Test Alert',
        IncidentSeverity::HIGH,
        'This is a test alert'
    );

    $slackChannel->shouldReceive('send')->once()->with($alert)->andThrow(new Exception('Channel failed'));

    // Should not throw exception
    expect(fn() => $dispatcher->dispatch($alert))->not->toThrow();
});