<?php

declare(strict_types=1);

namespace App\Domain\Hotspot\DTO;

readonly class MikrotikUserResult
{
    public function __construct(
        public string $username,
        public ?string $mikrotik_id = null,
        public array $raw = []
    ) {}

    public function toArray(): array
    {
        return [
            'username' => $this->username,
            'mikrotik_id' => $this->mikrotik_id,
            'raw' => $this->raw,
        ];
    }
}