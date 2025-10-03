# Controller Updates - Multi-Jetson Integration

## Tanggal: 1 Januari 2025
## Jenis: Backend Changes
## Lokasi: `app/Http/Controllers/`

## Perubahan yang Dilakukan

### 1. AdminRvmController.php
**File:** `app/Http/Controllers/AdminRvmController.php`

#### Update `getRvmMonitoring()` method:

**Sebelum:**
```php
// Get Jetson Orin health status
$jetsonHealth = $jetsonService->checkJetsonHealth($rvm->ip_address);

return [
    'id' => $rvm->id,
    'name' => $rvm->name,
    'location' => $rvm->location_description,
    'capacity' => $rvm->capacity ?? 0,
    'status' => $statusData['status'],
    'special_status' => $rvm->special_status,
    'status_info' => $statusData,
    'last_seen' => $rvm->last_capacity_update ? 
        TimezoneHelper::formatTime($rvm->last_capacity_update) : 
        TimezoneHelper::formatTime($rvm->updated_at),
    'ip_address' => $rvm->ip_address,
    'jetson_health' => $jetsonHealth,
    'jetson_connected' => $jetsonHealth['connected'] ?? false
];
```

**Sesudah:**
```php
// Get Jetson Orin health status (only if IP address is configured)
$jetsonHealth = null;
$jetsonConnected = false;

if ($rvm->ip_address) {
    $jetsonHealth = $jetsonService->checkJetsonHealth($rvm->ip_address);
    $jetsonConnected = $jetsonHealth['connected'] ?? false;
}

return [
    'id' => $rvm->id,
    'name' => $rvm->name,
    'location' => $rvm->location_description,
    'capacity' => $rvm->capacity ?? 0,
    'status' => $statusData['status'],
    'special_status' => $rvm->special_status,
    'status_info' => $statusData,
    'last_seen' => $rvm->last_capacity_update ? 
        TimezoneHelper::formatTime($rvm->last_capacity_update) : 
        TimezoneHelper::formatTime($rvm->updated_at),
    'ip_address' => $rvm->ip_address,
    'jetson_health' => $jetsonHealth,
    'jetson_connected' => $jetsonConnected
];
```

#### Update Statistics:
**Dihapus:**
```php
'cv_server_connected' => $cvServerHealth['connected'] ?? false
```

**Ditambahkan:**
```php
'jetson_connected' => $processedRvms->where('jetson_connected', true)->count()
```

#### Update Monitoring Data:
**Dihapus:**
```php
'cv_server_health' => $cvServerHealth,
'cv_server_api_base' => 'http://100.98.142.94:5000'
```

### 2. RvmController.php
**File:** `app/Http/Controllers/Admin/RvmController.php`

#### Update `ping()` method:
- Sudah menggunakan `JetsonOrinService` untuk health check
- Status koneksi berdasarkan Jetson health
- Response time dari Jetson API

**Kode yang sudah ada:**
```php
try {
    // Use Jetson Orin Service for health check
    $jetsonService = app(\App\Services\JetsonOrinService::class);
    $jetsonHealth = $jetsonService->checkJetsonHealth($rvm->ip_address);
    
    $connectionStatus = $jetsonHealth['connected'] ? 'connected' : 'disconnected';
    $success = $jetsonHealth['success'];
    $message = $jetsonHealth['message'];
    $responseTime = $jetsonHealth['response_time'];

    // Calculate new status based on Jetson health
    $newStatus = RvmStatusHelper::calculateStatus($rvm->capacity, $rvm->special_status, $connectionStatus);

    $rvm->update([
        'last_ping' => now(),
        'connection_status' => $connectionStatus,
        'status' => $newStatus
    ]);
```

### 3. JetsonOrinController.php
**File:** `app/Http/Controllers/Admin/JetsonOrinController.php`

#### Method yang tersedia:
- `getHealth($rvmId)` - Health status per RVM
- `getStatus($rvmId)` - Status dengan GPU info per RVM
- `getHardwareInfo($rvmId)` - Hardware info per RVM
- `getComprehensiveStatus($rvmId)` - Status lengkap per RVM
- `getCvServerHealth()` - CV Server health (tidak digunakan di UI)
- `getAllRvmsWithJetsonStatus()` - Semua RVM dengan status Jetson

## Data Structure Changes

### RVM Monitoring Response
```php
[
    'statistics' => [
        'total_rvm' => 5,
        'active_sessions' => 2,
        'deposits_today' => 150,
        'total_issues' => 1,
        'jetson_connected' => 4  // Jumlah RVM dengan Jetson terhubung
    ],
    'rvms' => [
        [
            'id' => 1,
            'name' => 'RVM-001',
            'location' => 'Lobby Building A',
            'capacity' => 75,
            'status' => 'active',
            'ip_address' => '100.117.234.2',
            'jetson_health' => [
                'success' => true,
                'connected' => true,
                'message' => 'Jetson Orin is healthy.',
                'status' => 'healthy',
                'response_time' => 45
            ],
            'jetson_connected' => true
        ]
    ],
    'jetson_integration' => [
        'enabled' => true,
        'jetson_api_base' => 'http://100.117.234.2:5000'
    ]
]
```

## Error Handling

### RVM tanpa IP Address
- `jetson_health`: `null`
- `jetson_connected`: `false`
- Tidak ada API call ke Jetson

### Jetson API Error
- `jetson_health`: Error response
- `jetson_connected`: `false`
- Log error untuk debugging

## Performance Impact

### Optimizations
- ✅ Conditional API calls (hanya jika ada IP)
- ✅ Caching di JetsonOrinService
- ✅ Timeout configuration (5 detik)
- ✅ Error handling yang robust

### Monitoring
- ✅ Log semua API calls
- ✅ Response time tracking
- ✅ Error rate monitoring
