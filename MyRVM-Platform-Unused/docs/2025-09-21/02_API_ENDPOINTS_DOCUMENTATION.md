# API Endpoints Documentation
**Date:** 2025-09-21  
**Version:** 1.0  
**Base URL:** `http://localhost:8001/api`  

## 🔐 Authentication

### API Token Generation
```http
POST /api/rvm/generate-token
Content-Type: application/json

{
    "rvm_id": "4",
    "ip_address": "172.28.93.97"
}
```

**Response:**
```json
{
    "success": true,
    "message": "API token generated successfully",
    "data": {
        "rvm_id": 4,
        "rvm_name": "RVM-Orin1",
        "api_token": "2o3z4v4H9E7GKk44fABJWbxy7ubbvUsjD211uo39pdU9j4H9VpeMvSmY0NzEttZA",
        "expires_at": "2025-10-21T17:11:21.000000Z",
        "server_url": "http://localhost:8001",
        "endpoints": {
            "health_check": "/api/health-check",
            "metrics": "/admin/rvm/4/metrics",
            "store_metrics": "/admin/rvm/4/store-metrics",
            "execute_command": "/admin/rvm/4/execute-command",
            "command_status": "/admin/rvm/4/command/{commandId}/status",
            "recent_commands": "/admin/rvm/4/recent-commands"
        }
    }
}
```

### API Token Validation
```http
POST /api/rvm/validate-token
Authorization: Bearer {token}
Content-Type: application/json

{}
```

**Response:**
```json
{
    "success": true,
    "message": "Token is valid",
    "data": {
        "rvm_id": 4,
        "rvm_name": "RVM-Orin1",
        "expires_at": "2025-10-21T17:11:21.000000Z",
        "last_access": "2025-09-21T17:11:26.000000Z"
    }
}
```

### API Token Revocation
```http
POST /api/rvm/revoke-token
Authorization: Bearer {token}
Content-Type: application/json

{}
```

**Response:**
```json
{
    "success": true,
    "message": "Token revoked successfully"
}
```

## 🏥 Health Check

### Server Health Status
```http
GET /api/health-check
Accept: application/json
```

**Response:**
```json
{
    "success": true,
    "message": "MyRVM Platform is healthy",
    "data": {
        "status": "healthy",
        "timestamp": "2025-09-21T16:35:34.483221Z",
        "server": {
            "name": "MyRVM Platform",
            "version": "1.0.0",
            "environment": "local",
            "uptime": "Unknown"
        },
        "database": {
            "status": "connected",
            "connection": "pgsql"
        },
        "services": {
            "api": "operational",
            "authentication": "operational",
            "metrics": "operational",
            "commands": "operational"
        },
        "rvm_support": {
            "csrf_enabled": true,
            "cors_enabled": true,
            "api_endpoints": {
                "health_check": "/api/health-check",
                "metrics": "/admin/rvm/{id}/metrics",
                "commands": "/admin/rvm/{id}/execute-command",
                "status": "/admin/rvm/{id}/command/{commandId}/status"
            }
        }
    }
}
```

### Server Status
```http
GET /api/status
Accept: application/json
```

**Response:**
```json
{
    "success": true,
    "message": "Server status retrieved",
    "data": {
        "status": "operational",
        "timestamp": "2025-09-21T16:35:34.483221Z",
        "version": "1.0.0",
        "environment": "local"
    }
}
```

## 📊 Metrics

### Get RVM Metrics
```http
GET /api/rvm/{id}/metrics
Authorization: Bearer {token}
Accept: application/json
```

**Response:**
```json
{
    "success": true,
    "message": "Metrics retrieved successfully",
    "data": {
        "system": {
            "cpu_usage": 45.2,
            "memory_usage": 67.8,
            "disk_usage": 40.5,
            "gpu_usage": 25.3,
            "temperature": 42.5,
            "gpu_temperature": 48.2,
            "disk_read_speed": 120,
            "disk_write_speed": 85,
            "network_upload_speed": 15.5,
            "network_download_speed": 25.8,
            "memory_available": 2048000000,
            "disk_available": 50000000000,
            "load_average": 1.25
        },
        "application": {
            "software_version": "1.0.0",
            "ai_model_version": "yolo11-v1.2",
            "ai_model_path": "/models/best.pt",
            "uptime_seconds": 86400,
            "deposit_count_since_restart": 45,
            "last_deposit_time": "2025-09-21T15:30:00Z",
            "error_count": 2,
            "warning_count": 5
        },
        "network": {
            "local_ip": "172.28.93.97",
            "virtual_ip": "10.3.52.161",
            "gateway_ip": "172.28.93.1",
            "dns_servers": ["8.8.8.8", "8.8.4.4"],
            "network_interface": "eth0",
            "connection_type": "ethernet",
            "signal_strength": -45
        }
    }
}
```

### Store RVM Metrics
```http
POST /api/rvm/{id}/store-metrics
Authorization: Bearer {token}
Content-Type: application/json
X-RVM-ID: {id}

{
    "system_metrics": {
        "cpu_usage": 45.2,
        "memory_usage": 67.8,
        "temperature": 42.5
    },
    "application_metrics": {
        "software_version": "1.0.0",
        "uptime_seconds": 86400
    },
    "network_info": {
        "local_ip": "172.28.93.97",
        "connection_type": "ethernet"
    }
}
```

**Response:**
```json
{
    "success": true,
    "message": "Metrics stored successfully"
}
```

## 🎮 Commands

### Execute Remote Command
```http
POST /api/rvm/{id}/execute-command
Authorization: Bearer {token}
Content-Type: application/json
X-RVM-ID: {id}

{
    "command_type": "system",
    "command_name": "check_system_health",
    "command_payload": {}
}
```

**Available Commands:**
- `reboot_system` - Reboot the RVM
- `restart_app` - Restart the application
- `open_door` - Open the collection door
- `close_door` - Close the collection door
- `run_motor_test` - Test motor functionality
- `git_pull` - Pull latest changes from GitHub
- `update_ai_model` - Update AI model from GitHub
- `check_system_health` - Check system health status

**Response:**
```json
{
    "success": true,
    "message": "Command executed successfully",
    "command_id": 19,
    "data": {
        "command_type": "system",
        "command_name": "check_system_health",
        "status": "completed",
        "result": "System health check completed successfully",
        "execution_time": "2.5s"
    }
}
```

### Get Command Status
```http
GET /api/rvm/{id}/command/{commandId}/status
Authorization: Bearer {token}
Accept: application/json
```

**Response:**
```json
{
    "success": true,
    "message": "Command status retrieved",
    "data": {
        "command_id": 19,
        "status": "completed",
        "result": "System health check completed successfully",
        "executed_at": "2025-09-21T17:16:52Z",
        "completed_at": "2025-09-21T17:16:54Z",
        "execution_time": "2.5s"
    }
}
```

### Get Recent Commands
```http
GET /api/rvm/{id}/recent-commands
Authorization: Bearer {token}
Accept: application/json
```

**Response:**
```json
{
    "success": true,
    "message": "Recent commands retrieved",
    "data": [
        {
            "id": 19,
            "command_type": "system",
            "command_name": "check_system_health",
            "status": "completed",
            "executed_at": "2025-09-21T17:16:52Z",
            "completed_at": "2025-09-21T17:16:54Z",
            "result": "System health check completed successfully"
        },
        {
            "id": 18,
            "command_type": "system",
            "command_name": "reboot_system",
            "status": "completed",
            "executed_at": "2025-09-21T17:10:30Z",
            "completed_at": "2025-09-21T17:10:35Z",
            "result": "System reboot initiated successfully"
        }
    ]
}
```

## 🔒 Security

### Headers Required
- `Authorization: Bearer {token}` - API token authentication
- `X-RVM-ID: {id}` - RVM identifier for requests
- `Content-Type: application/json` - For POST requests
- `Accept: application/json` - For response format

### Error Responses

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
    "command_id": null,
    "data": null
}
```

#### 500 Internal Server Error
```json
{
    "success": false,
    "error": "Database connection failed",
    "message": "Unable to process request"
}
```

## 📝 Usage Examples

### Python Example
```python
import requests

# Generate API token
response = requests.post('http://localhost:8001/api/rvm/generate-token', json={
    'rvm_id': '4',
    'ip_address': '172.28.93.97'
})
token = response.json()['data']['api_token']

# Send metrics
headers = {
    'Authorization': f'Bearer {token}',
    'X-RVM-ID': '4',
    'Content-Type': 'application/json'
}

metrics_data = {
    'system_metrics': {
        'cpu_usage': 45.2,
        'memory_usage': 67.8,
        'temperature': 42.5
    }
}

response = requests.post(
    'http://localhost:8001/api/rvm/4/store-metrics',
    json=metrics_data,
    headers=headers
)
```

### cURL Example
```bash
# Generate token
curl -X POST "http://localhost:8001/api/rvm/generate-token" \
  -H "Content-Type: application/json" \
  -d '{"rvm_id": "4", "ip_address": "172.28.93.97"}'

# Send metrics
curl -X POST "http://localhost:8001/api/rvm/4/store-metrics" \
  -H "Authorization: Bearer {token}" \
  -H "X-RVM-ID: 4" \
  -H "Content-Type: application/json" \
  -d '{"system_metrics": {"cpu_usage": 45.2, "memory_usage": 67.8}}'
```

---
**Documentation Generated:** 2025-09-21  
**API Version:** 1.0  
**Last Updated:** 2025-09-21  
**Next Review:** After command execution debugging completion
