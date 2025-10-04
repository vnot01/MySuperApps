# 🔄 Integration Flow Examples - MyRVM-Ecosystem v2.0

## 📍 Network Configuration
- **Server**: `100.123.143.87:8001` (MyRVM-Ecosystem-v2)
- **Jetson**: `100.117.234.2:5000` (MyCV-Platform)
- **Multi-RVM**: `100.117.234.X:5000` (Scalable RVM Network)

---

## 🔄 Complete Detection Flow

### 1. Upload Images to Jetson
**Step 1**: Upload images to Jetson for processing

```bash
curl -X POST http://100.117.234.2:5000/api/upload \
  -H "X-RVM-API-Key: 38bbe1d2ecf75df21546b05340f5878a24e74ed0d8b88d75db1ddeff198380c1" \
  -F "files=@image1.jpg" \
  -F "files=@image2.jpg" \
  -F "user_id=my_user" \
  -F "rvm_id=1"
```

**Response**:
```json
{
    "success": true,
    "session_id": "session_abc123",
    "timestamp": "20250102_103000",
    "user_id": "my_user",
    "message": "Files uploaded successfully. Processing started.",
    "status_url": "/api/process/session_abc123",
    "results_url": "/api/results/session_abc123",
    "rvm": {
        "id": 1,
        "name": "RVM-001",
        "location": "Mall Central"
    }
}
```

### 2. Check Processing Status
**Step 2**: Monitor processing status

```bash
curl -X GET http://100.117.234.2:5000/api/process/session_abc123
```

**Response**:
```json
{
    "status": "processing",
    "message": "Starting detection process...",
    "timestamp": "20250102_103000",
    "user_id": "my_user",
    "rvm_id": 1,
    "start_time": "2025-01-02T10:30:00Z"
}
```

### 3. Get Detection Results
**Step 3**: Retrieve detection results

```bash
curl -X GET http://100.117.234.2:5000/api/results/session_abc123
```

**Response**:
```json
{
    "results": {
        "detection_summary": {
            "total_objects": 3,
            "classes_detected": ["plastic_bottle", "glass_bottle", "aluminum_can"],
            "confidence_scores": [0.95, 0.87, 0.92]
        },
        "class_summary": {
            "plastic_bottle": 1,
            "glass_bottle": 1,
            "aluminum_can": 1
        },
        "object_count": 3,
        "images_processed": [
            {
                "image_name": "image1",
                "detections": [
                    {
                        "class_name": "plastic_bottle",
                        "confidence": 0.95,
                        "bbox": [100, 200, 300, 400]
                    }
                ],
                "detection_count": 1
            }
        ]
    },
    "session_id": "session_abc123",
    "status": "completed"
}
```

### 4. Store Results to Server
**Step 4**: Store detection results in server database

```bash
curl -X POST http://100.123.143.87:8001/api/detections/store \
  -H "Authorization: Bearer master_api_key_here" \
  -H "Content-Type: application/json" \
  -d '{
    "rvm_id": 1,
    "session_id": "session_abc123",
    "detection_data": {
        "objects": [
            {
                "class_name": "plastic_bottle",
                "confidence": 0.95,
                "bbox": [100, 200, 300, 400]
            }
        ]
    },
    "image_path": "/path/to/image.jpg",
    "detected_at": "2025-01-02T10:30:00Z"
  }'
```

**Response**:
```json
{
    "success": true,
    "message": "Detection stored successfully",
    "data": {
        "id": 1,
        "rvm_id": 1,
        "session_id": "session_abc123"
    }
}
```

### 5. Update RVM Status
**Step 5**: Update RVM status and metrics

```bash
curl -X POST http://100.123.143.87:8001/api/rvm/1/status \
  -H "Authorization: Bearer master_api_key_here" \
  -H "Content-Type: application/json" \
  -d '{
    "status": "active",
    "current_load": 45,
    "last_ping": "2025-01-02T10:30:00Z"
  }'
```

**Response**:
```json
{
    "success": true,
    "message": "RVM status updated successfully",
    "data": {
        "rvm_id": 1,
        "status": "active",
        "current_load": 45,
        "last_ping": "2025-01-02T10:30:00Z"
    }
}
```

---

## 💰 Economy System Flow

### 1. Calculate Reward
**Step 1**: Calculate reward based on detection results

```bash
curl -X POST http://100.123.143.87:8001/api/economy/calculate-reward \
  -H "Authorization: Bearer user_token_here" \
  -H "Content-Type: application/json" \
  -d '{
    "waste_type": "plastic_bottle",
    "weight": 0.5,
    "quality_grade": "A",
    "confidence": 0.95
  }'
```

**Response**:
```json
{
    "success": true,
    "data": {
        "waste_type": "plastic_bottle",
        "weight": 0.5,
        "quality_grade": "A",
        "confidence": 0.95,
        "reward_amount": 25.0,
        "currency": "IDR",
        "formatted_reward": "Rp 25.00"
    }
}
```

### 2. Add Balance
**Step 2**: Add reward to user balance

```bash
curl -X POST http://100.123.143.87:8001/api/economy/balance/add \
  -H "Authorization: Bearer user_token_here" \
  -H "Content-Type: application/json" \
  -d '{
    "amount": 25.0,
    "description": "Reward for plastic bottle deposit"
  }'
```

**Response**:
```json
{
    "success": true,
    "message": "Balance added successfully",
    "data": {
        "transaction_id": 1,
        "new_balance": 175.50,
        "amount_added": 25.0
    }
}
```

### 3. Get User Transactions
**Step 3**: Retrieve user transaction history

```bash
curl -X GET "http://100.123.143.87:8001/api/economy/transactions?page=1&limit=20" \
  -H "Authorization: Bearer user_token_here"
```

**Response**:
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "type": "credit",
            "amount": 25.0,
            "balance_before": 150.50,
            "new_balance": 175.50,
            "description": "Reward for plastic bottle deposit",
            "created_at": "2025-01-02T10:30:00Z"
        }
    ],
    "pagination": {
        "current_page": 1,
        "total_pages": 3,
        "total_items": 45,
        "items_per_page": 20
    }
}
```

---

## 📊 Monitoring Flow

### 1. Check Jetson Status
**Step 1**: Monitor Jetson system status

```bash
curl -X GET http://100.117.234.2:5000/api/monitoring/status
```

**Response**:
```json
{
    "status": "success",
    "monitoring": {
        "current_metrics": {
            "timestamp": "2025-01-02T10:30:00Z",
            "cpu_percent": 45.2,
            "memory_percent": 67.8,
            "gpu_memory_percent": 23.4,
            "disk_usage_percent": 40.0,
            "processing_time_ms": 2500.0,
            "detections_count": 15,
            "error_count": 0,
            "api_requests_count": 25
        },
        "performance_summary": {
            "average_cpu_usage": 42.5,
            "average_memory_usage": 65.2,
            "average_gpu_usage": 20.8,
            "total_detections": 150,
            "success_rate": 98.5,
            "average_processing_time": 2300.0
        },
        "recent_alerts": [
            {
                "timestamp": "2025-01-02T10:25:00Z",
                "level": "warning",
                "message": "High memory usage detected",
                "metric": "memory_percent",
                "value": 85.2,
                "threshold": 80.0
            }
        ]
    }
}
```

### 2. Get Performance Summary
**Step 2**: Get detailed performance analytics

```bash
curl -X GET "http://100.117.234.2:5000/api/monitoring/summary?hours=24"
```

**Response**:
```json
{
    "status": "success",
    "summary": {
        "period_hours": 24,
        "total_detections": 150,
        "success_rate": 98.5,
        "average_processing_time": 2300.0,
        "cpu_usage": {
            "average": 42.5,
            "peak": 78.2,
            "min": 15.3
        },
        "memory_usage": {
            "average": 65.2,
            "peak": 85.2,
            "min": 45.1
        },
        "gpu_usage": {
            "average": 20.8,
            "peak": 45.6,
            "min": 5.2
        },
        "alerts_count": 3,
        "error_rate": 0.015
    }
}
```

### 3. Check Server Analytics
**Step 3**: Get server-side analytics

```bash
curl -X GET "http://100.123.143.87:8001/api/analytics/dashboard?period=7d" \
  -H "Authorization: Bearer admin_token_here"
```

**Response**:
```json
{
    "success": true,
    "data": {
        "overview": {
            "total_rvms": 3,
            "active_rvms": 2,
            "total_detections": 1250,
            "total_revenue": 12500.0
        },
        "rvm_performance": [
            {
                "rvm_id": 1,
                "name": "RVM-001",
                "detections_count": 450,
                "revenue": 4500.0,
                "uptime_percentage": 98.5
            }
        ],
        "detection_analytics": {
            "by_class": {
                "plastic_bottle": 600,
                "glass_bottle": 300,
                "aluminum_can": 350
            },
            "by_hour": [10, 15, 20, 25, 30, 35, 40, 45, 50, 55, 60, 65]
        }
    }
}
```

---

## 🔐 Authentication Flow

### 1. User Login
**Step 1**: Authenticate user

```bash
curl -X POST http://100.123.143.87:8001/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "admin@myrvm.com",
    "password": "password123"
  }'
```

**Response**:
```json
{
    "success": true,
    "user": {
        "id": 1,
        "name": "Admin User",
        "email": "admin@myrvm.com",
        "role": "admin"
    },
    "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."
}
```

### 2. Validate RVM API Key
**Step 2**: Validate RVM API key for Jetson operations

```bash
curl -X POST http://100.117.234.2:5000/api/rvm/validate \
  -H "Content-Type: application/json" \
  -d '{
    "api_key": "38bbe1d2ecf75df21546b05340f5878a24e74ed0d8b88d75db1ddeff198380c1"
  }'
```

**Response**:
```json
{
    "valid": true,
    "rvm": {
        "id": 1,
        "name": "RVM-001",
        "location": "Mall Central",
        "status": "active"
    }
}
```

---

## 🔄 Multi-RVM Operations

### 1. Check All RVM Status
**Step 1**: Check status of all RVMs

```bash
curl -X POST http://100.123.143.87:8001/api/rvm/check-status \
  -H "Authorization: Bearer admin_token_here"
```

**Response**:
```json
{
    "success": true,
    "data": {
        "rvms_checked": 3,
        "online_rvms": 2,
        "offline_rvms": 1,
        "results": [
            {
                "rvm_id": 1,
                "name": "RVM-001",
                "connection_status": "connected",
                "api_status": "valid",
                "last_ping": "2025-01-02T10:30:00Z"
            },
            {
                "rvm_id": 2,
                "name": "RVM-002",
                "connection_status": "disconnected",
                "api_status": "invalid",
                "last_ping": "2025-01-01T15:30:00Z"
            }
        ]
    }
}
```

### 2. Upload to Specific RVM
**Step 2**: Upload images to specific RVM

```bash
# RVM-001
curl -X POST http://100.117.234.2:5000/api/upload \
  -H "X-RVM-API-Key: rvm1_api_key" \
  -F "files=@image1.jpg" \
  -F "user_id=my_user" \
  -F "rvm_id=1"

# RVM-002 (when available)
curl -X POST http://100.117.234.3:5000/api/upload \
  -H "X-RVM-API-Key: rvm2_api_key" \
  -F "files=@image1.jpg" \
  -F "user_id=my_user" \
  -F "rvm_id=2"
```

---

## 🧪 Testing Scripts

### Complete Integration Test
```bash
#!/bin/bash

# Test complete integration flow
echo "🧪 Testing Complete Integration Flow..."

# 1. Test Server Health
echo "1. Testing Server Health..."
curl -X GET http://100.123.143.87:8001/api/health

# 2. Test Jetson Health
echo "2. Testing Jetson Health..."
curl -X GET http://100.117.234.2:5000/api/health

# 3. Test Authentication
echo "3. Testing Authentication..."
TOKEN=$(curl -s -X POST http://100.123.143.87:8001/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@myrvm.com","password":"password123"}' | jq -r '.token')

# 4. Test RVM List
echo "4. Testing RVM List..."
curl -X GET http://100.123.143.87:8001/api/rvms \
  -H "Authorization: Bearer $TOKEN"

# 5. Test Upload to Jetson
echo "5. Testing Upload to Jetson..."
curl -X POST http://100.117.234.2:5000/api/upload \
  -H "X-RVM-API-Key: 38bbe1d2ecf75df21546b05340f5878a24e74ed0d8b88d75db1ddeff198380c1" \
  -F "files=@test_image.jpg" \
  -F "user_id=test_user" \
  -F "rvm_id=1"

echo "✅ Integration test completed!"
```

### Performance Test
```bash
#!/bin/bash

# Test system performance
echo "📊 Testing System Performance..."

# 1. Test Jetson Monitoring
echo "1. Testing Jetson Monitoring..."
curl -X GET http://100.117.234.2:5000/api/monitoring/status

# 2. Test Performance Summary
echo "2. Testing Performance Summary..."
curl -X GET "http://100.117.234.2:5000/api/monitoring/summary?hours=1"

# 3. Test Server Analytics
echo "3. Testing Server Analytics..."
curl -X GET "http://100.123.143.87:8001/api/analytics/dashboard?period=1d" \
  -H "Authorization: Bearer $TOKEN"

echo "✅ Performance test completed!"
```

---

## 📝 Error Handling Examples

### Common Error Scenarios

#### 1. Invalid Authentication
```json
{
    "success": false,
    "error": "Unauthorized",
    "code": "UNAUTHORIZED",
    "message": "Invalid or missing authentication token"
}
```

#### 2. RVM Not Found
```json
{
    "success": false,
    "error": "RVM not found",
    "code": "NOT_FOUND",
    "message": "RVM with ID 999 does not exist"
}
```

#### 3. Processing Error
```json
{
    "success": false,
    "error": "Processing failed",
    "code": "PROCESSING_ERROR",
    "message": "Failed to process image: Invalid format"
}
```

#### 4. Validation Error
```json
{
    "success": false,
    "error": "Validation failed",
    "code": "VALIDATION_ERROR",
    "details": {
        "email": "The email field is required",
        "password": "The password field is required"
    }
}
```

---

## 🔧 Configuration Examples

### Server Configuration
```bash
# .env file for MyRVM-Ecosystem-v2
APP_NAME="MyRVM-Ecosystem-v2"
APP_URL="http://100.123.143.87:8001"
DB_HOST=postgres
DB_PORT=5432
DB_DATABASE=myrvm_ecosystem
DB_USERNAME=myrvm_user
DB_PASSWORD=myrvm_password
```

### Jetson Configuration
```bash
# rvm_config.env file for MyCV-Platform
RVM_API_BASE_URL=http://100.123.143.87:8001/api
RVM_API_KEY=38bbe1d2ecf75df21546b05340f5878a24e74ed0d8b88d75db1ddeff198380c1
API_HOST=100.117.234.2
API_PORT=5000
API_DEBUG=false
```

---

**Last Updated**: 2025-01-02  
**Version**: 2.0.0  
**Status**: ✅ COMPLETE INTEGRATION FLOW DOCUMENTATION
