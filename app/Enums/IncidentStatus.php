<?php

declare(strict_types=1);

namespace App\Enums;

enum IncidentStatus: string
{
    case OPEN = 'open';
    case MONITORING = 'monitoring';
    case MITIGATED = 'mitigated';
    case RESOLVED = 'resolved';
    case FALSE_POSITIVE = 'false_positive';

    public function label(): string
    {
        return match ($this) {
            self::OPEN => 'Open',
            self::MONITORING => 'Monitoring',
            self::MITIGATED => 'Mitigated',
            self::RESOLVED => 'Resolved',
            self::FALSE_POSITIVE => 'False Positive',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::OPEN => 'red',
            self::MONITORING => 'yellow',
            self::MITIGATED => 'blue',
            self::RESOLVED => 'green',
            self::FALSE_POSITIVE => 'gray',
        };
    }
}