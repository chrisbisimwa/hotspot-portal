<?php

declare(strict_types=1);

namespace App\Domain\Billing\DTO;

use App\Enums\PaymentStatus;

readonly class CallbackParseResult
{
    public function __construct(
        public string $reference,
        public PaymentStatus $status,
        public float $amount,
        public array $raw = [],
        public bool $signature_valid = false
    ) {}

    public function toArray(): array
    {
        return [
            'reference' => $this->reference,
            'status' => $this->status->value,
            'amount' => $this->amount,
            'raw' => $this->raw,
            'signature_valid' => $this->signature_valid,
        ];
    }
}