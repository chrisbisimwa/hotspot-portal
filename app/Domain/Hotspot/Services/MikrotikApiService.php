<?php

declare(strict_types=1);

namespace App\Domain\Hotspot\Services;

use App\Domain\Hotspot\Contracts\MikrotikApiInterface;
use App\Domain\Hotspot\DTO\HotspotUserProvisionData;
use App\Domain\Hotspot\DTO\MikrotikUserResult;
use App\Domain\Hotspot\Exceptions\MikrotikApiException;
use EvilFreelancer\RouterOSAPI\Client;
use EvilFreelancer\RouterOSAPI\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class MikrotikApiService implements MikrotikApiInterface
{
    private ?Client $client = null;
    private array $config;

    public function __construct()
    {
        $this->config = config('mikrotik');
    }

    public function connect(): void
    {
        try {
            if ($this->isFakeMode()) {
                Log::info('Mikrotik: Using fake mode - skipping real connection');
                return;
            }

            $config = new Config([
                'host' => $this->config['host'],
                'user' => $this->config['username'],
                'pass' => $this->config['password'],
                'port' => (int) $this->config['port'],
                'ssl' => (bool) $this->config['use_ssl'],
                'timeout' => $this->config['timeout'],
            ]);

            $this->client = new Client($config);
            Log::info('Mikrotik: Successfully connected to RouterOS', [
                'host' => $this->config['host'],
                'port' => $this->config['port']
            ]);
        } catch (\Exception $e) {
            Log::error('Mikrotik: Connection failed', ['exception' => $e->getMessage()]);
            throw new MikrotikApiException('Failed to connect to Mikrotik: ' . $e->getMessage());
        }
    }

    public function createUser(HotspotUserProvisionData $data): MikrotikUserResult
    {
        try {
            if ($this->isFakeMode()) {
                return $this->createUserFake($data);
            }

            $this->ensureConnected();
            
            $response = $this->client->query('/ip/hotspot/user/add', [
                'name' => $data->username,
                'password' => $data->password,
                'profile' => $data->profileName,
                'limit-uptime' => $data->validityMinutes . 'm',
            ]);

            $mikrotikId = $response[0]['.id'] ?? null;

            Log::info('Mikrotik: User created successfully', [
                'username' => $data->username,
                'mikrotik_id' => $mikrotikId
            ]);

            return new MikrotikUserResult(
                username: $data->username,
                mikrotik_id: $mikrotikId,
                raw: $response
            );
        } catch (\Exception $e) {
            Log::error('Mikrotik: Failed to create user', [
                'username' => $data->username,
                'exception' => $e->getMessage()
            ]);
            throw new MikrotikApiException('Failed to create user: ' . $e->getMessage());
        }
    }

    public function removeUser(string $username): bool
    {
        try {
            if ($this->isFakeMode()) {
                Log::info('Mikrotik: Fake mode - removing user', ['username' => $username]);
                return true;
            }

            $this->ensureConnected();
            
            $users = $this->client->query('/ip/hotspot/user/print', ['?name' => $username]);
            
            if (empty($users)) {
                return false;
            }

            $this->client->query('/ip/hotspot/user/remove', ['numbers' => $users[0]['.id']]);
            
            Log::info('Mikrotik: User removed successfully', ['username' => $username]);
            return true;
        } catch (\Exception $e) {
            Log::error('Mikrotik: Failed to remove user', [
                'username' => $username,
                'exception' => $e->getMessage()
            ]);
            throw new MikrotikApiException('Failed to remove user: ' . $e->getMessage());
        }
    }

    public function getUsers(): array
    {
        try {
            if ($this->isFakeMode()) {
                return $this->getUsersFake();
            }

            $this->ensureConnected();
            
            $users = $this->client->query('/ip/hotspot/user/print');
            
            Log::info('Mikrotik: Retrieved users list', ['count' => count($users)]);
            return $users;
        } catch (\Exception $e) {
            Log::error('Mikrotik: Failed to get users', ['exception' => $e->getMessage()]);
            throw new MikrotikApiException('Failed to get users: ' . $e->getMessage());
        }
    }

    public function getActiveSessions(): array
    {
        try {
            if ($this->isFakeMode()) {
                return $this->getActiveSessionsFake();
            }

            $this->ensureConnected();
            
            $sessions = $this->client->query('/ip/hotspot/active/print');
            
            Log::info('Mikrotik: Retrieved active sessions', ['count' => count($sessions)]);
            return $sessions;
        } catch (\Exception $e) {
            Log::error('Mikrotik: Failed to get active sessions', ['exception' => $e->getMessage()]);
            throw new MikrotikApiException('Failed to get active sessions: ' . $e->getMessage());
        }
    }

    public function disconnectUser(string $username): bool
    {
        try {
            if ($this->isFakeMode()) {
                Log::info('Mikrotik: Fake mode - disconnecting user', ['username' => $username]);
                return true;
            }

            $this->ensureConnected();
            
            $sessions = $this->client->query('/ip/hotspot/active/print', ['?user' => $username]);
            
            if (empty($sessions)) {
                return false;
            }

            $this->client->query('/ip/hotspot/active/remove', ['numbers' => $sessions[0]['.id']]);
            
            Log::info('Mikrotik: User disconnected successfully', ['username' => $username]);
            return true;
        } catch (\Exception $e) {
            Log::error('Mikrotik: Failed to disconnect user', [
                'username' => $username,
                'exception' => $e->getMessage()
            ]);
            throw new MikrotikApiException('Failed to disconnect user: ' . $e->getMessage());
        }
    }

    public function suspendUser(string $username): bool
    {
        try {
            if ($this->isFakeMode()) {
                Log::info('Mikrotik: Fake mode - suspending user', ['username' => $username]);
                return true;
            }

            $this->ensureConnected();
            
            $users = $this->client->query('/ip/hotspot/user/print', ['?name' => $username]);
            
            if (empty($users)) {
                return false;
            }

            $this->client->query('/ip/hotspot/user/set', [
                'numbers' => $users[0]['.id'],
                'disabled' => 'yes'
            ]);
            
            Log::info('Mikrotik: User suspended successfully', ['username' => $username]);
            return true;
        } catch (\Exception $e) {
            Log::error('Mikrotik: Failed to suspend user', [
                'username' => $username,
                'exception' => $e->getMessage()
            ]);
            throw new MikrotikApiException('Failed to suspend user: ' . $e->getMessage());
        }
    }

    public function resumeUser(string $username): bool
    {
        try {
            if ($this->isFakeMode()) {
                Log::info('Mikrotik: Fake mode - resuming user', ['username' => $username]);
                return true;
            }

            $this->ensureConnected();
            
            $users = $this->client->query('/ip/hotspot/user/print', ['?name' => $username]);
            
            if (empty($users)) {
                return false;
            }

            $this->client->query('/ip/hotspot/user/set', [
                'numbers' => $users[0]['.id'],
                'disabled' => 'no'
            ]);
            
            Log::info('Mikrotik: User resumed successfully', ['username' => $username]);
            return true;
        } catch (\Exception $e) {
            Log::error('Mikrotik: Failed to resume user', [
                'username' => $username,
                'exception' => $e->getMessage()
            ]);
            throw new MikrotikApiException('Failed to resume user: ' . $e->getMessage());
        }
    }

    public function getApInterfacesLoad(): array
    {
        try {
            if ($this->isFakeMode()) {
                return $this->getApInterfacesLoadFake();
            }

            $this->ensureConnected();
            
            $interfaces = $this->client->query('/interface/print');
            $activeSessions = $this->getActiveSessions();
            
            $result = [];
            foreach ($interfaces as $interface) {
                $connectedUsers = count(array_filter($activeSessions, function ($session) use ($interface) {
                    return ($session['server'] ?? '') === ($interface['name'] ?? '');
                }));
                
                $result[] = [
                    'interface' => $interface['name'] ?? 'unknown',
                    'connected_users' => $connectedUsers,
                    'last_sync_at' => now()->toISOString(),
                ];
            }
            
            Log::info('Mikrotik: Retrieved AP interfaces load', ['interfaces_count' => count($result)]);
            return $result;
        } catch (\Exception $e) {
            Log::error('Mikrotik: Failed to get AP interfaces load', ['exception' => $e->getMessage()]);
            throw new MikrotikApiException('Failed to get AP interfaces load: ' . $e->getMessage());
        }
    }

    public function ping(): bool
    {
        try {
            if ($this->isFakeMode()) {
                Log::info('Mikrotik: Fake mode - ping successful');
                return true;
            }

            $this->ensureConnected();
            
            // Simple test query to check if connection is alive
            $this->client->query('/system/identity/print');
            
            Log::info('Mikrotik: Ping successful');
            return true;
        } catch (\Exception $e) {
            Log::warning('Mikrotik: Ping failed', ['exception' => $e->getMessage()]);
            return false;
        }
    }

    private function isFakeMode(): bool
    {
        return $this->config['fake'] || $this->config['host'] === 'demo';
    }

    private function ensureConnected(): void
    {
        if ($this->client === null) {
            $this->connect();
        }
    }

    private function createUserFake(HotspotUserProvisionData $data): MikrotikUserResult
    {
        $mikrotikId = '*' . strtoupper(Str::random(8));
        
        Log::info('Mikrotik: Fake mode - user created', [
            'username' => $data->username,
            'mikrotik_id' => $mikrotikId
        ]);

        return new MikrotikUserResult(
            username: $data->username,
            mikrotik_id: $mikrotikId,
            raw: [
                '.id' => $mikrotikId,
                'name' => $data->username,
                'profile' => $data->profileName,
                'limit-uptime' => $data->validityMinutes . 'm',
                'fake_mode' => true,
            ]
        );
    }

    private function getUsersFake(): array
    {
        return [
            [
                '.id' => '*1001',
                'name' => 'demo-user-1',
                'profile' => 'default',
                'disabled' => 'false',
                'fake_mode' => true,
            ],
            [
                '.id' => '*1002',
                'name' => 'demo-user-2',
                'profile' => 'premium',
                'disabled' => 'false',
                'fake_mode' => true,
            ]
        ];
    }

    private function getActiveSessionsFake(): array
    {
        return [
            [
                '.id' => '*2001',
                'user' => 'demo-user-1',
                'address' => '192.168.1.100',
                'mac-address' => '00:11:22:33:44:55',
                'server' => 'hotspot1',
                'uptime' => '00:05:30',
                'fake_mode' => true,
            ]
        ];
    }

    private function getApInterfacesLoadFake(): array
    {
        return [
            [
                'interface' => 'wlan1',
                'connected_users' => 5,
                'last_sync_at' => now()->toISOString(),
            ],
            [
                'interface' => 'wlan2',
                'connected_users' => 12,
                'last_sync_at' => now()->toISOString(),
            ],
            [
                'interface' => 'ether1',
                'connected_users' => 3,
                'last_sync_at' => now()->toISOString(),
            ]
        ];
    }

    public function __destruct()
    {
        // TODO: Add proper connection cleanup if needed by the RouterOS API client
        $this->client = null;
    }
}