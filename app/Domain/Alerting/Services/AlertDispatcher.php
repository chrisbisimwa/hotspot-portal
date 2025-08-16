<?php

declare(strict_types=1);

namespace App\Domain\Alerting\Services;

use App\Domain\Alerting\Channels\EmailAlertChannel;
use App\Domain\Alerting\Channels\SlackAlertChannel;
use App\Domain\Alerting\Contracts\AlertChannelInterface;
use App\Domain\Alerting\DTO\AlertMessage;
use App\Enums\IncidentSeverity;
use Illuminate\Support\Facades\Log;

class AlertDispatcher
{
    private array $channels = [];

    public function __construct()
    {
        $this->initializeChannels();
    }

    public function dispatch(AlertMessage $message): void
    {
        $activeChannels = $this->getActiveChannelsForSeverity($message->severity);
        
        Log::info('Dispatching alert', [
            'code' => $message->code,
            'severity' => $message->severity->value,
            'channels' => array_keys($activeChannels),
        ]);

        foreach ($activeChannels as $channelName => $channel) {
            try {
                $success = $channel->send($message);
                
                Log::info("Alert channel {$channelName} result", [
                    'code' => $message->code,
                    'success' => $success,
                ]);
            } catch (\Exception $e) {
                Log::error("Alert channel {$channelName} failed", [
                    'code' => $message->code,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    private function initializeChannels(): void
    {
        $configuredChannels = config('alerting.channels', []);

        if (in_array('slack', $configuredChannels)) {
            $this->channels['slack'] = new SlackAlertChannel();
        }

        if (in_array('email', $configuredChannels)) {
            $this->channels['email'] = new EmailAlertChannel();
        }
    }

    private function getActiveChannelsForSeverity(IncidentSeverity $severity): array
    {
        $activeChannels = [];

        foreach ($this->channels as $channelName => $channel) {
            if ($this->shouldSendToChannel($channelName, $severity)) {
                $activeChannels[$channelName] = $channel;
            }
        }

        return $activeChannels;
    }

    private function shouldSendToChannel(string $channelName, IncidentSeverity $severity): bool
    {
        $minSeverityConfig = match ($channelName) {
            'email' => config('alerting.severity_email_min', 'high'),
            'slack' => config('alerting.severity_slack_min', 'medium'),
            default => 'low',
        };

        $minSeverity = IncidentSeverity::from($minSeverityConfig);
        
        return $severity->priority() >= $minSeverity->priority();
    }

    public function addChannel(string $name, AlertChannelInterface $channel): void
    {
        $this->channels[$name] = $channel;
    }
}