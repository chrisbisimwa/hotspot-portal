<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Jobs\DispatchPendingNotificationsJob;
use Illuminate\Console\Command;

class NotificationsDispatchPendingCommand extends Command
{
    protected $signature = 'notifications:dispatch-pending {--batch-size=50 : Number of notifications to process in one batch}';

    protected $description = 'Dispatch pending notifications to users';

    public function handle(): int
    {
        $batchSize = (int) $this->option('batch-size');
        
        $this->info("Running pending notifications dispatch job with batch size: {$batchSize}...");
        
        DispatchPendingNotificationsJob::dispatch($batchSize);
        
        $this->info('Pending notifications dispatch job dispatched successfully.');
        
        return Command::SUCCESS;
    }
}