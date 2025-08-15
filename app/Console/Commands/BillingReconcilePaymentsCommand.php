<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Jobs\ReconcilePaymentsJob;
use Illuminate\Console\Command;

class BillingReconcilePaymentsCommand extends Command
{
    protected $signature = 'billing:reconcile-payments {--batch-size=50 : Number of payments to process in one batch}';

    protected $description = 'Reconcile pending payments with gateway status';

    public function handle(): int
    {
        $batchSize = (int) $this->option('batch-size');
        
        $this->info("Running payment reconciliation job with batch size: {$batchSize}...");
        
        ReconcilePaymentsJob::dispatch($batchSize);
        
        $this->info('Payment reconciliation job dispatched successfully.');
        
        return Command::SUCCESS;
    }
}