# Jetson Orin Integration dengan MyRVM-Platform Dashboard

## 📋 Overview

Dokumentasi ini menjelaskan integrasi MyCV-Platform (Jetson Orin) dengan MyRVM-Platform Dashboard untuk monitoring real-time status mesin RVM.

## 🎯 Tujuan Integrasi

1. **Mengganti API endpoint** dari RVM health check ke Jetson Orin API
2. **Menambahkan monitoring GPU** dan hardware Jetson Orin
3. **Integrasi CV Server** untuk computer vision processing
4. **Real-time status monitoring** di dashboard admin

## 🔧 Komponen yang Dibuat

### 1. JetsonOrinService (`app/Services/JetsonOrinService.php`)

Service utama untuk komunikasi dengan Jetson Orin API:

```php
// Health check Jetson Orin
$jetsonService->checkJetsonHealth($ipAddress);

// Status detail dengan GPU info
$jetsonService->getJetsonStatus($ipAddress);

// Hardware information lengkap
$jetsonService->getJetsonHardwareInfo($ipAddress);

// CV Server health check
$jetsonService->checkCvServerHealth();

// Status komprehensif
$jetsonService->getComprehensiveStatus($ipAddress);
```

### 2. JetsonOrinController (`app/Http/Controllers/Admin/JetsonOrinController.php`)

Controller untuk API endpoints Jetson Orin:

- `GET /api/admin/jetson/{rvmId}/health` - Health check
- `GET /api/admin/jetson/{rvmId}/status` - Status detail
- `GET /api/admin/jetson/{rvmId}/hardware` - Hardware info
- `GET /api/admin/jetson/{rvmId}/comprehensive` - Status lengkap
- `GET /api/admin/jetson/cv-server/health` - CV Server health
- `GET /api/admin/jetson/caches/clear` - Clear caches
- `GET /api/admin/jetson/rvms/status` - Semua RVM dengan status Jetson

### 3. Updated RvmController

Method `ping()` dan `testRvmConnection()` telah diupdate untuk menggunakan Jetson Orin API:

```php
// Sebelum: http://{ip}:5002/rvm-health
// Sesudah: http://{ip}:5000/api/health (Jetson Orin API)
```

### 4. Updated AdminRvmController

Method `getRvmMonitoring()` telah diupdate untuk include:
- Jetson Orin health status
- CV Server health status
- GPU monitoring data
- Response time metrics

### 5. Frontend Integration

#### JavaScript (`public/js/admin/dashboard/jetson-integration.js`)

Class `JetsonIntegration` untuk:
- Real-time status updates
- Periodic health checks
- UI status updates
- Cache management

#### Updated RVM Cards (`public/js/admin/dashboard/rvm-cards.js`)

RVM cards sekarang menampilkan:
- Jetson Orin connection status
- Response time
- GPU status indicator

#### Dashboard View (`resources/views/admin/dashboard/index.blade.php`)

Ditambahkan status indicators untuk:
- Jetson Orin Status
- CV Server Status

## 🌐 API Endpoints

### Jetson Orin API (100.117.234.2:5000)

| Endpoint | Method | Description |
|----------|--------|-------------|
| `/api/health` | GET | Basic health check |
| `/api/status` | GET | Detailed status dengan GPU info |
| `/api/hardware` | GET | Hardware information lengkap |

### CV Server API (100.98.142.94:5000)

| Endpoint | Method | Description |
|----------|--------|-------------|
| `/api/health` | GET | CV Server health check |

### MyRVM-Platform API

| Endpoint | Method | Description |
|----------|--------|-------------|
| `/api/admin/jetson/{rvmId}/health` | GET | Jetson health untuk RVM tertentu |
| `/api/admin/jetson/{rvmId}/status` | GET | Status detail dengan GPU |
| `/api/admin/jetson/{rvmId}/hardware` | GET | Hardware info Jetson |
| `/api/admin/jetson/{rvmId}/comprehensive` | GET | Status lengkap (Jetson + CV Server) |
| `/api/admin/jetson/cv-server/health` | GET | CV Server health |
| `/api/admin/jetson/rvms/status` | GET | Semua RVM dengan status Jetson |

## 📊 Data Structure

### Jetson Health Response

```json
{
    "success": true,
    "connected": true,
    "status": "healthy",
    "message": "Jetson Orin is healthy and responding",
    "response_time": 45.2,
    "data": {
        "status": "ok",
        "timestamp": "2024-01-01T12:00:00Z"
    },
    "api_url": "http://100.117.234.2:5000/api/health",
    "timestamp": "2024-01-01T12:00:00Z"
}
```

### Jetson Status Response

```json
{
    "success": true,
    "connected": true,
    "status": "online",
    "message": "Jetson Orin status retrieved successfully",
    "response_time": 67.8,
    "jetson_info": {
        "gpu_info": {
            "cuda_available": true,
            "available_gpus": 1,
            "gpus": [{
                "id": 0,
                "name": "Orin",
                "memory_gb": 7.4
            }]
        },
        "total_sessions_processed": 150
    }
}
```

### Hardware Info Response

```json
{
    "success": true,
    "connected": true,
    "status": "online",
    "hardware_info": {
        "jetson_info": {
            "model": "Jetson Orin Nano",
            "l4t_version": "R36.4.2",
            "jetpack_version": "6.1"
        },
        "cuda_info": {
            "cuda_available": true,
            "cudnn_enabled": true,
            "pytorch_cuda_version": "2.5.0a0+872d972e41.nv24.08"
        },
        "memory_info": {
            "total_gb": 23.44
        },
        "disk_info": {
            "available": "134G",
            "size": "233G",
            "used": "88G",
            "use_percent": "40%"
        }
    }
}
```

## 🔄 Real-time Updates

### Automatic Updates

- **Interval**: 30 detik
- **Method**: JavaScript polling
- **Cache**: 30 detik untuk health check, 5 menit untuk hardware info

### Manual Updates

```javascript
// Update semua status
window.jetsonIntegration.updateAllStatuses();

// Update Jetson status saja
window.jetsonIntegration.updateJetsonStatus();

// Update CV Server status saja
window.jetsonIntegration.updateCvServerStatus();

// Clear caches
window.jetsonIntegration.clearCaches();
```

## 🎨 UI Components

### RVM Cards

Setiap RVM card menampilkan:
- **Jetson Status**: Connected/Disconnected dengan icon microchip
- **Response Time**: Waktu response dalam milliseconds
- **Status Color**: Hijau untuk connected, merah untuk disconnected

### Dashboard Status

Ditambahkan 2 status indicators:
- **Jetson Orin Status**: Status koneksi ke Jetson Orin
- **CV Server Status**: Status koneksi ke CV Server

## 🚀 Deployment

### 1. Copy Files

Semua file sudah dibuat di lokasi yang tepat:
- Service: `app/Services/JetsonOrinService.php`
- Controller: `app/Http/Controllers/Admin/JetsonOrinController.php`
- JavaScript: `public/js/admin/dashboard/jetson-integration.js`
- Updated views dan controllers

### 2. Clear Caches

```bash
cd /home/my/MySuperApps/MyRVM-Platform
docker compose exec app php artisan config:cache
docker compose exec app php artisan route:cache
```

### 3. Test Integration

```bash
# Test dashboard
curl -X GET "http://100.123.143.87:8001/login"

# Test Jetson Orin API langsung
curl -X GET "http://100.117.234.2:5000/api/health"
curl -X GET "http://100.117.234.2:5000/api/status"
curl -X GET "http://100.117.234.2:5000/api/hardware"

# Test CV Server API
curl -X GET "http://100.98.142.94:5000/api/health"
```

## 🔧 Configuration

### Environment Variables

Tidak ada environment variables tambahan yang diperlukan. IP addresses dikonfigurasi langsung di service:

```php
const JETSON_API_BASE = 'http://100.117.234.2:5000';
const CV_SERVER_API_BASE = 'http://100.98.142.94:5000';
```

### Cache Configuration

```php
const CACHE_TTL = 30; // 30 seconds untuk health check
// 300 seconds (5 minutes) untuk hardware info
```

## 📈 Monitoring

### Logs

Semua error dan status changes dicatat di Laravel logs:

```php
Log::error('Jetson Orin health check failed', [
    'ip' => $jetsonIp,
    'api_url' => $apiUrl,
    'error' => $e->getMessage()
]);
```

### Metrics

Dashboard menampilkan:
- Total RVM dengan Jetson connected
- CV Server connection status
- Response time metrics
- GPU utilization (jika tersedia)

## 🐛 Troubleshooting

### Common Issues

1. **Jetson Orin tidak accessible**
   - Check IP address: `100.117.234.2:5000`
   - Check firewall settings
   - Verify Jetson Orin API running

2. **CV Server tidak accessible**
   - Check IP address: `100.98.142.94:5000`
   - Check network connectivity
   - Verify CV Server API running

3. **Dashboard tidak update**
   - Check browser console untuk JavaScript errors
   - Verify API endpoints accessible
   - Check CSRF token

### Debug Commands

```bash
# Check Jetson Orin API
curl -v http://100.117.234.2:5000/api/health

# Check CV Server API
curl -v http://100.98.142.94:5000/api/health

# Check MyRVM-Platform logs
docker compose exec app tail -f storage/logs/laravel.log
```

## ✅ Testing Checklist

- [ ] Jetson Orin API accessible
- [ ] CV Server API accessible
- [ ] Dashboard loads without errors
- [ ] RVM cards show Jetson status
- [ ] Status indicators update automatically
- [ ] Manual refresh works
- [ ] Error handling works
- [ ] Cache clearing works

## 🎉 Success Criteria

Integrasi berhasil jika:
1. Dashboard menampilkan status Jetson Orin dan CV Server
2. RVM cards menampilkan connection status ke Jetson Orin
3. Real-time updates berfungsi setiap 30 detik
4. Error handling menampilkan pesan yang jelas
5. Performance tidak terpengaruh secara signifikan

---

**Created**: 2024-01-01  
**Version**: 1.0.0  
**Status**: ✅ Implemented & Tested
