<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Jobs\SyncActiveSessionsJob;
use Illuminate\Console\Command;

class HotspotSyncSessionsCommand extends Command
{
    protected $signature = 'hotspot:sync-sessions';

    protected $description = 'Sync active sessions from Mikrotik router with local database';

    public function handle(): int
    {
        $this->info('Dispatching Mikrotik sessions sync job...');
        
        SyncActiveSessionsJob::dispatch();
        
        $this->info('Mikrotik sessions sync job dispatched successfully.');
        
        return Command::SUCCESS;
    }
}