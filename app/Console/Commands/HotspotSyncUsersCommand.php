<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Jobs\SyncMikrotikUsersJob;
use Illuminate\Console\Command;

class HotspotSyncUsersCommand extends Command
{
    protected $signature = 'hotspot:sync-users';

    protected $description = 'Sync users from Mikrotik router with local database';

    public function handle(): int
    {
        $this->info('Dispatching Mikrotik users sync job...');
        
        SyncMikrotikUsersJob::dispatch();
        
        $this->info('Mikrotik users sync job dispatched successfully.');
        
        return Command::SUCCESS;
    }
}