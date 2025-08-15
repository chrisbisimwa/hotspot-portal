<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Jobs\PruneOldLogsJob;
use Illuminate\Console\Command;

class LogsPruneCommand extends Command
{
    protected $signature = 'logs:prune {--days=30 : Number of days to keep logs}';

    protected $description = 'Prune old logs from database';

    public function handle(): int
    {
        $days = (int) $this->option('days');
        
        $this->info("Running log pruning job for logs older than {$days} days...");
        
        PruneOldLogsJob::dispatch($days);
        
        $this->info('Log pruning job dispatched successfully.');
        
        return Command::SUCCESS;
    }
}