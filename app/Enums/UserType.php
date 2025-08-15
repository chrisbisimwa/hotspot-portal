<?php

declare(strict_types=1);

namespace App\Enums;

use App\Enums\Concerns\EnumHelpers;

enum UserType: string
{
    use EnumHelpers;

    case USER = 'user';
    case ADMIN = 'admin';
    case AGENT = 'agent';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}