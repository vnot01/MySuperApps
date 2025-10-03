# Jetson Service Integration - Multi-Jetson Support

## Tanggal: 1 Januari 2025
## Jenis: Integration Changes
## Lokasi: `app/Services/JetsonOrinService.php`

## Service Overview

### JetsonOrinService
**File:** `app/Services/JetsonOrinService.php`
**Purpose:** Centralized service for interacting with Jetson Orin devices

## Configuration

### Service Configuration
**File:** `config/services.php`

```php
'jetson' => [
    'api_base_url' => 'http://100.117.234.2:5000',
    'timeout' => 5
],
'cv_server' => [
    'api_base_url' => 'http://100.98.142.94:5000',
    'timeout' => 5
]
```

### Environment Variables
```env
JETSON_API_BASE_URL=http://100.117.234.2:5000
JETSON_TIMEOUT=5
CV_SERVER_API_BASE_URL=http://100.98.142.94:5000
CV_SERVER_TIMEOUT=5
```

## Service Methods

### 1. checkJetsonHealth()
**Purpose:** Check health status of specific Jetson Orin device
**Parameters:** `string $ipAddress`
**Returns:** `array`

```php
public function checkJetsonHealth(string $ipAddress): array
{
    $healthUrl = "http://{$ipAddress}:5000/api/health";
    // Implementation with timeout, error handling, and response parsing
}
```

**Response Structure:**
```php
[
    'success' => true,
    'connected' => true,
    'message' => 'Jetson Orin is healthy.',
    'status' => 'healthy',
    'response_time' => 45,
    'data' => [
        'status' => 'healthy',
        'timestamp' => '2025-01-01T11:58:01Z'
    ]
]
```

### 2. getJetsonStatus()
**Purpose:** Get detailed status including GPU info
**Parameters:** `string $ipAddress`
**Returns:** `array`

```php
public function getJetsonStatus(string $ipAddress): array
{
    $statusUrl = "http://{$ipAddress}:5000/api/status";
    // Implementation with GPU monitoring
}
```

**Response Structure:**
```php
[
    'success' => true,
    'connected' => true,
    'message' => 'Jetson Orin status retrieved successfully.',
    'response_time' => 52,
    'data' => [
        'status' => 'healthy',
        'gpu_usage' => 15.5,
        'memory_usage' => 45.2,
        'temperature' => 42.1,
        'timestamp' => '2025-01-01T11:58:01Z'
    ]
]
```

### 3. getJetsonHardwareInfo()
**Purpose:** Get comprehensive hardware information
**Parameters:** `string $ipAddress`
**Returns:** `array`

```php
public function getJetsonHardwareInfo(string $ipAddress): array
{
    $hardwareUrl = "http://{$ipAddress}:5000/api/hardware";
    // Implementation with hardware details
}
```

**Response Structure:**
```php
[
    'success' => true,
    'connected' => true,
    'message' => 'Jetson Orin hardware info retrieved successfully.',
    'response_time' => 38,
    'data' => [
        'jetson_info' => [
            'model' => 'Jetson Orin Nano',
            'jetpack_version' => '5.1.2',
            'l4t_version' => '35.4.1'
        ],
        'cuda_info' => [
            'version' => '11.4',
            'devices' => 1
        ],
        'memory_info' => [
            'total' => '8GB',
            'available' => '6.2GB',
            'used' => '1.8GB'
        ],
        'disk_info' => [
            'total' => '64GB',
            'available' => '45.2GB',
            'used' => '18.8GB'
        ]
    ]
]
```

### 4. checkCvServerHealth()
**Purpose:** Check CV Server health (not displayed in UI)
**Parameters:** None
**Returns:** `array`

```php
public function checkCvServerHealth(): array
{
    $healthUrl = "{$this->cvServerApiBaseUrl}/api/health";
    // Implementation for CV Server health check
}
```

### 5. getComprehensiveStatus()
**Purpose:** Get all status information in one call
**Parameters:** `string $rvmIp`
**Returns:** `array`

```php
public function getComprehensiveStatus(string $rvmIp): array
{
    $jetsonHealth = $this->checkJetsonHealth($rvmIp);
    $jetsonStatus = $this->getJetsonStatus($rvmIp);
    $jetsonHardware = $this->getJetsonHardwareInfo($rvmIp);
    
    return [
        'health' => $jetsonHealth,
        'status' => $jetsonStatus,
        'hardware' => $jetsonHardware,
        'overall' => $this->calculateOverallStatus($jetsonHealth, $jetsonStatus)
    ];
}
```

## Error Handling

### Connection Exceptions
```php
catch (\Illuminate\Http\Client\ConnectionException $e) {
    Log::warning("Jetson Orin connection failed for {$ipAddress}: " . $e->getMessage());
    return [
        'success' => false,
        'connected' => false,
        'message' => 'Could not connect to Jetson Orin API.',
        'status' => 'disconnected',
        'response_time' => null,
        'error' => $e->getMessage()
    ];
}
```

### General Exceptions
```php
catch (\Exception $e) {
    Log::error("Error checking Jetson Orin health for {$ipAddress}: " . $e->getMessage());
    return [
        'success' => false,
        'connected' => false,
        'message' => 'An unexpected error occurred during Jetson Orin health check.',
        'status' => 'error',
        'response_time' => null,
        'error' => $e->getMessage()
    ];
}
```

## Caching Strategy

### Cache Keys
```php
'jetson_health_' . md5($ipAddress)
'jetson_status_' . md5($ipAddress)
'jetson_hardware_' . md5($ipAddress)
```

### Cache TTL
- Health: 30 seconds
- Status: 60 seconds
- Hardware: 300 seconds

### Cache Invalidation
```php
public function clearCaches(): bool
{
    $patterns = [
        'jetson_status_*',
        'jetson_hardware_*',
        'jetson_health_*'
    ];
    
    foreach ($patterns as $pattern) {
        $keys = Cache::getRedis()->keys($pattern);
        if (!empty($keys)) {
            Cache::getRedis()->del($keys);
        }
    }
    
    return true;
}
```

## Performance Optimizations

### Timeout Configuration
```php
protected $timeout = 5; // 5 seconds timeout
```

### Response Time Tracking
```php
$start = microtime(true);
$response = Http::timeout($this->timeout)->get($healthUrl);
$end = microtime(true);
$responseTime = round(($end - $start) * 1000); // in milliseconds
```

### Logging
```php
Log::info('Jetson Orin health check', [
    'ip_address' => $ipAddress,
    'response_time' => $responseTime,
    'status' => $data['status'] ?? 'unknown'
]);
```

## Multi-Jetson Support

### Dynamic IP Handling
- Each RVM can have different Jetson IP
- Service automatically constructs URLs based on IP
- No hardcoded IP addresses in service

### Concurrent Requests
- Each RVM health check is independent
- No blocking between different Jetson devices
- Parallel processing in controllers

### Error Isolation
- One Jetson failure doesn't affect others
- Individual error logging per device
- Graceful degradation for offline devices

## Integration Points

### Controller Integration
```php
// AdminRvmController
$jetsonService = app(\App\Services\JetsonOrinService::class);
$jetsonHealth = $jetsonService->checkJetsonHealth($rvm->ip_address);
```

### Frontend Integration
```javascript
// rvm-cards.js
const jetsonHealth = rvm.jetson_health || {};
const jetsonConnected = rvm.jetson_connected || false;
const jetsonResponseTime = jetsonHealth.response_time || null;
```

### Route Integration
```php
// routes/web.php
Route::prefix('api/admin/jetson')->name('api.admin.jetson.')->group(function () {
    Route::get('/{rvmId}/health', [JetsonOrinController::class, 'getHealth']);
    Route::get('/{rvmId}/status', [JetsonOrinController::class, 'getStatus']);
    Route::get('/{rvmId}/hardware', [JetsonOrinController::class, 'getHardwareInfo']);
});
```
