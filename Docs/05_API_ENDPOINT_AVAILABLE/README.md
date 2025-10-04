# 📡 API Endpoints Documentation - MyRVM-Ecosystem v2.0

## 📚 Documentation Index

### 📖 [INDEX.md](./INDEX.md) - Complete Documentation Index
### 🖥️ [SERVER_API_ENDPOINTS.md](./SERVER_API_ENDPOINTS.md) - Server API Details
### 🤖 [JETSON_API_ENDPOINTS.md](./JETSON_API_ENDPOINTS.md) - Jetson API Details
### 🔄 [INTEGRATION_FLOW_EXAMPLES.md](./INTEGRATION_FLOW_EXAMPLES.md) - Integration Examples
### 🧪 [TESTING_AND_TROUBLESHOOTING.md](./TESTING_AND_TROUBLESHOOTING.md) - Testing Guide
### 📮 [POSTMAN_COLLECTION.md](./POSTMAN_COLLECTION.md) - Postman Collection
### 📋 [OPENAPI_SPECIFICATION.md](./OPENAPI_SPECIFICATION.md) - OpenAPI/Swagger Specs

---

## 🌐 Network Configuration

### Server (MyRVM-Ecosystem-v2)
- **IP**: `100.123.143.87`
- **Port**: `8001`
- **Base URL**: `http://100.123.143.87:8001`
- **Technology**: Laravel 12 + Vue.js 3 + PostgreSQL

### Jetson (MyCV-Platform) - RVM Device
- **IP**: `100.117.234.2`
- **Port**: `5000`
- **Base URL**: `http://100.117.234.2:5000`
- **Technology**: Python + Flask + YOLO + SAM2

### GPU Server (CV Host)
- **IP**: `100.98.142.94`
- **Port**: `TBD`
- **Technology**: Heavy GPU Processing

### Multi-RVM Support
- **RVM-001**: `100.117.234.2:5000` (Primary Jetson)
- **RVM-002**: `100.117.234.3:5000` (Additional Jetson - Future)
- **RVM-003**: `100.117.234.4:5000` (Additional Jetson - Future)
- **RVM-XXX**: `100.117.234.X:5000` (Scalable RVM Network)

---

## 🖥️ SERVER API ENDPOINTS (MyRVM-Ecosystem-v2)

### 🔐 Authentication Endpoints

#### 1. User Login
- **Endpoint**: `POST /api/auth/login`
- **URL**: `http://100.123.143.87:8001/api/auth/login`
- **Payload**:
```json
{
    "email": "admin@myrvm.com",
    "password": "password123"
}
```
- **Response**:
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

#### 2. User Logout
- **Endpoint**: `POST /api/auth/logout`
- **URL**: `http://100.123.143.87:8001/api/auth/logout`
- **Headers**: `Authorization: Bearer {token}`
- **Response**:
```json
{
    "success": true,
    "message": "Logged out successfully"
}
```

### 🏪 RVM Management Endpoints

#### 3. Get All RVMs
- **Endpoint**: `GET /api/rvms`
- **URL**: `http://100.123.143.87:8001/api/rvms`
- **Headers**: `Authorization: Bearer {token}`
- **Response**:
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

#### 4. Create New RVM
- **Endpoint**: `POST /api/rvms`
- **URL**: `http://100.123.143.87:8001/api/rvms`
- **Headers**: `Authorization: Bearer {token}`
- **Payload**:
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
- **Response**:
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

#### 5. Get RVM Details
- **Endpoint**: `GET /api/rvms/{id}`
- **URL**: `http://100.123.143.87:8001/api/rvms/1`
- **Headers**: `Authorization: Bearer {token}`
- **Response**:
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

#### 6. Update RVM
- **Endpoint**: `PUT /api/rvms/{id}`
- **URL**: `http://100.123.143.87:8001/api/rvms/1`
- **Headers**: `Authorization: Bearer {token}`
- **Payload**:
```json
{
    "name": "RVM-001 Updated",
    "location": "Mall Central Updated",
    "ip_address": "100.117.234.2"
}
```
- **Response**:
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

#### 7. Update RVM API Settings
- **Endpoint**: `PUT /api/rvms/{id}/api`
- **URL**: `http://100.123.143.87:8001/api/rvms/1/api`
- **Headers**: `Authorization: Bearer {token}`
- **Payload**:
```json
{
    "api_expiration_period": "3_months",
    "regenerate_api_key": true
}
```
- **Response**:
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

#### 8. Delete RVM
- **Endpoint**: `DELETE /api/rvms/{id}`
- **URL**: `http://100.123.143.87:8001/api/rvms/1`
- **Headers**: `Authorization: Bearer {token}`
- **Response**:
```json
{
    "success": true,
    "message": "RVM deleted successfully"
}
```

### 🔍 Detection Results Endpoints

#### 9. Get All Detection Results
- **Endpoint**: `GET /api/detection-results`
- **URL**: `http://100.123.143.87:8001/api/detection-results`
- **Headers**: `Authorization: Bearer {token}`
- **Query Parameters**: `?page=1&limit=20&rvm_id=1`
- **Response**:
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

#### 10. Store Detection Result
- **Endpoint**: `POST /api/detection-results`
- **URL**: `http://100.123.143.87:8001/api/detection-results`
- **Headers**: `Authorization: Bearer {token}`
- **Payload**:
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
- **Response**:
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

### 💰 Economy System Endpoints

#### 11. Get User Balance
- **Endpoint**: `GET /api/economy/balance`
- **URL**: `http://100.123.143.87:8001/api/economy/balance`
- **Headers**: `Authorization: Bearer {token}`
- **Response**:
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

#### 12. Add Balance
- **Endpoint**: `POST /api/economy/balance/add`
- **URL**: `http://100.123.143.87:8001/api/economy/balance/add`
- **Headers**: `Authorization: Bearer {token}`
- **Payload**:
```json
{
    "amount": 25.00,
    "description": "Reward for plastic bottle deposit"
}
```
- **Response**:
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

#### 13. Get Transactions
- **Endpoint**: `GET /api/economy/transactions`
- **URL**: `http://100.123.143.87:8001/api/economy/transactions`
- **Headers**: `Authorization: Bearer {token}`
- **Query Parameters**: `?page=1&limit=20&type=credit`
- **Response**:
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

#### 14. Get Available Vouchers
- **Endpoint**: `GET /api/economy/vouchers`
- **URL**: `http://100.123.143.87:8001/api/economy/vouchers`
- **Headers**: `Authorization: Bearer {token}`
- **Response**:
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

#### 15. Redeem Voucher
- **Endpoint**: `POST /api/economy/vouchers/redeem`
- **URL**: `http://100.123.143.87:8001/api/economy/vouchers/redeem`
- **Headers**: `Authorization: Bearer {token}`
- **Payload**:
```json
{
    "voucher_code": "WELCOME10",
    "purchase_amount": 100.0
}
```
- **Response**:
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

### 📊 Analytics Endpoints

#### 16. Get Dashboard Analytics
- **Endpoint**: `GET /api/analytics/dashboard`
- **URL**: `http://100.123.143.87:8001/api/analytics/dashboard`
- **Headers**: `Authorization: Bearer {token}`
- **Query Parameters**: `?period=7d`
- **Response**:
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

#### 17. Get RVM Analytics
- **Endpoint**: `GET /api/analytics/rvm/{id}`
- **URL**: `http://100.123.143.87:8001/api/analytics/rvm/1`
- **Headers**: `Authorization: Bearer {token}`
- **Query Parameters**: `?period=30d`
- **Response**:
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

### 🔄 RVM Integration Endpoints

#### 18. Validate RVM API Key
- **Endpoint**: `POST /api/rvm/validate-api-key`
- **URL**: `http://100.123.143.87:8001/api/rvm/validate-api-key`
- **Headers**: `Authorization: Bearer {master_api_key}`
- **Payload**:
```json
{
    "api_key": "38bbe1d2ecf75df21546b05340f5878a24e74ed0d8b88d75db1ddeff198380c1"
}
```
- **Response**:
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

#### 19. Get RVM Information
- **Endpoint**: `GET /api/rvm/{id}`
- **URL**: `http://100.123.143.87:8001/api/rvm/1`
- **Headers**: `Authorization: Bearer {master_api_key}`
- **Response**:
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

#### 20. Store Detection from RVM
- **Endpoint**: `POST /api/detections/store`
- **URL**: `http://100.123.143.87:8001/api/detections/store`
- **Headers**: `Authorization: Bearer {master_api_key}`
- **Payload**:
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
- **Response**:
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

#### 21. Get RVM Statistics
- **Endpoint**: `GET /api/rvm/{id}/stats`
- **URL**: `http://100.123.143.87:8001/api/rvm/1/stats`
- **Headers**: `Authorization: Bearer {master_api_key}`
- **Response**:
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

#### 22. Update RVM Status
- **Endpoint**: `POST /api/rvm/{id}/status`
- **URL**: `http://100.123.143.87:8001/api/rvm/1/status`
- **Headers**: `Authorization: Bearer {master_api_key}`
- **Payload**:
```json
{
    "status": "active",
    "current_load": 45,
    "last_ping": "2025-01-02T10:30:00Z"
}
```
- **Response**:
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

### 🔄 Status Check Endpoints

#### 23. Check RVM Status
- **Endpoint**: `POST /api/rvm/check-status`
- **URL**: `http://100.123.143.87:8001/api/rvm/check-status`
- **Headers**: `Authorization: Bearer {token}`
- **Response**:
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

---

## 🤖 JETSON API ENDPOINTS (MyCV-Platform)

### 🏥 Health & Status Endpoints

#### 1. Health Check
- **Endpoint**: `GET /api/health`
- **URL**: `http://100.117.234.2:5000/api/health`
- **Response**:
```json
{
    "status": "healthy",
    "service": "MyCV-Edge-API",
    "version": "1.0.0",
    "timestamp": "2025-01-02T10:30:00Z",
    "uptime": 3600
}
```

#### 2. API Status
- **Endpoint**: `GET /api/status`
- **URL**: `http://100.117.234.2:5000/api/status`
- **Response**:
```json
{
    "api_status": "online",
    "service": "MyCV-Edge-API",
    "version": "1.0.0",
    "endpoints": [
        "/api/health",
        "/api/status",
        "/api/hardware",
        "/api/upload",
        "/api/process/<session_id>",
        "/api/results/<session_id>",
        "/api/download/<session_id>/<filename>",
        "/api/detections"
    ],
    "timestamp": "2025-01-02T10:30:00Z",
    "gpu_info": {
        "status": "success",
        "cuda_available": true,
        "cudnn_enabled": true,
        "available_gpus": 1,
        "gpus": [
            {
                "id": 0,
                "name": "Orin",
                "memory_gb": 7.4
            }
        ],
        "total_memory_all_gpus_gb": 7.4
    },
    "total_sessions_processed": 150
}
```

#### 3. Hardware Information
- **Endpoint**: `GET /api/hardware`
- **URL**: `http://100.117.234.2:5000/api/hardware`
- **Response**:
```json
{
    "status": "success",
    "service": "MyCV-Edge-API",
    "hardware_info": {
        "jetson_info": {
            "model": "Jetson Orin Nano",
            "l4t_version": "R36.4.2",
            "jetpack_version": "6.1",
            "kernel_version": "5.10.120-tegra",
            "architecture": "aarch64"
        },
        "cuda_info": {
            "status": "success",
            "cuda_available": true,
            "cudnn_enabled": true,
            "pytorch_cuda_version": "2.5.0a0+872d972e41.nv24.08",
            "available_gpus": 1,
            "gpus": [
                {
                    "id": 0,
                    "name": "Orin",
                    "memory_gb": 7.44
                }
            ],
            "total_memory_all_gpus_gb": 7.44
        },
        "memory_info": {
            "total_gb": 23.44
        },
        "disk_info": {
            "available": "134G",
            "size": "233G",
            "used": "88G",
            "use_percent": "40%"
        },
        "camera_info": {
            "usb_cameras": [
                {
                    "device": "/dev/video3",
                    "name": "Integrated_Webcam_HD: Integrate (usb-3610000.usb-2.4)"
                }
            ],
            "usb_devices": [
                {
                    "device_id": "0c45:64ab",
                    "name": "Microdia Integrated_Webcam_HD",
                    "raw_line": "Bus 001 Device 004: ID 0c45:64ab Microdia Integrated_Webcam_HD"
                }
            ],
            "nvargus_status": "active"
        },
        "network_info": {
            "local_ip": "192.168.1.11",
            "network_ip": "100.117.234.2",
            "public_ip": "182.8.226.98",
            "network_connected": true,
            "tailscale_ip": [
                "100.117.234.2/32",
                "fd7a:115c:a1e0::1f35:ea02/128"
            ]
        },
        "updated_at": "2025-01-02T10:30:00Z"
    }
}
```

### 📊 Advanced Monitoring Endpoints

#### 4. Monitoring Status
- **Endpoint**: `GET /api/monitoring/status`
- **URL**: `http://100.117.234.2:5000/api/monitoring/status`
- **Response**:
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
        ],
        "timestamp": "2025-01-02T10:30:00Z"
    }
}
```

#### 5. Performance Summary
- **Endpoint**: `GET /api/monitoring/summary`
- **URL**: `http://100.117.234.2:5000/api/monitoring/summary?hours=24`
- **Response**:
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
    },
    "period_hours": 24,
    "timestamp": "2025-01-02T10:30:00Z"
}
```

#### 6. Recent Alerts
- **Endpoint**: `GET /api/monitoring/alerts`
- **URL**: `http://100.117.234.2:5000/api/monitoring/alerts`
- **Response**:
```json
{
    "status": "success",
    "alerts": [
        {
            "timestamp": "2025-01-02T10:25:00Z",
            "level": "warning",
            "message": "High memory usage detected",
            "metric": "memory_percent",
            "value": 85.2,
            "threshold": 80.0
        },
        {
            "timestamp": "2025-01-02T09:15:00Z",
            "level": "info",
            "message": "System performance normal",
            "metric": "cpu_percent",
            "value": 45.2,
            "threshold": 70.0
        }
    ],
    "count": 2,
    "timestamp": "2025-01-02T10:30:00Z"
}
```

### 📤 Upload & Processing Endpoints

#### 7. Upload Images
- **Endpoint**: `POST /api/upload`
- **URL**: `http://100.117.234.2:5000/api/upload`
- **Headers**: `X-RVM-API-Key: {rvm_api_key}`
- **Content-Type**: `multipart/form-data`
- **Payload**:
```
files: [image1.jpg, image2.jpg]
user_id: my_user
rvm_id: 1
api_key: 38bbe1d2ecf75df21546b05340f5878a24e74ed0d8b88d75db1ddeff198380c1
```
- **Response**:
```json
{
    "success": true,
    "session_id": "session_abc123",
    "timestamp": "20250102_103000",
    "user_id": "my_user",
    "uploaded_files": [
        {
            "original_name": "image1.jpg",
            "saved_name": "image1.jpg",
            "path": "/path/to/saved/image1.jpg"
        }
    ],
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

#### 8. Get Processing Status
- **Endpoint**: `GET /api/process/{session_id}`
- **URL**: `http://100.117.234.2:5000/api/process/session_abc123`
- **Response**:
```json
{
    "status": "completed",
    "message": "Detection completed successfully",
    "timestamp": "20250102_103000",
    "user_id": "my_user",
    "rvm_id": 1,
    "start_time": "2025-01-02T10:30:00Z",
    "end_time": "2025-01-02T10:30:15Z"
}
```

#### 9. Get Detection Results
- **Endpoint**: `GET /api/results/{session_id}`
- **URL**: `http://100.117.234.2:5000/api/results/session_abc123`
- **Response**:
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
                "detection_count": 1,
                "visualizations": [
                    {
                        "type": "compare",
                        "file": "image1-best_pt-compare.png",
                        "path": "/output/image1-best_pt-compare.png"
                    },
                    {
                        "type": "best",
                        "file": "image1-best_pt-best.png",
                        "path": "/output/image1-best_pt-best.png"
                    }
                ]
            }
        ]
    },
    "session_id": "session_abc123",
    "status": "completed",
    "timestamp": "20250102_103000",
    "user_id": "my_user"
}
```

### 📥 Download & History Endpoints

#### 10. Download Result File
- **Endpoint**: `GET /api/download/{session_id}/{filename}`
- **URL**: `http://100.117.234.2:5000/api/download/session_abc123/image1-best_pt-compare.png`
- **Response**: Binary file download

#### 11. Create Session Backup
- **Endpoint**: `GET /api/backup/{session_id}`
- **URL**: `http://100.117.234.2:5000/api/backup/session_abc123`
- **Response**: TAR.GZ file download

#### 12. Get All Detections
- **Endpoint**: `GET /api/detections`
- **URL**: `http://100.117.234.2:5000/api/detections?page=1&limit=20&rvm_id=1`
- **Headers**: `X-RVM-API-Key: {rvm_api_key}` (for RVM-specific queries)
- **Response**:
```json
{
    "pagination": {
        "current_page": 1,
        "total_pages": 5,
        "total_items": 95,
        "items_per_page": 20,
        "has_next": true,
        "has_prev": false,
        "next_page": 2,
        "prev_page": null
    },
    "filters": {
        "user_id": null,
        "rvm_id": 1
    },
    "recent_detections": [
        {
            "timestamp": "20250102_103000",
            "user_id": "my_user",
            "image_name": "image1",
            "detections": [
                {
                    "class_name": "plastic_bottle",
                    "confidence": 0.95,
                    "bbox": [100, 200, 300, 400]
                }
            ],
            "detection_count": 1,
            "rvm_id": 1
        }
    ]
}
```

#### 13. Search Detections
- **Endpoint**: `POST /api/detections/search`
- **URL**: `http://100.117.234.2:5000/api/detections/search`
- **Headers**: `X-RVM-API-Key: {rvm_api_key}`
- **Payload**:
```json
{
    "page": 1,
    "limit": 20,
    "user_id": "my_user",
    "timestamp": "20250102",
    "class_name": "plastic_bottle",
    "rvm_id": 1
}
```
- **Response**:
```json
{
    "pagination": {
        "current_page": 1,
        "total_pages": 3,
        "total_items": 45,
        "items_per_page": 20,
        "has_next": true,
        "has_prev": false,
        "next_page": 2,
        "prev_page": null
    },
    "filters": {
        "user_id": "my_user",
        "timestamp": "20250102",
        "class_name": "plastic_bottle",
        "rvm_id": 1
    },
    "recent_detections": [
        {
            "timestamp": "20250102_103000",
            "user_id": "my_user",
            "image_name": "image1",
            "detections": [
                {
                    "class_name": "plastic_bottle",
                    "confidence": 0.95,
                    "bbox": [100, 200, 300, 400]
                }
            ],
            "detection_count": 1,
            "rvm_id": 1
        }
    ]
}
```

### 🔐 RVM Integration Endpoints

#### 14. Validate RVM API Key
- **Endpoint**: `POST /api/rvm/validate`
- **URL**: `http://100.117.234.2:5000/api/rvm/validate`
- **Payload**:
```json
{
    "api_key": "38bbe1d2ecf75df21546b05340f5878a24e74ed0d8b88d75db1ddeff198380c1"
}
```
- **Response**:
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

#### 15. Get RVM Statistics
- **Endpoint**: `GET /api/rvm/{id}/stats`
- **URL**: `http://100.117.234.2:5000/api/rvm/1/stats`
- **Headers**: `X-RVM-API-Key: {rvm_api_key}`
- **Response**:
```json
{
    "rvm_id": 1,
    "total_sessions": 150,
    "total_detections": 450,
    "recent_activity": [
        {
            "timestamp": "20250102_103000",
            "user_id": "my_user",
            "detection_count": 3
        }
    ]
}
```

---

## 🔄 Integration Flow Examples

### Complete Detection Flow
1. **Upload to Jetson**: `POST http://100.117.234.2:5000/api/upload`
2. **Check Status**: `GET http://100.117.234.2:5000/api/process/{session_id}`
3. **Get Results**: `GET http://100.117.234.2:5000/api/results/{session_id}`
4. **Store to Server**: `POST http://100.123.143.87:8001/api/detections/store`
5. **Update RVM Status**: `POST http://100.123.143.87:8001/api/rvm/{id}/status`

### Economy System Flow
1. **Calculate Reward**: `POST http://100.123.143.87:8001/api/economy/calculate-reward`
2. **Add Balance**: `POST http://100.123.143.87:8001/api/economy/balance/add`
3. **Get Transactions**: `GET http://100.123.143.87:8001/api/economy/transactions`

### Monitoring Flow
1. **Check Jetson Status**: `GET http://100.117.234.2:5000/api/monitoring/status`
2. **Get Performance Summary**: `GET http://100.117.234.2:5000/api/monitoring/summary`
3. **Check Server Analytics**: `GET http://100.123.143.87:8001/api/analytics/dashboard`

---

## 📝 Notes

### Authentication
- **Server API**: Uses Bearer token authentication
- **Jetson API**: Uses X-RVM-API-Key header for RVM-specific operations
- **Master API Key**: Required for server-to-server communication

### Error Handling
- All endpoints return consistent error format:
```json
{
    "success": false,
    "error": "Error message",
    "code": "ERROR_CODE"
}
```

### Rate Limiting
- Server API: 100 requests per minute per user
- Jetson API: 50 requests per minute per RVM

### Multi-RVM Support
- Each RVM has unique IP address: `100.117.234.X`
- RVM IDs are managed by the server
- API keys are unique per RVM
- Data isolation is maintained per RVM

---

**Last Updated**: 2025-01-02  
**Version**: 2.0.0  
**Status**: ✅ COMPLETE API DOCUMENTATION
