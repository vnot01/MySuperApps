# 🤖 JETSON API ENDPOINTS (MyCV-Platform)

## 📍 Jetson Information
- **IP**: `100.117.234.2` (Primary RVM)
- **Port**: `5000`
- **Base URL**: `http://100.117.234.2:5000`
- **Technology**: Python + Flask + YOLO + SAM2

## 🌐 Multi-RVM Network Configuration
- **RVM-001**: `100.117.234.2:5000` (Primary Jetson)
- **RVM-002**: `100.117.234.3:5000` (Additional Jetson - Future)
- **RVM-003**: `100.117.234.4:5000` (Additional Jetson - Future)
- **RVM-XXX**: `100.117.234.X:5000` (Scalable RVM Network)

---

## 🏥 Health & Status Endpoints

### 1. Health Check
- **Endpoint**: `GET /api/health`
- **URL**: `http://100.117.234.2:5000/api/health`

**Response**:
```json
{
    "status": "healthy",
    "service": "MyCV-Edge-API",
    "version": "1.0.0",
    "timestamp": "2025-01-02T10:30:00Z",
    "uptime": 3600
}
```

**cURL Example**:
```bash
curl -X GET http://100.117.234.2:5000/api/health
```

### 2. API Status
- **Endpoint**: `GET /api/status`
- **URL**: `http://100.117.234.2:5000/api/status`

**Response**:
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

**cURL Example**:
```bash
curl -X GET http://100.117.234.2:5000/api/status
```

### 3. Hardware Information
- **Endpoint**: `GET /api/hardware`
- **URL**: `http://100.117.234.2:5000/api/hardware`

**Response**:
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

**cURL Example**:
```bash
curl -X GET http://100.117.234.2:5000/api/hardware
```

---

## 📊 Advanced Monitoring Endpoints

### 4. Monitoring Status
- **Endpoint**: `GET /api/monitoring/status`
- **URL**: `http://100.117.234.2:5000/api/monitoring/status`

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
        ],
        "timestamp": "2025-01-02T10:30:00Z"
    }
}
```

**cURL Example**:
```bash
curl -X GET http://100.117.234.2:5000/api/monitoring/status
```

### 5. Performance Summary
- **Endpoint**: `GET /api/monitoring/summary`
- **URL**: `http://100.117.234.2:5000/api/monitoring/summary?hours=24`

**Query Parameters**:
- `hours` (optional): Number of hours for summary (default: 24)

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
    },
    "period_hours": 24,
    "timestamp": "2025-01-02T10:30:00Z"
}
```

**cURL Example**:
```bash
curl -X GET "http://100.117.234.2:5000/api/monitoring/summary?hours=24"
```

### 6. Recent Alerts
- **Endpoint**: `GET /api/monitoring/alerts`
- **URL**: `http://100.117.234.2:5000/api/monitoring/alerts`

**Response**:
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

**cURL Example**:
```bash
curl -X GET http://100.117.234.2:5000/api/monitoring/alerts
```

---

## 📤 Upload & Processing Endpoints

### 7. Upload Images
- **Endpoint**: `POST /api/upload`
- **URL**: `http://100.117.234.2:5000/api/upload`
- **Content-Type**: `multipart/form-data`
- **Headers**: `X-RVM-API-Key: {rvm_api_key}` (optional for RVM-specific operations)

**Form Data**:
```
files: [image1.jpg, image2.jpg] (multiple files supported)
user_id: my_user
rvm_id: 1 (optional)
api_key: 38bbe1d2ecf75df21546b05340f5878a24e74ed0d8b88d75db1ddeff198380c1 (optional)
```

**Response**:
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

**cURL Example**:
```bash
curl -X POST http://100.117.234.2:5000/api/upload \
  -H "X-RVM-API-Key: 38bbe1d2ecf75df21546b05340f5878a24e74ed0d8b88d75db1ddeff198380c1" \
  -F "files=@image1.jpg" \
  -F "files=@image2.jpg" \
  -F "user_id=my_user" \
  -F "rvm_id=1"
```

### 8. Get Processing Status
- **Endpoint**: `GET /api/process/{session_id}`
- **URL**: `http://100.117.234.2:5000/api/process/session_abc123`

**Response**:
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

**cURL Example**:
```bash
curl -X GET http://100.117.234.2:5000/api/process/session_abc123
```

### 9. Get Detection Results
- **Endpoint**: `GET /api/results/{session_id}`
- **URL**: `http://100.117.234.2:5000/api/results/session_abc123`

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
                    },
                    {
                        "type": "yolo",
                        "file": "image1-yolo11m-detection.png",
                        "path": "/output/image1-yolo11m-detection.png"
                    },
                    {
                        "type": "segmentation",
                        "file": "image1-best_pt-segmentation.png",
                        "path": "/output/image1-best_pt-segmentation.png"
                    },
                    {
                        "type": "hybrid",
                        "file": "image1-best_pt-hybrid.png",
                        "path": "/output/image1-best_pt-hybrid.png"
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

**cURL Example**:
```bash
curl -X GET http://100.117.234.2:5000/api/results/session_abc123
```

---

## 📥 Download & History Endpoints

### 10. Download Result File
- **Endpoint**: `GET /api/download/{session_id}/{filename}`
- **URL**: `http://100.117.234.2:5000/api/download/session_abc123/image1-best_pt-compare.png`

**Response**: Binary file download

**cURL Example**:
```bash
curl -X GET http://100.117.234.2:5000/api/download/session_abc123/image1-best_pt-compare.png \
  -o image1-best_pt-compare.png
```

### 11. Create Session Backup
- **Endpoint**: `GET /api/backup/{session_id}`
- **URL**: `http://100.117.234.2:5000/api/backup/session_abc123`

**Response**: TAR.GZ file download containing all session files

**cURL Example**:
```bash
curl -X GET http://100.117.234.2:5000/api/backup/session_abc123 \
  -o session_backup_20250102_103000_my_user.tar.gz
```

### 12. Get All Detections
- **Endpoint**: `GET /api/detections`
- **URL**: `http://100.117.234.2:5000/api/detections`
- **Headers**: `X-RVM-API-Key: {rvm_api_key}` (for RVM-specific queries)

**Query Parameters**:
- `page` (optional): Page number (default: 1)
- `limit` (optional): Items per page (default: 20, max: 100)
- `user_id` (optional): Filter by user ID
- `rvm_id` (optional): Filter by RVM ID

**Response**:
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

**cURL Example**:
```bash
curl -X GET "http://100.117.234.2:5000/api/detections?page=1&limit=20&rvm_id=1" \
  -H "X-RVM-API-Key: 38bbe1d2ecf75df21546b05340f5878a24e74ed0d8b88d75db1ddeff198380c1"
```

### 13. Search Detections
- **Endpoint**: `POST /api/detections/search`
- **URL**: `http://100.117.234.2:5000/api/detections/search`
- **Headers**: `X-RVM-API-Key: {rvm_api_key}`
- **Content-Type**: `application/json`

**Request Body**:
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

**Response**:
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

**cURL Example**:
```bash
curl -X POST http://100.117.234.2:5000/api/detections/search \
  -H "X-RVM-API-Key: 38bbe1d2ecf75df21546b05340f5878a24e74ed0d8b88d75db1ddeff198380c1" \
  -H "Content-Type: application/json" \
  -d '{
    "page": 1,
    "limit": 20,
    "user_id": "my_user",
    "timestamp": "20250102",
    "class_name": "plastic_bottle",
    "rvm_id": 1
  }'
```

---

## 🔐 RVM Integration Endpoints

### 14. Validate RVM API Key
- **Endpoint**: `POST /api/rvm/validate`
- **URL**: `http://100.117.234.2:5000/api/rvm/validate`
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
    "valid": true,
    "rvm": {
        "id": 1,
        "name": "RVM-001",
        "location": "Mall Central",
        "status": "active"
    }
}
```

**cURL Example**:
```bash
curl -X POST http://100.117.234.2:5000/api/rvm/validate \
  -H "Content-Type: application/json" \
  -d '{
    "api_key": "38bbe1d2ecf75df21546b05340f5878a24e74ed0d8b88d75db1ddeff198380c1"
  }'
```

### 15. Get RVM Statistics
- **Endpoint**: `GET /api/rvm/{id}/stats`
- **URL**: `http://100.117.234.2:5000/api/rvm/1/stats`
- **Headers**: `X-RVM-API-Key: {rvm_api_key}`

**Response**:
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

**cURL Example**:
```bash
curl -X GET http://100.117.234.2:5000/api/rvm/1/stats \
  -H "X-RVM-API-Key: 38bbe1d2ecf75df21546b05340f5878a24e74ed0d8b88d75db1ddeff198380c1"
```

---

## 🔄 Multi-RVM Support

### RVM Network Configuration
Each RVM device has its own IP address and can be accessed independently:

#### RVM-001 (Primary)
- **IP**: `100.117.234.2`
- **URL**: `http://100.117.234.2:5000`
- **Status**: Active

#### RVM-002 (Future)
- **IP**: `100.117.234.3`
- **URL**: `http://100.117.234.3:5000`
- **Status**: Planned

#### RVM-003 (Future)
- **IP**: `100.117.234.4`
- **URL**: `http://100.117.234.4:5000`
- **Status**: Planned

### Multi-RVM API Calls
All endpoints work the same way across all RVM devices. Simply change the IP address:

```bash
# RVM-001
curl -X GET http://100.117.234.2:5000/api/health

# RVM-002 (when available)
curl -X GET http://100.117.234.3:5000/api/health

# RVM-003 (when available)
curl -X GET http://100.117.234.4:5000/api/health
```

---

## 📝 Error Response Format

All endpoints return consistent error format:

```json
{
    "error": "Error message",
    "code": "ERROR_CODE",
    "details": {
        "field": "validation error details"
    }
}
```

**Common Error Codes**:
- `UNAUTHORIZED`: Invalid or missing RVM API key
- `FORBIDDEN`: Insufficient permissions for RVM operations
- `NOT_FOUND`: Session or resource not found
- `VALIDATION_ERROR`: Invalid request data
- `PROCESSING_ERROR`: Error during image processing
- `SERVER_ERROR`: Internal server error

---

## 🔧 Configuration

### Environment Variables
The Jetson API uses the following environment variables (configured in `rvm_config.env`):

```bash
# MyRVM-Ecosystem-v2 Integration
RVM_API_BASE_URL=http://100.123.143.87:8001/api
RVM_API_KEY=38bbe1d2ecf75df21546b05340f5878a24e74ed0d8b88d75db1ddeff198380c1

# API Configuration
API_HOST=100.117.234.2
API_PORT=5000
API_DEBUG=false

# Data Directories
BASE_DATA_DIR=../../data-jetson
UPLOAD_FOLDER=../../data-jetson/input
OUTPUT_FOLDER=../../data-jetson/output

# File Upload Settings
MAX_CONTENT_LENGTH=16777216  # 16MB
ALLOWED_EXTENSIONS=png,jpg,jpeg,gif,bmp

# Cache Settings
RVM_CACHE_TTL=300  # 5 minutes

# GPU Settings
CUDA_VISIBLE_DEVICES=0

# RVM IDs (comma-separated)
RVM_IDS=1,2,3
```

---

**Last Updated**: 2025-01-02  
**Version**: 2.0.0  
**Status**: ✅ COMPLETE JETSON API DOCUMENTATION
