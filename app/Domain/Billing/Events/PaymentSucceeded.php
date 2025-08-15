<?php

declare(strict_types=1);

namespace App\Domain\Billing\Events;

use App\Models\Payment;

class PaymentSucceeded
{
    public function __construct(
        public readonly Payment $payment
    ) {
    }
}