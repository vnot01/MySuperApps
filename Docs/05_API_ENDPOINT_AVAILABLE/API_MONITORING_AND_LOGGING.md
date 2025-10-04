# 📊 API Monitoring & Logging - MyRVM-Ecosystem v2.0

## 📍 Monitoring Overview

### Monitoring Stack
- **Application Monitoring**: Laravel Telescope, Custom metrics
- **Infrastructure Monitoring**: Docker stats, System metrics
- **Database Monitoring**: PostgreSQL performance metrics
- **Cache Monitoring**: Redis performance metrics
- **Network Monitoring**: API response times, error rates
- **Logging**: Structured logging with ELK stack

---

## 📊 Application Monitoring

### Laravel Telescope Integration
```php
<?php
// config/telescope.php

return [
    'enabled' => env('TELESCOPE_ENABLED', true),
    'domain' => env('TELESCOPE_DOMAIN'),
    'path' => env('TELESCOPE_PATH', 'telescope'),
    'driver' => env('TELESCOPE_DRIVER', 'database'),
    'storage' => [
        'database' => [
            'connection' => env('DB_CONNECTION', 'mysql'),
            'chunk' => 1000,
        ],
    ],
    'watchers' => [
        Watchers\CacheWatcher::class => env('TELESCOPE_CACHE_WATCHER', true),
        Watchers\CommandWatcher::class => env('TELESCOPE_COMMAND_WATCHER', true),
        Watchers\ExceptionWatcher::class => env('TELESCOPE_EXCEPTION_WATCHER', true),
        Watchers\JobWatcher::class => env('TELESCOPE_JOB_WATCHER', true),
        Watchers\LogWatcher::class => env('TELESCOPE_LOG_WATCHER', true),
        Watchers\MailWatcher::class => env('TELESCOPE_MAIL_WATCHER', true),
        Watchers\ModelWatcher::class => env('TELESCOPE_MODEL_WATCHER', true),
        Watchers\NotificationWatcher::class => env('TELESCOPE_NOTIFICATION_WATCHER', true),
        Watchers\QueryWatcher::class => env('TELESCOPE_QUERY_WATCHER', true),
        Watchers\RedisWatcher::class => env('TELESCOPE_REDIS_WATCHER', true),
        Watchers\RequestWatcher::class => env('TELESCOPE_REQUEST_WATCHER', true),
        Watchers\GateWatcher::class => env('TELESCOPE_GATE_WATCHER', true),
        Watchers\ScheduleWatcher::class => env('TELESCOPE_SCHEDULE_WATCHER', true),
        Watchers\ViewWatcher::class => env('TELESCOPE_VIEW_WATCHER', true),
    ],
];
```

### Custom Metrics Collection
```php
<?php
// app/Services/MetricsService.php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use App\Models\ReverseVendingMachine;
use App\Models\DetectionResult;
use App\Models\User;

class MetricsService
{
    public function collectApplicationMetrics(): array
    {
        return [
            'requests' => $this->getRequestMetrics(),
            'database' => $this->getDatabaseMetrics(),
            'cache' => $this->getCacheMetrics(),
            'queue' => $this->getQueueMetrics(),
            'memory' => $this->getMemoryMetrics(),
            'business' => $this->getBusinessMetrics()
        ];
    }
    
    private function getRequestMetrics(): array
    {
        $cacheKey = 'request_metrics_1h';
        $metrics = Cache::get($cacheKey, [
            'total_requests' => 0,
            'successful_requests' => 0,
            'failed_requests' => 0,
            'avg_response_time' => 0,
            'p95_response_time' => 0
        ]);
        
        return [
            'total_requests' => $metrics['total_requests'],
            'successful_requests' => $metrics['successful_requests'],
            'failed_requests' => $metrics['failed_requests'],
            'success_rate' => $metrics['total_requests'] > 0 
                ? ($metrics['successful_requests'] / $metrics['total_requests']) * 100 
                : 0,
            'avg_response_time' => $metrics['avg_response_time'],
            'p95_response_time' => $metrics['p95_response_time']
        ];
    }
    
    private function getDatabaseMetrics(): array
    {
        try {
            $connection = DB::connection();
            $pdo = $connection->getPdo();
            
            // Get connection info
            $connectionInfo = $connection->select('SELECT version() as version')[0];
            
            // Get database size
            $dbSize = $connection->select("
                SELECT pg_size_pretty(pg_database_size(current_database())) as size
            ")[0];
            
            // Get active connections
            $activeConnections = $connection->select("
                SELECT count(*) as count FROM pg_stat_activity WHERE state = 'active'
            ")[0];
            
            // Get slow queries
            $slowQueries = $connection->select("
                SELECT query, mean_time, calls, total_time
                FROM pg_stat_statements 
                WHERE mean_time > 1000
                ORDER BY mean_time DESC 
                LIMIT 5
            ");
            
            return [
                'version' => $connectionInfo->version,
                'size' => $dbSize->size,
                'active_connections' => $activeConnections->count,
                'slow_queries' => $slowQueries,
                'connection_status' => 'healthy'
            ];
            
        } catch (\Exception $e) {
            return [
                'connection_status' => 'error',
                'error' => $e->getMessage()
            ];
        }
    }
    
    private function getCacheMetrics(): array
    {
        try {
            $redis = Redis::connection();
            $info = $redis->info();
            
            return [
                'status' => 'healthy',
                'used_memory' => $info['used_memory_human'],
                'used_memory_peak' => $info['used_memory_peak_human'],
                'connected_clients' => $info['connected_clients'],
                'total_commands_processed' => $info['total_commands_processed'],
                'keyspace_hits' => $info['keyspace_hits'],
                'keyspace_misses' => $info['keyspace_misses'],
                'hit_rate' => $info['keyspace_hits'] > 0 
                    ? ($info['keyspace_hits'] / ($info['keyspace_hits'] + $info['keyspace_misses'])) * 100 
                    : 0
            ];
            
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'error' => $e->getMessage()
            ];
        }
    }
    
    private function getQueueMetrics(): array
    {
        try {
            $redis = Redis::connection();
            
            $queues = [
                'default' => $redis->llen('queues:default'),
                'high' => $redis->llen('queues:high'),
                'low' => $redis->llen('queues:low')
            ];
            
            $totalJobs = array_sum($queues);
            
            return [
                'total_jobs' => $totalJobs,
                'queues' => $queues,
                'status' => $totalJobs > 1000 ? 'warning' : 'healthy'
            ];
            
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'error' => $e->getMessage()
            ];
        }
    }
    
    private function getMemoryMetrics(): array
    {
        $memoryUsage = memory_get_usage(true);
        $memoryPeak = memory_get_peak_usage(true);
        $memoryLimit = ini_get('memory_limit');
        
        return [
            'current_usage' => $this->formatBytes($memoryUsage),
            'peak_usage' => $this->formatBytes($memoryPeak),
            'limit' => $memoryLimit,
            'usage_percentage' => $this->getMemoryUsagePercentage($memoryUsage, $memoryLimit)
        ];
    }
    
    private function getBusinessMetrics(): array
    {
        return [
            'total_rvms' => ReverseVendingMachine::count(),
            'active_rvms' => ReverseVendingMachine::where('status', 'active')->count(),
            'total_detections' => DetectionResult::count(),
            'today_detections' => DetectionResult::whereDate('created_at', today())->count(),
            'total_users' => User::count(),
            'active_users' => User::where('last_login_at', '>=', now()->subDays(30))->count()
        ];
    }
    
    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= pow(1024, $pow);
        
        return round($bytes, 2) . ' ' . $units[$pow];
    }
    
    private function getMemoryUsagePercentage(int $usage, string $limit): float
    {
        $limitBytes = $this->parseMemoryLimit($limit);
        return $limitBytes > 0 ? ($usage / $limitBytes) * 100 : 0;
    }
    
    private function parseMemoryLimit(string $limit): int
    {
        $limit = trim($limit);
        $last = strtolower($limit[strlen($limit) - 1]);
        $limit = (int) $limit;
        
        switch ($last) {
            case 'g':
                $limit *= 1024;
            case 'm':
                $limit *= 1024;
            case 'k':
                $limit *= 1024;
        }
        
        return $limit;
    }
}
```

### Performance Monitoring Middleware
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
        
        // Update metrics cache
        $this->updateMetricsCache($duration, $response->getStatusCode());
        
        // Add performance headers
        $response->headers->set('X-Response-Time', $duration);
        $response->headers->set('X-Memory-Usage', $memoryUsed);
        
        return $response;
    }
    
    private function logPerformanceMetrics(Request $request, $response, float $duration, int $memoryUsed): void
    {
        $metrics = [
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'status_code' => $response->getStatusCode(),
            'duration' => $duration,
            'memory_used' => $memoryUsed,
            'memory_peak' => memory_get_peak_usage(),
            'user_agent' => $request->userAgent(),
            'ip' => $request->ip(),
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
        
        // Log all requests for analysis
        Log::info('Request processed', $metrics);
    }
    
    private function updateMetricsCache(float $duration, int $statusCode): void
    {
        $cacheKey = 'request_metrics_1h';
        $metrics = Cache::get($cacheKey, [
            'total_requests' => 0,
            'successful_requests' => 0,
            'failed_requests' => 0,
            'response_times' => []
        ]);
        
        $metrics['total_requests']++;
        
        if ($statusCode >= 200 && $statusCode < 400) {
            $metrics['successful_requests']++;
        } else {
            $metrics['failed_requests']++;
        }
        
        $metrics['response_times'][] = $duration;
        
        // Keep only last 1000 response times
        if (count($metrics['response_times']) > 1000) {
            $metrics['response_times'] = array_slice($metrics['response_times'], -1000);
        }
        
        // Calculate averages
        $metrics['avg_response_time'] = array_sum($metrics['response_times']) / count($metrics['response_times']);
        $sortedTimes = $metrics['response_times'];
        sort($sortedTimes);
        $metrics['p95_response_time'] = $sortedTimes[int(0.95 * count($sortedTimes))];
        
        Cache::put($cacheKey, $metrics, 3600); // 1 hour
    }
}
```

---

## 📊 Infrastructure Monitoring

### System Metrics Collection
```php
<?php
// app/Console/Commands/CollectSystemMetrics.php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CollectSystemMetrics extends Command
{
    protected $signature = 'metrics:collect-system';
    protected $description = 'Collect system metrics for monitoring';
    
    public function handle()
    {
        $metrics = [
            'cpu' => $this->getCpuMetrics(),
            'memory' => $this->getMemoryMetrics(),
            'disk' => $this->getDiskMetrics(),
            'network' => $this->getNetworkMetrics(),
            'docker' => $this->getDockerMetrics(),
            'timestamp' => now()->toISOString()
        ];
        
        // Store metrics in cache
        Cache::put('system_metrics', $metrics, 300); // 5 minutes
        
        // Log metrics
        Log::info('System metrics collected', $metrics);
        
        $this->info('System metrics collected successfully');
    }
    
    private function getCpuMetrics(): array
    {
        $loadAvg = sys_getloadavg();
        
        return [
            'load_1min' => $loadAvg[0],
            'load_5min' => $loadAvg[1],
            'load_15min' => $loadAvg[2],
            'usage_percentage' => $this->getCpuUsagePercentage()
        ];
    }
    
    private function getMemoryMetrics(): array
    {
        $memory = [
            'total' => $this->getMemoryTotal(),
            'free' => $this->getMemoryFree(),
            'used' => $this->getMemoryUsed(),
            'cached' => $this->getMemoryCached(),
            'buffers' => $this->getMemoryBuffers()
        ];
        
        $memory['usage_percentage'] = ($memory['used'] / $memory['total']) * 100;
        
        return $memory;
    }
    
    private function getDiskMetrics(): array
    {
        $diskTotal = disk_total_space('/');
        $diskFree = disk_free_space('/');
        $diskUsed = $diskTotal - $diskFree;
        
        return [
            'total' => $this->formatBytes($diskTotal),
            'free' => $this->formatBytes($diskFree),
            'used' => $this->formatBytes($diskUsed),
            'usage_percentage' => ($diskUsed / $diskTotal) * 100
        ];
    }
    
    private function getNetworkMetrics(): array
    {
        // This would require more complex implementation
        // For now, return basic info
        return [
            'interfaces' => $this->getNetworkInterfaces(),
            'connections' => $this->getActiveConnections()
        ];
    }
    
    private function getDockerMetrics(): array
    {
        try {
            $containers = $this->getDockerContainers();
            $images = $this->getDockerImages();
            
            return [
                'containers' => $containers,
                'images' => $images,
                'status' => 'healthy'
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'error' => $e->getMessage()
            ];
        }
    }
    
    private function getCpuUsagePercentage(): float
    {
        // This would require more complex implementation
        // For now, return a placeholder
        return 0.0;
    }
    
    private function getMemoryTotal(): int
    {
        $meminfo = file_get_contents('/proc/meminfo');
        preg_match('/MemTotal:\s+(\d+)\s+kB/', $meminfo, $matches);
        return isset($matches[1]) ? (int)$matches[1] * 1024 : 0;
    }
    
    private function getMemoryFree(): int
    {
        $meminfo = file_get_contents('/proc/meminfo');
        preg_match('/MemFree:\s+(\d+)\s+kB/', $meminfo, $matches);
        return isset($matches[1]) ? (int)$matches[1] * 1024 : 0;
    }
    
    private function getMemoryUsed(): int
    {
        return $this->getMemoryTotal() - $this->getMemoryFree();
    }
    
    private function getMemoryCached(): int
    {
        $meminfo = file_get_contents('/proc/meminfo');
        preg_match('/Cached:\s+(\d+)\s+kB/', $meminfo, $matches);
        return isset($matches[1]) ? (int)$matches[1] * 1024 : 0;
    }
    
    private function getMemoryBuffers(): int
    {
        $meminfo = file_get_contents('/proc/meminfo');
        preg_match('/Buffers:\s+(\d+)\s+kB/', $meminfo, $matches);
        return isset($matches[1]) ? (int)$matches[1] * 1024 : 0;
    }
    
    private function getNetworkInterfaces(): array
    {
        // This would require more complex implementation
        return [];
    }
    
    private function getActiveConnections(): int
    {
        // This would require more complex implementation
        return 0;
    }
    
    private function getDockerContainers(): array
    {
        // This would require Docker API integration
        return [];
    }
    
    private function getDockerImages(): array
    {
        // This would require Docker API integration
        return [];
    }
    
    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= pow(1024, $pow);
        
        return round($bytes, 2) . ' ' . $units[$pow];
    }
}
```

---

## 📝 Structured Logging

### Log Configuration
```php
<?php
// config/logging.php

return [
    'default' => env('LOG_CHANNEL', 'stack'),
    'channels' => [
        'stack' => [
            'driver' => 'stack',
            'channels' => ['single', 'slack'],
            'ignore_exceptions' => false,
        ],
        'single' => [
            'driver' => 'single',
            'path' => storage_path('logs/laravel.log'),
            'level' => env('LOG_LEVEL', 'debug'),
        ],
        'daily' => [
            'driver' => 'daily',
            'path' => storage_path('logs/laravel.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 14,
        ],
        'slack' => [
            'driver' => 'slack',
            'url' => env('LOG_SLACK_WEBHOOK_URL'),
            'username' => 'Laravel Log',
            'emoji' => ':boom:',
            'level' => env('LOG_LEVEL', 'critical'),
        ],
        'papertrail' => [
            'driver' => 'monolog',
            'level' => env('LOG_LEVEL', 'debug'),
            'handler' => SyslogUdpHandler::class,
            'handler_with' => [
                'host' => env('PAPERTRAIL_URL'),
                'port' => env('PAPERTRAIL_PORT'),
            ],
        ],
        'stderr' => [
            'driver' => 'monolog',
            'level' => env('LOG_LEVEL', 'debug'),
            'handler' => StreamHandler::class,
            'formatter' => env('LOG_STDERR_FORMATTER'),
            'with' => [
                'stream' => 'php://stderr',
            ],
        ],
        'syslog' => [
            'driver' => 'syslog',
            'level' => env('LOG_LEVEL', 'debug'),
        ],
        'errorlog' => [
            'driver' => 'errorlog',
            'level' => env('LOG_LEVEL', 'debug'),
        ],
        'null' => [
            'driver' => 'monolog',
            'handler' => NullHandler::class,
        ],
        'emergency' => [
            'path' => storage_path('logs/laravel.log'),
        ],
    ],
];
```

### Custom Log Channels
```php
<?php
// app/Logging/CustomLogger.php

namespace App\Logging;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Formatter\JsonFormatter;

class CustomLogger
{
    public function __invoke(array $config)
    {
        $logger = new Logger('custom');
        
        $handler = new StreamHandler(
            storage_path('logs/custom.log'),
            $config['level'] ?? Logger::DEBUG
        );
        
        $handler->setFormatter(new JsonFormatter());
        $logger->pushHandler($handler);
        
        return $logger;
    }
}
```

### API Request Logging
```php
<?php
// app/Http/Middleware/ApiRequestLogger.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ApiRequestLogger
{
    public function handle(Request $request, Closure $next)
    {
        $startTime = microtime(true);
        
        $response = $next($request);
        
        $endTime = microtime(true);
        $duration = $endTime - $startTime;
        
        $this->logApiRequest($request, $response, $duration);
        
        return $response;
    }
    
    private function logApiRequest(Request $request, $response, float $duration): void
    {
        $logData = [
            'type' => 'api_request',
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'status_code' => $response->getStatusCode(),
            'duration' => $duration,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'user_id' => $request->user()?->id,
            'api_key' => $this->maskApiKey($request->header('Authorization')),
            'request_size' => strlen($request->getContent()),
            'response_size' => strlen($response->getContent()),
            'timestamp' => now()->toISOString()
        ];
        
        Log::channel('api')->info('API request processed', $logData);
    }
    
    private function maskApiKey(?string $authHeader): ?string
    {
        if (!$authHeader) {
            return null;
        }
        
        if (strpos($authHeader, 'Bearer ') === 0) {
            $key = substr($authHeader, 7);
            return 'Bearer ' . substr($key, 0, 8) . '...' . substr($key, -4);
        }
        
        return $authHeader;
    }
}
```

---

## 📊 Monitoring Dashboard

### Real-time Metrics API
```php
<?php
// app/Http/Controllers/Api/MonitoringController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\MetricsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class MonitoringController extends Controller
{
    protected $metricsService;
    
    public function __construct(MetricsService $metricsService)
    {
        $this->metricsService = $metricsService;
    }
    
    public function dashboard()
    {
        $metrics = $this->metricsService->collectApplicationMetrics();
        $systemMetrics = Cache::get('system_metrics', []);
        
        return response()->json([
            'data' => [
                'application' => $metrics,
                'system' => $systemMetrics,
                'timestamp' => now()->toISOString()
            ]
        ]);
    }
    
    public function health()
    {
        $health = [
            'status' => 'healthy',
            'checks' => [
                'database' => $this->checkDatabase(),
                'cache' => $this->checkCache(),
                'queue' => $this->checkQueue(),
                'storage' => $this->checkStorage()
            ],
            'timestamp' => now()->toISOString()
        ];
        
        // Determine overall health
        $failedChecks = array_filter($health['checks'], fn($check) => $check['status'] !== 'healthy');
        if (count($failedChecks) > 0) {
            $health['status'] = 'unhealthy';
        }
        
        return response()->json($health);
    }
    
    public function metrics()
    {
        $metrics = $this->metricsService->collectApplicationMetrics();
        
        return response()->json([
            'data' => $metrics
        ]);
    }
    
    public function alerts()
    {
        $alerts = Cache::get('system_alerts', []);
        
        return response()->json([
            'data' => $alerts
        ]);
    }
    
    private function checkDatabase(): array
    {
        try {
            \DB::connection()->getPdo();
            return ['status' => 'healthy', 'message' => 'Database connection successful'];
        } catch (\Exception $e) {
            return ['status' => 'unhealthy', 'message' => $e->getMessage()];
        }
    }
    
    private function checkCache(): array
    {
        try {
            \Cache::put('health_check', 'ok', 60);
            $value = \Cache::get('health_check');
            
            if ($value === 'ok') {
                return ['status' => 'healthy', 'message' => 'Cache is working'];
            } else {
                return ['status' => 'unhealthy', 'message' => 'Cache read/write failed'];
            }
        } catch (\Exception $e) {
            return ['status' => 'unhealthy', 'message' => $e->getMessage()];
        }
    }
    
    private function checkQueue(): array
    {
        try {
            \Queue::push(new \App\Jobs\HealthCheckJob());
            return ['status' => 'healthy', 'message' => 'Queue is working'];
        } catch (\Exception $e) {
            return ['status' => 'unhealthy', 'message' => $e->getMessage()];
        }
    }
    
    private function checkStorage(): array
    {
        try {
            $testFile = storage_path('app/health_check.txt');
            file_put_contents($testFile, 'ok');
            $content = file_get_contents($testFile);
            unlink($testFile);
            
            if ($content === 'ok') {
                return ['status' => 'healthy', 'message' => 'Storage is working'];
            } else {
                return ['status' => 'unhealthy', 'message' => 'Storage read/write failed'];
            }
        } catch (\Exception $e) {
            return ['status' => 'unhealthy', 'message' => $e->getMessage()];
        }
    }
}
```

### Frontend Monitoring Dashboard
```vue
<!-- resources/js/Pages/Monitoring/Dashboard.vue -->
<template>
  <div class="monitoring-dashboard">
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
      <!-- System Health Card -->
      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <div :class="healthStatusClass" class="w-8 h-8 rounded-full flex items-center justify-center">
              <i :class="healthIconClass"></i>
            </div>
          </div>
          <div class="ml-4">
            <h3 class="text-lg font-medium text-gray-900">System Health</h3>
            <p class="text-sm text-gray-500">{{ healthStatus }}</p>
          </div>
        </div>
      </div>
      
      <!-- Request Rate Card -->
      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <i class="fas fa-chart-line text-blue-500 text-2xl"></i>
          </div>
          <div class="ml-4">
            <h3 class="text-lg font-medium text-gray-900">Request Rate</h3>
            <p class="text-sm text-gray-500">{{ metrics.requests.total_requests }}/min</p>
          </div>
        </div>
      </div>
      
      <!-- Response Time Card -->
      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <i class="fas fa-clock text-green-500 text-2xl"></i>
          </div>
          <div class="ml-4">
            <h3 class="text-lg font-medium text-gray-900">Avg Response Time</h3>
            <p class="text-sm text-gray-500">{{ metrics.requests.avg_response_time }}ms</p>
          </div>
        </div>
      </div>
      
      <!-- Error Rate Card -->
      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <i class="fas fa-exclamation-triangle text-red-500 text-2xl"></i>
          </div>
          <div class="ml-4">
            <h3 class="text-lg font-medium text-gray-900">Error Rate</h3>
            <p class="text-sm text-gray-500">{{ metrics.requests.success_rate }}%</p>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Metrics Charts -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <!-- Response Time Chart -->
      <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Response Time Trend</h3>
        <div ref="responseTimeChart" class="h-64"></div>
      </div>
      
      <!-- Request Volume Chart -->
      <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Request Volume</h3>
        <div ref="requestVolumeChart" class="h-64"></div>
      </div>
    </div>
    
    <!-- Alerts Section -->
    <div class="mt-8">
      <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200">
          <h3 class="text-lg font-medium text-gray-900">Recent Alerts</h3>
        </div>
        <div class="p-6">
          <div v-if="alerts.length === 0" class="text-center text-gray-500">
            No alerts
          </div>
          <div v-else class="space-y-4">
            <div v-for="alert in alerts" :key="alert.id" 
                 :class="alertClass(alert.severity)"
                 class="p-4 rounded-lg">
              <div class="flex items-center">
                <i :class="alertIconClass(alert.severity)" class="mr-3"></i>
                <div>
                  <p class="font-medium">{{ alert.message }}</p>
                  <p class="text-sm text-gray-500">{{ formatTime(alert.timestamp) }}</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { Chart } from 'chart.js'

const metrics = ref({})
const healthStatus = ref('Checking...')
const alerts = ref([])
const refreshInterval = ref(null)

const healthStatusClass = computed(() => {
  switch (healthStatus.value) {
    case 'healthy': return 'bg-green-100'
    case 'warning': return 'bg-yellow-100'
    case 'unhealthy': return 'bg-red-100'
    default: return 'bg-gray-100'
  }
})

const healthIconClass = computed(() => {
  switch (healthStatus.value) {
    case 'healthy': return 'fas fa-check text-green-500'
    case 'warning': return 'fas fa-exclamation-triangle text-yellow-500'
    case 'unhealthy': return 'fas fa-times text-red-500'
    default: return 'fas fa-question text-gray-500'
  }
})

const alertClass = (severity) => {
  switch (severity) {
    case 'high': return 'bg-red-50 border border-red-200'
    case 'medium': return 'bg-yellow-50 border border-yellow-200'
    case 'low': return 'bg-blue-50 border border-blue-200'
    default: return 'bg-gray-50 border border-gray-200'
  }
}

const alertIconClass = (severity) => {
  switch (severity) {
    case 'high': return 'fas fa-exclamation-circle text-red-500'
    case 'medium': return 'fas fa-exclamation-triangle text-yellow-500'
    case 'low': return 'fas fa-info-circle text-blue-500'
    default: return 'fas fa-question-circle text-gray-500'
  }
}

const fetchMetrics = async () => {
  try {
    const response = await fetch('/api/monitoring/dashboard')
    const data = await response.json()
    
    metrics.value = data.data.application
    healthStatus.value = data.data.system.status || 'healthy'
  } catch (error) {
    console.error('Failed to fetch metrics:', error)
  }
}

const fetchAlerts = async () => {
  try {
    const response = await fetch('/api/monitoring/alerts')
    const data = await response.json()
    
    alerts.value = data.data
  } catch (error) {
    console.error('Failed to fetch alerts:', error)
  }
}

const formatTime = (timestamp) => {
  return new Date(timestamp).toLocaleString()
}

onMounted(() => {
  fetchMetrics()
  fetchAlerts()
  
  // Refresh every 30 seconds
  refreshInterval.value = setInterval(() => {
    fetchMetrics()
    fetchAlerts()
  }, 30000)
})

onUnmounted(() => {
  if (refreshInterval.value) {
    clearInterval(refreshInterval.value)
  }
})
</script>
```

---

## 🚨 Alerting System

### Alert Configuration
```php
<?php
// app/Services/AlertService.php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\SystemAlert;

class AlertService
{
    public function checkThresholds(array $metrics): void
    {
        $this->checkResponseTime($metrics);
        $this->checkErrorRate($metrics);
        $this->checkMemoryUsage($metrics);
        $this->checkDiskUsage($metrics);
        $this->checkQueueSize($metrics);
    }
    
    private function checkResponseTime(array $metrics): void
    {
        $avgResponseTime = $metrics['requests']['avg_response_time'] ?? 0;
        
        if ($avgResponseTime > 2000) { // 2 seconds
            $this->sendAlert('high', 'High response time detected', [
                'metric' => 'response_time',
                'value' => $avgResponseTime,
                'threshold' => 2000
            ]);
        } elseif ($avgResponseTime > 1000) { // 1 second
            $this->sendAlert('medium', 'Elevated response time detected', [
                'metric' => 'response_time',
                'value' => $avgResponseTime,
                'threshold' => 1000
            ]);
        }
    }
    
    private function checkErrorRate(array $metrics): void
    {
        $successRate = $metrics['requests']['success_rate'] ?? 100;
        $errorRate = 100 - $successRate;
        
        if ($errorRate > 10) { // 10% error rate
            $this->sendAlert('high', 'High error rate detected', [
                'metric' => 'error_rate',
                'value' => $errorRate,
                'threshold' => 10
            ]);
        } elseif ($errorRate > 5) { // 5% error rate
            $this->sendAlert('medium', 'Elevated error rate detected', [
                'metric' => 'error_rate',
                'value' => $errorRate,
                'threshold' => 5
            ]);
        }
    }
    
    private function checkMemoryUsage(array $metrics): void
    {
        $memoryUsage = $metrics['memory']['usage_percentage'] ?? 0;
        
        if ($memoryUsage > 90) { // 90% memory usage
            $this->sendAlert('high', 'High memory usage detected', [
                'metric' => 'memory_usage',
                'value' => $memoryUsage,
                'threshold' => 90
            ]);
        } elseif ($memoryUsage > 80) { // 80% memory usage
            $this->sendAlert('medium', 'Elevated memory usage detected', [
                'metric' => 'memory_usage',
                'value' => $memoryUsage,
                'threshold' => 80
            ]);
        }
    }
    
    private function checkDiskUsage(array $metrics): void
    {
        $diskUsage = $metrics['disk']['usage_percentage'] ?? 0;
        
        if ($diskUsage > 90) { // 90% disk usage
            $this->sendAlert('high', 'High disk usage detected', [
                'metric' => 'disk_usage',
                'value' => $diskUsage,
                'threshold' => 90
            ]);
        } elseif ($diskUsage > 80) { // 80% disk usage
            $this->sendAlert('medium', 'Elevated disk usage detected', [
                'metric' => 'disk_usage',
                'value' => $diskUsage,
                'threshold' => 80
            ]);
        }
    }
    
    private function checkQueueSize(array $metrics): void
    {
        $queueSize = $metrics['queue']['total_jobs'] ?? 0;
        
        if ($queueSize > 1000) { // 1000 jobs
            $this->sendAlert('high', 'Large queue size detected', [
                'metric' => 'queue_size',
                'value' => $queueSize,
                'threshold' => 1000
            ]);
        } elseif ($queueSize > 500) { // 500 jobs
            $this->sendAlert('medium', 'Elevated queue size detected', [
                'metric' => 'queue_size',
                'value' => $queueSize,
                'threshold' => 500
            ]);
        }
    }
    
    private function sendAlert(string $severity, string $message, array $data): void
    {
        $alert = [
            'id' => uniqid(),
            'severity' => $severity,
            'message' => $message,
            'data' => $data,
            'timestamp' => now()->toISOString()
        ];
        
        // Log alert
        Log::warning('System alert triggered', $alert);
        
        // Store alert
        $this->storeAlert($alert);
        
        // Send notification for high severity alerts
        if ($severity === 'high') {
            $this->sendNotification($alert);
        }
    }
    
    private function storeAlert(array $alert): void
    {
        $alerts = cache('system_alerts', []);
        $alerts[] = $alert;
        
        // Keep only last 100 alerts
        if (count($alerts) > 100) {
            $alerts = array_slice($alerts, -100);
        }
        
        cache(['system_alerts' => $alerts], 3600);
    }
    
    private function sendNotification(array $alert): void
    {
        try {
            Mail::to('admin@myrvm.com')->send(new SystemAlert($alert));
        } catch (\Exception $e) {
            Log::error('Failed to send alert notification', [
                'alert' => $alert,
                'error' => $e->getMessage()
            ]);
        }
    }
}
```

---

**Last Updated**: 2025-01-02  
**Version**: 2.0.0  
**Status**: ✅ COMPLETE MONITORING & LOGGING DOCUMENTATION
