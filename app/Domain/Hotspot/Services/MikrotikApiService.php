<?php

declare(strict_types=1);

namespace App\Domain\Hotspot\Services;

use App\Domain\Hotspot\Contracts\MikrotikApiInterface;
use App\Domain\Hotspot\DTO\HotspotUserProvisionData;
use App\Domain\Hotspot\DTO\MikrotikUserResult;
use App\Domain\Hotspot\DTO\MikrotikProfileProvisionData;
use App\Domain\Hotspot\Exceptions\MikrotikApiException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Service d’intégration MikroTik (RouterOS API).
 *
 * Implémente toutes les méthodes exigées par MikrotikApiInterface.
 * Gère deux namespaces possibles selon la version du package :
 *  - RouterOS\{Client, Config}
 *  - EvilFreelancer\RouterOSAPI\{Client, Config}
 *
 * En mode "fake" (config('mikrotik.fake') = true ou enabled = false), retourne des données simulées.
 */
class MikrotikApiService implements MikrotikApiInterface
{
    /** @var object|null */
    private ?object $client = null;

    /** @var array<string,mixed> */
    private array $config;

    private ?string $configClass = null;
    private ?string $clientClass = null;

    public function __construct()
    {
        $this->config = config('mikrotik', []);
        $this->resolveClasses();
    }

    /**
     * Détection dynamique des classes disponibles.
     */
    private function resolveClasses(): void
    {
        $candidates = [
            ['config' => 'RouterOS\\Config', 'client' => 'RouterOS\\Client'],
            ['config' => 'EvilFreelancer\\RouterOSAPI\\Config', 'client' => 'EvilFreelancer\\RouterOSAPI\\Client'],
        ];

        foreach ($candidates as $pair) {
            if (class_exists($pair['config']) && class_exists($pair['client'])) {
                $this->configClass = $pair['config'];
                $this->clientClass = $pair['client'];
                return;
            }
        }
    }

    public function connect(): void
    {
        if ($this->isFakeMode()) {
            Log::info('Mikrotik: Fake mode activé - aucune connexion');
            return;
        }

        if (!$this->configClass || !$this->clientClass) {
            throw new MikrotikApiException(
                'Package RouterOS API introuvable. Installe-le (ex: composer require evilfreelancer/routeros-api-php) puis dump-autoload.'
            );
        }

        if ($this->client !== null) {
            return; // Déjà connecté
        }

        try {
            $cfgArray = [
                'host' => $this->config['host'] ?? '127.0.0.1',
                'user' => $this->config['username'] ?? 'admin',
                'pass' => $this->config['password'] ?? '',
                'port' => (int)($this->config['port'] ?? 8728),
                'ssl' => (bool)($this->config['use_ssl'] ?? false),
                'timeout' => (int)($this->config['timeout'] ?? 10),
            ];

            $configObj = new ($this->configClass)($cfgArray);
            $this->client = new ($this->clientClass)($configObj);

            Log::info('Mikrotik: Connexion réussie', [
                'host' => $cfgArray['host'],
                'port' => $cfgArray['port'],
                'ns'   => $this->clientClass,
            ]);
        } catch (\Throwable $e) {
            $this->client = null;
            Log::error('Mikrotik: Connexion échouée', ['error' => $e->getMessage()]);
            throw new MikrotikApiException('Failed to connect to Mikrotik: ' . $e->getMessage(), 0, $e);
        }
    }

    private function ensureConnected(): void
    {
        if ($this->isFakeMode()) {
            return;
        }
        if (!$this->client) {
            $this->connect();
        }
    }

    private function isFakeMode(): bool
    {
        return !($this->config['enabled'] ?? true) || ($this->config['fake'] ?? false);
    }

    /* -------------------------------------------------
     * USERS
     * ------------------------------------------------- */

    public function createUser(HotspotUserProvisionData $data): MikrotikUserResult
    {
        try {
            if ($this->isFakeMode()) {
                return $this->createUserFake($data);
            }
            $this->ensureConnected();

            $response = $this->client->query('/ip/hotspot/user/add', [
                'name'        => $data->username,
                'password'    => $data->password,
                'profile'     => $data->profileName,
                'limit-uptime'=> $data->validityMinutes . 'm',
            ]);

            $mikrotikId = $response[0]['.id'] ?? null;

            Log::info('Mikrotik: Utilisateur créé', [
                'username'   => $data->username,
                'mikrotik_id'=> $mikrotikId
            ]);

            return new MikrotikUserResult(
                username: $data->username,
                mikrotik_id: $mikrotikId,
                raw: $response
            );

        } catch (\Throwable $e) {
            Log::error('Mikrotik: Échec création utilisateur', [
                'username' => $data->username,
                'error'    => $e->getMessage()
            ]);
            throw new MikrotikApiException('Failed to create user: ' . $e->getMessage(), 0, $e);
        }
    }

    public function removeUser(string $username): bool
    {
        try {
            if ($this->isFakeMode()) {
                return true;
            }
            $this->ensureConnected();

            $id = $this->findUserId($username);
            if (!$id) {
                Log::warning('Mikrotik: removeUser - utilisateur introuvable', ['username' => $username]);
                return false;
            }

            $this->client->query('/ip/hotspot/user/remove', [
                '.id' => $id,
            ]);

            Log::info('Mikrotik: Utilisateur supprimé', ['username' => $username, 'id' => $id]);
            return true;
        } catch (\Throwable $e) {
            Log::error('Mikrotik: Échec removeUser', [
                'username' => $username,
                'error'    => $e->getMessage()
            ]);
            throw new MikrotikApiException('Failed to remove user: ' . $e->getMessage(), 0, $e);
        }
    }

    public function getUsers(): array
    {
        try {
            if ($this->isFakeMode()) {
                return [
                    ['name' => 'demo1', 'profile' => 'basic', 'disabled' => 'false'],
                    ['name' => 'demo2', 'profile' => 'premium', 'disabled' => 'true'],
                ];
            }
            $this->ensureConnected();

            return $this->client->query('/ip/hotspot/user/print');
        } catch (\Throwable $e) {
            Log::error('Mikrotik: Échec getUsers', ['error' => $e->getMessage()]);
            return [];
        }
    }

    public function getActiveSessions(): array
    {
        try {
            if ($this->isFakeMode()) {
                return [
                    ['user' => 'demo1', 'address' => '192.168.88.101', 'uptime' => '5m', 'idle-time' => '1m'],
                    ['user' => 'demo2', 'address' => '192.168.88.102', 'uptime' => '12m', 'idle-time' => '30s'],
                ];
            }
            $this->ensureConnected();
            return $this->client->query('/ip/hotspot/active/print');
        } catch (\Throwable $e) {
            Log::error('Mikrotik: Échec getActiveSessions', ['error' => $e->getMessage()]);
            return [];
        }
    }

    public function disconnectUser(string $username): bool
    {
        try {
            if ($this->isFakeMode()) {
                return true;
            }
            $this->ensureConnected();

            // Trouver la session active
            $activeList = $this->client->query('/ip/hotspot/active/print', [
                '?user' => $username,
            ]);

            $id = $activeList[0]['.id'] ?? null;
            if (!$id) {
                Log::warning('Mikrotik: disconnectUser - session non trouvée', ['username' => $username]);
                return false;
            }

            $this->client->query('/ip/hotspot/active/remove', [
                '.id' => $id,
            ]);

            Log::info('Mikrotik: Session déconnectée', ['username' => $username, 'active_id' => $id]);
            return true;
        } catch (\Throwable $e) {
            Log::error('Mikrotik: Échec disconnectUser', [
                'username' => $username,
                'error'    => $e->getMessage()
            ]);
            return false;
        }
    }

    public function suspendUser(string $username): bool
    {
        try {
            if ($this->isFakeMode()) {
                return true;
            }
            $this->ensureConnected();

            $id = $this->findUserId($username);
            if (!$id) {
                return false;
            }

            $this->client->query('/ip/hotspot/user/set', [
                '.id'      => $id,
                'disabled' => 'yes',
            ]);

            Log::info('Mikrotik: Utilisateur suspendu', ['username' => $username]);
            return true;
        } catch (\Throwable $e) {
            Log::error('Mikrotik: Échec suspendUser', [
                'username' => $username,
                'error'    => $e->getMessage()
            ]);
            return false;
        }
    }

    public function resumeUser(string $username): bool
    {
        try {
            if ($this->isFakeMode()) {
                return true;
            }
            $this->ensureConnected();

            $id = $this->findUserId($username);
            if (!$id) {
                return false;
            }

            $this->client->query('/ip/hotspot/user/set', [
                '.id'      => $id,
                'disabled' => 'no',
            ]);

            Log::info('Mikrotik: Utilisateur réactivé', ['username' => $username]);
            return true;
        } catch (\Throwable $e) {
            Log::error('Mikrotik: Échec resumeUser', [
                'username' => $username,
                'error'    => $e->getMessage()
            ]);
            return false;
        }
    }

    /* -------------------------------------------------
     * PROFILES
     * ------------------------------------------------- */

    public function createUserProfile(MikrotikProfileProvisionData $data): bool
    {
        try {
            if ($this->isFakeMode()) {
                return true;
            }
            $this->ensureConnected();

            // Vérifie si existe
            $existing = $this->client->query('/ip/hotspot/user/profile/print', [
                '?name' => $data->profileName,
            ]);

            if (!empty($existing)) {
                Log::info('Mikrotik: Profile déjà présent', ['profile' => $data->profileName]);
                return true;
            }

            $payload = [
                'name'        => $data->profileName,
                'rate-limit'  => $data->rateLimit,
                'shared-users'=> $data->sharedUsers,
            ];
            if ($data->sessionTimeoutMinutes) {
                $payload['session-timeout'] = $data->sessionTimeoutMinutes . 'm';
            }

            $this->client->query('/ip/hotspot/user/profile/add', $payload);

            Log::info('Mikrotik: Profile créé', ['profile' => $data->profileName]);
            return true;
        } catch (\Throwable $e) {
            Log::error('Mikrotik: Échec createUserProfile', [
                'profile' => $data->profileName,
                'error'   => $e->getMessage()
            ]);
            return false;
        }
    }

    public function updateUserProfile(string $name, MikrotikProfileProvisionData $data): bool
    {
        try {
            if ($this->isFakeMode()) {
                return true;
            }
            $this->ensureConnected();

            $id = $this->findProfileId($name);
            if (!$id) {
                Log::warning('Mikrotik: updateUserProfile - profile introuvable', ['profile' => $name]);
                return false;
            }

            $payload = [
                '.id'         => $id,
                'rate-limit'  => $data->rateLimit,
                'shared-users'=> $data->sharedUsers,
            ];
            if ($data->sessionTimeoutMinutes) {
                $payload['session-timeout'] = $data->sessionTimeoutMinutes . 'm';
            }

            $this->client->query('/ip/hotspot/user/profile/set', $payload);

            Log::info('Mikrotik: Profile mis à jour', ['profile' => $name]);
            return true;
        } catch (\Throwable $e) {
            Log::error('Mikrotik: Échec updateUserProfile', [
                'profile' => $name,
                'error'   => $e->getMessage()
            ]);
            return false;
        }
    }

    public function removeUserProfile(string $name): bool
    {
        try {
            if ($this->isFakeMode()) {
                return true;
            }
            $this->ensureConnected();

            $id = $this->findProfileId($name);
            if (!$id) {
                return false;
            }

            $this->client->query('/ip/hotspot/user/profile/remove', [
                '.id' => $id,
            ]);

            Log::info('Mikrotik: Profile supprimé', ['profile' => $name]);
            return true;
        } catch (\Throwable $e) {
            Log::error('Mikrotik: Échec removeUserProfile', [
                'profile' => $name,
                'error'   => $e->getMessage()
            ]);
            return false;
        }
    }

    public function getUserProfiles(): array
    {
        try {
            if ($this->isFakeMode()) {
                return [
                    ['name' => 'basic', 'rate-limit' => '2M/2M', 'shared-users' => '1'],
                    ['name' => 'premium', 'rate-limit' => '10M/10M', 'shared-users' => '3'],
                ];
            }
            $this->ensureConnected();

            return $this->client->query('/ip/hotspot/user/profile/print');
        } catch (\Throwable $e) {
            Log::error('Mikrotik: Échec getUserProfiles', ['error' => $e->getMessage()]);
            return [];
        }
    }

    /* -------------------------------------------------
     * INTERFACES
     * ------------------------------------------------- */

    public function getApInterfacesLoad(): array
    {
        try {
            if ($this->isFakeMode()) {
                return [
                    ['name' => 'ether1', 'rx-kbps' => 1200, 'tx-kbps' => 800, 'fake' => true],
                    ['name' => 'wlan1',  'rx-kbps' => 560,  'tx-kbps' => 430, 'fake' => true],
                ];
            }
            $this->ensureConnected();

            $interfaces = $this->client->query('/interface/print');

            // NOTE: Pour des stats traffic précises, il faudrait utiliser monitor-traffic (souvent en mode continu).
            return array_map(static function ($iface) {
                return [
                    'name'     => $iface['name'] ?? null,
                    'type'     => $iface['type'] ?? null,
                    'running'  => $iface['running'] ?? null,
                    'rx-kbps'  => null,
                    'tx-kbps'  => null,
                ];
            }, $interfaces);
        } catch (\Throwable $e) {
            Log::error('Mikrotik: Échec getApInterfacesLoad', ['error' => $e->getMessage()]);
            return [
                'error' => true,
                'message' => $e->getMessage(),
            ];
        }
    }

    /* -------------------------------------------------
     * HEALTH / PING
     * ------------------------------------------------- */

    public function ping(): bool
    {
        try {
            if ($this->isFakeMode()) {
                return true;
            }
            $this->ensureConnected();

            $target = $this->config['host'] ?? '127.0.0.1';
            $response = $this->client->query('/ping', [
                'address' => $target,
                'count'   => 1,
            ]);

            // Selon version, peut contenir 'time' ou juste status
            return !empty($response);
        } catch (\Throwable $e) {
            Log::warning('Mikrotik: Ping échoué', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /* -------------------------------------------------
     * Helpers privés
     * ------------------------------------------------- */

    private function findUserId(string $username): ?string
    {
        $users = $this->client->query('/ip/hotspot/user/print', [
            '?name' => $username,
        ]);
        return $users[0]['.id'] ?? null;
    }

    private function findProfileId(string $profileName): ?string
    {
        $profiles = $this->client->query('/ip/hotspot/user/profile/print', [
            '?name' => $profileName,
        ]);
        return $profiles[0]['.id'] ?? null;
    }

    private function createUserFake(HotspotUserProvisionData $data): MikrotikUserResult
    {
        return new MikrotikUserResult(
            username: $data->username,
            mikrotik_id: 'FAKE-' . Str::random(6),
            raw: ['fake' => true]
        );
    }
}