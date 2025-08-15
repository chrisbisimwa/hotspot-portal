<?php

declare(strict_types=1);

namespace App\Domain\Shared\Services;

use App\Domain\Shared\Channels\EmailChannel;
use App\Domain\Shared\Channels\SmsChannel;
use App\Domain\Shared\Contracts\NotificationChannelInterface;
use App\Domain\Shared\DTO\NotificationData;
use App\Enums\NotificationStatus;
use App\Models\Notification;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    private array $channels = [];

    public function __construct()
    {
        $this->channels = [
            'sms' => new SmsChannel(),
            'email' => new EmailChannel(),
        ];
    }

    public function sendSms(string $to, string $message, array $meta = []): bool
    {
        $data = new NotificationData(
            to: $to,
            message: $message,
            channel: 'sms',
            meta: $meta
        );

        return $this->send($data);
    }

    public function sendEmail(string $to, string $subject, string $message, array $meta = []): bool
    {
        $data = new NotificationData(
            to: $to,
            message: $message,
            channel: 'email',
            subject: $subject,
            meta: $meta
        );

        return $this->send($data);
    }

    public function sendWhatsapp(string $to, string $message, array $meta = []): bool
    {
        // TODO: Implement WhatsApp channel
        Log::info('WhatsApp notification (not implemented)', [
            'to' => $to,
            'message' => $message,
            'meta' => $meta
        ]);

        return $this->logNotification('whatsapp', $to, null, $message, NotificationStatus::FAILED, 'WhatsApp channel not implemented', $meta);
    }

    private function send(NotificationData $data): bool
    {
        // Create notification record as queued
        $notification = $this->logNotification(
            $data->channel,
            $data->to,
            $data->subject,
            $data->message,
            NotificationStatus::QUEUED,
            null,
            $data->meta
        );

        try {
            $channel = $this->getChannel($data->channel);
            $success = $channel->send($data);

            // Update notification status
            if ($success) {
                $notification->update([
                    'status' => NotificationStatus::SENT->value,
                    'sent_at' => now(),
                ]);
            } else {
                $notification->update([
                    'status' => NotificationStatus::FAILED->value,
                    'failed_at' => now(),
                    'error_message' => 'Channel returned false',
                ]);
            }

            return $success;
        } catch (\Exception $e) {
            $notification->update([
                'status' => NotificationStatus::FAILED->value,
                'failed_at' => now(),
                'error_message' => $e->getMessage(),
            ]);

            Log::error('Notification sending failed', [
                'notification_id' => $notification->id,
                'channel' => $data->channel,
                'to' => $data->to,
                'exception' => $e->getMessage()
            ]);

            return false;
        }
    }

    private function getChannel(string $channelName): NotificationChannelInterface
    {
        if (!isset($this->channels[$channelName])) {
            throw new \InvalidArgumentException("Unknown notification channel: {$channelName}");
        }

        return $this->channels[$channelName];
    }

    private function logNotification(
        string $channel,
        string $to,
        ?string $subject,
        string $message,
        NotificationStatus $status,
        ?string $errorMessage = null,
        array $meta = []
    ): Notification {
        return Notification::create([
            'channel' => $channel,
            'to' => $to,
            'subject' => $subject,
            'message' => $message,
            'status' => $status->value,
            'error_message' => $errorMessage,
            'meta' => $meta,
        ]);
    }
}