<?php

declare(strict_types=1);

namespace App\Domain\Alerting\Contracts;

use App\Domain\Alerting\DTO\AlertMessage;

interface AlertChannelInterface
{
    public function send(AlertMessage $message): bool;
}