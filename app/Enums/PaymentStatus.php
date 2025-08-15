<?php

declare(strict_types=1);

namespace App\Enums;

use App\Enums\Concerns\EnumHelpers;

enum PaymentStatus: string
{
    use EnumHelpers;

    case PENDING = 'pending';
    case INITIATED = 'initiated';
    case PROCESSING = 'processing';
    case SUCCESS = 'success';
    case FAILED = 'failed';
    case CANCELLED = 'cancelled';
    case REFUNDED = 'refunded';
}