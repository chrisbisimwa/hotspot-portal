<?php

declare(strict_types=1);

namespace App\Enums\Concerns;

trait EnumHelpers
{
    /**
     * Get all enum values as an array
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get all enum labels as an array with readable format
     */
    public static function labels(): array
    {
        $labels = [];

        foreach (self::cases() as $case) {
            $labels[$case->value] = self::formatLabel($case->name);
        }

        return $labels;
    }

    /**
     * Format enum name to readable label
     */
    private static function formatLabel(string $name): string
    {
        // Convert PENDING_PAYMENT to "Pending Payment"
        return ucwords(str_replace('_', ' ', strtolower($name)));
    }
}
