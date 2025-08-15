<?php

declare(strict_types=1);

namespace App\Domain\Billing\DTO;

use App\Enums\PaymentStatus;

readonly class VerifyPaymentResult
{
    public function __construct(
        public PaymentStatus $status,
        public string $reference,
        public float $amount,
        public string $currency,
        public array $raw = []
    ) {}

    public function toArray(): array
    {
        return [
            'status' => $this->status->value,
            'reference' => $this->reference,
            'amount' => $this->amount,
            'currency' => $this->currency,
            'raw' => $this->raw,
        ];
    }
}