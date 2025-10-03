<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class DatabaseOptimizationService
{
    /**
     * Query optimization methods
     */
    
    /**
     * Optimize Eloquent query with eager loading
     */
    public static function optimizeWithEagerLoading(Builder $query, array $relations = []): Builder
    {
        if (!empty($relations)) {
            $query->with($relations);
        }
        
        return $query;
    }
    
    /**
     * Optimize query with select specific columns
     */
    public static function optimizeWithSelect(Builder $query, array $columns = []): Builder
    {
        if (!empty($columns)) {
            $query->select($columns);
        }
        
        return $query;
    }
    
    /**
     * Optimize query with proper indexing hints
     */
    public static function optimizeWithIndex(Builder $query, string $index = null): Builder
    {
        if ($index) {
            $query->useIndex($index);
        }
        
        return $query;
    }
    
    /**
     * Optimize query with proper joins
     */
    public static function optimizeWithJoins(Builder $query, array $joins = []): Builder
    {
        foreach ($joins as $join) {
            $query->join($join['table'], $join['first'], $join['operator'], $join['second']);
        }
        
        return $query;
    }
    
    /**
     * Optimize query with proper where conditions
     */
    public static function optimizeWithWhere(Builder $query, array $conditions = []): Builder
    {
        foreach ($conditions as $condition) {
            if (isset($condition['column'], $condition['operator'], $condition['value'])) {
                $query->where($condition['column'], $condition['operator'], $condition['value']);
            }
        }
        
        return $query;
    }
    
    /**
     * Optimize query with proper ordering
     */
    public static function optimizeWithOrderBy(Builder $query, array $orderBy = []): Builder
    {
        foreach ($orderBy as $order) {
            $query->orderBy($order['column'], $order['direction'] ?? 'asc');
        }
        
        return $query;
    }
    
    /**
     * Optimize query with proper grouping
     */
    public static function optimizeWithGroupBy(Builder $query, array $groupBy = []): Builder
    {
        if (!empty($groupBy)) {
            $query->groupBy($groupBy);
        }
        
        return $query;
    }
    
    /**
     * Optimize query with proper having conditions
     */
    public static function optimizeWithHaving(Builder $query, array $having = []): Builder
    {
        foreach ($having as $condition) {
            $query->having($condition['column'], $condition['operator'], $condition['value']);
        }
        
        return $query;
    }
    
    /**
     * Optimize query with proper limit and offset
     */
    public static function optimizeWithPagination(Builder $query, int $limit = null, int $offset = null): Builder
    {
        if ($limit) {
            $query->limit($limit);
        }
        
        if ($offset) {
            $query->offset($offset);
        }
        
        return $query;
    }
    
    /**
     * Get optimized query for RVM monitoring
     */
    public static function getOptimizedRvmMonitoringQuery(): Builder
    {
        return \App\Models\ReverseVendingMachine::query()
            ->select([
                'id',
                'name',
                'location_description',
                'status',
                'last_status_change',
                'created_at',
                'remote_access_enabled',
                'kiosk_mode_enabled',
                'api_key'
            ])
            ->withCount([
                'sessions as active_sessions' => function($query) {
                    $query->where('status', 'active');
                },
                'sessions as total_sessions_today' => function($query) {
                    $query->whereDate('created_at', today());
                },
                'deposits as deposits_today' => function($query) {
                    $query->whereDate('created_at', today());
                }
            ])
            ->orderBy('name');
    }
    
    /**
     * Get optimized query for RVM statistics
     */
    public static function getOptimizedRvmStatsQuery(): Builder
    {
        return \App\Models\ReverseVendingMachine::query()
            ->select([
                'status',
                DB::raw('COUNT(*) as count')
            ])
            ->groupBy('status');
    }
    
    /**
     * Get optimized query for user sessions
     */
    public static function getOptimizedUserSessionsQuery(int $userId): Builder
    {
        return \App\Models\RvmSession::query()
            ->select([
                'id',
                'user_id',
                'rvm_id',
                'status',
                'created_at',
                'updated_at'
            ])
            ->where('user_id', $userId)
            ->with(['rvm:id,name,location_description'])
            ->orderBy('created_at', 'desc');
    }
    
    /**
     * Get optimized query for deposits
     */
    public static function getOptimizedDepositsQuery(array $filters = []): Builder
    {
        $query = \App\Models\Deposit::query()
            ->select([
                'id',
                'user_id',
                'rvm_id',
                'status',
                'reward_amount',
                'created_at',
                'updated_at'
            ])
            ->with(['user:id,name,email', 'rvm:id,name,location_description']);
        
        // Apply filters
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        
        if (isset($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }
        
        if (isset($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }
        
        return $query->orderBy('created_at', 'desc');
    }
    
    /**
     * Prevent N+1 queries with proper eager loading
     */
    public static function preventNPlusOneQueries(Builder $query, array $relations = []): Builder
    {
        // Always eager load common relations
        $defaultRelations = [
            'user:id,name,email',
            'rvm:id,name,location_description'
        ];
        
        $allRelations = array_merge($defaultRelations, $relations);
        
        return $query->with($allRelations);
    }
    
    /**
     * Optimize bulk operations
     */
    public static function optimizeBulkInsert(string $table, array $data, int $chunkSize = 1000): void
    {
        $chunks = array_chunk($data, $chunkSize);
        
        foreach ($chunks as $chunk) {
            DB::table($table)->insert($chunk);
        }
    }
    
    /**
     * Optimize bulk update
     */
    public static function optimizeBulkUpdate(string $table, array $data, string $keyColumn = 'id'): void
    {
        foreach ($data as $row) {
            DB::table($table)
                ->where($keyColumn, $row[$keyColumn])
                ->update($row);
        }
    }
    
    /**
     * Optimize bulk delete
     */
    public static function optimizeBulkDelete(string $table, array $ids, string $keyColumn = 'id'): void
    {
        DB::table($table)->whereIn($keyColumn, $ids)->delete();
    }
    
    /**
     * Get query execution plan
     */
    public static function getQueryExecutionPlan(Builder $query): array
    {
        $sql = $query->toSql();
        $bindings = $query->getBindings();
        
        $explainQuery = "EXPLAIN (ANALYZE, BUFFERS, FORMAT JSON) " . $sql;
        
        try {
            $result = DB::select($explainQuery, $bindings);
            return json_decode($result[0]->explain, true);
        } catch (\Exception $e) {
            Log::error('Failed to get query execution plan', [
                'query' => $sql,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }
    
    /**
     * Analyze query performance
     */
    public static function analyzeQueryPerformance(Builder $query): array
    {
        $startTime = microtime(true);
        $startMemory = memory_get_usage();
        
        $results = $query->get();
        
        $endTime = microtime(true);
        $endMemory = memory_get_usage();
        
        return [
            'execution_time' => round(($endTime - $startTime) * 1000, 2), // milliseconds
            'memory_usage' => $endMemory - $startMemory,
            'result_count' => $results->count(),
            'query' => $query->toSql(),
            'bindings' => $query->getBindings()
        ];
    }
    
    /**
     * Get slow queries
     */
    public static function getSlowQueries(int $minDuration = 1000): array
    {
        try {
            $queries = DB::select("
                SELECT 
                    query,
                    calls,
                    total_time,
                    mean_time,
                    rows
                FROM pg_stat_statements 
                WHERE mean_time > ? 
                ORDER BY mean_time DESC 
                LIMIT 50
            ", [$minDuration]);
            
            return $queries;
        } catch (\Exception $e) {
            Log::error('Failed to get slow queries', [
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }
    
    /**
     * Get database statistics
     */
    public static function getDatabaseStats(): array
    {
        try {
            $stats = [];
            
            // Table sizes
            $tableSizes = DB::select("
                SELECT 
                    schemaname,
                    tablename,
                    pg_size_pretty(pg_total_relation_size(schemaname||'.'||tablename)) as size
                FROM pg_tables 
                WHERE schemaname = 'public'
                ORDER BY pg_total_relation_size(schemaname||'.'||tablename) DESC
            ");
            
            $stats['table_sizes'] = $tableSizes;
            
            // Index usage
            $indexUsage = DB::select("
                SELECT 
                    schemaname,
                    tablename,
                    indexname,
                    idx_tup_read,
                    idx_tup_fetch
                FROM pg_stat_user_indexes 
                ORDER BY idx_tup_read DESC
            ");
            
            $stats['index_usage'] = $indexUsage;
            
            // Connection stats
            $connectionStats = DB::select("
                SELECT 
                    state,
                    COUNT(*) as count
                FROM pg_stat_activity 
                GROUP BY state
            ");
            
            $stats['connections'] = $connectionStats;
            
            return $stats;
        } catch (\Exception $e) {
            Log::error('Failed to get database statistics', [
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }
    
    /**
     * Optimize database connections
     */
    public static function optimizeConnections(): array
    {
        try {
            $results = [];
            
            // Update connection settings
            DB::statement("SET shared_preload_libraries = 'pg_stat_statements'");
            DB::statement("SET track_activity_query_size = 2048");
            DB::statement("SET log_min_duration_statement = 1000");
            
            $results['connection_optimization'] = 'Applied';
            
            // Analyze tables
            $tables = ['reverse_vending_machines', 'rvm_sessions', 'deposits', 'users'];
            foreach ($tables as $table) {
                DB::statement("ANALYZE {$table}");
            }
            
            $results['table_analysis'] = 'Completed';
            
            // Vacuum tables
            foreach ($tables as $table) {
                DB::statement("VACUUM ANALYZE {$table}");
            }
            
            $results['table_vacuum'] = 'Completed';
            
            return $results;
        } catch (\Exception $e) {
            Log::error('Failed to optimize database connections', [
                'error' => $e->getMessage()
            ]);
            return ['error' => $e->getMessage()];
        }
    }
}
