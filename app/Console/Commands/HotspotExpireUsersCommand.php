<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Jobs\UpdateExpiredHotspotUsersJob;
use Illuminate\Console\Command;

class HotspotExpireUsersCommand extends Command
{
    protected $signature = 'hotspot:expire-users';

    protected $description = 'Update expired hotspot users status';

    public function handle(): int
    {
        $this->info('Running expired hotspot users update job...');
        
        UpdateExpiredHotspotUsersJob::dispatch();
        
        $this->info('Expired hotspot users update job dispatched successfully.');
        
        return Command::SUCCESS;
    }
}