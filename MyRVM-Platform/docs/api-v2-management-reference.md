# MyRVM v2.1 - Management APIs Quick Reference

## Base URL
```
http://localhost:8000/api/v2
```

## Authentication
```
Authorization: Bearer <token>
```

## Platform Commands

### Windows PowerShell
```powershell
curl.exe -X GET "http://localhost:8000/api/v2/endpoint" -H "Authorization: Bearer <token>"
```

### Windows CMD / macOS / Linux
```bash
curl -X GET "http://localhost:8000/api/v2/endpoint" -H "Authorization: Bearer <token>"
```

## Endpoints Summary

### Admin Controller
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/admin/dashboard/stats` | Dashboard statistics |
| GET | `/admin/users` | Get users with pagination |
| POST | `/admin/users` | Create user |
| PUT | `/admin/users/{id}` | Update user |
| DELETE | `/admin/users/{id}` | Delete user |
| GET | `/admin/system/settings` | Get system settings |
| PUT | `/admin/system/settings` | Update system settings |

### Tenant Controller
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/tenants` | Get tenants |
| GET | `/tenants/{id}` | Get tenant details |
| POST | `/tenants` | Create tenant |
| PUT | `/tenants/{id}` | Update tenant |
| DELETE | `/tenants/{id}` | Delete tenant |
| GET | `/tenants/{id}/statistics` | Tenant statistics |
| PATCH | `/tenants/{id}/toggle-status` | Toggle tenant status |

### RVM Controller
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/rvms` | Get RVMs |
| GET | `/rvms/{id}` | Get RVM details |
| POST | `/rvms` | Create RVM |
| PUT | `/rvms/{id}` | Update RVM |
| DELETE | `/rvms/{id}` | Delete RVM |
| GET | `/rvms/{id}/statistics` | RVM statistics |
| PATCH | `/rvms/{id}/status` | Update RVM status |
| POST | `/rvms/{id}/regenerate-api-key` | Regenerate API key |

### User Management Controller
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/users` | Get users |
| GET | `/users/roles` | Get roles |
| GET | `/users/{id}` | Get user details |
| POST | `/users` | Create user |
| PUT | `/users/{id}` | Update user |
| DELETE | `/users/{id}` | Delete user |
| GET | `/users/{id}/statistics` | User statistics |
| PATCH | `/users/{id}/balance` | Update user balance |

### Analytics Controller
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/analytics/dashboard` | Dashboard analytics |
| GET | `/analytics/deposits` | Deposit analytics |
| GET | `/analytics/economy` | Economy analytics |
| GET | `/analytics/users` | User analytics |
| GET | `/analytics/rvms` | RVM analytics |
| POST | `/analytics/reports` | Generate custom report |

## Common Request Bodies

### Create User
```json
{
  "name": "User Name",
  "email": "user@example.com",
  "password": "password123",
  "password_confirmation": "password123",
  "role_id": 4
}
```

### Create Tenant
```json
{
  "name": "Tenant Name",
  "description": "Tenant description"
}
```

### Create RVM
```json
{
  "name": "RVM Name",
  "location": "RVM Location",
  "status": "active"
}
```

### Update User Balance
```json
{
  "balance": 5000,
  "reason": "Admin balance adjustment"
}
```

### Generate Report
```json
{
  "type": "deposits",
  "start_date": "2025-09-01",
  "end_date": "2025-09-30",
  "filters": {
    "status": "completed"
  }
}
```

## Response Format

### Success Response
```json
{
  "success": true,
  "message": "Operation successful",
  "data": { ... }
}
```

### Error Response
```json
{
  "success": false,
  "message": "Error message",
  "errors": {
    "field": ["Error details"]
  }
}
```

## HTTP Status Codes
- `200` - Success
- `201` - Created
- `400` - Bad Request
- `401` - Unauthorized
- `403` - Forbidden
- `404` - Not Found
- `422` - Validation Error
- `500` - Internal Server Error

## Testing Files
- `api-v2-management-testing.md` - Detailed testing guide
- `api-v2-management-postman-collection.json` - Postman collection
- `api-v2-management-environment.json` - Postman environment
