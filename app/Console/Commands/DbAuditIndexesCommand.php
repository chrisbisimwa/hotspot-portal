<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Support\StructuredLog;

class DbAuditIndexesCommand extends Command
{
    protected $signature = 'db:audit-indexes {--table= : Specific table to audit}';

    protected $description = 'Audit database indexes and suggest optimizations';

    public function handle(): int
    {
        $this->info('Starting database index audit...');
        
        $table = $this->option('table');
        $suggestions = [];
        
        try {
            if ($table) {
                $tableSuggestions = $this->auditTable($table);
                if (!empty($tableSuggestions)) {
                    $suggestions[$table] = $tableSuggestions;
                }
            } else {
                $suggestions = $this->auditAllTables();
            }
            
            $this->displaySuggestions($suggestions);
            
            StructuredLog::info('db_index_audit_completed', [
                'table_filter' => $table,
                'suggestions_count' => count($suggestions),
                'suggestions' => $suggestions,
            ]);
            
            return Command::SUCCESS;
            
        } catch (\Exception $e) {
            $this->error("Index audit failed: {$e->getMessage()}");
            
            StructuredLog::error('db_index_audit_failed', [
                'error' => $e->getMessage(),
                'table_filter' => $table,
            ]);
            
            return Command::FAILURE;
        }
    }
    
    /**
     * Audit all tables
     */
    private function auditAllTables(): array
    {
        $suggestions = [];
        $tables = $this->getAllTables();
        
        foreach ($tables as $table) {
            $tableSuggestions = $this->auditTable($table);
            if (!empty($tableSuggestions)) {
                $suggestions[$table] = $tableSuggestions;
            }
        }
        
        return $suggestions;
    }
    
    /**
     * Audit specific table
     */
    private function auditTable(string $table): array
    {
        if (!Schema::hasTable($table)) {
            $this->error("Table '{$table}' does not exist");
            return [];
        }
        
        $this->info("Auditing table: {$table}");
        $suggestions = [];
        
        // Get existing indexes
        $indexes = $this->getTableIndexes($table);
        $columns = Schema::getColumnListing($table);
        
        // Analyze based on table-specific patterns
        switch ($table) {
            case 'payments':
                $suggestions = array_merge($suggestions, $this->auditPaymentsTable($indexes));
                break;
                
            case 'logs':
                $suggestions = array_merge($suggestions, $this->auditLogsTable($indexes));
                break;
                
            case 'hotspot_sessions':
                $suggestions = array_merge($suggestions, $this->auditHotspotSessionsTable($indexes));
                break;
                
            case 'jobs':
                $suggestions = array_merge($suggestions, $this->auditJobsTable($indexes));
                break;
                
            case 'webhook_attempts':
                $suggestions = array_merge($suggestions, $this->auditWebhookAttemptsTable($indexes));
                break;
                
            default:
                $suggestions = array_merge($suggestions, $this->auditGenericTable($table, $indexes, $columns));
                break;
        }
        
        return $suggestions;
    }
    
    /**
     * Audit payments table specifically
     */
    private function auditPaymentsTable(array $indexes): array
    {
        $suggestions = [];
        
        // Check for composite index on (status, created_at)
        if (!$this->hasCompositeIndex($indexes, ['status', 'created_at'])) {
            $suggestions[] = [
                'type' => 'composite_index',
                'columns' => ['status', 'created_at'],
                'reason' => 'Optimize queries filtering by payment status with date range',
                'migration' => '$table->index([\'status\', \'created_at\']);',
            ];
        }
        
        // Check for index on user_id for user payment history
        if (!$this->hasIndex($indexes, 'user_id')) {
            $suggestions[] = [
                'type' => 'single_index',
                'column' => 'user_id',
                'reason' => 'Optimize user payment history queries',
                'migration' => '$table->index(\'user_id\');',
            ];
        }
        
        return $suggestions;
    }
    
    /**
     * Audit logs table specifically
     */
    private function auditLogsTable(array $indexes): array
    {
        $suggestions = [];
        
        // Check for composite index on (level, created_at)
        if (!$this->hasCompositeIndex($indexes, ['level', 'created_at'])) {
            $suggestions[] = [
                'type' => 'composite_index',
                'columns' => ['level', 'created_at'],
                'reason' => 'Optimize log filtering by level with date range',
                'migration' => '$table->index([\'level\', \'created_at\']);',
            ];
        }
        
        return $suggestions;
    }
    
    /**
     * Audit hotspot_sessions table specifically
     */
    private function auditHotspotSessionsTable(array $indexes): array
    {
        $suggestions = [];
        
        // Check for composite index on (hotspot_user_id, start_time)
        if (!$this->hasCompositeIndex($indexes, ['hotspot_user_id', 'start_time'])) {
            $suggestions[] = [
                'type' => 'composite_index',
                'columns' => ['hotspot_user_id', 'start_time'],
                'reason' => 'Optimize session queries by user with time ordering',
                'migration' => '$table->index([\'hotspot_user_id\', \'start_time\']);',
            ];
        }
        
        return $suggestions;
    }
    
    /**
     * Audit jobs table specifically
     */
    private function auditJobsTable(array $indexes): array
    {
        $suggestions = [];
        
        // Check for composite index on (queue, created_at)
        if (!$this->hasCompositeIndex($indexes, ['queue', 'created_at'])) {
            $suggestions[] = [
                'type' => 'composite_index',
                'columns' => ['queue', 'created_at'],
                'reason' => 'Optimize queue monitoring and oldest job queries',
                'migration' => '$table->index([\'queue\', \'created_at\']);',
            ];
        }
        
        return $suggestions;
    }
    
    /**
     * Audit webhook_attempts table specifically
     */
    private function auditWebhookAttemptsTable(array $indexes): array
    {
        $suggestions = [];
        
        // Check for composite index on (status, scheduled_at)
        if (!$this->hasCompositeIndex($indexes, ['status', 'scheduled_at'])) {
            $suggestions[] = [
                'type' => 'composite_index',
                'columns' => ['status', 'scheduled_at'],
                'reason' => 'Optimize webhook retry and processing queries',
                'migration' => '$table->index([\'status\', \'scheduled_at\']);',
            ];
        }
        
        return $suggestions;
    }
    
    /**
     * Generic table audit
     */
    private function auditGenericTable(string $table, array $indexes, array $columns): array
    {
        $suggestions = [];
        
        // Check for common patterns
        if (in_array('created_at', $columns) && !$this->hasIndex($indexes, 'created_at')) {
            $suggestions[] = [
                'type' => 'single_index',
                'column' => 'created_at',
                'reason' => 'Optimize date-based queries and ordering',
                'migration' => '$table->index(\'created_at\');',
            ];
        }
        
        return $suggestions;
    }
    
    /**
     * Get all tables in the database
     */
    private function getAllTables(): array
    {
        $tables = DB::select('SELECT name FROM sqlite_master WHERE type="table" AND name NOT LIKE "sqlite_%"');
        return array_column($tables, 'name');
    }
    
    /**
     * Get indexes for a table
     */
    private function getTableIndexes(string $table): array
    {
        $indexes = DB::select("PRAGMA index_list({$table})");
        $result = [];
        
        foreach ($indexes as $index) {
            $indexInfo = DB::select("PRAGMA index_info({$index->name})");
            $columns = array_column($indexInfo, 'name');
            $result[] = [
                'name' => $index->name,
                'unique' => $index->unique,
                'columns' => $columns,
            ];
        }
        
        return $result;
    }
    
    /**
     * Check if table has specific index
     */
    private function hasIndex(array $indexes, string $column): bool
    {
        foreach ($indexes as $index) {
            if (count($index['columns']) === 1 && $index['columns'][0] === $column) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * Check if table has composite index
     */
    private function hasCompositeIndex(array $indexes, array $columns): bool
    {
        foreach ($indexes as $index) {
            if ($index['columns'] === $columns) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * Display audit suggestions
     */
    private function displaySuggestions(array $suggestions): void
    {
        if (empty($suggestions)) {
            $this->info('✅ No index optimizations needed');
            return;
        }
        
        $this->info('📊 Index Optimization Suggestions:');
        $this->line('');
        
        foreach ($suggestions as $table => $tableSuggestions) {
            $this->info("Table: {$table}");
            
            foreach ($tableSuggestions as $suggestion) {
                $this->line("  • {$suggestion['reason']}");
                $this->line("    Migration: {$suggestion['migration']}");
                $this->line('');
            }
        }
        
        $this->info('💡 Run these migrations to improve performance');
    }
}