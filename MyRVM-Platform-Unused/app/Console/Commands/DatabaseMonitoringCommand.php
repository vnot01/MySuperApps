<?php

namespace App\Console\Commands;

use App\Services\DatabaseOptimizationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DatabaseMonitoringCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:monitor 
                            {action : The action to perform (stats, slow-queries, optimize, analyze)}
                            {--min-duration=1000 : Minimum duration for slow queries in milliseconds}
                            {--limit=50 : Limit number of results}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Monitor database performance and optimization';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $action = $this->argument('action');

        switch ($action) {
            case 'stats':
                $this->showDatabaseStats();
                break;
            case 'slow-queries':
                $this->showSlowQueries();
                break;
            case 'optimize':
                $this->optimizeDatabase();
                break;
            case 'analyze':
                $this->analyzeTables();
                break;
            default:
                $this->error("Unknown action: {$action}");
                $this->info('Available actions: stats, slow-queries, optimize, analyze');
                return 1;
        }

        return 0;
    }

    /**
     * Show database statistics
     */
    private function showDatabaseStats(): void
    {
        $this->info('Database Statistics:');

        try {
            $stats = DatabaseOptimizationService::getDatabaseStats();
            
            if (empty($stats)) {
                $this->warn('No database statistics available');
                return;
            }

            // Table sizes
            if (!empty($stats['table_sizes'])) {
                $this->info("\nTable Sizes:");
                $tableData = [];
                foreach ($stats['table_sizes'] as $table) {
                    $tableData[] = [
                        $table->tablename,
                        $table->size
                    ];
                }
                $this->table(['Table', 'Size'], $tableData);
            }

            // Index usage
            if (!empty($stats['index_usage'])) {
                $this->info("\nIndex Usage (Top 10):");
                $indexData = [];
                $topIndexes = array_slice($stats['index_usage'], 0, 10);
                foreach ($topIndexes as $index) {
                    $indexData[] = [
                        $index->tablename . '.' . $index->indexname,
                        number_format($index->idx_tup_read),
                        number_format($index->idx_tup_fetch)
                    ];
                }
                $this->table(['Index', 'Tuples Read', 'Tuples Fetched'], $indexData);
            }

            // Connection stats
            if (!empty($stats['connections'])) {
                $this->info("\nConnection Statistics:");
                $connectionData = [];
                foreach ($stats['connections'] as $connection) {
                    $connectionData[] = [
                        $connection->state,
                        $connection->count
                    ];
                }
                $this->table(['State', 'Count'], $connectionData);
            }

        } catch (\Exception $e) {
            $this->error("Failed to get database statistics: {$e->getMessage()}");
        }
    }

    /**
     * Show slow queries
     */
    private function showSlowQueries(): void
    {
        $minDuration = (int) $this->option('min-duration');
        $limit = (int) $this->option('limit');

        $this->info("Slow Queries (>{$minDuration}ms):");

        try {
            $slowQueries = DatabaseOptimizationService::getSlowQueries($minDuration);
            
            if (empty($slowQueries)) {
                $this->info('No slow queries found');
                return;
            }

            $queryData = [];
            $limitedQueries = array_slice($slowQueries, 0, $limit);
            
            foreach ($limitedQueries as $query) {
                $queryData[] = [
                    number_format($query->calls),
                    number_format($query->total_time, 2) . 'ms',
                    number_format($query->mean_time, 2) . 'ms',
                    number_format($query->rows),
                    $this->truncateQuery($query->query, 80)
                ];
            }

            $this->table(
                ['Calls', 'Total Time', 'Mean Time', 'Rows', 'Query'],
                $queryData
            );

        } catch (\Exception $e) {
            $this->error("Failed to get slow queries: {$e->getMessage()}");
        }
    }

    /**
     * Optimize database
     */
    private function optimizeDatabase(): void
    {
        $this->info('Optimizing database...');

        try {
            $results = DatabaseOptimizationService::optimizeConnections();
            
            if (isset($results['error'])) {
                $this->error("Database optimization failed: {$results['error']}");
                return;
            }

            $this->info('Database optimization completed:');
            foreach ($results as $key => $value) {
                $this->line("  - {$key}: {$value}");
            }

        } catch (\Exception $e) {
            $this->error("Database optimization failed: {$e->getMessage()}");
        }
    }

    /**
     * Analyze tables
     */
    private function analyzeTables(): void
    {
        $this->info('Analyzing tables...');

        try {
            $tables = ['reverse_vending_machines', 'rvm_sessions', 'deposits', 'users', 'transactions', 'vouchers'];
            
            foreach ($tables as $table) {
                $this->line("Analyzing table: {$table}");
                DB::statement("ANALYZE {$table}");
            }

            $this->info('Table analysis completed successfully');

        } catch (\Exception $e) {
            $this->error("Table analysis failed: {$e->getMessage()}");
        }
    }

    /**
     * Truncate query for display
     */
    private function truncateQuery(string $query, int $length): string
    {
        if (strlen($query) <= $length) {
            return $query;
        }
        
        return substr($query, 0, $length) . '...';
    }
}
