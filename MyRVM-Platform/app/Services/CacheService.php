<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CacheService
{
    /**
     * Cache TTL constants
     */
    const TTL_SHORT = 60;        // 1 minute
    const TTL_MEDIUM = 300;      // 5 minutes
    const TTL_LONG = 1800;       // 30 minutes
    const TTL_VERY_LONG = 3600;  // 1 hour
    const TTL_EXTREME = 86400;   // 24 hours

    /**
     * Cache key prefixes
     */
    const PREFIX_RVM = 'rvm';
    const PREFIX_USER = 'user';
    const PREFIX_SESSION = 'session';
    const PREFIX_DEPOSIT = 'deposit';
    const PREFIX_ANALYTICS = 'analytics';
    const PREFIX_CONFIG = 'config';

    /**
     * Get cache key with prefix
     */
    public static function getKey(string $prefix, string $key, array $params = []): string
    {
        $key = $prefix . ':' . $key;
        if (!empty($params)) {
            $key .= ':' . md5(serialize($params));
        }
        return $key;
    }

    /**
     * Cache with automatic key generation
     */
    public static function remember(string $prefix, string $key, callable $callback, int $ttl = self::TTL_MEDIUM, array $params = []): mixed
    {
        $cacheKey = self::getKey($prefix, $key, $params);
        
        try {
            return Cache::remember($cacheKey, $ttl, function () use ($callback, $cacheKey) {
                Log::info("Cache miss for key: {$cacheKey}");
                return $callback();
            });
        } catch (\Exception $e) {
            Log::error("Cache error for key: {$cacheKey}", ['error' => $e->getMessage()]);
            return $callback();
        }
    }

    /**
     * Cache with automatic key generation and tags
     */
    public static function rememberWithTags(string $prefix, string $key, callable $callback, array $tags, int $ttl = self::TTL_MEDIUM, array $params = []): mixed
    {
        $cacheKey = self::getKey($prefix, $key, $params);
        
        try {
            return Cache::tags($tags)->remember($cacheKey, $ttl, function () use ($callback, $cacheKey) {
                Log::info("Cache miss for tagged key: {$cacheKey}");
                return $callback();
            });
        } catch (\Exception $e) {
            Log::error("Cache error for tagged key: {$cacheKey}", ['error' => $e->getMessage()]);
            return $callback();
        }
    }

    /**
     * Put cache with automatic key generation
     */
    public static function put(string $prefix, string $key, mixed $value, int $ttl = self::TTL_MEDIUM, array $params = []): bool
    {
        $cacheKey = self::getKey($prefix, $key, $params);
        
        try {
            return Cache::put($cacheKey, $value, $ttl);
        } catch (\Exception $e) {
            Log::error("Cache put error for key: {$cacheKey}", ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Get cache with automatic key generation
     */
    public static function get(string $prefix, string $key, mixed $default = null, array $params = []): mixed
    {
        $cacheKey = self::getKey($prefix, $key, $params);
        
        try {
            return Cache::get($cacheKey, $default);
        } catch (\Exception $e) {
            Log::error("Cache get error for key: {$cacheKey}", ['error' => $e->getMessage()]);
            return $default;
        }
    }

    /**
     * Forget cache with automatic key generation
     */
    public static function forget(string $prefix, string $key, array $params = []): bool
    {
        $cacheKey = self::getKey($prefix, $key, $params);
        
        try {
            return Cache::forget($cacheKey);
        } catch (\Exception $e) {
            Log::error("Cache forget error for key: {$cacheKey}", ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Clear cache by prefix
     */
    public static function clearByPrefix(string $prefix): bool
    {
        try {
            $pattern = self::getKey($prefix, '*');
            $keys = Cache::getRedis()->keys($pattern);
            
            if (!empty($keys)) {
                Cache::getRedis()->del($keys);
                Log::info("Cleared cache for prefix: {$prefix}", ['keys_count' => count($keys)]);
            }
            
            return true;
        } catch (\Exception $e) {
            Log::error("Cache clear error for prefix: {$prefix}", ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Clear cache by tags
     */
    public static function clearByTags(array $tags): bool
    {
        try {
            Cache::tags($tags)->flush();
            Log::info("Cleared cache for tags", ['tags' => $tags]);
            return true;
        } catch (\Exception $e) {
            Log::error("Cache clear error for tags", ['tags' => $tags, 'error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Get cache statistics
     */
    public static function getStats(): array
    {
        try {
            $redis = Cache::getRedis();
            $info = $redis->info();
            
            return [
                'used_memory' => $info['used_memory_human'] ?? 'N/A',
                'used_memory_peak' => $info['used_memory_peak_human'] ?? 'N/A',
                'connected_clients' => $info['connected_clients'] ?? 0,
                'total_commands_processed' => $info['total_commands_processed'] ?? 0,
                'keyspace_hits' => $info['keyspace_hits'] ?? 0,
                'keyspace_misses' => $info['keyspace_misses'] ?? 0,
                'hit_rate' => self::calculateHitRate($info),
            ];
        } catch (\Exception $e) {
            Log::error("Cache stats error", ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Calculate cache hit rate
     */
    private static function calculateHitRate(array $info): float
    {
        $hits = $info['keyspace_hits'] ?? 0;
        $misses = $info['keyspace_misses'] ?? 0;
        $total = $hits + $misses;
        
        return $total > 0 ? round(($hits / $total) * 100, 2) : 0;
    }

    /**
     * Warm up cache
     */
    public static function warmUp(): array
    {
        $results = [];
        
        try {
            // Warm up RVM data
            $results['rvm_list'] = self::warmUpRvmList();
            
            // Warm up user data
            $results['user_stats'] = self::warmUpUserStats();
            
            // Warm up configuration
            $results['config'] = self::warmUpConfig();
            
            Log::info("Cache warm up completed", $results);
            
        } catch (\Exception $e) {
            Log::error("Cache warm up error", ['error' => $e->getMessage()]);
            $results['error'] = $e->getMessage();
        }
        
        return $results;
    }

    /**
     * Warm up RVM list cache
     */
    private static function warmUpRvmList(): bool
    {
        try {
            $rvms = \App\Models\ReverseVendingMachine::select('id', 'name', 'status', 'created_at')
                ->orderBy('name')
                ->get();
            
            self::put(self::PREFIX_RVM, 'list', $rvms, self::TTL_LONG);
            return true;
        } catch (\Exception $e) {
            Log::error("RVM list warm up error", ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Warm up user statistics cache
     */
    private static function warmUpUserStats(): bool
    {
        try {
            $userCount = \App\Models\User::count();
            $activeUserCount = \App\Models\User::whereNotNull('email_verified_at')->count();
            
            $stats = [
                'total_users' => $userCount,
                'active_users' => $activeUserCount,
                'inactive_users' => $userCount - $activeUserCount,
            ];
            
            self::put(self::PREFIX_USER, 'stats', $stats, self::TTL_MEDIUM);
            return true;
        } catch (\Exception $e) {
            Log::error("User stats warm up error", ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Warm up configuration cache
     */
    private static function warmUpConfig(): bool
    {
        try {
            $config = [
                'app_name' => config('app.name'),
                'app_env' => config('app.env'),
                'cache_driver' => config('cache.default'),
                'session_driver' => config('session.driver'),
            ];
            
            self::put(self::PREFIX_CONFIG, 'app', $config, self::TTL_EXTREME);
            return true;
        } catch (\Exception $e) {
            Log::error("Config warm up error", ['error' => $e->getMessage()]);
            return false;
        }
    }
}
