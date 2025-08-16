<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Facades\Feature;
use App\Support\StructuredLog;

class FeatureDisableCommand extends Command
{
    protected $signature = 'feature:disable {key : Feature flag key}';

    protected $description = 'Disable a feature flag';

    public function handle(): int
    {
        $key = $this->argument('key');
        
        try {
            $flag = Feature::disable($key);
            
            $this->info("❌ Feature flag '{$key}' has been disabled");
            
            StructuredLog::info('feature_flag_disabled', [
                'feature_key' => $key,
                'disabled_by' => 'cli',
            ]);
            
            return Command::SUCCESS;
            
        } catch (\Exception $e) {
            $this->error("Failed to disable feature flag '{$key}': {$e->getMessage()}");
            
            StructuredLog::error('feature_flag_disable_failed', [
                'feature_key' => $key,
                'error' => $e->getMessage(),
            ]);
            
            return Command::FAILURE;
        }
    }
}