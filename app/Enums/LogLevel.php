<?php

declare(strict_types=1);

namespace App\Enums;

use App\Enums\Concerns\EnumHelpers;

enum LogLevel: string
{
    use EnumHelpers;

    case INFO = 'info';
    case WARNING = 'warning';
    case ERROR = 'error';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}