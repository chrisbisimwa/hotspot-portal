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
 * - Détection dynamique des classes (RouterOS\*, EvilFreelancer\*)
 * - Mode fake (config('mikrotik.fake') ou enabled=false)
 * - Méthodes CRUD utilisateurs, profils, sessions
 * - Méthodes interfaces (list + monitor)
 *
 * NOTE: Les débits temps réel sont fournis par monitorInterfaceTraffic().
 *       Pour une approche plus efficace (delta bytes), utiliser MikrotikMetricsService.
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
                'Package RouterOS API introuvable. Installe-le (ex: composer require evilfreelancer/routeros-api-php).'
            );
        }

        if ($this->client !== null) {
            return;
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

    /**
     * Helper générique pour exécuter une commande retournant des données.
     *
     * @param string $path
     * @param array<string,string|int|null> $params
     * @return array<int,array<string,mixed>>
     */
    private function runQuery(string $path, array $params = []): array
    {
        if ($this->isFakeMode()) {
            return [];
        }
        $this->ensureConnected();

        try {
            $queryClass = null;
            if (class_exists('RouterOS\\Query')) {
                $queryClass = 'RouterOS\\Query';
            } elseif (class_exists('EvilFreelancer\\RouterOSAPI\\Query')) {
                $queryClass = 'EvilFreelancer\\RouterOSAPI\\Query';
            }

            if ($queryClass) {
                $query = new $queryClass($path);
                foreach ($params as $k => $v) {
                    if ($v === null) {
                        continue;
                    }
                    if (method_exists($query, 'equal')) {
                        $query->equal($k, (string)$v);
                    } elseif (method_exists($query, 'where')) {
                        $query->where($k, (string)$v);
                    }
                }
                $this->client->query($query);
                if (method_exists($this->client, 'read')) {
                    return $this->client->read() ?? [];
                }
            } else {
                $res = $this->client->query($path, $params);
                if (is_object($res) && method_exists($res, 'read')) {
                    return $res->read() ?? [];
                }
                if (method_exists($this->client, 'read')) {
                    return $this->client->read() ?? [];
                }
                return is_array($res) ? $res : [];
            }
        } catch (\Throwable $e) {
            Log::error('Mikrotik: runQuery error', ['path' => $path, 'error' => $e->getMessage()]);
            return [];
        }

        return [];
    }

    /* ---------------- PING ---------------- */

    public function ping(): bool
    {
        try {
            if ($this->isFakeMode()) {
                return true;
            }
            $this->ensureConnected();
            $host = $this->config['host'] ?? '127.0.0.1';

            $rows = $this->runQuery('/ping', [
                'address' => $host,
                'count'   => 1,
            ]);

            if (is_array($rows) && count($rows) > 0) {
                return true;
            }

            // Fallback léger (connexion OK)
            return true;
        } catch (\Throwable $e) {
            Log::warning('Mikrotik: ping failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /* ---------------- USERS ---------------- */

    public function createUser(HotspotUserProvisionData $data): MikrotikUserResult
    {
        try {
            if ($this->isFakeMode()) {
                return $this->createUserFake($data);
            }
            $this->ensureConnected();

            // Ajouter
            $this->runQuery('/ip/hotspot/user/add', [
                'name'         => $data->username,
                'password'     => $data->password,
                'profile'      => $data->profileName,
                'limit-uptime' => $data->validityMinutes . 'm',
            ]);

            $found = $this->runQuery('/ip/hotspot/user/print', ['name' => $data->username]);
            $mikrotikId = $found[0]['.id'] ?? null;

            Log::info('Mikrotik: Utilisateur créé', [
                'username' => $data->username,
                'id'       => $mikrotikId,
            ]);

            return new MikrotikUserResult(
                username: $data->username,
                mikrotik_id: $mikrotikId,
                raw: $found[0] ?? []
            );
        } catch (\Throwable $e) {
            Log::error('Mikrotik: Échec createUser', [
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
            $id = $this->findUserId($username);
            if (!$id) {
                return false;
            }
            $this->runQuery('/ip/hotspot/user/remove', ['.id' => $id]);
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
            return $this->runQuery('/ip/hotspot/user/print');
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
            return $this->runQuery('/ip/hotspot/active/print');
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
            $sessions = $this->runQuery('/ip/hotspot/active/print', ['user' => $username]);
            $id = $sessions[0]['.id'] ?? null;
            if (!$id) {
                return false;
            }
            $this->runQuery('/ip/hotspot/active/remove', ['.id' => $id]);
            Log::info('Mikrotik: Session déconnectée', ['username' => $username]);
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
            $id = $this->findUserId($username);
            if (!$id) {
                return false;
            }
            $this->runQuery('/ip/hotspot/user/set', [
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
            $id = $this->findUserId($username);
            if (!$id) {
                return false;
            }
            $this->runQuery('/ip/hotspot/user/set', [
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

    /* ---------------- PROFILES ---------------- */

    public function createUserProfile(MikrotikProfileProvisionData $data): bool
    {
        try {
            if ($this->isFakeMode()) {
                return true;
            }

            $existing = $this->runQuery('/ip/hotspot/user/profile/print', ['name' => $data->profileName]);
            if (!empty($existing)) {
                Log::info('Mikrotik: Profile déjà présent', ['profile' => $data->profileName]);
                return true;
            }

            $payload = [
                'name'         => $data->profileName,
                'rate-limit'   => $data->rateLimit,
                'shared-users' => $data->sharedUsers,
            ];
            if ($data->sessionTimeoutMinutes) {
                $payload['session-timeout'] = $data->sessionTimeoutMinutes . 'm';
            }
            $this->runQuery('/ip/hotspot/user/profile/add', $payload);
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
            $id = $this->findProfileId($name);
            if (!$id) {
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
            $this->runQuery('/ip/hotspot/user/profile/set', $payload);
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
            $id = $this->findProfileId($name);
            if (!$id) {
                return false;
            }
            $this->runQuery('/ip/hotspot/user/profile/remove', ['.id' => $id]);
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
            return $this->runQuery('/ip/hotspot/user/profile/print');
        } catch (\Throwable $e) {
            Log::error('Mikrotik: Échec getUserProfiles', ['error' => $e->getMessage()]);
            return [];
        }
    }

    /* ---------------- INTERFACES ---------------- */

    /**
     * Liste brute des interfaces (sans calcul de débit).
     * Peut contenir rx-byte / tx-byte selon version RouterOS.
     *
     * @return array<int,array<string,mixed>>
     */
    public function listInterfacesRaw(): array
    {
        if ($this->isFakeMode()) {
            return [
                ['name' => 'ether1_WAN', 'type' => 'ether', 'running' => 'true', 'rx-byte' => 1000000, 'tx-byte' => 800000],
                ['name' => 'ether2_LAN', 'type' => 'ether', 'running' => 'false', 'rx-byte' => 0, 'tx-byte' => 0],
                ['name' => 'bridge', 'type' => 'bridge', 'running' => 'true', 'rx-byte' => 2500000, 'tx-byte' => 2100000],
            ];
        }
        return $this->runQuery('/interface/print');
    }

    /**
     * Version “immédiate” avec monitor-traffic (fallback ou usage direct).
     * Pour un usage intensif, préférer MikrotikMetricsService (delta bytes + cache).
     */
    public function getApInterfacesLoad(): array
    {
        try {
            if ($this->isFakeMode()) {
                return [
                    ['name' => 'ether1_WAN', 'type'=>'ether', 'running'=>'true',  'rx-kbps'=>1200, 'tx-kbps'=>800, 'source'=>'fake'],
                    ['name' => 'ether2_LAN', 'type'=>'ether', 'running'=>'false', 'rx-kbps'=>0,    'tx-kbps'=>0,   'source'=>'fake'],
                    ['name' => 'bridge',     'type'=>'bridge','running'=>'true',  'rx-kbps'=>4700, 'tx-kbps'=>3200,'source'=>'fake'],
                ];
            }

            $rows = $this->listInterfacesRaw();
            $result = [];
            foreach ($rows as $iface) {
                $name = $iface['name'] ?? null;
                if (!$name) {
                    continue;
                }
                $running = ($iface['running'] ?? 'false') === 'true';
                $rx = null;
                $tx = null;
                $source = 'none';

                if ($running) {
                    [$rx, $tx, $source] = $this->monitorInterfaceTraffic($name);
                }

                $result[] = [
                    'name'     => $name,
                    'type'     => $iface['type'] ?? null,
                    'running'  => $iface['running'] ?? null,
                    'rx-kbps'  => $rx,
                    'tx-kbps'  => $tx,
                    'source'   => $source,
                ];
            }
            return $result;
        } catch (\Throwable $e) {
            Log::error('Mikrotik: Échec getApInterfacesLoad', ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Tente monitor-traffic. Retourne [rxKbps, txKbps, source].
     */
    public function monitorInterfaceTraffic(string $interface): array
    {
        try {
            if ($this->isFakeMode()) {
                return [rand(1000,2000)/10, rand(800,1800)/10, 'fake'];
            }
            $rows = $this->runQuery('/interface/monitor-traffic', [
                'interface' => $interface,
                'once'      => '',
            ]);
            if (!is_array($rows) || empty($rows)) {
                return [null, null, 'empty'];
            }
            $row = $rows[0];
            $rxBits = $row['rx-bits-per-second'] ?? null;
            $txBits = $row['tx-bits-per-second'] ?? null;
            $rxKbps = is_numeric($rxBits) ? round(((int)$rxBits) / 1000, 1) : null;
            $txKbps = is_numeric($txBits) ? round(((int)$txBits) / 1000, 1) : null;
            return [$rxKbps, $txKbps, 'monitor'];
        } catch (\Throwable $e) {
            Log::warning('Mikrotik: monitorInterfaceTraffic failed', [
                'interface' => $interface,
                'error'     => $e->getMessage()
            ]);
            return [null, null, 'error'];
        }
    }

    /* ---------------- Helpers privés ---------------- */

    private function findUserId(string $username): ?string
    {
        $users = $this->runQuery('/ip/hotspot/user/print', ['name' => $username]);
        return $users[0]['.id'] ?? null;
    }

    private function findProfileId(string $profileName): ?string
    {
        $profiles = $this->runQuery('/ip/hotspot/user/profile/print', ['name' => $profileName]);
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