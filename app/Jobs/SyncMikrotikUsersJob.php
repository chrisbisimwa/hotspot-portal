<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Domain\Hotspot\Contracts\MikrotikApiInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncMikrotikUsersJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct()
    {
        //
    }

    public function handle(MikrotikApiInterface $mikrotikApi): void
    {
        $startTime = microtime(true);
        
        Log::info('SyncMikrotikUsersJob: Starting sync process');

        try {
            // Retrieve users list from Mikrotik
            $users = $mikrotikApi->getUsers();
            
            Log::info('SyncMikrotikUsersJob: Retrieved users from Mikrotik', [
                'count' => count($users)
            ]);

            // TODO: Implement deep mapping between Mikrotik users and hotspot_users table
            // For now, just log the users for future mapping implementation
            foreach ($users as $user) {
                $username = $user['name'] ?? 'unknown';
                // Log unknown usernames as warnings for future processing
                Log::warning('SyncMikrotikUsersJob: Unknown username found', [
                    'username' => $username,
                    'user_data' => $user
                ]);
            }

            $executionTime = microtime(true) - $startTime;
            Log::info('SyncMikrotikUsersJob: Sync completed successfully', [
                'execution_time_seconds' => round($executionTime, 3),
                'users_processed' => count($users)
            ]);

        } catch (\Exception $e) {
            $executionTime = microtime(true) - $startTime;
            Log::error('SyncMikrotikUsersJob: Sync failed', [
                'error' => $e->getMessage(),
                'execution_time_seconds' => round($executionTime, 3)
            ]);
            
            throw $e;
        }
    }

    public function tags(): array
    {
        return ['mikrotik', 'sync', 'users'];
    }
}