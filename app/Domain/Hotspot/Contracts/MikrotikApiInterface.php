<?php

declare(strict_types=1);

namespace App\Domain\Hotspot\Contracts;

use App\Domain\Hotspot\DTO\HotspotUserProvisionData;
use App\Domain\Hotspot\DTO\MikrotikUserResult;

interface MikrotikApiInterface
{
    /**
     * Connect to the Mikrotik router
     */
    public function connect(): void;

    /**
     * Create a new hotspot user
     */
    public function createUser(HotspotUserProvisionData $data): MikrotikUserResult;

    /**
     * Remove a hotspot user
     */
    public function removeUser(string $username): bool;

    /**
     * Get all hotspot users
     */
    public function getUsers(): array;

    /**
     * Get active hotspot sessions
     */
    public function getActiveSessions(): array;

    /**
     * Disconnect a user session
     */
    public function disconnectUser(string $username): bool;

    /**
     * Suspend a hotspot user
     */
    public function suspendUser(string $username): bool;

    /**
     * Resume a suspended hotspot user
     */
    public function resumeUser(string $username): bool;

    /**
     * Get access point interface load statistics
     */
    public function getApInterfacesLoad(): array;

    /**
     * Ping the Mikrotik router to check connectivity
     */
    public function ping(): bool;
}