# API Endpoints - Multi-Jetson Integration

## Tanggal: 1 Januari 2025
## Jenis: API Changes
## Lokasi: `routes/web.php` dan `app/Http/Controllers/Admin/JetsonOrinController.php`

## Endpoints yang Tersedia

### 1. RVM Monitoring API
**Endpoint:** `GET /admin/rvm/monitoring`
**Controller:** `AdminRvmController@getRvmMonitoring`
**Authentication:** Required

#### Response Structure:
```json
{
    "success": true,
    "data": {
        "statistics": {
            "total_rvm": 5,
            "active_sessions": 2,
            "deposits_today": 150,
            "total_issues": 1,
            "jetson_connected": 4
        },
        "rvms": [
            {
                "id": 1,
                "name": "RVM-001",
                "location": "Lobby Building A",
                "capacity": 75,
                "status": "active",
                "special_status": null,
                "status_info": {
                    "status": "active",
                    "class": "success",
                    "icon": "fas fa-check-circle",
                    "label": "Active",
                    "color": "#28a745"
                },
                "last_seen": "2 minutes ago",
                "ip_address": "100.117.234.2",
                "jetson_health": {
                    "success": true,
                    "connected": true,
                    "message": "Jetson Orin is healthy.",
                    "status": "healthy",
                    "response_time": 45,
                    "data": {
                        "status": "healthy",
                        "timestamp": "2025-01-01T11:58:01Z"
                    }
                },
                "jetson_connected": true
            }
        ],
        "jetson_integration": {
            "enabled": true,
            "jetson_api_base": "http://100.117.234.2:5000"
        }
    },
    "cached": false
}
```

### 2. Individual RVM Jetson Health
**Endpoint:** `GET /api/admin/jetson/{rvmId}/health`
**Controller:** `JetsonOrinController@getHealth`
**Authentication:** Required

#### Response Structure:
```json
{
    "success": true,
    "data": {
        "success": true,
        "connected": true,
        "message": "Jetson Orin is healthy.",
        "status": "healthy",
        "response_time": 45,
        "data": {
            "status": "healthy",
            "timestamp": "2025-01-01T11:58:01Z"
        }
    }
}
```

### 3. Individual RVM Jetson Status
**Endpoint:** `GET /api/admin/jetson/{rvmId}/status`
**Controller:** `JetsonOrinController@getStatus`
**Authentication:** Required

#### Response Structure:
```json
{
    "success": true,
    "data": {
        "success": true,
        "connected": true,
        "message": "Jetson Orin status retrieved successfully.",
        "response_time": 52,
        "data": {
            "status": "healthy",
            "gpu_usage": 15.5,
            "memory_usage": 45.2,
            "temperature": 42.1,
            "timestamp": "2025-01-01T11:58:01Z"
        }
    }
}
```

### 4. Individual RVM Jetson Hardware Info
**Endpoint:** `GET /api/admin/jetson/{rvmId}/hardware`
**Controller:** `JetsonOrinController@getHardwareInfo`
**Authentication:** Required

#### Response Structure:
```json
{
    "success": true,
    "data": {
        "success": true,
        "connected": true,
        "message": "Jetson Orin hardware info retrieved successfully.",
        "response_time": 38,
        "data": {
            "jetson_info": {
                "model": "Jetson Orin Nano",
                "jetpack_version": "5.1.2",
                "l4t_version": "35.4.1"
            },
            "cuda_info": {
                "version": "11.4",
                "devices": 1
            },
            "memory_info": {
                "total": "8GB",
                "available": "6.2GB",
                "used": "1.8GB"
            },
            "disk_info": {
                "total": "64GB",
                "available": "45.2GB",
                "used": "18.8GB"
            }
        }
    }
}
```

### 5. Comprehensive RVM Jetson Status
**Endpoint:** `GET /api/admin/jetson/{rvmId}/comprehensive`
**Controller:** `JetsonOrinController@getComprehensiveStatus`
**Authentication:** Required

#### Response Structure:
```json
{
    "success": true,
    "data": {
        "health": {
            "success": true,
            "connected": true,
            "message": "Jetson Orin is healthy.",
            "status": "healthy",
            "response_time": 45
        },
        "status": {
            "success": true,
            "connected": true,
            "message": "Jetson Orin status retrieved successfully.",
            "response_time": 52
        },
        "hardware": {
            "success": true,
            "connected": true,
            "message": "Jetson Orin hardware info retrieved successfully.",
            "response_time": 38
        }
    }
}
```

### 6. All RVMs with Jetson Status
**Endpoint:** `GET /api/admin/jetson/rvms/status`
**Controller:** `JetsonOrinController@getAllRvmsWithJetsonStatus`
**Authentication:** Required

#### Response Structure:
```json
{
    "success": true,
    "data": [
        {
            "rvm_id": 1,
            "rvm_name": "RVM-001",
            "ip_address": "100.117.234.2",
            "jetson_health": {
                "success": true,
                "connected": true,
                "message": "Jetson Orin is healthy.",
                "status": "healthy",
                "response_time": 45
            },
            "jetson_status": {
                "success": true,
                "connected": true,
                "message": "Jetson Orin status retrieved successfully.",
                "response_time": 52
            },
            "overall_health": true
        }
    ],
    "total_rvms": 5,
    "healthy_rvms": 4
}
```

## Error Responses

### RVM Not Found
```json
{
    "success": false,
    "message": "RVM not found"
}
```

### Jetson API Error
```json
{
    "success": false,
    "connected": false,
    "message": "Could not connect to Jetson Orin API.",
    "status": "disconnected",
    "response_time": null,
    "error": "Connection timeout"
}
```

### No IP Address Configured
```json
{
    "success": false,
    "message": "No IP address configured for this RVM"
}
```

## Authentication

### Required Headers:
```http
Authorization: Bearer <token>
Accept: application/json
X-CSRF-TOKEN: <csrf_token>
```

### Session-based Authentication:
- Login via `/login`
- Session cookie: `laravel-session`
- CSRF token: `XSRF-TOKEN`

## Rate Limiting

### Default Limits:
- 60 requests per minute per user
- 1000 requests per hour per user

### Jetson API Calls:
- 5 second timeout per call
- Retry mechanism: 3 attempts
- Circuit breaker: 5 failures = 30 second cooldown

## Caching

### Cache Keys:
- `jetson_health_{ip_address}` - TTL: 30 seconds
- `jetson_status_{ip_address}` - TTL: 60 seconds
- `jetson_hardware_{ip_address}` - TTL: 300 seconds

### Cache Invalidation:
- Manual: `GET /api/admin/jetson/caches/clear`
- Automatic: On RVM status update
- Background: Every 5 minutes
