<?php

namespace App\Traits;

use App\Services\CacheService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

trait Cacheable
{
    /**
     * Cache TTL for this model
     */
    protected int $cacheTtl = CacheService::TTL_MEDIUM;

    /**
     * Cache prefix for this model
     */
    protected string $cachePrefix = 'model';

    /**
     * Get cache prefix for this model
     */
    protected function getCachePrefix(): string
    {
        // Use specific prefix for RVM model
        if (class_basename($this) === 'ReverseVendingMachine') {
            return 'rvm';
        }
        
        return $this->cachePrefix ?? strtolower(class_basename($this));
    }

    /**
     * Get cache key for this model
     */
    protected function getCacheKey(string $key, array $params = []): string
    {
        $modelName = strtolower(class_basename($this));
        return CacheService::getKey($this->getCachePrefix(), "{$modelName}:{$key}", $params);
    }

    /**
     * Cache model data
     */
    protected function cacheModel(string $key, callable $callback, array $params = [], ?int $ttl = null): mixed
    {
        $ttl = $ttl ?? $this->cacheTtl;
        
        return CacheService::remember(
            $this->getCachePrefix(),
            strtolower(class_basename($this)) . ":{$key}",
            $callback,
            $ttl,
            $params
        );
    }

    /**
     * Cache model with tags
     */
    protected function cacheModelWithTags(string $key, callable $callback, array $tags, array $params = [], ?int $ttl = null): mixed
    {
        $ttl = $ttl ?? $this->cacheTtl;
        
        return CacheService::rememberWithTags(
            $this->getCachePrefix(),
            strtolower(class_basename($this)) . ":{$key}",
            $callback,
            $tags,
            $ttl,
            $params
        );
    }

    /**
     * Clear model cache
     */
    protected function clearModelCache(string $key, array $params = []): bool
    {
        return CacheService::forget(
            $this->getCachePrefix(),
            strtolower(class_basename($this)) . ":{$key}",
            $params
        );
    }

    /**
     * Clear all model cache
     */
    protected function clearAllModelCache(): bool
    {
        $modelName = strtolower(class_basename($this));
        return CacheService::clearByPrefix("{$this->getCachePrefix()}:{$modelName}");
    }

    /**
     * Boot the cacheable trait
     */
    protected static function bootCacheable(): void
    {
        // Clear cache when model is updated
        static::updated(function (Model $model) {
            $model->clearAllModelCache();
        });

        // Clear cache when model is deleted
        static::deleted(function (Model $model) {
            $model->clearAllModelCache();
        });

        // Clear cache when model is created
        static::created(function (Model $model) {
            $model->clearAllModelCache();
        });
    }

    /**
     * Get cached model by ID
     */
    public static function findCached(int $id): ?Model
    {
        $model = new static();
        
        return $model->cacheModel("id:{$id}", function () use ($id) {
            return static::find($id);
        }, [], CacheService::TTL_LONG);
    }

    /**
     * Get cached model by ID or fail
     */
    public static function findCachedOrFail(int $id): Model
    {
        $model = static::findCached($id);
        
        if (!$model) {
            abort(404, 'Model not found');
        }
        
        return $model;
    }

    /**
     * Get cached models with conditions
     */
    public static function getCached(array $conditions = [], array $orderBy = [], int $limit = null): \Illuminate\Database\Eloquent\Collection
    {
        $model = new static();
        $params = array_merge($conditions, $orderBy, ['limit' => $limit]);
        
        return $model->cacheModel('list', function () use ($conditions, $orderBy, $limit) {
            $query = static::query();
            
            foreach ($conditions as $column => $value) {
                if (is_array($value)) {
                    $query->whereIn($column, $value);
                } else {
                    $query->where($column, $value);
                }
            }
            
            foreach ($orderBy as $column => $direction) {
                $query->orderBy($column, $direction);
            }
            
            if ($limit) {
                $query->limit($limit);
            }
            
            return $query->get();
        }, $params, CacheService::TTL_MEDIUM);
    }

    /**
     * Get cached count
     */
    public static function countCached(array $conditions = []): int
    {
        $model = new static();
        
        return $model->cacheModel('count', function () use ($conditions) {
            $query = static::query();
            
            foreach ($conditions as $column => $value) {
                if (is_array($value)) {
                    $query->whereIn($column, $value);
                } else {
                    $query->where($column, $value);
                }
            }
            
            return $query->count();
        }, $conditions, CacheService::TTL_MEDIUM);
    }

    /**
     * Get cached paginated results
     */
    public static function paginateCached(array $conditions = [], array $orderBy = [], int $perPage = 15): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $model = new static();
        $params = array_merge($conditions, $orderBy, ['per_page' => $perPage]);
        
        return $model->cacheModel('paginate', function () use ($conditions, $orderBy, $perPage) {
            $query = static::query();
            
            foreach ($conditions as $column => $value) {
                if (is_array($value)) {
                    $query->whereIn($column, $value);
                } else {
                    $query->where($column, $value);
                }
            }
            
            foreach ($orderBy as $column => $direction) {
                $query->orderBy($column, $direction);
            }
            
            return $query->paginate($perPage);
        }, $params, CacheService::TTL_SHORT);
    }

    /**
     * Get cached relationship data
     */
    public function getCachedRelation(string $relation, array $params = []): mixed
    {
        $relationKey = "relation:{$relation}";
        
        return $this->cacheModel($relationKey, function () use ($relation) {
            return $this->$relation;
        }, $params, CacheService::TTL_MEDIUM);
    }

    /**
     * Get cached relationship count
     */
    public function getCachedRelationCount(string $relation, array $params = []): int
    {
        $relationKey = "relation_count:{$relation}";
        
        return $this->cacheModel($relationKey, function () use ($relation) {
            return $this->$relation()->count();
        }, $params, CacheService::TTL_MEDIUM);
    }

    /**
     * Warm up model cache
     */
    public static function warmUpCache(): array
    {
        $results = [];
        $model = new static();
        
        try {
            // Cache frequently accessed data
            $results['count'] = static::countCached();
            $results['recent'] = static::getCached([], ['created_at' => 'desc'], 10);
            
            // Cache model-specific data
            if (method_exists($model, 'warmUpModelCache')) {
                $results['specific'] = $model->warmUpModelCache();
            }
            
            Log::info("Model cache warm up completed", [
                'model' => class_basename(static::class),
                'results' => $results
            ]);
            
        } catch (\Exception $e) {
            Log::error("Model cache warm up error", [
                'model' => class_basename(static::class),
                'error' => $e->getMessage()
            ]);
            $results['error'] = $e->getMessage();
        }
        
        return $results;
    }
}
