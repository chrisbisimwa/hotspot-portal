<?php

declare(strict_types=1);

namespace App\Enums;

use App\Enums\Concerns\EnumHelpers;

enum OrderStatus: string
{
    use EnumHelpers;

    case PENDING = 'pending';
    case PAYMENT_RECEIVED = 'payment_received';
    case PROCESSING = 'processing';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';
    case EXPIRED = 'expired';
}