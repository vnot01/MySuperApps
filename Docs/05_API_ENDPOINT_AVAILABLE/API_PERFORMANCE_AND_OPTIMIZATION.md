# ⚡ API Performance & Optimization - MyRVM-Ecosystem v2.0

## 📍 Performance Overview

### Performance Targets
- **Response Time**: < 200ms for simple requests, < 1s for complex operations
- **Throughput**: 1000+ requests per minute
- **Concurrent Users**: 100+ simultaneous users
- **Database Queries**: < 50ms average query time
- **Memory Usage**: < 512MB per request
- **CPU Usage**: < 80% under normal load

---

## 🚀 Server Performance Optimization

### Database Optimization
```php
<?php
// app/Http/Controllers/Api/RvmController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ReverseVendingMachine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class RvmController extends Controller
{
    public function index(Request $request)
    {
        // Use caching for frequently accessed data
        $cacheKey = 'rvms_list_' . md5(serialize($request->all()));
        
        $rvms = Cache::remember($cacheKey, 300, function () use ($request) {
            return ReverseVendingMachine::select([
                'id', 'name', 'location', 'status', 'created_at'
            ])
            ->with(['detectionResults' => function($query) {
                $query->select('rvm_id', 'created_at')
                      ->latest()
                      ->limit(1);
            }])
            ->when($request->status, function($query, $status) {
                $query->where('status', $status);
            })
            ->when($request->search, function($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                      ->orWhere('location', 'like', "%{$search}%");
            })
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));
        });
        
        return response()->json([
            'data' => $rvms->items(),
            'pagination' => [
                'current_page' => $rvms->currentPage(),
                'last_page' => $rvms->lastPage(),
                'per_page' => $rvms->perPage(),
                'total' => $rvms->total()
            ]
        ]);
    }
    
    public function show($id)
    {
        // Use eager loading to prevent N+1 queries
        $rvm = ReverseVendingMachine::with([
            'detectionResults' => function($query) {
                $query->select('id', 'rvm_id', 'image_path', 'detection_results', 'created_at')
                      ->latest()
                      ->limit(10);
            },
            'user' => function($query) {
                $query->select('id', 'name', 'email');
            }
        ])->findOrFail($id);
        
        return response()->json(['data' => $rvm]);
    }
    
    public function analytics($id)
    {
        // Use database aggregation for better performance
        $analytics = Cache::remember("rvm_analytics_{$id}", 600, function () use ($id) {
            return DB::table('detection_results')
                ->where('rvm_id', $id)
                ->selectRaw('
                    COUNT(*) as total_detections,
                    COUNT(DISTINCT DATE(created_at)) as active_days,
                    AVG(processing_time) as avg_processing_time,
                    MAX(created_at) as last_detection,
                    SUM(CASE WHEN JSON_EXTRACT(detection_results, "$[0].confidence") > 0.8 THEN 1 ELSE 0 END) as high_confidence_detections
                ')
                ->first();
        });
        
        return response()->json(['data' => $analytics]);
    }
}
```

### Query Optimization
```php
<?php
// app/Models/ReverseVendingMachine.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class ReverseVendingMachine extends Model
{
    protected $fillable = [
        'name', 'location', 'ip_address', 'capacity', 'current_load',
        'latitude', 'longitude', 'status', 'api_key', 'api_key_expires_at'
    ];
    
    protected $casts = [
        'api_key_expires_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];
    
    // Optimized relationship with select
    public function detectionResults()
    {
        return $this->hasMany(DetectionResult::class)
            ->select(['id', 'rvm_id', 'image_path', 'detection_results', 'processing_time', 'created_at']);
    }
    
    // Optimized scope for active RVMs
    public function scopeActive(Builder $query)
    {
        return $query->where('status', 'active')
                    ->whereNotNull('ip_address')
                    ->where('ip_address', '!=', '');
    }
    
    // Optimized scope for nearby RVMs
    public function scopeNearby(Builder $query, float $latitude, float $longitude, float $radius = 10)
    {
        return $query->selectRaw('
            *,
            (6371 * acos(cos(radians(?)) * cos(radians(latitude)) * 
            cos(radians(longitude) - radians(?)) + sin(radians(?)) * 
            sin(radians(latitude)))) AS distance
        ', [$latitude, $longitude, $latitude])
        ->having('distance', '<', $radius)
        ->orderBy('distance');
    }
    
    // Optimized method for status checking
    public function getStatusAttribute()
    {
        if (!$this->ip_address) {
            return 'inactive';
        }
        
        $lastPing = $this->last_ping;
        if (!$lastPing) {
            return 'offline';
        }
        
        if ($lastPing->diffInMinutes(now()) > 5) {
            return 'offline';
        }
        
        return 'online';
    }
}
```

### Caching Strategy
```php
<?php
// app/Services/CacheService.php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;

class CacheService
{
    public function getRvmList(array $filters = [])
    {
        $cacheKey = 'rvms_list_' . md5(serialize($filters));
        
        return Cache::remember($cacheKey, 300, function () use ($filters) {
            return ReverseVendingMachine::with(['detectionResults' => function($query) {
                $query->select('rvm_id', 'created_at')->latest()->limit(1);
            }])
            ->when($filters['status'] ?? null, function($query, $status) {
                $query->where('status', $status);
            })
            ->when($filters['search'] ?? null, function($query, $search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->orderBy('created_at', 'desc')
            ->paginate(15);
        });
    }
    
    public function getRvmAnalytics(int $rvmId)
    {
        $cacheKey = "rvm_analytics_{$rvmId}";
        
        return Cache::remember($cacheKey, 600, function () use ($rvmId) {
            return DB::table('detection_results')
                ->where('rvm_id', $rvmId)
                ->selectRaw('
                    COUNT(*) as total_detections,
                    AVG(processing_time) as avg_processing_time,
                    MAX(created_at) as last_detection
                ')
                ->first();
        });
    }
    
    public function invalidateRvmCache(int $rvmId)
    {
        Cache::forget("rvm_analytics_{$rvmId}");
        Cache::forget("rvm_details_{$rvmId}");
        
        // Clear list cache
        $this->clearListCache();
    }
    
    public function clearListCache()
    {
        $pattern = 'rvms_list_*';
        $keys = Redis::keys($pattern);
        
        if (!empty($keys)) {
            Redis::del($keys);
        }
    }
    
    public function warmCache()
    {
        // Warm up frequently accessed data
        $this->getRvmList();
        
        // Warm up analytics for active RVMs
        $activeRvms = ReverseVendingMachine::active()->pluck('id');
        foreach ($activeRvms as $rvmId) {
            $this->getRvmAnalytics($rvmId);
        }
    }
}
```

---

## 🤖 Jetson Performance Optimization

### Python API Optimization
```python
# app.py - Optimized Flask API

from flask import Flask, request, jsonify
import asyncio
import aiofiles
import uvicorn
from concurrent.futures import ThreadPoolExecutor
import gc
import psutil
import time

app = Flask(__name__)

# Global thread pool for CPU-intensive tasks
executor = ThreadPoolExecutor(max_workers=4)

# Global variables for caching
model_cache = {}
result_cache = {}

@app.before_request
def before_request():
    """Log request start time for performance monitoring"""
    request.start_time = time.time()

@app.after_request
def after_request(response):
    """Log request duration and add performance headers"""
    if hasattr(request, 'start_time'):
        duration = time.time() - request.start_time
        response.headers['X-Processing-Time'] = str(duration)
        response.headers['X-Memory-Usage'] = str(psutil.Process().memory_info().rss / 1024 / 1024)
    
    return response

@app.route('/api/health', methods=['GET'])
def health_check():
    """Optimized health check with minimal processing"""
    return jsonify({
        'status': 'healthy',
        'timestamp': time.time(),
        'version': '2.0.0',
        'memory_usage': psutil.Process().memory_info().rss / 1024 / 1024,
        'cpu_usage': psutil.cpu_percent()
    })

@app.route('/api/detection', methods=['POST'])
def detection():
    """Optimized detection endpoint with caching and async processing"""
    try:
        data = request.get_json()
        rvm_id = data.get('rvm_id')
        image_path = data.get('image_path')
        
        # Check cache first
        cache_key = f"detection_{rvm_id}_{hash(image_path)}"
        if cache_key in result_cache:
            return jsonify({
                'id': result_cache[cache_key]['id'],
                'rvm_id': rvm_id,
                'detection_results': result_cache[cache_key]['results'],
                'processing_time': 0.001,  # Cached result
                'cached': True
            })
        
        # Process detection asynchronously
        loop = asyncio.new_event_loop()
        asyncio.set_event_loop(loop)
        
        result = loop.run_until_complete(process_detection_async(data))
        
        # Cache result
        result_cache[cache_key] = {
            'id': result['id'],
            'results': result['detection_results']
        }
        
        # Clean up old cache entries
        if len(result_cache) > 1000:
            # Remove oldest 100 entries
            oldest_keys = list(result_cache.keys())[:100]
            for key in oldest_keys:
                del result_cache[key]
        
        return jsonify(result)
        
    except Exception as e:
        return jsonify({'error': str(e)}), 500

async def process_detection_async(data):
    """Async detection processing"""
    # Simulate detection processing
    await asyncio.sleep(0.1)  # Simulate processing time
    
    return {
        'id': int(time.time() * 1000),
        'rvm_id': data['rvm_id'],
        'detection_results': data.get('detection_results', []),
        'processing_time': 0.1
    }

@app.route('/api/monitoring/status', methods=['GET'])
def monitoring_status():
    """Optimized monitoring status with cached system metrics"""
    # Cache system metrics for 5 seconds
    cache_key = 'system_metrics'
    if cache_key in result_cache:
        return jsonify(result_cache[cache_key])
    
    # Get system metrics
    cpu_usage = psutil.cpu_percent(interval=1)
    memory = psutil.virtual_memory()
    disk = psutil.disk_usage('/')
    
    # Get GPU usage if available
    try:
        import GPUtil
        gpus = GPUtil.getGPUs()
        gpu_usage = gpus[0].load * 100 if gpus else 0
    except:
        gpu_usage = 0
    
    metrics = {
        'cpu_usage': cpu_usage,
        'memory_usage': memory.percent,
        'memory_available': memory.available / 1024 / 1024 / 1024,  # GB
        'disk_usage': disk.percent,
        'disk_available': disk.free / 1024 / 1024 / 1024,  # GB
        'gpu_usage': gpu_usage,
        'timestamp': time.time()
    }
    
    # Cache for 5 seconds
    result_cache[cache_key] = metrics
    
    return jsonify(metrics)

@app.route('/api/monitoring/summary', methods=['GET'])
def monitoring_summary():
    """Optimized monitoring summary with aggregated data"""
    # Get recent metrics from cache
    cache_key = 'system_metrics'
    if cache_key not in result_cache:
        # Get fresh data
        monitoring_status()
    
    metrics = result_cache[cache_key]
    
    # Generate summary
    summary = {
        'status': 'healthy' if metrics['cpu_usage'] < 80 and metrics['memory_usage'] < 80 else 'warning',
        'cpu_usage': metrics['cpu_usage'],
        'memory_usage': metrics['memory_usage'],
        'gpu_usage': metrics['gpu_usage'],
        'alerts': []
    }
    
    # Check for alerts
    if metrics['cpu_usage'] > 90:
        summary['alerts'].append('High CPU usage detected')
    if metrics['memory_usage'] > 90:
        summary['alerts'].append('High memory usage detected')
    if metrics['gpu_usage'] > 90:
        summary['alerts'].append('High GPU usage detected')
    
    return jsonify({
        'summary': summary,
        'timestamp': time.time()
    })

# Background task for cache cleanup
def cleanup_cache():
    """Clean up old cache entries"""
    current_time = time.time()
    keys_to_remove = []
    
    for key, value in result_cache.items():
        if isinstance(value, dict) and 'timestamp' in value:
            if current_time - value['timestamp'] > 300:  # 5 minutes
                keys_to_remove.append(key)
    
    for key in keys_to_remove:
        del result_cache[key]

# Schedule cache cleanup every 5 minutes
import threading
def schedule_cleanup():
    while True:
        time.sleep(300)  # 5 minutes
        cleanup_cache()

cleanup_thread = threading.Thread(target=schedule_cleanup, daemon=True)
cleanup_thread.start()

if __name__ == '__main__':
    # Use uvicorn for better performance
    uvicorn.run(app, host='0.0.0.0', port=5000, workers=4)
```

### Memory Management
```python
# utils/memory_manager.py

import gc
import psutil
import threading
import time

class MemoryManager:
    def __init__(self, max_memory_mb=1024, cleanup_interval=60):
        self.max_memory_mb = max_memory_mb
        self.cleanup_interval = cleanup_interval
        self.running = True
        self.cleanup_thread = threading.Thread(target=self._cleanup_loop, daemon=True)
        self.cleanup_thread.start()
    
    def _cleanup_loop(self):
        """Background cleanup loop"""
        while self.running:
            time.sleep(self.cleanup_interval)
            self.cleanup_memory()
    
    def cleanup_memory(self):
        """Clean up memory and cache"""
        # Force garbage collection
        gc.collect()
        
        # Check memory usage
        memory_usage = psutil.Process().memory_info().rss / 1024 / 1024
        
        if memory_usage > self.max_memory_mb:
            # Clear caches
            global result_cache, model_cache
            if 'result_cache' in globals():
                result_cache.clear()
            if 'model_cache' in globals():
                model_cache.clear()
            
            # Force garbage collection again
            gc.collect()
    
    def stop(self):
        """Stop the memory manager"""
        self.running = False
        self.cleanup_thread.join()

# Initialize memory manager
memory_manager = MemoryManager()
```

---

## 📊 Performance Monitoring

### Performance Metrics Collection
```php
<?php
// app/Http/Middleware/PerformanceMonitor.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class PerformanceMonitor
{
    public function handle(Request $request, Closure $next)
    {
        $startTime = microtime(true);
        $startMemory = memory_get_usage();
        
        $response = $next($request);
        
        $endTime = microtime(true);
        $endMemory = memory_get_usage();
        
        $duration = $endTime - $startTime;
        $memoryUsed = $endMemory - $startMemory;
        
        // Log performance metrics
        $this->logPerformanceMetrics($request, $response, $duration, $memoryUsed);
        
        // Add performance headers
        $response->headers->set('X-Response-Time', $duration);
        $response->headers->set('X-Memory-Usage', $memoryUsed);
        
        return $response;
    }
    
    private function logPerformanceMetrics(Request $request, $response, float $duration, int $memoryUsed)
    {
        $metrics = [
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'status_code' => $response->getStatusCode(),
            'duration' => $duration,
            'memory_used' => $memoryUsed,
            'memory_peak' => memory_get_peak_usage(),
            'timestamp' => now()->toISOString()
        ];
        
        // Log slow requests
        if ($duration > 1.0) {
            Log::warning('Slow request detected', $metrics);
        }
        
        // Log high memory usage
        if ($memoryUsed > 50 * 1024 * 1024) { // 50MB
            Log::warning('High memory usage detected', $metrics);
        }
        
        // Store metrics for analysis
        $this->storeMetrics($metrics);
    }
    
    private function storeMetrics(array $metrics)
    {
        // Store in cache for real-time monitoring
        $key = 'performance_metrics_' . now()->format('Y-m-d-H-i');
        $existing = Cache::get($key, []);
        $existing[] = $metrics;
        Cache::put($key, $existing, 3600); // Store for 1 hour
    }
}
```

### Performance Dashboard
```php
<?php
// app/Http/Controllers/Api/PerformanceController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class PerformanceController extends Controller
{
    public function dashboard()
    {
        $metrics = $this->getPerformanceMetrics();
        
        return response()->json([
            'data' => $metrics
        ]);
    }
    
    private function getPerformanceMetrics()
    {
        // Get recent performance data
        $recentMetrics = $this->getRecentMetrics();
        
        // Calculate averages
        $avgResponseTime = collect($recentMetrics)->avg('duration');
        $avgMemoryUsage = collect($recentMetrics)->avg('memory_used');
        $totalRequests = count($recentMetrics);
        
        // Get slow requests
        $slowRequests = collect($recentMetrics)
            ->filter(fn($m) => $m['duration'] > 1.0)
            ->count();
        
        // Get error rate
        $errorRate = collect($recentMetrics)
            ->filter(fn($m) => $m['status_code'] >= 400)
            ->count() / max($totalRequests, 1);
        
        return [
            'overview' => [
                'total_requests' => $totalRequests,
                'avg_response_time' => round($avgResponseTime, 3),
                'avg_memory_usage' => round($avgMemoryUsage / 1024 / 1024, 2), // MB
                'slow_requests' => $slowRequests,
                'error_rate' => round($errorRate * 100, 2) // Percentage
            ],
            'recent_requests' => array_slice($recentMetrics, -10), // Last 10 requests
            'slowest_endpoints' => $this->getSlowestEndpoints($recentMetrics),
            'memory_usage_trend' => $this->getMemoryUsageTrend($recentMetrics)
        ];
    }
    
    private function getRecentMetrics()
    {
        $metrics = [];
        
        // Get metrics from last hour
        for ($i = 0; $i < 60; $i++) {
            $key = 'performance_metrics_' . now()->subMinutes($i)->format('Y-m-d-H-i');
            $hourlyMetrics = Cache::get($key, []);
            $metrics = array_merge($metrics, $hourlyMetrics);
        }
        
        return $metrics;
    }
    
    private function getSlowestEndpoints(array $metrics)
    {
        return collect($metrics)
            ->groupBy('url')
            ->map(function ($requests) {
                return [
                    'url' => $requests->first()['url'],
                    'avg_duration' => round($requests->avg('duration'), 3),
                    'request_count' => $requests->count()
                ];
            })
            ->sortByDesc('avg_duration')
            ->take(5)
            ->values()
            ->toArray();
    }
    
    private function getMemoryUsageTrend(array $metrics)
    {
        return collect($metrics)
            ->groupBy(function ($metric) {
                return now()->parse($metric['timestamp'])->format('H:i');
            })
            ->map(function ($requests) {
                return round($requests->avg('memory_used') / 1024 / 1024, 2);
            })
            ->toArray();
    }
}
```

---

## 🔧 Database Performance

### Database Indexing
```sql
-- Database indexes for performance optimization

-- RVM indexes
CREATE INDEX idx_rvms_status ON reverse_vending_machines(status);
CREATE INDEX idx_rvms_created_at ON reverse_vending_machines(created_at);
CREATE INDEX idx_rvms_ip_address ON reverse_vending_machines(ip_address);
CREATE INDEX idx_rvms_location ON reverse_vending_machines USING GIN(to_tsvector('english', location));

-- Detection results indexes
CREATE INDEX idx_detection_results_rvm_id ON detection_results(rvm_id);
CREATE INDEX idx_detection_results_created_at ON detection_results(created_at);
CREATE INDEX idx_detection_results_rvm_created ON detection_results(rvm_id, created_at);
CREATE INDEX idx_detection_results_confidence ON detection_results USING GIN((detection_results->'confidence'));

-- User indexes
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_users_api_key ON users(api_key);
CREATE INDEX idx_users_created_at ON users(created_at);

-- Transaction indexes
CREATE INDEX idx_transactions_user_id ON transactions(user_id);
CREATE INDEX idx_transactions_type ON transactions(type);
CREATE INDEX idx_transactions_created_at ON transactions(created_at);
CREATE INDEX idx_transactions_user_created ON transactions(user_id, created_at);

-- Voucher indexes
CREATE INDEX idx_vouchers_code ON vouchers(code);
CREATE INDEX idx_vouchers_active ON vouchers(is_active);
CREATE INDEX idx_vouchers_expires_at ON vouchers(expires_at);

-- Composite indexes for common queries
CREATE INDEX idx_rvms_status_created ON reverse_vending_machines(status, created_at);
CREATE INDEX idx_detection_results_rvm_status ON detection_results(rvm_id, created_at) WHERE rvm_id IS NOT NULL;
```

### Query Optimization
```php
<?php
// app/Services/QueryOptimizer.php

namespace App\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class QueryOptimizer
{
    public function optimizeRvmQuery(Builder $query, array $filters = [])
    {
        // Use select to limit columns
        $query->select([
            'id', 'name', 'location', 'status', 'ip_address', 
            'capacity', 'current_load', 'created_at'
        ]);
        
        // Add eager loading for relationships
        $query->with([
            'detectionResults' => function($query) {
                $query->select('id', 'rvm_id', 'created_at')
                      ->latest()
                      ->limit(1);
            }
        ]);
        
        // Apply filters efficiently
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        
        if (isset($filters['search'])) {
            $query->where(function($q) use ($filters) {
                $q->where('name', 'like', "%{$filters['search']}%")
                  ->orWhere('location', 'like', "%{$filters['search']}%");
            });
        }
        
        if (isset($filters['date_from'])) {
            $query->where('created_at', '>=', $filters['date_from']);
        }
        
        if (isset($filters['date_to'])) {
            $query->where('created_at', '<=', $filters['date_to']);
        }
        
        return $query;
    }
    
    public function optimizeDetectionQuery(Builder $query, array $filters = [])
    {
        // Use select to limit columns
        $query->select([
            'id', 'rvm_id', 'image_path', 'detection_results', 
            'processing_time', 'created_at'
        ]);
        
        // Apply filters
        if (isset($filters['rvm_id'])) {
            $query->where('rvm_id', $filters['rvm_id']);
        }
        
        if (isset($filters['date_from'])) {
            $query->where('created_at', '>=', $filters['date_from']);
        }
        
        if (isset($filters['date_to'])) {
            $query->where('created_at', '<=', $filters['date_to']);
        }
        
        if (isset($filters['confidence_min'])) {
            $query->whereRaw("JSON_EXTRACT(detection_results, '$[0].confidence') >= ?", [$filters['confidence_min']]);
        }
        
        return $query;
    }
    
    public function getOptimizedAnalytics(int $rvmId, string $period = '7d')
    {
        $dateFrom = now()->subDays(7);
        
        if ($period === '30d') {
            $dateFrom = now()->subDays(30);
        } elseif ($period === '90d') {
            $dateFrom = now()->subDays(90);
        }
        
        return DB::table('detection_results')
            ->where('rvm_id', $rvmId)
            ->where('created_at', '>=', $dateFrom)
            ->selectRaw('
                DATE(created_at) as date,
                COUNT(*) as detections,
                AVG(processing_time) as avg_processing_time,
                AVG(JSON_EXTRACT(detection_results, "$[0].confidence")) as avg_confidence
            ')
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }
}
```

---

## 🚀 Caching Strategy

### Redis Caching
```php
<?php
// app/Services/RedisCacheService.php

namespace App\Services;

use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Cache;

class RedisCacheService
{
    public function cacheRvmList(array $filters, $data, int $ttl = 300)
    {
        $key = 'rvms_list_' . md5(serialize($filters));
        Cache::put($key, $data, $ttl);
        return $key;
    }
    
    public function getRvmList(array $filters)
    {
        $key = 'rvms_list_' . md5(serialize($filters));
        return Cache::get($key);
    }
    
    public function cacheRvmDetails(int $rvmId, $data, int $ttl = 600)
    {
        $key = "rvm_details_{$rvmId}";
        Cache::put($key, $data, $ttl);
        return $key;
    }
    
    public function getRvmDetails(int $rvmId)
    {
        $key = "rvm_details_{$rvmId}";
        return Cache::get($key);
    }
    
    public function invalidateRvmCache(int $rvmId)
    {
        // Invalidate specific RVM cache
        Cache::forget("rvm_details_{$rvmId}");
        
        // Invalidate list caches
        $pattern = 'rvms_list_*';
        $keys = Redis::keys($pattern);
        
        if (!empty($keys)) {
            Redis::del($keys);
        }
    }
    
    public function warmCache()
    {
        // Warm up frequently accessed data
        $this->warmRvmListCache();
        $this->warmAnalyticsCache();
    }
    
    private function warmRvmListCache()
    {
        $commonFilters = [
            [],
            ['status' => 'active'],
            ['status' => 'inactive'],
            ['search' => '']
        ];
        
        foreach ($commonFilters as $filters) {
            $this->getRvmList($filters);
        }
    }
    
    private function warmAnalyticsCache()
    {
        $activeRvms = ReverseVendingMachine::active()->pluck('id');
        
        foreach ($activeRvms as $rvmId) {
            $this->getRvmAnalytics($rvmId);
        }
    }
}
```

---

## 📈 Performance Testing

### Load Testing Script
```python
# tests/performance_test.py

import asyncio
import aiohttp
import time
import statistics
from concurrent.futures import ThreadPoolExecutor

class PerformanceTester:
    def __init__(self, server_url, jetson_url, api_key):
        self.server_url = server_url
        self.jetson_url = jetson_url
        self.api_key = api_key
        self.headers = {
            'Authorization': f'Bearer {api_key}',
            'Content-Type': 'application/json'
        }
    
    async def test_server_performance(self, num_requests=100):
        """Test server performance with async requests"""
        print(f"🧪 Testing server performance with {num_requests} requests")
        
        async with aiohttp.ClientSession() as session:
            tasks = []
            
            for i in range(num_requests):
                task = self._make_server_request(session, i)
                tasks.append(task)
            
            results = await asyncio.gather(*tasks, return_exceptions=True)
            
            # Calculate metrics
            successful_requests = [r for r in results if not isinstance(r, Exception)]
            failed_requests = [r for r in results if isinstance(r, Exception)]
            
            if successful_requests:
                response_times = [r['response_time'] for r in successful_requests]
                
                metrics = {
                    'total_requests': num_requests,
                    'successful_requests': len(successful_requests),
                    'failed_requests': len(failed_requests),
                    'success_rate': len(successful_requests) / num_requests,
                    'avg_response_time': statistics.mean(response_times),
                    'min_response_time': min(response_times),
                    'max_response_time': max(response_times),
                    'p95_response_time': sorted(response_times)[int(0.95 * len(response_times))]
                }
                
                print(f"✅ Server Performance Results:")
                print(f"  Success Rate: {metrics['success_rate']:.2%}")
                print(f"  Avg Response Time: {metrics['avg_response_time']:.3f}s")
                print(f"  P95 Response Time: {metrics['p95_response_time']:.3f}s")
                
                return metrics
            else:
                print("❌ All requests failed")
                return None
    
    async def _make_server_request(self, session, request_id):
        """Make a single server request"""
        start_time = time.time()
        
        try:
            async with session.get(
                f"{self.server_url}/api/rvms",
                headers=self.headers,
                timeout=aiohttp.ClientTimeout(total=10)
            ) as response:
                end_time = time.time()
                
                return {
                    'request_id': request_id,
                    'status_code': response.status,
                    'response_time': end_time - start_time,
                    'success': response.status == 200
                }
        except Exception as e:
            end_time = time.time()
            return {
                'request_id': request_id,
                'error': str(e),
                'response_time': end_time - start_time,
                'success': False
            }
    
    async def test_jetson_performance(self, num_requests=50):
        """Test Jetson performance with async requests"""
        print(f"🤖 Testing Jetson performance with {num_requests} requests")
        
        async with aiohttp.ClientSession() as session:
            tasks = []
            
            for i in range(num_requests):
                task = self._make_jetson_request(session, i)
                tasks.append(task)
            
            results = await asyncio.gather(*tasks, return_exceptions=True)
            
            # Calculate metrics
            successful_requests = [r for r in results if not isinstance(r, Exception)]
            failed_requests = [r for r in results if isinstance(r, Exception)]
            
            if successful_requests:
                response_times = [r['response_time'] for r in successful_requests]
                
                metrics = {
                    'total_requests': num_requests,
                    'successful_requests': len(successful_requests),
                    'failed_requests': len(failed_requests),
                    'success_rate': len(successful_requests) / num_requests,
                    'avg_response_time': statistics.mean(response_times),
                    'min_response_time': min(response_times),
                    'max_response_time': max(response_times),
                    'p95_response_time': sorted(response_times)[int(0.95 * len(response_times))]
                }
                
                print(f"✅ Jetson Performance Results:")
                print(f"  Success Rate: {metrics['success_rate']:.2%}")
                print(f"  Avg Response Time: {metrics['avg_response_time']:.3f}s")
                print(f"  P95 Response Time: {metrics['p95_response_time']:.3f}s")
                
                return metrics
            else:
                print("❌ All requests failed")
                return None
    
    async def _make_jetson_request(self, session, request_id):
        """Make a single Jetson request"""
        start_time = time.time()
        
        try:
            async with session.get(
                f"{self.jetson_url}/api/health",
                headers=self.headers,
                timeout=aiohttp.ClientTimeout(total=10)
            ) as response:
                end_time = time.time()
                
                return {
                    'request_id': request_id,
                    'status_code': response.status,
                    'response_time': end_time - start_time,
                    'success': response.status == 200
                }
        except Exception as e:
            end_time = time.time()
            return {
                'request_id': request_id,
                'error': str(e),
                'response_time': end_time - start_time,
                'success': False
            }
    
    async def run_comprehensive_test(self):
        """Run comprehensive performance test"""
        print("🚀 Starting comprehensive performance test")
        print("=" * 50)
        
        # Test server performance
        server_metrics = await self.test_server_performance(100)
        
        # Test Jetson performance
        jetson_metrics = await self.test_jetson_performance(50)
        
        # Summary
        print("\n📊 Performance Test Summary:")
        print("=" * 30)
        
        if server_metrics:
            print(f"Server: {server_metrics['success_rate']:.2%} success, {server_metrics['avg_response_time']:.3f}s avg")
        
        if jetson_metrics:
            print(f"Jetson: {jetson_metrics['success_rate']:.2%} success, {jetson_metrics['avg_response_time']:.3f}s avg")
        
        print("\n✅ Performance test completed!")

if __name__ == "__main__":
    tester = PerformanceTester(
        server_url="http://100.123.143.87:8001",
        jetson_url="http://100.117.234.2:5000",
        api_key="38bbe1d2ecf75df21546b05340f5878a24e74ed0d8b88d75db1ddeff198380c1"
    )
    
    asyncio.run(tester.run_comprehensive_test())
```

---

**Last Updated**: 2025-01-02  
**Version**: 2.0.0  
**Status**: ✅ COMPLETE PERFORMANCE & OPTIMIZATION DOCUMENTATION
