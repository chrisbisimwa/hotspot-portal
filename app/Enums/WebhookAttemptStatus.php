<?php

declare(strict_types=1);

namespace App\Enums;

enum WebhookAttemptStatus: string
{
    case PENDING = 'pending';
    case SUCCESS = 'success';
    case FAILED = 'failed';
    case DISCARDED = 'discarded';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::SUCCESS => 'Success',
            self::FAILED => 'Failed',
            self::DISCARDED => 'Discarded',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PENDING => 'yellow',
            self::SUCCESS => 'green',
            self::FAILED => 'red',
            self::DISCARDED => 'gray',
        };
    }
}