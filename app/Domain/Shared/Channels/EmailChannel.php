<?php

declare(strict_types=1);

namespace App\Domain\Shared\Channels;

use App\Domain\Shared\Contracts\NotificationChannelInterface;
use App\Domain\Shared\DTO\NotificationData;
use Illuminate\Support\Facades\Log;

class EmailChannel implements NotificationChannelInterface
{
    public function send(NotificationData $data): bool
    {
        // TODO: Implement real email sending
        Log::info('Email notification sent (stub)', [
            'to' => $data->to,
            'subject' => $data->subject,
            'message' => $data->message,
            'meta' => $data->meta
        ]);
        
        return true;
    }
}