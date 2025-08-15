<?php

declare(strict_types=1);

namespace App\Domain\Hotspot\Listeners;

use App\Domain\Billing\Events\PaymentSucceeded;
use App\Domain\Hotspot\Services\HotspotProvisioningService;
use Illuminate\Support\Facades\Log;

class OnPaymentSucceededProvisionOrder
{
    public function __construct(
        private HotspotProvisioningService $provisioningService
    ) {
    }

    public function handle(PaymentSucceeded $event): void
    {
        $payment = $event->payment;
        $order = $payment->order;

        Log::info('Processing payment success for provisioning', [
            'payment_id' => $payment->id,
            'order_id' => $order->id
        ]);

        try {
            $this->provisioningService->provisionOrder($order);
        } catch (\Exception $e) {
            Log::error('Failed to provision order after payment success', [
                'payment_id' => $payment->id,
                'order_id' => $order->id,
                'exception' => $e->getMessage()
            ]);
        }
    }
}