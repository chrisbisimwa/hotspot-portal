<?php

declare(strict_types=1);

namespace App\Domain\Hotspot\Listeners;

use App\Domain\Hotspot\Events\OrderCompleted;
use Illuminate\Support\Facades\Log;

class OnOrderCompletedSendSummary
{
    public function handle(OrderCompleted $event): void
    {
        $order = $event->order;

        Log::info('Order completed - summary notification (placeholder)', [
            'order_id' => $order->id,
            'user_id' => $order->user_id,
            'quantity' => $order->quantity
        ]);

        // TODO: Implement order completion summary notification
        // This could include:
        // - Order summary with all provisioned users
        // - Total amount paid
        // - Profile details
        // - Links to access the users
    }
}