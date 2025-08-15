<?php

declare(strict_types=1);

namespace App\Domain\Shared\DTO;

class NotificationData
{
    public function __construct(
        public readonly string $to,
        public readonly string $message,
        public readonly string $channel,
        public readonly ?string $subject = null,
        public readonly array $meta = []
    ) {
    }
}