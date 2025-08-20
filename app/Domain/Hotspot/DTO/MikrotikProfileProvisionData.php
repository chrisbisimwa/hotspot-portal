<?php

declare(strict_types=1);

namespace App\Domain\Hotspot\DTO;

readonly class MikrotikProfileProvisionData
{
    public function __construct(
        public string $name,
        public ?string $rateLimit = null,
        public ?string $sessionTimeout = null,
        public ?string $idleTimeout = null,
        public ?string $keepaliveTimeout = null,
        public ?int $sharedUsers = null,
    ) {}

    public function toPayload(): array
    {
        $payload = ['name' => $this->name];

        if ($this->rateLimit)        $payload['rate-limit'] = $this->rateLimit;
        if ($this->sessionTimeout)   $payload['session-timeout'] = $this->sessionTimeout;
        if ($this->idleTimeout)      $payload['idle-timeout'] = $this->idleTimeout;
        if ($this->keepaliveTimeout) $payload['keepalive-timeout'] = $this->keepaliveTimeout;
        if ($this->sharedUsers !== null) $payload['shared-users'] = (string)$this->sharedUsers;

        return $payload;
    }
}