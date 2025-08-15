<?php

declare(strict_types=1);

namespace App\Domain\Hotspot\Events;

use App\Models\Order;

class OrderCompleted
{
    public function __construct(
        public readonly Order $order
    ) {
    }
}