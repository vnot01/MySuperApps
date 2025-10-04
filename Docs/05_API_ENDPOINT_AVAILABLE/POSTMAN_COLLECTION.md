# 📮 Postman Collection - MyRVM-Ecosystem v2.0

## 📍 Collection Overview

This document provides Postman collection configurations for testing all API endpoints in the MyRVM-Ecosystem v2.0 system.

### Network Configuration
- **Server**: `100.123.143.87:8001` (MyRVM-Ecosystem-v2)
- **Jetson**: `100.117.234.2:5000` (MyCV-Platform)

---

## 🗂️ Collection Structure

### 1. Server API Collection
**Base URL**: `http://100.123.143.87:8001`

#### Authentication Folder
- **Login** - `POST /api/auth/login`
- **Logout** - `POST /api/auth/logout`

#### RVM Management Folder
- **Get All RVMs** - `GET /api/rvms`
- **Create RVM** - `POST /api/rvms`
- **Get RVM Details** - `GET /api/rvms/{id}`
- **Update RVM** - `PUT /api/rvms/{id}`
- **Update RVM API** - `PUT /api/rvms/{id}/api`
- **Delete RVM** - `DELETE /api/rvms/{id}`

#### Detection Results Folder
- **Get All Detections** - `GET /api/detection-results`
- **Store Detection** - `POST /api/detection-results`

#### Economy System Folder
- **Get Balance** - `GET /api/economy/balance`
- **Add Balance** - `POST /api/economy/balance/add`
- **Get Transactions** - `GET /api/economy/transactions`
- **Get Vouchers** - `GET /api/economy/vouchers`
- **Redeem Voucher** - `POST /api/economy/vouchers/redeem`

#### Analytics Folder
- **Dashboard Analytics** - `GET /api/analytics/dashboard`
- **RVM Analytics** - `GET /api/analytics/rvm/{id}`

#### RVM Integration Folder
- **Validate API Key** - `POST /api/rvm/validate-api-key`
- **Get RVM Info** - `GET /api/rvm/{id}`
- **Store Detection** - `POST /api/detections/store`
- **Get RVM Stats** - `GET /api/rvm/{id}/stats`
- **Update RVM Status** - `POST /api/rvm/{id}/status`

#### Status Check Folder
- **Check RVM Status** - `POST /api/rvm/check-status`

### 2. Jetson API Collection
**Base URL**: `http://100.117.234.2:5000`

#### Health & Status Folder
- **Health Check** - `GET /api/health`
- **API Status** - `GET /api/status`
- **Hardware Info** - `GET /api/hardware`

#### Advanced Monitoring Folder
- **Monitoring Status** - `GET /api/monitoring/status`
- **Performance Summary** - `GET /api/monitoring/summary`
- **Recent Alerts** - `GET /api/monitoring/alerts`

#### Upload & Processing Folder
- **Upload Images** - `POST /api/upload`
- **Get Processing Status** - `GET /api/process/{session_id}`
- **Get Results** - `GET /api/results/{session_id}`

#### Download & History Folder
- **Download File** - `GET /api/download/{session_id}/{filename}`
- **Create Backup** - `GET /api/backup/{session_id}`
- **Get All Detections** - `GET /api/detections`
- **Search Detections** - `POST /api/detections/search`

#### RVM Integration Folder
- **Validate RVM** - `POST /api/rvm/validate`
- **Get RVM Stats** - `GET /api/rvm/{id}/stats`

---

## 🔧 Environment Variables

### Server Environment
```json
{
    "server_base_url": "http://100.123.143.87:8001",
    "server_auth_token": "{{server_auth_token}}",
    "master_api_key": "{{master_api_key}}",
    "rvm_id": "1",
    "user_id": "test_user"
}
```

### Jetson Environment
```json
{
    "jetson_base_url": "http://100.117.234.2:5000",
    "rvm_api_key": "38bbe1d2ecf75df21546b05340f5878a24e74ed0d8b88d75db1ddeff198380c1",
    "session_id": "{{session_id}}",
    "user_id": "test_user",
    "rvm_id": "1"
}
```

---

## 📋 Request Examples

### 1. Server Authentication

#### Login Request
```json
{
    "method": "POST",
    "url": "{{server_base_url}}/api/auth/login",
    "headers": {
        "Content-Type": "application/json"
    },
    "body": {
        "email": "admin@myrvm.com",
        "password": "password123"
    }
}
```

#### Login Response
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

### 2. RVM Management

#### Create RVM Request
```json
{
    "method": "POST",
    "url": "{{server_base_url}}/api/rvms",
    "headers": {
        "Authorization": "Bearer {{server_auth_token}}",
        "Content-Type": "application/json"
    },
    "body": {
        "name": "RVM-002",
        "location": "Mall North",
        "ip_address": "100.117.234.3",
        "address": "Jl. Utara No. 123",
        "latitude": -6.200000,
        "longitude": 106.816666
    }
}
```

#### Create RVM Response
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

### 3. Jetson Upload

#### Upload Images Request
```json
{
    "method": "POST",
    "url": "{{jetson_base_url}}/api/upload",
    "headers": {
        "X-RVM-API-Key": "{{rvm_api_key}}"
    },
    "body": {
        "files": "@image1.jpg",
        "files": "@image2.jpg",
        "user_id": "{{user_id}}",
        "rvm_id": "{{rvm_id}}"
    }
}
```

#### Upload Response
```json
{
    "success": true,
    "session_id": "session_abc123",
    "timestamp": "20250102_103000",
    "user_id": "test_user",
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

### 4. Economy System

#### Add Balance Request
```json
{
    "method": "POST",
    "url": "{{server_base_url}}/api/economy/balance/add",
    "headers": {
        "Authorization": "Bearer {{server_auth_token}}",
        "Content-Type": "application/json"
    },
    "body": {
        "amount": 25.0,
        "description": "Reward for plastic bottle deposit"
    }
}
```

#### Add Balance Response
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

---

## 🔄 Pre-request Scripts

### Server Authentication Script
```javascript
// Set server_auth_token from login response
if (pm.response.code === 200) {
    const response = pm.response.json();
    if (response.success && response.token) {
        pm.environment.set("server_auth_token", response.token);
    }
}
```

### Jetson Session ID Script
```javascript
// Set session_id from upload response
if (pm.response.code === 200) {
    const response = pm.response.json();
    if (response.success && response.session_id) {
        pm.environment.set("session_id", response.session_id);
    }
}
```

---

## 🧪 Test Scripts

### Authentication Test
```javascript
pm.test("Login successful", function () {
    pm.response.to.have.status(200);
    const response = pm.response.json();
    pm.expect(response.success).to.be.true;
    pm.expect(response.token).to.exist;
});
```

### RVM Creation Test
```javascript
pm.test("RVM created successfully", function () {
    pm.response.to.have.status(200);
    const response = pm.response.json();
    pm.expect(response.success).to.be.true;
    pm.expect(response.data.id).to.exist;
    pm.expect(response.data.api_key).to.exist;
});
```

### Upload Test
```javascript
pm.test("Upload successful", function () {
    pm.response.to.have.status(200);
    const response = pm.response.json();
    pm.expect(response.success).to.be.true;
    pm.expect(response.session_id).to.exist;
    pm.expect(response.uploaded_files).to.be.an('array');
});
```

### Processing Status Test
```javascript
pm.test("Processing status retrieved", function () {
    pm.response.to.have.status(200);
    const response = pm.response.json();
    pm.expect(response.status).to.exist;
    pm.expect(response.timestamp).to.exist;
});
```

---

## 🔄 Collection Runner

### Complete Flow Test
```json
{
    "collection": "MyRVM-Ecosystem-v2-Complete-Flow",
    "environment": "Production",
    "iterations": 1,
    "delay": {
        "item": 1000
    },
    "data": [
        {
            "user_email": "admin@myrvm.com",
            "user_password": "password123",
            "test_image": "test_image.jpg"
        }
    ]
}
```

### Load Test
```json
{
    "collection": "MyRVM-Ecosystem-v2-Load-Test",
    "environment": "Production",
    "iterations": 10,
    "delay": {
        "item": 100
    },
    "data": [
        {
            "user_email": "admin@myrvm.com",
            "user_password": "password123"
        }
    ]
}
```

---

## 📊 Monitoring Tests

### Health Check Test
```javascript
pm.test("Health check successful", function () {
    pm.response.to.have.status(200);
    const response = pm.response.json();
    pm.expect(response.status).to.equal("healthy");
    pm.expect(response.service).to.exist;
    pm.expect(response.version).to.exist;
});
```

### Performance Test
```javascript
pm.test("Response time is acceptable", function () {
    pm.expect(pm.response.responseTime).to.be.below(2000);
});

pm.test("Server is responding", function () {
    pm.response.to.have.status(200);
});
```

### Monitoring Status Test
```javascript
pm.test("Monitoring data retrieved", function () {
    pm.response.to.have.status(200);
    const response = pm.response.json();
    pm.expect(response.status).to.equal("success");
    pm.expect(response.monitoring).to.exist;
    pm.expect(response.monitoring.current_metrics).to.exist;
});
```

---

## 🔧 Environment Setup

### 1. Create Environment
1. Open Postman
2. Click "Environments" tab
3. Click "Create Environment"
4. Name: "MyRVM-Ecosystem-v2-Production"
5. Add variables as shown above

### 2. Import Collection
1. Click "Import" button
2. Select "MyRVM-Ecosystem-v2.postman_collection.json"
3. Click "Import"

### 3. Set Environment
1. Select "MyRVM-Ecosystem-v2-Production" environment
2. Update variables as needed
3. Start testing

---

## 📝 Collection Export

### Export Collection
```bash
# Export collection to JSON
curl -X GET "https://api.getpostman.com/collections/{{collection_id}}" \
  -H "X-API-Key: {{postman_api_key}}" \
  -o MyRVM-Ecosystem-v2.postman_collection.json
```

### Export Environment
```bash
# Export environment to JSON
curl -X GET "https://api.getpostman.com/environments/{{environment_id}}" \
  -H "X-API-Key: {{postman_api_key}}" \
  -o MyRVM-Ecosystem-v2.postman_environment.json
```

---

## 🚀 Quick Start

### 1. Import Collection
- Download `MyRVM-Ecosystem-v2.postman_collection.json`
- Import into Postman
- Set up environment variables

### 2. Run Health Checks
- Execute "Health Check" requests
- Verify all services are running
- Check response times

### 3. Test Authentication
- Run "Login" request
- Verify token is set in environment
- Test authenticated requests

### 4. Test Complete Flow
- Run "Complete Flow" collection
- Monitor all requests
- Check for errors

### 5. Run Load Tests
- Execute "Load Test" collection
- Monitor performance
- Check system stability

---

## 📊 Performance Monitoring

### Response Time Monitoring
```javascript
// Add to test scripts
pm.test("Response time monitoring", function () {
    const responseTime = pm.response.responseTime;
    console.log("Response time: " + responseTime + "ms");
    
    if (responseTime > 5000) {
        console.warn("Slow response detected: " + responseTime + "ms");
    }
});
```

### Error Rate Monitoring
```javascript
// Add to test scripts
pm.test("Error rate monitoring", function () {
    const statusCode = pm.response.code;
    if (statusCode >= 400) {
        console.error("Error response: " + statusCode);
    }
});
```

---

## 🔍 Debugging

### Request Logging
```javascript
// Add to pre-request scripts
console.log("Request URL: " + pm.request.url);
console.log("Request Method: " + pm.request.method);
console.log("Request Headers: " + JSON.stringify(pm.request.headers));
```

### Response Logging
```javascript
// Add to test scripts
console.log("Response Status: " + pm.response.code);
console.log("Response Time: " + pm.response.responseTime + "ms");
console.log("Response Body: " + pm.response.text());
```

---

**Last Updated**: 2025-01-02  
**Version**: 2.0.0  
**Status**: ✅ COMPLETE POSTMAN COLLECTION DOCUMENTATION
