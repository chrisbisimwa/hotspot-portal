<?php

declare(strict_types=1);

namespace App\Domain\Billing\DTO;

readonly class InitiatePaymentResponse
{
    public function __construct(
        public string $reference,
        public ?string $redirect_url = null,
        public array $raw = []
    ) {}

    public function toArray(): array
    {
        return [
            'reference' => $this->reference,
            'redirect_url' => $this->redirect_url,
            'raw' => $this->raw,
        ];
    }
}