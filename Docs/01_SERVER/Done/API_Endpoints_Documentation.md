# API Endpoints Documentation
**Date:** 2025-10-03  
**Version:** 2.0  
**Base URL:** `http://100.123.143.87:8001/api`  
**Status:** ✅ PRODUCTION READY

## 🔐 Authentication

### User Login (API Token Generation)
```http
POST /api/login
Content-Type: application/json

{
    "email": "admin@myrvm.com",
    "password": "password"
}
```

**Response:**
```json
{
    "success": true,
    "message": "Login successful",
    "data": {
        "user": {
            "id": 1,
            "name": "Admin MyRVM",
            "email": "admin@myrvm.com",
            "email_verified_at": "2025-10-03T10:03:39.000000Z",
            "created_at": "2025-10-03T10:03:40.000000Z",
            "updated_at": "2025-10-03T10:03:40.000000Z"
        },
        "token": "4|hQKhHFQib72QR4EwhpvU71pfhqSpjLjNel3wmAlFa6d188d0",
        "token_type": "Bearer"
    }
}
```

### User Logout (Token Revocation)
```http
POST /api/logout
Authorization: Bearer {token}
Content-Type: application/json

{}
```

**Response:**
```json
{
    "success": true,
    "message": "Logout successful"
}
```

### Get Authenticated User
```http
GET /api/user
Authorization: Bearer {token}
Accept: application/json
```

**Response:**
```json
{
    "success": true,
    "data": {
        "id": 1,
        "name": "Admin MyRVM",
        "email": "admin@myrvm.com",
        "email_verified_at": "2025-10-03T10:03:39.000000Z",
        "created_at": "2025-10-03T10:03:40.000000Z",
        "updated_at": "2025-10-03T10:03:40.000000Z"
    }
}
```

## 🏥 Health Check

### API Health Check
```http
GET /api/test
Accept: application/json
```

**Response:**
```json
{
    "message": "API is working"
}
```

### System Health Check (Protected)
```http
GET /api/health
Authorization: Bearer {token}
Accept: application/json
```

**Response:**
```json
{
    "success": true,
    "message": "MyRVM Ecosystem API is healthy",
    "timestamp": "2025-10-03T10:57:08.000000Z",
    "version": "2.0",
    "database": {
        "status": "connected",
        "rvms_count": 6,
        "users_count": 3
    }
}
```

## 🤖 RVM Management

### List All RVMs (Protected)
```http
GET /api/rvms
Authorization: Bearer {token}
Accept: application/json
```

**Query Parameters:**
- `status` - Filter by status (active, inactive, maintenance, error)
- `online` - Filter by online status (true/false)
- `search` - Search by name or location
- `per_page` - Items per page (default: 15)

**Response:**
```json
{
    "success": true,
    "message": "RVMs retrieved successfully",
    "data": {
        "current_page": 1,
        "data": [
            {
                "id": 1,
                "name": "RVM-001",
                "location": "Mall Central Jakarta",
                "address": "Jl. Sudirman No. 1, Jakarta Pusat",
                "latitude": "-6.20880000",
                "longitude": "106.84560000",
                "status": "active",
                "capacity": 100,
                "current_load": 45,
                "ip_address": "100.117.234.2",
                "api_key": "336ca0d245663c993694cf32ec8de6fbc9be69e96f6366da8781f213d5b89952",
                "last_ping": "2025-10-03T10:01:40.000000Z",
                "last_maintenance": null,
                "configuration": {
                    "auto_sort": true,
                    "max_items_per_session": 50,
                    "reward_multiplier": 1
                },
                "metrics": {
                    "cpu_usage": 35.2,
                    "memory_usage": 67.8,
                    "temperature": 42.5,
                    "uptime_hours": 168
                },
                "created_at": "2025-10-03T10:03:40.000000Z",
                "updated_at": "2025-10-03T10:03:40.000000Z",
                "api_key_expires_at": "2026-10-03T10:03:40.000000Z",
                "last_api_access": null
            }
        ],
        "total": 6
    }
}
```

### Get Specific RVM (Protected)
```http
GET /api/rvms/{id}
Authorization: Bearer {token}
Accept: application/json
```

**Response:**
```json
{
    "success": true,
    "message": "RVM retrieved successfully",
    "data": {
        "id": 1,
        "name": "RVM-001",
        "location": "Mall Central Jakarta",
        "status": "active",
        "capacity": 100,
        "current_load": 45,
        "ip_address": "100.117.234.2"
    }
}
```

### Create New RVM (Protected)
```http
POST /api/rvms
Authorization: Bearer {token}
Content-Type: application/json

{
    "name": "RVM-007",
    "location": "New Location",
    "address": "New Address",
    "latitude": -6.2088,
    "longitude": 106.8456,
    "capacity": 100,
    "ip_address": "192.168.1.104",
    "configuration": {
        "auto_sort": true,
        "max_items_per_session": 50,
        "reward_multiplier": 1.0
    }
}
```

**Response:**
```json
{
    "success": true,
    "message": "RVM created successfully",
    "data": {
        "rvm": {
            "id": 7,
            "name": "RVM-007",
            "location": "New Location",
            "status": "active",
            "capacity": 100,
            "current_load": 0,
            "api_key": "generated_api_key_here",
            "created_at": "2025-10-03T10:57:08.000000Z"
        },
        "api_key": "generated_api_key_here"
    }
}
```

### Update RVM (Protected)
```http
PUT /api/rvms/{id}
Authorization: Bearer {token}
Content-Type: application/json

{
    "name": "RVM-001-Updated",
    "location": "Updated Location",
    "status": "maintenance",
    "capacity": 120
}
```

**Response:**
```json
{
    "success": true,
    "message": "RVM updated successfully",
    "data": {
        "id": 1,
        "name": "RVM-001-Updated",
        "location": "Updated Location",
        "status": "maintenance",
        "capacity": 120,
        "updated_at": "2025-10-03T10:57:08.000000Z"
    }
}
```

### Delete RVM (Protected)
```http
DELETE /api/rvms/{id}
Authorization: Bearer {token}
```

**Response:**
```json
{
    "success": true,
    "message": "RVM deleted successfully"
}
```

### Update RVM Status (Protected)
```http
POST /api/rvms/{id}/status
Authorization: Bearer {token}
Content-Type: application/json

{
    "status": "maintenance"
}
```

**Response:**
```json
{
    "success": true,
    "message": "RVM status updated successfully",
    "data": {
        "id": 1,
        "name": "RVM-001",
        "status": "maintenance",
        "updated_at": "2025-10-03T10:57:08.000000Z"
    }
}
```

### Update RVM Metrics (Protected)
```http
POST /api/rvms/{id}/metrics
Authorization: Bearer {token}
Content-Type: application/json

{
    "metrics": {
        "cpu_usage": 45.2,
        "memory_usage": 67.8,
        "temperature": 42.5,
        "uptime_hours": 168
    },
    "current_load": 50
}
```

**Response:**
```json
{
    "success": true,
    "message": "RVM metrics updated successfully",
    "data": {
        "id": 1,
        "name": "RVM-001",
        "metrics": {
            "cpu_usage": 45.2,
            "memory_usage": 67.8,
            "temperature": 42.5,
            "uptime_hours": 168
        },
        "current_load": 50,
        "updated_at": "2025-10-03T10:57:08.000000Z"
    }
}
```

### RVM Ping/Heartbeat (Protected)
```http
POST /api/rvms/{id}/ping
Authorization: Bearer {token}
```

**Response:**
```json
{
    "success": true,
    "message": "RVM ping updated successfully",
    "data": {
        "rvm_id": 1,
        "name": "RVM-001",
        "status": "active",
        "is_online": true,
        "last_ping": "2025-10-03T10:57:08.000000Z"
    }
}
```

### RVM Statistics (Protected)
```http
GET /api/rvms-statistics
Authorization: Bearer {token}
Accept: application/json
```

**Response:**
```json
{
    "success": true,
    "message": "RVM statistics retrieved successfully",
    "data": {
        "total": 6,
        "active": 4,
        "inactive": 1,
        "maintenance": 1,
        "error": 0,
        "online": 4,
        "offline": 2,
        "capacity_usage": {
            "total_capacity": 750,
            "total_load": 386,
            "average_usage": 64.33
        }
    }
}
```

## 🔗 RVM Integration (Public for Jetson)

### Get RVM Info (Public)
```http
GET /api/rvm/{id}
Accept: application/json
```

**Response:**
```json
{
    "id": 1,
    "name": "RVM-001",
    "location": "Mall Central Jakarta",
    "ip_address": "100.117.234.2",
    "status": "active",
    "capacity": 100,
    "current_load": 45,
    "last_online_at": "2025-10-03T10:01:40.000000Z",
    "api_key_valid": true
}
```

### Get RVM Statistics (Public)
```http
GET /api/rvm/{id}/stats
Accept: application/json
```

**Response:**
```json
{
    "total_detections": 15,
    "today_detections": 3,
    "completed_detections": 12,
    "failed_detections": 1,
    "last_detection": {
        "id": 6,
        "session_id": "integration_test_001",
        "detected_at": "2025-10-03T10:57:08.000000Z",
        "status": "pending"
    }
}
```

### Get RVM Detections (Public)
```http
GET /api/rvm/{id}/detections
Accept: application/json
```

**Query Parameters:**
- `status` - Filter by status (pending, processing, completed, failed)
- `limit` - Limit results (default: 50, max: 100)
- `date_from` - Start date (YYYY-MM-DD)
- `date_to` - End date (YYYY-MM-DD)

**Response:**
```json
[
    {
        "id": 6,
        "rvm_id": 1,
        "session_id": "integration_test_001",
        "detection_data": {
            "detections": [
                {
                    "type": "plastic",
                    "confidence": 0.95,
                    "weight": 0.5
                }
            ]
        },
        "detected_at": "2025-10-03T10:57:08.000000Z",
        "status": "pending"
    }
]
```

### Update RVM Status (Public)
```http
PATCH /api/rvm/{id}/status
Content-Type: application/json

{
    "status": "active",
    "current_load": 50,
    "metrics": {
        "cpu_usage": 45.2,
        "memory_usage": 67.8,
        "temperature": 42.5
    }
}
```

**Response:**
```json
{
    "success": true
}
```

## 🔍 Detection Results

### Store Detection Result (Public)
```http
POST /api/detections/store
Content-Type: application/json

{
    "rvm_id": 1,
    "session_id": "detection_session_001",
    "user_id": "user_123",
    "detection_data": {
        "detections": [
            {
                "type": "plastic",
                "confidence": 0.95,
                "weight": 0.5,
                "quality_grade": "A",
                "position": {
                    "x": 100,
                    "y": 200,
                    "width": 50,
                    "height": 60
                }
            },
            {
                "type": "glass",
                "confidence": 0.87,
                "weight": 0.3,
                "quality_grade": "B",
                "position": {
                    "x": 150,
                    "y": 250,
                    "width": 40,
                    "height": 45
                }
            }
        ],
        "image_metadata": {
            "width": 1920,
            "height": 1080,
            "format": "jpg",
            "timestamp": "2025-10-03T10:57:08.000000Z"
        },
        "processing_info": {
            "model_version": "yolo11-v1.2",
            "processing_time": 2.5,
            "gpu_used": true
        }
    },
    "image_path": "/storage/detections/2025/10/03/detection_001.jpg",
    "status": "pending",
    "metadata": {
        "source": "jetson_device",
        "camera_id": "cam_001",
        "environment": "indoor"
    }
}
```

**Response:**
```json
{
    "success": true,
    "data": {
        "rvm_id": 1,
        "session_id": "detection_session_001",
        "user_id": "user_123",
        "detection_data": {
            "detections": [
                {
                    "type": "plastic",
                    "confidence": 0.95,
                    "weight": 0.5,
                    "quality_grade": "A"
                }
            ]
        },
        "detected_at": "2025-10-03T10:57:08.000000Z",
        "updated_at": "2025-10-03T10:57:08.000000Z",
        "created_at": "2025-10-03T10:57:08.000000Z",
        "id": 7
    }
}
```

### Get Detection Statistics (Public)
```http
GET /api/detections/statistics
Accept: application/json
```

**Response:**
```json
{
    "success": true,
    "data": {
        "total_detections": 4,
        "today_detections": 4,
        "completed_detections": 0,
        "failed_detections": 0,
        "pending_detections": 4,
        "processing_detections": 0,
        "last_detection": {
            "id": 6,
            "rvm_id": 1,
            "session_id": "integration_test_001",
            "detection_data": {
                "detections": [
                    {
                        "type": "plastic",
                        "confidence": 0.95,
                        "weight": 0.5
                    }
                ]
            },
            "detected_at": "2025-10-03T10:57:08.000000Z",
            "status": "pending"
        },
        "detections_by_status": {
            "pending": 4
        }
    }
}
```

### List Detections (Protected)
```http
GET /api/detections
Authorization: Bearer {token}
Accept: application/json
```

**Query Parameters:**
- `rvm_id` - Filter by RVM ID
- `status` - Filter by status
- `session_id` - Filter by session ID
- `per_page` - Items per page (default: 15)

**Response:**
```json
{
    "success": true,
    "data": {
        "current_page": 1,
        "data": [
            {
                "id": 6,
                "rvm_id": 1,
                "session_id": "integration_test_001",
                "user_id": null,
                "detection_data": {
                    "detections": [
                        {
                            "type": "plastic",
                            "confidence": 0.95,
                            "weight": 0.5
                        }
                    ]
                },
                "image_path": null,
                "detected_at": "2025-10-03T10:57:08.000000Z",
                "status": "pending",
                "error_message": null,
                "metadata": null,
                "created_at": "2025-10-03T10:57:08.000000Z",
                "updated_at": "2025-10-03T10:57:08.000000Z"
            }
        ],
        "total": 4
    }
}
```

### Get Specific Detection (Protected)
```http
GET /api/detections/{id}
Authorization: Bearer {token}
Accept: application/json
```

**Response:**
```json
{
    "success": true,
    "data": {
        "id": 6,
        "rvm_id": 1,
        "session_id": "integration_test_001",
        "detection_data": {
            "detections": [
                {
                    "type": "plastic",
                    "confidence": 0.95,
                    "weight": 0.5
                }
            ]
        },
        "detected_at": "2025-10-03T10:57:08.000000Z",
        "status": "pending"
    }
}
```

### Update Detection (Protected)
```http
PUT /api/detections/{id}
Authorization: Bearer {token}
Content-Type: application/json

{
    "status": "completed",
    "error_message": null,
    "metadata": {
        "processed_by": "admin",
        "processing_time": 2.5
    }
}
```

**Response:**
```json
{
    "success": true,
    "data": {
        "id": 6,
        "status": "completed",
        "metadata": {
            "processed_by": "admin",
            "processing_time": 2.5
        },
        "updated_at": "2025-10-03T10:57:08.000000Z"
    }
}
```

### Delete Detection (Protected)
```http
DELETE /api/detections/{id}
Authorization: Bearer {token}
```

**Response:**
```json
{
    "success": true,
    "message": "Detection result deleted"
}
```

### Get Detailed Statistics (Protected)
```http
GET /api/detections-statistics
Authorization: Bearer {token}
Accept: application/json
```

**Response:**
```json
{
    "success": true,
    "data": {
        "total_detections": 4,
        "today_detections": 4,
        "completed_detections": 0,
        "failed_detections": 0,
        "pending_detections": 4,
        "processing_detections": 0,
        "last_detection": {
            "id": 6,
            "rvm_id": 1,
            "session_id": "integration_test_001",
            "detected_at": "2025-10-03T10:57:08.000000Z",
            "status": "pending"
        },
        "detections_by_status": {
            "pending": 4
        }
    }
}
```

### Get Recent Detections (Protected)
```http
GET /api/detections-recent
Authorization: Bearer {token}
Accept: application/json
```

**Response:**
```json
{
    "success": true,
    "data": [
        {
            "id": 6,
            "rvm_id": 1,
            "session_id": "integration_test_001",
            "detected_at": "2025-10-03T10:57:08.000000Z",
            "status": "pending",
            "detection_summary": "1 objects detected"
        }
    ]
}
```

## 🔒 Security

### Headers Required
- `Authorization: Bearer {token}` - API token authentication (for protected endpoints)
- `Content-Type: application/json` - For POST/PUT requests
- `Accept: application/json` - For response format

### Error Responses

#### 400 Bad Request
```json
{
    "error": {
        "rvm_id": ["The rvm id field is required."],
        "detection_data": ["The detection data field is required."]
    }
}
```

#### 401 Unauthorized
```json
{
    "success": false,
    "error": "Invalid or expired token",
    "message": "API token is invalid or has expired"
}
```

#### 404 Not Found
```json
{
    "success": false,
    "message": "RVM not found",
    "data": null
}
```

#### 500 Internal Server Error
```json
{
    "success": false,
    "error": "Failed to store detection result",
    "message": "Database connection failed"
}
```

## 📝 Usage Examples

### Python Example - Complete Integration
```python
import requests
import json
from datetime import datetime

# Base URL
BASE_URL = "http://100.123.143.87:8001/api"

# 1. Login and get token
login_response = requests.post(f"{BASE_URL}/login", json={
    "email": "admin@myrvm.com",
    "password": "password"
})
token = login_response.json()['data']['token']

# Headers for authenticated requests
headers = {
    'Authorization': f'Bearer {token}',
    'Content-Type': 'application/json',
    'Accept': 'application/json'
}

# 2. Store detection result
detection_data = {
    "rvm_id": 1,
    "session_id": f"python_test_{datetime.now().strftime('%Y%m%d_%H%M%S')}",
    "detection_data": {
        "detections": [
            {
                "type": "plastic",
                "confidence": 0.95,
                "weight": 0.5,
                "quality_grade": "A"
            }
        ]
    }
}

response = requests.post(f"{BASE_URL}/detections/store", json=detection_data)
print("Detection stored:", response.json())

# 3. Get statistics
stats_response = requests.get(f"{BASE_URL}/detections/statistics")
print("Statistics:", stats_response.json())

# 4. Get RVM info
rvm_response = requests.get(f"{BASE_URL}/rvm/1")
print("RVM Info:", rvm_response.json())
```

### cURL Examples

#### Store Detection Result
```bash
curl -X POST "http://100.123.143.87:8001/api/detections/store" \
  -H "Content-Type: application/json" \
  -d '{
    "rvm_id": 1,
    "session_id": "curl_test_001",
    "detection_data": {
      "detections": [
        {
          "type": "plastic",
          "confidence": 0.95,
          "weight": 0.5
        }
      ]
    }
  }'
```

#### Get Statistics
```bash
curl -X GET "http://100.123.143.87:8001/api/detections/statistics" \
  -H "Accept: application/json"
```

#### Login and Get Token
```bash
curl -X POST "http://100.123.143.87:8001/api/login" \
  -H "Content-Type: application/json" \
  -d '{"email": "admin@myrvm.com", "password": "password"}'
```

#### Get RVMs with Authentication
```bash
curl -X GET "http://100.123.143.87:8001/api/rvms" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Accept: application/json"
```

## 🌐 Network Configuration

### Server IPs
- **MyRVM Server**: `100.123.143.87:8001`
- **Jetson/Edge**: `100.117.234.2`
- **GPU Server**: `100.98.142.94`

### Port Configuration
- **API Port**: 8001
- **Database**: PostgreSQL (internal)
- **Web Interface**: 8001 (same as API)

### CORS Configuration
- **Allowed Origins**: All (*)
- **Allowed Methods**: GET, POST, PUT, DELETE, PATCH
- **Allowed Headers**: Content-Type, Authorization, Accept

## 📊 Performance Metrics

### Response Times (Tested)
- **API Health Check**: < 100ms
- **Detection Store**: < 200ms
- **Statistics**: < 150ms
- **RVM List**: < 300ms
- **Authentication**: < 150ms

### Rate Limits
- **Public Endpoints**: 1000 requests/minute
- **Protected Endpoints**: 500 requests/minute
- **Detection Store**: 100 requests/minute

---

**Documentation Generated:** 2025-10-03  
**API Version:** 2.0  
**Last Updated:** 2025-10-03  
**Status:** ✅ PRODUCTION READY  
**Next Review:** After Jetson integration completion