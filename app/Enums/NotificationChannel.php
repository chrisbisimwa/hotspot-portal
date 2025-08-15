<?php

declare(strict_types=1);

namespace App\Enums;

use App\Enums\Concerns\EnumHelpers;

enum NotificationChannel: string
{
    use EnumHelpers;

    case SMS = 'sms';
    case EMAIL = 'email';
    case WHATSAPP = 'whatsapp';
    case PUSH = 'push';
    case WEBHOOK = 'webhook';
}