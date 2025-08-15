<?php

declare(strict_types=1);

namespace App\Domain\Hotspot\DTO;

readonly class HotspotUserProvisionData
{
    public function __construct(
        public string $username,
        public string $password,
        public string $profileName,
        public int $validityMinutes,
        public ?int $dataLimitMb = null
    ) {}

    public function toArray(): array
    {
        return [
            'username' => $this->username,
            'password' => $this->password,
            'profile_name' => $this->profileName,
            'validity_minutes' => $this->validityMinutes,
            'data_limit_mb' => $this->dataLimitMb,
        ];
    }
}