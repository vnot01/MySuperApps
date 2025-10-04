# 🖥️ SERVER API ENDPOINTS (MyRVM-Ecosystem-v2)

## 📍 Server Information
- **IP**: `100.123.143.87`
- **Port**: `8001`
- **Base URL**: `http://100.123.143.87:8001`
- **Technology**: Laravel 12 + Vue.js 3 + PostgreSQL

---

## 🔐 Authentication Endpoints

### 1. User Login
- **Endpoint**: `POST /api/auth/login`
- **URL**: `http://100.123.143.87:8001/api/auth/login`
- **Content-Type**: `application/json`

**Request Body**:
```json
{
    "email": "admin@myrvm.com",
    "password": "password123"
}
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

**cURL Example**:
```bash
curl -X POST http://100.123.143.87:8001/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "admin@myrvm.com",
    "password": "password123"
  }'
```

### 2. User Logout
- **Endpoint**: `POST /api/auth/logout`
- **URL**: `http://100.123.143.87:8001/api/auth/logout`
- **Headers**: `Authorization: Bearer {token}`

**Response**:
```json
{
    "success": true,
    "message": "Logged out successfully"
}
```

**cURL Example**:
```bash
curl -X POST http://100.123.143.87:8001/api/auth/logout \
  -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."
```

---

## 🏪 RVM Management Endpoints

### 3. Get All RVMs
- **Endpoint**: `GET /api/rvms`
- **URL**: `http://100.123.143.87:8001/api/rvms`
- **Headers**: `Authorization: Bearer {token}`

**Response**:
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "name": "RVM-001",
            "location": "Mall Central",
            "ip_address": "100.117.234.2",
            "status": "active",
            "connection_status": "connected",
            "api_status": "valid",
            "current_load": 45,
            "capacity": 100,
            "usage_percentage": 45.0,
            "last_ping": "2025-01-02T10:30:00Z",
            "api_key": "38bbe1d2ecf75df21546b05340f5878a24e74ed0d8b88d75db1ddeff198380c1"
        }
    ]
}
```

**cURL Example**:
```bash
curl -X GET http://100.123.143.87:8001/api/rvms \
  -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."
```

### 4. Create New RVM
- **Endpoint**: `POST /api/rvms`
- **URL**: `http://100.123.143.87:8001/api/rvms`
- **Headers**: `Authorization: Bearer {token}`
- **Content-Type**: `application/json`

**Request Body**:
```json
{
    "name": "RVM-002",
    "location": "Mall North",
    "ip_address": "100.117.234.3",
    "address": "Jl. Utara No. 123",
    "latitude": -6.200000,
    "longitude": 106.816666
}
```

**Response**:
```json
{
    "success": true,
    "message": "RVM created successfully",
    "data": {
        "id": 2,
        "name": "RVM-002",
        "location": "Mall North",
        "ip_address": "100.117.234.3",
        "api_key": "new_generated_api_key_here",
        "status": "active"
    }
}
```

**cURL Example**:
```bash
curl -X POST http://100.123.143.87:8001/api/rvms \
  -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..." \
  -H "Content-Type: application/json" \
  -d '{
    "name": "RVM-002",
    "location": "Mall North",
    "ip_address": "100.117.234.3",
    "address": "Jl. Utara No. 123",
    "latitude": -6.200000,
    "longitude": 106.816666
  }'
```

### 5. Get RVM Details
- **Endpoint**: `GET /api/rvms/{id}`
- **URL**: `http://100.123.143.87:8001/api/rvms/1`
- **Headers**: `Authorization: Bearer {token}`

**Response**:
```json
{
    "success": true,
    "data": {
        "id": 1,
        "name": "RVM-001",
        "location": "Mall Central",
        "ip_address": "100.117.234.2",
        "address": "Jl. Central No. 456",
        "latitude": -6.200000,
        "longitude": 106.816666,
        "status": "active",
        "connection_status": "connected",
        "api_status": "valid",
        "current_load": 45,
        "capacity": 100,
        "usage_percentage": 45.0,
        "last_ping": "2025-01-02T10:30:00Z",
        "api_key": "38bbe1d2ecf75df21546b05340f5878a24e74ed0d8b88d75db1ddeff198380c1",
        "api_key_expires_at": "2025-02-02T10:30:00Z"
    }
}
```

**cURL Example**:
```bash
curl -X GET http://100.123.143.87:8001/api/rvms/1 \
  -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."
```

### 6. Update RVM
- **Endpoint**: `PUT /api/rvms/{id}`
- **URL**: `http://100.123.143.87:8001/api/rvms/1`
- **Headers**: `Authorization: Bearer {token}`
- **Content-Type**: `application/json`

**Request Body**:
```json
{
    "name": "RVM-001 Updated",
    "location": "Mall Central Updated",
    "ip_address": "100.117.234.2"
}
```

**Response**:
```json
{
    "success": true,
    "message": "RVM updated successfully",
    "data": {
        "id": 1,
        "name": "RVM-001 Updated",
        "location": "Mall Central Updated",
        "ip_address": "100.117.234.2"
    }
}
```

**cURL Example**:
```bash
curl -X PUT http://100.123.143.87:8001/api/rvms/1 \
  -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..." \
  -H "Content-Type: application/json" \
  -d '{
    "name": "RVM-001 Updated",
    "location": "Mall Central Updated",
    "ip_address": "100.117.234.2"
  }'
```

### 7. Update RVM API Settings
- **Endpoint**: `PUT /api/rvms/{id}/api`
- **URL**: `http://100.123.143.87:8001/api/rvms/1/api`
- **Headers**: `Authorization: Bearer {token}`
- **Content-Type**: `application/json`

**Request Body**:
```json
{
    "api_expiration_period": "3_months",
    "regenerate_api_key": true
}
```

**Response**:
```json
{
    "success": true,
    "message": "API settings updated successfully",
    "data": {
        "id": 1,
        "api_key": "new_regenerated_api_key_here",
        "api_key_expires_at": "2025-04-02T10:30:00Z",
        "expiration_period": "3_months"
    }
}
```

**cURL Example**:
```bash
curl -X PUT http://100.123.143.87:8001/api/rvms/1/api \
  -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..." \
  -H "Content-Type: application/json" \
  -d '{
    "api_expiration_period": "3_months",
    "regenerate_api_key": true
  }'
```

### 8. Delete RVM
- **Endpoint**: `DELETE /api/rvms/{id}`
- **URL**: `http://100.123.143.87:8001/api/rvms/1`
- **Headers**: `Authorization: Bearer {token}`

**Response**:
```json
{
    "success": true,
    "message": "RVM deleted successfully"
}
```

**cURL Example**:
```bash
curl -X DELETE http://100.123.143.87:8001/api/rvms/1 \
  -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."
```

---

## 🔍 Detection Results Endpoints

### 9. Get All Detection Results
- **Endpoint**: `GET /api/detection-results`
- **URL**: `http://100.123.143.87:8001/api/detection-results`
- **Headers**: `Authorization: Bearer {token}`
- **Query Parameters**: `?page=1&limit=20&rvm_id=1`

**Response**:
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
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
            "status": "completed",
            "detected_at": "2025-01-02T10:30:00Z"
        }
    ],
    "pagination": {
        "current_page": 1,
        "total_pages": 5,
        "total_items": 95,
        "items_per_page": 20
    }
}
```

**cURL Example**:
```bash
curl -X GET "http://100.123.143.87:8001/api/detection-results?page=1&limit=20&rvm_id=1" \
  -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."
```

### 10. Store Detection Result
- **Endpoint**: `POST /api/detection-results`
- **URL**: `http://100.123.143.87:8001/api/detection-results`
- **Headers**: `Authorization: Bearer {token}`
- **Content-Type**: `application/json`

**Request Body**:
```json
{
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
    "image_path": "/path/to/image.jpg"
}
```

**Response**:
```json
{
    "success": true,
    "message": "Detection result stored successfully",
    "data": {
        "id": 1,
        "rvm_id": 1,
        "session_id": "session_abc123",
        "status": "completed"
    }
}
```

**cURL Example**:
```bash
curl -X POST http://100.123.143.87:8001/api/detection-results \
  -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..." \
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
    "image_path": "/path/to/image.jpg"
  }'
```

---

## 💰 Economy System Endpoints

### 11. Get User Balance
- **Endpoint**: `GET /api/economy/balance`
- **URL**: `http://100.123.143.87:8001/api/economy/balance`
- **Headers**: `Authorization: Bearer {token}`

**Response**:
```json
{
    "success": true,
    "data": {
        "user_id": 1,
        "balance": 150.50,
        "currency": "IDR",
        "formatted_balance": "Rp 150.50"
    }
}
```

**cURL Example**:
```bash
curl -X GET http://100.123.143.87:8001/api/economy/balance \
  -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."
```

### 12. Add Balance
- **Endpoint**: `POST /api/economy/balance/add`
- **URL**: `http://100.123.143.87:8001/api/economy/balance/add`
- **Headers**: `Authorization: Bearer {token}`
- **Content-Type**: `application/json`

**Request Body**:
```json
{
    "amount": 25.00,
    "description": "Reward for plastic bottle deposit"
}
```

**Response**:
```json
{
    "success": true,
    "message": "Balance added successfully",
    "data": {
        "transaction_id": 1,
        "new_balance": 175.50,
        "amount_added": 25.00
    }
}
```

**cURL Example**:
```bash
curl -X POST http://100.123.143.87:8001/api/economy/balance/add \
  -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..." \
  -H "Content-Type: application/json" \
  -d '{
    "amount": 25.00,
    "description": "Reward for plastic bottle deposit"
  }'
```

### 13. Get Transactions
- **Endpoint**: `GET /api/economy/transactions`
- **URL**: `http://100.123.143.87:8001/api/economy/transactions`
- **Headers**: `Authorization: Bearer {token}`
- **Query Parameters**: `?page=1&limit=20&type=credit`

**Response**:
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "type": "credit",
            "amount": 25.00,
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

**cURL Example**:
```bash
curl -X GET "http://100.123.143.87:8001/api/economy/transactions?page=1&limit=20&type=credit" \
  -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."
```

### 14. Get Available Vouchers
- **Endpoint**: `GET /api/economy/vouchers`
- **URL**: `http://100.123.143.87:8001/api/economy/vouchers`
- **Headers**: `Authorization: Bearer {token}`

**Response**:
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "code": "WELCOME10",
            "name": "Welcome Discount",
            "description": "10% discount for new users",
            "type": "percentage",
            "value": 10.0,
            "min_purchase_amount": 50.0,
            "expires_at": "2025-12-31T23:59:59Z",
            "is_active": true
        }
    ]
}
```

**cURL Example**:
```bash
curl -X GET http://100.123.143.87:8001/api/economy/vouchers \
  -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."
```

### 15. Redeem Voucher
- **Endpoint**: `POST /api/economy/vouchers/redeem`
- **URL**: `http://100.123.143.87:8001/api/economy/vouchers/redeem`
- **Headers**: `Authorization: Bearer {token}`
- **Content-Type**: `application/json`

**Request Body**:
```json
{
    "voucher_code": "WELCOME10",
    "purchase_amount": 100.0
}
```

**Response**:
```json
{
    "success": true,
    "message": "Voucher redeemed successfully",
    "data": {
        "voucher_id": 1,
        "discount_amount": 10.0,
        "final_amount": 90.0,
        "transaction_id": 2
    }
}
```

**cURL Example**:
```bash
curl -X POST http://100.123.143.87:8001/api/economy/vouchers/redeem \
  -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..." \
  -H "Content-Type: application/json" \
  -d '{
    "voucher_code": "WELCOME10",
    "purchase_amount": 100.0
  }'
```

---

## 📊 Analytics Endpoints

### 16. Get Dashboard Analytics
- **Endpoint**: `GET /api/analytics/dashboard`
- **URL**: `http://100.123.143.87:8001/api/analytics/dashboard`
- **Headers**: `Authorization: Bearer {token}`
- **Query Parameters**: `?period=7d`

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

**cURL Example**:
```bash
curl -X GET "http://100.123.143.87:8001/api/analytics/dashboard?period=7d" \
  -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."
```

### 17. Get RVM Analytics
- **Endpoint**: `GET /api/analytics/rvm/{id}`
- **URL**: `http://100.123.143.87:8001/api/analytics/rvm/1`
- **Headers**: `Authorization: Bearer {token}`
- **Query Parameters**: `?period=30d`

**Response**:
```json
{
    "success": true,
    "data": {
        "rvm_id": 1,
        "name": "RVM-001",
        "performance": {
            "total_detections": 450,
            "average_processing_time": 2.5,
            "error_rate": 0.02,
            "uptime_percentage": 98.5
        },
        "detection_analytics": {
            "by_class": {
                "plastic_bottle": 200,
                "glass_bottle": 150,
                "aluminum_can": 100
            },
            "trends": {
                "daily": [10, 15, 20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
                "hourly": [5, 8, 12, 15, 18, 22, 25, 28, 30, 32, 35, 38]
            }
        },
        "revenue_analytics": {
            "total_revenue": 4500.0,
            "average_per_detection": 10.0,
            "trends": {
                "daily": [100, 150, 200, 250, 300, 350, 400, 450, 500, 550, 600, 650]
            }
        }
    }
}
```

**cURL Example**:
```bash
curl -X GET "http://100.123.143.87:8001/api/analytics/rvm/1?period=30d" \
  -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."
```

---

## 🔄 RVM Integration Endpoints

### 18. Validate RVM API Key
- **Endpoint**: `POST /api/rvm/validate-api-key`
- **URL**: `http://100.123.143.87:8001/api/rvm/validate-api-key`
- **Headers**: `Authorization: Bearer {master_api_key}`
- **Content-Type**: `application/json`

**Request Body**:
```json
{
    "api_key": "38bbe1d2ecf75df21546b05340f5878a24e74ed0d8b88d75db1ddeff198380c1"
}
```

**Response**:
```json
{
    "success": true,
    "data": {
        "id": 1,
        "name": "RVM-001",
        "location": "Mall Central",
        "status": "active",
        "api_key_valid": true
    }
}
```

**cURL Example**:
```bash
curl -X POST http://100.123.143.87:8001/api/rvm/validate-api-key \
  -H "Authorization: Bearer master_api_key_here" \
  -H "Content-Type: application/json" \
  -d '{
    "api_key": "38bbe1d2ecf75df21546b05340f5878a24e74ed0d8b88d75db1ddeff198380c1"
  }'
```

### 19. Get RVM Information
- **Endpoint**: `GET /api/rvm/{id}`
- **URL**: `http://100.123.143.87:8001/api/rvm/1`
- **Headers**: `Authorization: Bearer {master_api_key}`

**Response**:
```json
{
    "success": true,
    "data": {
        "id": 1,
        "name": "RVM-001",
        "location": "Mall Central",
        "ip_address": "100.117.234.2",
        "status": "active",
        "current_load": 45,
        "capacity": 100,
        "api_key": "38bbe1d2ecf75df21546b05340f5878a24e74ed0d8b88d75db1ddeff198380c1"
    }
}
```

**cURL Example**:
```bash
curl -X GET http://100.123.143.87:8001/api/rvm/1 \
  -H "Authorization: Bearer master_api_key_here"
```

### 20. Store Detection from RVM
- **Endpoint**: `POST /api/detections/store`
- **URL**: `http://100.123.143.87:8001/api/detections/store`
- **Headers**: `Authorization: Bearer {master_api_key}`
- **Content-Type**: `application/json`

**Request Body**:
```json
{
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
}
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

**cURL Example**:
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

### 21. Get RVM Statistics
- **Endpoint**: `GET /api/rvm/{id}/stats`
- **URL**: `http://100.123.143.87:8001/api/rvm/1/stats`
- **Headers**: `Authorization: Bearer {master_api_key}`

**Response**:
```json
{
    "success": true,
    "data": {
        "rvm_id": 1,
        "total_detections": 450,
        "total_sessions": 150,
        "average_processing_time": 2.5,
        "error_rate": 0.02,
        "last_activity": "2025-01-02T10:30:00Z"
    }
}
```

**cURL Example**:
```bash
curl -X GET http://100.123.143.87:8001/api/rvm/1/stats \
  -H "Authorization: Bearer master_api_key_here"
```

### 22. Update RVM Status
- **Endpoint**: `POST /api/rvm/{id}/status`
- **URL**: `http://100.123.143.87:8001/api/rvm/1/status`
- **Headers**: `Authorization: Bearer {master_api_key}`
- **Content-Type**: `application/json`

**Request Body**:
```json
{
    "status": "active",
    "current_load": 45,
    "last_ping": "2025-01-02T10:30:00Z"
}
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

**cURL Example**:
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

---

## 🔄 Status Check Endpoints

### 23. Check RVM Status
- **Endpoint**: `POST /api/rvm/check-status`
- **URL**: `http://100.123.143.87:8001/api/rvm/check-status`
- **Headers**: `Authorization: Bearer {token}`

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
            }
        ]
    }
}
```

**cURL Example**:
```bash
curl -X POST http://100.123.143.87:8001/api/rvm/check-status \
  -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."
```

---

## 📝 Error Response Format

All endpoints return consistent error format:

```json
{
    "success": false,
    "error": "Error message",
    "code": "ERROR_CODE",
    "details": {
        "field": "validation error details"
    }
}
```

**Common Error Codes**:
- `UNAUTHORIZED`: Invalid or missing authentication
- `FORBIDDEN`: Insufficient permissions
- `NOT_FOUND`: Resource not found
- `VALIDATION_ERROR`: Invalid request data
- `SERVER_ERROR`: Internal server error

---

**Last Updated**: 2025-01-02  
**Version**: 2.0.0  
**Status**: ✅ COMPLETE SERVER API DOCUMENTATION
