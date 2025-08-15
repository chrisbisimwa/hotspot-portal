<?php

declare(strict_types=1);

namespace App\Domain\Shared\Contracts;

use App\Domain\Shared\DTO\NotificationData;

interface NotificationChannelInterface
{
    public function send(NotificationData $data): bool;
}