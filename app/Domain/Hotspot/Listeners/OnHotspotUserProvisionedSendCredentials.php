<?php

declare(strict_types=1);

namespace App\Domain\Hotspot\Listeners;

use App\Domain\Hotspot\Events\HotspotUserProvisioned;
use App\Domain\Shared\Services\NotificationService;
use Illuminate\Support\Facades\Log;

class OnHotspotUserProvisionedSendCredentials
{
    public function __construct(
        private NotificationService $notificationService
    ) {
    }

    public function handle(HotspotUserProvisioned $event): void
    {
        $hotspotUser = $event->hotspotUser;
        $profile = $hotspotUser->userProfile;
        $owner = $hotspotUser->owner;

        Log::info('Sending credentials notification', [
            'hotspot_user_id' => $hotspotUser->id,
            'username' => $hotspotUser->username,
            'owner_id' => $owner->id
        ]);

        // Build the message with credentials
        $message = $this->buildCredentialsMessage($hotspotUser);

        // Get the default notification channel
        $channel = config('notifications.default_channel', 'sms');

        try {
            // Determine recipient (phone or email based on channel)
            $recipient = $this->getRecipient($owner, $channel);

            if ($recipient) {
                if ($channel === 'sms') {
                    $this->notificationService->sendSms($recipient, $message, [
                        'user_id' => $owner->id,
                        'hotspot_user_id' => $hotspotUser->id,
                        'type' => 'credentials'
                    ]);
                } elseif ($channel === 'email') {
                    $this->notificationService->sendEmail(
                        $recipient,
                        'Vos identifiants Hotspot',
                        $message,
                        [
                            'user_id' => $owner->id,
                            'hotspot_user_id' => $hotspotUser->id,
                            'type' => 'credentials'
                        ]
                    );
                }

                Log::info('Credentials notification sent successfully', [
                    'hotspot_user_id' => $hotspotUser->id,
                    'channel' => $channel,
                    'recipient' => $recipient
                ]);
            } else {
                Log::warning('No recipient found for credentials notification', [
                    'hotspot_user_id' => $hotspotUser->id,
                    'channel' => $channel,
                    'owner_id' => $owner->id
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to send credentials notification', [
                'hotspot_user_id' => $hotspotUser->id,
                'owner_id' => $owner->id,
                'exception' => $e->getMessage()
            ]);
        }
    }

    private function buildCredentialsMessage(HotspotUser $hotspotUser): string
    {
        $profile = $hotspotUser->userProfile;
        $validityHours = round($hotspotUser->validity_minutes / 60, 1);

        return sprintf(
            "Vos identifiants Hotspot: %s / %s – Profil: %s – Validité: %s minutes.",
            $hotspotUser->username,
            $hotspotUser->password,
            $profile->name,
            $hotspotUser->validity_minutes
        );
    }

    private function getRecipient($owner, string $channel): ?string
    {
        return match ($channel) {
            'sms' => $owner->phone,
            'email' => $owner->email,
            default => $owner->email, // fallback to email
        };
    }
}