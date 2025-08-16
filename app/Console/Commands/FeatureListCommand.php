<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\FeatureFlag;

class FeatureListCommand extends Command
{
    protected $signature = 'feature:list {--enabled : Show only enabled features}';

    protected $description = 'List all feature flags';

    public function handle(): int
    {
        $onlyEnabled = $this->option('enabled');
        
        $query = FeatureFlag::query()->orderBy('key');
        
        if ($onlyEnabled) {
            $query->where('enabled', true);
        }
        
        $features = $query->get();
        
        if ($features->isEmpty()) {
            $message = $onlyEnabled ? 'No enabled feature flags found' : 'No feature flags found';
            $this->info($message);
            return Command::SUCCESS;
        }
        
        $this->info($onlyEnabled ? 'Enabled Feature Flags:' : 'All Feature Flags:');
        $this->line('');
        
        $headers = ['Key', 'Status', 'Updated At', 'Meta'];
        $rows = [];
        
        foreach ($features as $feature) {
            $rows[] = [
                $feature->key,
                $feature->enabled ? '✅ Enabled' : '❌ Disabled',
                $feature->updated_at->format('Y-m-d H:i:s'),
                $feature->meta ? json_encode($feature->meta, JSON_UNESCAPED_SLASHES) : '-',
            ];
        }
        
        $this->table($headers, $rows);
        
        $this->line('');
        $this->info("Total: {$features->count()} feature flags");
        
        if (!$onlyEnabled) {
            $enabledCount = $features->where('enabled', true)->count();
            $disabledCount = $features->where('enabled', false)->count();
            $this->info("Enabled: {$enabledCount}, Disabled: {$disabledCount}");
        }
        
        return Command::SUCCESS;
    }
}