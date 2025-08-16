<?php

declare(strict_types=1);

namespace App\Domain\Alerting\Channels;

use App\Domain\Alerting\Contracts\AlertChannelInterface;
use App\Domain\Alerting\DTO\AlertMessage;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;

class SlackAlertChannel implements AlertChannelInterface
{
    private Client $client;
    private ?string $webhookUrl;

    public function __construct()
    {
        $this->client = new Client(['timeout' => 10]);
        $this->webhookUrl = config('alerting.slack_webhook_url');
    }

    public function send(AlertMessage $message): bool
    {
        if (empty($this->webhookUrl)) {
            Log::warning('Slack alert webhook URL not configured, skipping alert', [
                'alert_code' => $message->code,
                'severity' => $message->severity->value,
            ]);
            return true; // Graceful fallback
        }

        try {
            $payload = [
                'text' => "🚨 {$message->title}",
                'attachments' => [
                    [
                        'color' => $this->getSeverityColor($message->severity),
                        'fields' => [
                            [
                                'title' => 'Severity',
                                'value' => $message->severity->label(),
                                'short' => true,
                            ],
                            [
                                'title' => 'Code',
                                'value' => $message->code,
                                'short' => true,
                            ],
                            [
                                'title' => 'Time',
                                'value' => $message->occurredAt->format('Y-m-d H:i:s T'),
                                'short' => false,
                            ],
                            [
                                'title' => 'Details',
                                'value' => $message->body,
                                'short' => false,
                            ],
                        ],
                        'footer' => 'Hotspot Portal',
                        'ts' => $message->occurredAt->timestamp,
                    ],
                ],
            ];

            $response = $this->client->post($this->webhookUrl, [
                'json' => $payload,
                'headers' => ['Content-Type' => 'application/json'],
            ]);

            return $response->getStatusCode() === 200;
        } catch (GuzzleException $e) {
            Log::error('Failed to send Slack alert', [
                'alert_code' => $message->code,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    private function getSeverityColor(\App\Enums\IncidentSeverity $severity): string
    {
        return match ($severity) {
            \App\Enums\IncidentSeverity::CRITICAL => 'danger',
            \App\Enums\IncidentSeverity::HIGH => 'warning',
            \App\Enums\IncidentSeverity::MEDIUM => '#ffa500',
            \App\Enums\IncidentSeverity::LOW => 'good',
        };
    }
}