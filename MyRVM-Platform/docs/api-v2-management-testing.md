# API v2 Management Controllers Testing Guide

## Overview
Dokumentasi ini menjelaskan cara testing untuk semua Management Controllers API endpoints yang menyediakan fungsionalitas admin dashboard, tenant management, RVM management, user management, dan analytics.

## Prerequisites
- Docker container running
- Database seeded dengan data awal
- Token authentication (Bearer token)

## Platform-Specific Commands

### Windows (PowerShell)
```powershell
# Gunakan curl.exe untuk menghindari alias PowerShell
curl.exe -X GET "http://localhost:8000/api/v2/endpoint" -H "Authorization: Bearer <token>"

# Untuk JSON data, gunakan file terpisah
curl.exe -X POST "http://localhost:8000/api/v2/endpoint" -H "Authorization: Bearer <token>" -H "Content-Type: application/json" -d "@data.json"
```

### Windows (Command Prompt)
```cmd
curl -X GET "http://localhost:8000/api/v2/endpoint" -H "Authorization: Bearer <token>"
curl -X POST "http://localhost:8000/api/v2/endpoint" -H "Authorization: Bearer <token>" -H "Content-Type: application/json" -d "@data.json"
```

### macOS/Linux (Terminal)
```bash
curl -X GET "http://localhost:8000/api/v2/endpoint" -H "Authorization: Bearer <token>"
curl -X POST "http://localhost:8000/api/v2/endpoint" -H "Authorization: Bearer <token>" -H "Content-Type: application/json" -d "@data.json"
```

## Authentication
Semua endpoint memerlukan authentication dengan Bearer token:
```bash
Authorization: Bearer <token>
```

## Management Controllers Overview

### 1. AdminController
- Dashboard statistics
- User management
- System settings

### 2. TenantController  
- Tenant management
- Tenant statistics
- Tenant operations

### 3. RVMController
- RVM management
- RVM statistics
- RVM operations

### 4. UserManagementController
- User CRUD operations
- User balance management
- User statistics

### 5. AnalyticsController
- Dashboard analytics
- Detailed analytics
- Custom reports

---

## AdminController Testing

### 1. Dashboard Statistics
**GET** `/api/v2/admin/dashboard/stats`

#### Windows (PowerShell):
```powershell
curl.exe -X GET "http://localhost:8000/api/v2/admin/dashboard/stats" -H "Authorization: Bearer 2|eJnDVVbwaoNtfiFYgm7ewEetIHQadfy60252ZSnT70844d92"
```

#### Windows (CMD) / macOS / Linux:
```bash
curl -X GET "http://localhost:8000/api/v2/admin/dashboard/stats" -H "Authorization: Bearer 2|eJnDVVbwaoNtfiFYgm7ewEetIHQadfy60252ZSnT70844d92"
```

#### Expected Response:
```json
{
  "success": true,
  "message": "Dashboard statistics retrieved successfully",
  "data": {
    "users": {
      "total": 6,
      "verified": 4,
      "unverified": 2
    },
    "tenants": {
      "total": 3,
      "active": 3,
      "inactive": 0
    },
    "rvms": {
      "total": 3,
      "active": 1,
      "inactive": 1,
      "maintenance": 1,
      "full": 0
    },
    "deposits": {
      "total": 3,
      "completed": 1,
      "pending": 0,
      "processing": 1,
      "rejected": 1
    },
    "economy": {
      "total_balance": "912.5000",
      "total_transactions": 3,
      "total_rewards": "1912.50"
    },
    "vouchers": {
      "total": 3,
      "active": 3,
      "total_redemptions": 1
    }
  }
}
```

### 2. Get Users
**GET** `/api/v2/admin/users`

#### Windows (PowerShell):
```powershell
curl.exe -X GET "http://localhost:8000/api/v2/admin/users" -H "Authorization: Bearer 2|eJnDVVbwaoNtfiFYgm7ewEetIHQadfy60252ZSnT70844d92"
```

#### Windows (CMD) / macOS / Linux:
```bash
curl -X GET "http://localhost:8000/api/v2/admin/users" -H "Authorization: Bearer 2|eJnDVVbwaoNtfiFYgm7ewEetIHQadfy60252ZSnT70844d92"
```

### 3. Create User
**POST** `/api/v2/admin/users`

#### Create JSON file (admin_user.json):
```json
{
  "name": "Admin Test User",
  "email": "admintest@example.com",
  "password": "password123",
  "password_confirmation": "password123",
  "role_id": 2
}
```

#### Windows (PowerShell):
```powershell
curl.exe -X POST "http://localhost:8000/api/v2/admin/users" -H "Authorization: Bearer 2|eJnDVVbwaoNtfiFYgm7ewEetIHQadfy60252ZSnT70844d92" -H "Content-Type: application/json" -d "@admin_user.json"
```

#### Windows (CMD) / macOS / Linux:
```bash
curl -X POST "http://localhost:8000/api/v2/admin/users" -H "Authorization: Bearer 2|eJnDVVbwaoNtfiFYgm7ewEetIHQadfy60252ZSnT70844d92" -H "Content-Type: application/json" -d "@admin_user.json"
```

---

## TenantController Testing

### 1. Get Tenants
**GET** `/api/v2/tenants`

#### Windows (PowerShell):
```powershell
curl.exe -X GET "http://localhost:8000/api/v2/tenants" -H "Authorization: Bearer 2|eJnDVVbwaoNtfiFYgm7ewEetIHQadfy60252ZSnT70844d92"
```

#### Windows (CMD) / macOS / Linux:
```bash
curl -X GET "http://localhost:8000/api/v2/tenants" -H "Authorization: Bearer 2|eJnDVVbwaoNtfiFYgm7ewEetIHQadfy60252ZSnT70844d92"
```

### 2. Create Tenant
**POST** `/api/v2/tenants`

#### Create JSON file (tenant.json):
```json
{
  "name": "Test Tenant",
  "description": "Test tenant for API testing"
}
```

#### Windows (PowerShell):
```powershell
curl.exe -X POST "http://localhost:8000/api/v2/tenants" -H "Authorization: Bearer 2|eJnDVVbwaoNtfiFYgm7ewEetIHQadfy60252ZSnT70844d92" -H "Content-Type: application/json" -d "@tenant.json"
```

#### Windows (CMD) / macOS / Linux:
```bash
curl -X POST "http://localhost:8000/api/v2/tenants" -H "Authorization: Bearer 2|eJnDVVbwaoNtfiFYgm7ewEetIHQadfy60252ZSnT70844d92" -H "Content-Type: application/json" -d "@tenant.json"
```

---

## RVMController Testing

### 1. Get RVMs
**GET** `/api/v2/rvms`

#### Windows (PowerShell):
```powershell
curl.exe -X GET "http://localhost:8000/api/v2/rvms" -H "Authorization: Bearer 2|eJnDVVbwaoNtfiFYgm7ewEetIHQadfy60252ZSnT70844d92"
```

#### Windows (CMD) / macOS / Linux:
```bash
curl -X GET "http://localhost:8000/api/v2/rvms" -H "Authorization: Bearer 2|eJnDVVbwaoNtfiFYgm7ewEetIHQadfy60252ZSnT70844d92"
```

### 2. Create RVM
**POST** `/api/v2/rvms`

#### Create JSON file (rvm.json):
```json
{
  "name": "Test RVM",
  "location": "Test Location",
  "status": "active"
}
```

#### Windows (PowerShell):
```powershell
curl.exe -X POST "http://localhost:8000/api/v2/rvms" -H "Authorization: Bearer 2|eJnDVVbwaoNtfiFYgm7ewEetIHQadfy60252ZSnT70844d92" -H "Content-Type: application/json" -d "@rvm.json"
```

#### Windows (CMD) / macOS / Linux:
```bash
curl -X POST "http://localhost:8000/api/v2/rvms" -H "Authorization: Bearer 2|eJnDVVbwaoNtfiFYgm7ewEetIHQadfy60252ZSnT70844d92" -H "Content-Type: application/json" -d "@rvm.json"
```

---

## UserManagementController Testing

### 1. Get Users
**GET** `/api/v2/users`

#### Windows (PowerShell):
```powershell
curl.exe -X GET "http://localhost:8000/api/v2/users" -H "Authorization: Bearer 2|eJnDVVbwaoNtfiFYgm7ewEetIHQadfy60252ZSnT70844d92"
```

#### Windows (CMD) / macOS / Linux:
```bash
curl -X GET "http://localhost:8000/api/v2/users" -H "Authorization: Bearer 2|eJnDVVbwaoNtfiFYgm7ewEetIHQadfy60252ZSnT70844d92"
```

### 2. Get Roles
**GET** `/api/v2/users/roles`

#### Windows (PowerShell):
```powershell
curl.exe -X GET "http://localhost:8000/api/v2/users/roles" -H "Authorization: Bearer 2|eJnDVVbwaoNtfiFYgm7ewEetIHQadfy60252ZSnT70844d92"
```

#### Windows (CMD) / macOS / Linux:
```bash
curl -X GET "http://localhost:8000/api/v2/users/roles" -H "Authorization: Bearer 2|eJnDVVbwaoNtfiFYgm7ewEetIHQadfy60252ZSnT70844d92"
```

### 3. Create User
**POST** `/api/v2/users`

#### Create JSON file (user.json):
```json
{
  "name": "Test User Management",
  "email": "testmanagement@example.com",
  "password": "password123",
  "password_confirmation": "password123",
  "role_id": 4
}
```

#### Windows (PowerShell):
```powershell
curl.exe -X POST "http://localhost:8000/api/v2/users" -H "Authorization: Bearer 2|eJnDVVbwaoNtfiFYgm7ewEetIHQadfy60252ZSnT70844d92" -H "Content-Type: application/json" -d "@user.json"
```

#### Windows (CMD) / macOS / Linux:
```bash
curl -X POST "http://localhost:8000/api/v2/users" -H "Authorization: Bearer 2|eJnDVVbwaoNtfiFYgm7ewEetIHQadfy60252ZSnT70844d92" -H "Content-Type: application/json" -d "@user.json"
```

### 4. Update User Balance
**PATCH** `/api/v2/users/{id}/balance`

#### Create JSON file (balance_update.json):
```json
{
  "balance": 5000,
  "reason": "Admin balance adjustment for testing"
}
```

#### Windows (PowerShell):
```powershell
curl.exe -X PATCH "http://localhost:8000/api/v2/users/10/balance" -H "Authorization: Bearer 2|eJnDVVbwaoNtfiFYgm7ewEetIHQadfy60252ZSnT70844d92" -H "Content-Type: application/json" -d "@balance_update.json"
```

#### Windows (CMD) / macOS / Linux:
```bash
curl -X PATCH "http://localhost:8000/api/v2/users/10/balance" -H "Authorization: Bearer 2|eJnDVVbwaoNtfiFYgm7ewEetIHQadfy60252ZSnT70844d92" -H "Content-Type: application/json" -d "@balance_update.json"
```

---

## AnalyticsController Testing

### 1. Dashboard Analytics
**GET** `/api/v2/analytics/dashboard`

#### Windows (PowerShell):
```powershell
curl.exe -X GET "http://localhost:8000/api/v2/analytics/dashboard" -H "Authorization: Bearer 2|eJnDVVbwaoNtfiFYgm7ewEetIHQadfy60252ZSnT70844d92"
```

#### Windows (CMD) / macOS / Linux:
```bash
curl -X GET "http://localhost:8000/api/v2/analytics/dashboard" -H "Authorization: Bearer 2|eJnDVVbwaoNtfiFYgm7ewEetIHQadfy60252ZSnT70844d92"
```

### 2. Deposit Analytics
**GET** `/api/v2/analytics/deposits`

#### Windows (PowerShell):
```powershell
curl.exe -X GET "http://localhost:8000/api/v2/analytics/deposits" -H "Authorization: Bearer 2|eJnDVVbwaoNtfiFYgm7ewEetIHQadfy60252ZSnT70844d92"
```

#### Windows (CMD) / macOS / Linux:
```bash
curl -X GET "http://localhost:8000/api/v2/analytics/deposits" -H "Authorization: Bearer 2|eJnDVVbwaoNtfiFYgm7ewEetIHQadfy60252ZSnT70844d92"
```

---

## Testing Checklist

### AdminController
- [ ] Dashboard statistics
- [ ] Get users with pagination
- [ ] Create user
- [ ] Update user
- [ ] Delete user
- [ ] System settings

### TenantController
- [ ] Get tenants
- [ ] Get tenant details
- [ ] Create tenant
- [ ] Update tenant
- [ ] Delete tenant
- [ ] Tenant statistics
- [ ] Toggle tenant status

### RVMController
- [ ] Get RVMs
- [ ] Get RVM details
- [ ] Create RVM
- [ ] Update RVM
- [ ] Delete RVM
- [ ] RVM statistics
- [ ] Update RVM status
- [ ] Regenerate API key

### UserManagementController
- [ ] Get users
- [ ] Get roles
- [ ] Get user details
- [ ] Create user
- [ ] Update user
- [ ] Delete user
- [ ] User statistics
- [ ] Update user balance

### AnalyticsController
- [ ] Dashboard analytics
- [ ] Deposit analytics
- [ ] Economy analytics
- [ ] User analytics
- [ ] RVM analytics
- [ ] Generate custom report

## Error Handling

### Common Error Responses:
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "field_name": ["Error message"]
  }
}
```

### HTTP Status Codes:
- `200` - Success
- `201` - Created
- `400` - Bad Request
- `401` - Unauthorized
- `403` - Forbidden
- `404` - Not Found
- `422` - Validation Error
- `500` - Internal Server Error

## Notes
- Semua endpoint memerlukan authentication
- Gunakan file JSON terpisah untuk POST/PUT requests
- Pastikan Docker container berjalan sebelum testing
- Token authentication berlaku untuk semua endpoints
- Database harus sudah di-seed dengan data awal
