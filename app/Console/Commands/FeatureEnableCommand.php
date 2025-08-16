<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Facades\Feature;
use App\Support\StructuredLog;

class FeatureEnableCommand extends Command
{
    protected $signature = 'feature:enable {key : Feature flag key} {--meta= : JSON metadata for the feature}';

    protected $description = 'Enable a feature flag';

    public function handle(): int
    {
        $key = $this->argument('key');
        $metaJson = $this->option('meta');
        $meta = [];
        
        if ($metaJson) {
            $meta = json_decode($metaJson, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->error('Invalid JSON provided for meta option');
                return Command::FAILURE;
            }
        }
        
        try {
            $flag = Feature::enable($key, $meta);
            
            $this->info("✅ Feature flag '{$key}' has been enabled");
            
            if (!empty($meta)) {
                $this->info("📝 Metadata: " . json_encode($meta, JSON_PRETTY_PRINT));
            }
            
            StructuredLog::info('feature_flag_enabled', [
                'feature_key' => $key,
                'meta' => $meta,
                'enabled_by' => 'cli',
            ]);
            
            return Command::SUCCESS;
            
        } catch (\Exception $e) {
            $this->error("Failed to enable feature flag '{$key}': {$e->getMessage()}");
            
            StructuredLog::error('feature_flag_enable_failed', [
                'feature_key' => $key,
                'error' => $e->getMessage(),
            ]);
            
            return Command::FAILURE;
        }
    }
}