<?php

namespace App\Console\Commands;

use App\Services\CacheService;
use App\Models\ReverseVendingMachine;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class CacheManagementCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cache:manage 
                            {action : The action to perform (warm, clear, stats, clear-prefix)}
                            {--prefix= : Cache prefix to clear}
                            {--model= : Model to warm up cache for}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Manage application cache (warm, clear, stats, clear-prefix)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $action = $this->argument('action');

        switch ($action) {
            case 'warm':
                $this->warmUpCache();
                break;
            case 'clear':
                $this->clearCache();
                break;
            case 'stats':
                $this->showCacheStats();
                break;
            case 'clear-prefix':
                $this->clearCacheByPrefix();
                break;
            default:
                $this->error("Unknown action: {$action}");
                $this->info('Available actions: warm, clear, stats, clear-prefix');
                return 1;
        }

        return 0;
    }

    /**
     * Warm up cache
     */
    private function warmUpCache(): void
    {
        $this->info('Warming up cache...');

        try {
            // Warm up general cache
            $results = CacheService::warmUp();
            
            $this->info('General cache warm up completed:');
            foreach ($results as $key => $value) {
                $this->line("  - {$key}: " . ($value ? 'Success' : 'Failed'));
            }

            // Warm up model-specific cache
            $model = $this->option('model');
            if ($model) {
                $this->warmUpModelCache($model);
            } else {
                // Warm up all models
                $this->warmUpModelCache('ReverseVendingMachine');
                $this->warmUpModelCache('User');
            }

            $this->info('Cache warm up completed successfully!');

        } catch (\Exception $e) {
            $this->error("Cache warm up failed: {$e->getMessage()}");
        }
    }

    /**
     * Warm up model cache
     */
    private function warmUpModelCache(string $modelName): void
    {
        $this->info("Warming up {$modelName} cache...");

        try {
            $modelClass = "App\\Models\\{$modelName}";
            
            if (!class_exists($modelClass)) {
                $this->error("Model {$modelName} not found");
                return;
            }

            $results = $modelClass::warmUpCache();
            
            $this->info("{$modelName} cache warm up completed:");
            foreach ($results as $key => $value) {
                if (is_array($value)) {
                    $this->line("  - {$key}: " . count($value) . " items");
                } else {
                    $this->line("  - {$key}: " . ($value ? 'Success' : 'Failed'));
                }
            }

        } catch (\Exception $e) {
            $this->error("{$modelName} cache warm up failed: {$e->getMessage()}");
        }
    }

    /**
     * Clear cache
     */
    private function clearCache(): void
    {
        $this->info('Clearing cache...');

        try {
            Cache::flush();
            $this->info('Cache cleared successfully!');

        } catch (\Exception $e) {
            $this->error("Cache clear failed: {$e->getMessage()}");
        }
    }

    /**
     * Clear cache by prefix
     */
    private function clearCacheByPrefix(): void
    {
        $prefix = $this->option('prefix');
        
        if (!$prefix) {
            $this->error('Prefix is required for clear-prefix action');
            $this->info('Usage: php artisan cache:manage clear-prefix --prefix=rvm');
            return;
        }

        $this->info("Clearing cache with prefix: {$prefix}");

        try {
            $success = CacheService::clearByPrefix($prefix);
            
            if ($success) {
                $this->info("Cache with prefix '{$prefix}' cleared successfully!");
            } else {
                $this->error("Failed to clear cache with prefix '{$prefix}'");
            }

        } catch (\Exception $e) {
            $this->error("Cache clear failed: {$e->getMessage()}");
        }
    }

    /**
     * Show cache statistics
     */
    private function showCacheStats(): void
    {
        $this->info('Cache Statistics:');

        try {
            $stats = CacheService::getStats();
            
            if (empty($stats)) {
                $this->warn('No cache statistics available');
                return;
            }

            $this->table(
                ['Metric', 'Value'],
                [
                    ['Used Memory', $stats['used_memory'] ?? 'N/A'],
                    ['Peak Memory', $stats['used_memory_peak'] ?? 'N/A'],
                    ['Connected Clients', $stats['connected_clients'] ?? 'N/A'],
                    ['Total Commands', $stats['total_commands_processed'] ?? 'N/A'],
                    ['Cache Hits', $stats['keyspace_hits'] ?? 'N/A'],
                    ['Cache Misses', $stats['keyspace_misses'] ?? 'N/A'],
                    ['Hit Rate', ($stats['hit_rate'] ?? 0) . '%'],
                ]
            );

        } catch (\Exception $e) {
            $this->error("Failed to get cache statistics: {$e->getMessage()}");
        }
    }
}
