<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Support\StructuredLog;

class ChaosToggleCommand extends Command
{
    protected $signature = 'chaos:toggle {action : enable or disable}';

    protected $description = 'Toggle chaos engineering on/off (staging environment only)';

    public function handle(): int
    {
        $action = $this->argument('action');
        
        if (!in_array($action, ['enable', 'disable'])) {
            $this->error("Invalid action. Use 'enable' or 'disable'");
            return Command::FAILURE;
        }
        
        if (!app()->environment('staging')) {
            $this->error('Chaos engineering can only be toggled in staging environment');
            return Command::FAILURE;
        }
        
        try {
            $envFile = base_path('.env');
            $envContent = file_get_contents($envFile);
            
            $enabled = $action === 'enable';
            $newValue = $enabled ? 'true' : 'false';
            
            // Update CHAOS_ENABLED value
            if (str_contains($envContent, 'CHAOS_ENABLED=')) {
                $envContent = preg_replace('/CHAOS_ENABLED=.*/', "CHAOS_ENABLED={$newValue}", $envContent);
            } else {
                $envContent .= "\nCHAOS_ENABLED={$newValue}\n";
            }
            
            file_put_contents($envFile, $envContent);
            
            $status = $enabled ? '🔥 Enabled' : '✅ Disabled';
            $this->info("Chaos engineering has been {$status}");
            
            if ($enabled) {
                $this->warn('⚠️  Chaos is now active! Random errors and latency will be injected.');
                $this->warn('⚠️  This should only be used for resilience testing.');
            }
            
            StructuredLog::info('chaos_toggled', [
                'action' => $action,
                'enabled' => $enabled,
                'environment' => app()->environment(),
                'toggled_by' => 'cli',
            ]);
            
            $this->info('💡 Restart the application for changes to take effect');
            
            return Command::SUCCESS;
            
        } catch (\Exception $e) {
            $this->error("Failed to toggle chaos: {$e->getMessage()}");
            
            StructuredLog::error('chaos_toggle_failed', [
                'action' => $action,
                'error' => $e->getMessage(),
            ]);
            
            return Command::FAILURE;
        }
    }
}