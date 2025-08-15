<?php

declare(strict_types=1);

namespace App\Domain\Shared\Channels;

use App\Domain\Shared\Contracts\NotificationChannelInterface;
use App\Domain\Shared\DTO\NotificationData;
use Illuminate\Support\Facades\Log;

class SmsChannel implements NotificationChannelInterface
{
    public function send(NotificationData $data): bool
    {
        // TODO: Implement real SMS sending via provider
        Log::info('SMS notification sent (stub)', [
            'to' => $data->to,
            'message' => $data->message,
            'meta' => $data->meta
        ]);
        
        return true;
    }
}