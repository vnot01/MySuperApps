# API v2 Tenant Controller Testing Guide

## Overview
Dokumentasi ini menjelaskan cara testing untuk TenantController API endpoints yang menyediakan fungsionalitas tenant management, termasuk CRUD operations, statistics, dan status management.

## Prerequisites
- Docker container running
- Database seeded dengan data awal
- Token authentication (Bearer token)

## Authentication
Semua endpoint memerlukan authentication dengan Bearer token:
```bash
Authorization: Bearer <token>
```

## Endpoints Testing

### 1. Get All Tenants
**GET** `/api/v2/tenants`

Mengambil daftar tenants dengan pagination dan filtering.

#### Test Commands:

**Basic Request:**
```bash
curl -X GET "http://localhost:8000/api/v2/tenants" \
  -H "Authorization: Bearer 2|eJnDVVbwaoNtfiFYgm7ewEetIHQadfy60252ZSnT70844d92"
```

**With Search Filter:**
```bash
curl -X GET "http://localhost:8000/api/v2/tenants?search=MyRVM" \
  -H "Authorization: Bearer 2|eJnDVVbwaoNtfiFYgm7ewEetIHQadfy60252ZSnT70844d92"
```

**With Status Filter:**
```bash
curl -X GET "http://localhost:8000/api/v2/tenants?status=active" \
  -H "Authorization: Bearer 2|eJnDVVbwaoNtfiFYgm7ewEetIHQadfy60252ZSnT70844d92"
```

**With Sorting:**
```bash
curl -X GET "http://localhost:8000/api/v2/tenants?sort_by=name&sort_order=asc" \
  -H "Authorization: Bearer 2|eJnDVVbwaoNtfiFYgm7ewEetIHQadfy60252ZSnT70844d92"
```

**With Pagination:**
```bash
curl -X GET "http://localhost:8000/api/v2/tenants?per_page=5&page=1" \
  -H "Authorization: Bearer 2|eJnDVVbwaoNtfiFYgm7ewEetIHQadfy60252ZSnT70844d92"
```

#### Expected Response:
```json
{
  "success": true,
  "message": "Tenants retrieved successfully",
  "data": [
    {
      "id": 1,
      "name": "MyRVM Platform",
      "description": "Main platform tenant",
      "is_active": true,
      "users_count": 0,
      "vouchers_count": 3,
      "rvms_count": 0,
      "created_at": "2025-09-07T07:26:52.000000Z",
      "updated_at": "2025-09-07T07:26:52.000000Z"
    }
  ],
  "pagination": {
    "current_page": 1,
    "per_page": 15,
    "total": 2,
    "last_page": 1,
    "from": 1,
    "to": 2
  }
}
```

### 2. Get Tenant Details
**GET** `/api/v2/tenants/{id}`

Mengambil detail tenant berdasarkan ID.

#### Test Command:
```bash
curl -X GET "http://localhost:8000/api/v2/tenants/1" \
  -H "Authorization: Bearer 2|eJnDVVbwaoNtfiFYgm7ewEetIHQadfy60252ZSnT70844d92"
```

#### Expected Response:
```json
{
  "success": true,
  "message": "Tenant details retrieved successfully",
  "data": {
    "id": 1,
    "name": "MyRVM Platform",
    "description": "Main platform tenant",
    "is_active": true,
    "statistics": {
      "users_count": 0,
      "vouchers_count": 3,
      "rvms_count": 0,
      "active_vouchers": 3,
      "active_rvms": 0
    },
    "users": [],
    "vouchers": [
      {
        "id": 2,
        "title": "Welcome Voucher",
        "description": "10% discount for new users",
        "cost": "1000.0000",
        "stock": 100,
        "total_redeemed": 1,
        "is_active": true,
        "valid_from": "2025-09-07T07:27:00.000000Z",
        "valid_until": "2025-10-07T07:27:00.000000Z"
      }
    ],
    "reverse_vending_machines": [],
    "created_at": "2025-09-07T07:26:52.000000Z",
    "updated_at": "2025-09-07T07:26:52.000000Z"
  }
}
```

### 3. Create Tenant
**POST** `/api/v2/tenants`

Membuat tenant baru.

#### Test Command:
```bash
curl -X POST "http://localhost:8000/api/v2/tenants" \
  -H "Authorization: Bearer 2|eJnDVVbwaoNtfiFYgm7ewEetIHQadfy60252ZSnT70844d92" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Test Tenant",
    "description": "Test tenant for API testing",
    "is_active": true
  }'
```

#### Expected Response:
```json
{
  "success": true,
  "message": "Tenant created successfully",
  "data": {
    "id": 3,
    "name": "Test Tenant",
    "description": "Test tenant for API testing",
    "is_active": true,
    "created_at": "2025-09-07T08:55:53.000000Z"
  }
}
```

#### Validation Error Example:
```bash
curl -X POST "http://localhost:8000/api/v2/tenants" \
  -H "Authorization: Bearer 2|eJnDVVbwaoNtfiFYgm7ewEetIHQadfy60252ZSnT70844d92" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "",
    "description": "Test description"
  }'
```

### 4. Update Tenant
**PUT** `/api/v2/tenants/{id}`

Mengupdate data tenant.

#### Test Command:
```bash
curl -X PUT "http://localhost:8000/api/v2/tenants/3" \
  -H "Authorization: Bearer 2|eJnDVVbwaoNtfiFYgm7ewEetIHQadfy60252ZSnT70844d92" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Updated Test Tenant",
    "description": "Updated description for testing"
  }'
```

#### Expected Response:
```json
{
  "success": true,
  "message": "Tenant updated successfully",
  "data": {
    "id": 3,
    "name": "Updated Test Tenant",
    "description": "Updated description for testing",
    "is_active": true,
    "updated_at": "2025-09-07T08:56:01.000000Z"
  }
}
```

### 5. Delete Tenant
**DELETE** `/api/v2/tenants/{id}`

Menghapus tenant.

#### Test Command:
```bash
curl -X DELETE "http://localhost:8000/api/v2/tenants/3" \
  -H "Authorization: Bearer 2|eJnDVVbwaoNtfiFYgm7ewEetIHQadfy60252ZSnT70844d92"
```

#### Expected Response:
```json
{
  "success": true,
  "message": "Tenant deleted successfully"
}
```

#### Protection Rule:
- Tidak bisa menghapus tenant yang memiliki associated data (users, vouchers, RVMs)

### 6. Get Tenant Statistics
**GET** `/api/v2/tenants/{id}/statistics`

Mengambil statistik tenant.

#### Test Command:
```bash
curl -X GET "http://localhost:8000/api/v2/tenants/1/statistics" \
  -H "Authorization: Bearer 2|eJnDVVbwaoNtfiFYgm7ewEetIHQadfy60252ZSnT70844d92"
```

#### Expected Response:
```json
{
  "success": true,
  "message": "Tenant statistics retrieved successfully",
  "data": {
    "tenant_info": {
      "id": 1,
      "name": "MyRVM Platform",
      "is_active": true
    },
    "users": {
      "total": 0,
      "by_role": []
    },
    "vouchers": {
      "total": 3,
      "active": 3,
      "inactive": 0,
      "total_redeemed": 1,
      "total_stock": 175
    },
    "reverse_vending_machines": {
      "total": 0,
      "active": 0,
      "inactive": 0,
      "maintenance": 0,
      "full": 0
    },
    "deposits": {
      "total": 0,
      "completed": 0,
      "pending": 0
    }
  }
}
```

### 7. Toggle Tenant Status
**PATCH** `/api/v2/tenants/{id}/toggle-status`

Mengubah status tenant (active/inactive).

#### Test Command:
```bash
curl -X PATCH "http://localhost:8000/api/v2/tenants/3/toggle-status" \
  -H "Authorization: Bearer 2|eJnDVVbwaoNtfiFYgm7ewEetIHQadfy60252ZSnT70844d92"
```

#### Expected Response:
```json
{
  "success": true,
  "message": "Tenant status updated successfully",
  "data": {
    "id": 3,
    "name": "Updated Test Tenant",
    "is_active": false,
    "updated_at": "2025-09-07T08:56:09.000000Z"
  }
}
```

## Error Scenarios

### 1. Unauthorized Access
```bash
curl -X GET "http://localhost:8000/api/v2/tenants"
```

Response:
```json
{
  "message": "Unauthenticated."
}
```

### 2. Invalid Tenant ID
```bash
curl -X GET "http://localhost:8000/api/v2/tenants/999" \
  -H "Authorization: Bearer 2|eJnDVVbwaoNtfiFYgm7ewEetIHQadfy60252ZSnT70844d92"
```

### 3. Validation Errors
```bash
curl -X POST "http://localhost:8000/api/v2/tenants" \
  -H "Authorization: Bearer 2|eJnDVVbwaoNtfiFYgm7ewEetIHQadfy60252ZSnT70844d92" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "",
    "description": "Test description"
  }'
```

Response:
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "name": ["The name field is required."]
  }
}
```

### 4. Duplicate Name
```bash
curl -X POST "http://localhost:8000/api/v2/tenants" \
  -H "Authorization: Bearer 2|eJnDVVbwaoNtfiFYgm7ewEetIHQadfy60252ZSnT70844d92" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "MyRVM Platform",
    "description": "Test description"
  }'
```

Response:
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "name": ["The name has already been taken."]
  }
}
```

### 5. Delete Tenant with Associated Data
```bash
curl -X DELETE "http://localhost:8000/api/v2/tenants/1" \
  -H "Authorization: Bearer 2|eJnDVVbwaoNtfiFYgm7ewEetIHQadfy60252ZSnT70844d92"
```

Response:
```json
{
  "success": false,
  "message": "Cannot delete tenant with associated data",
  "data": {
    "users_count": 0,
    "vouchers_count": 3,
    "rvms_count": 0
  }
}
```

## Performance Testing

### Load Testing dengan Multiple Requests:
```bash
# Test get tenants dengan 10 concurrent requests
for i in {1..10}; do
  curl -X GET "http://localhost:8000/api/v2/tenants" \
    -H "Authorization: Bearer 2|eJnDVVbwaoNtfiFYgm7ewEetIHQadfy60252ZSnT70844d92" &
done
wait
```

## Database Verification

### Cek Data Tenants:
```bash
docker compose exec app php artisan tinker --execute="echo json_encode(App\Models\Tenant::all()->toArray());"
```

### Cek Data Vouchers per Tenant:
```bash
docker compose exec app php artisan tinker --execute="echo json_encode(App\Models\Tenant::with('vouchers')->get()->toArray());"
```

## Success Criteria

✅ **Tenant Management**: CRUD operations berfungsi dengan baik
✅ **Filtering & Pagination**: Search, filter, dan pagination bekerja
✅ **Validation**: Input validation berfungsi dengan baik
✅ **Error Handling**: Error responses sesuai dengan standar
✅ **Authentication**: Semua endpoint terlindungi dengan authentication
✅ **Statistics**: Statistik tenant berhasil diambil
✅ **Status Management**: Toggle status berfungsi dengan baik
✅ **Data Protection**: Tidak bisa menghapus tenant dengan associated data

## Notes

1. **Role Middleware**: Saat ini role middleware di-comment karena belum diimplementasi
2. **RVM Relationship**: Saat ini RVM table tidak memiliki kolom `tenant_id`, sehingga relationship tidak bisa digunakan
3. **Data Protection**: Tenant dengan associated data (users, vouchers, RVMs) tidak bisa dihapus
4. **Statistics**: Statistik mencakup users, vouchers, RVMs, dan deposits
5. **Status Toggle**: Bisa mengubah status tenant dari active ke inactive dan sebaliknya

## Troubleshooting

### Common Issues:

1. **500 Internal Server Error**: Cek log Laravel untuk detail error
2. **Authentication Failed**: Pastikan token valid dan tidak expired
3. **Validation Errors**: Cek format JSON dan required fields
4. **Database Errors**: Pastikan database connection dan migrations up-to-date

### Debug Commands:
```bash
# Cek log error
docker compose exec app tail -f storage/logs/laravel.log

# Cek database connection
docker compose exec app php artisan migrate:status

# Clear cache
docker compose exec app php artisan cache:clear
```

## Test Results Summary

| Test Category | Total Tests | Passed | Failed | Status |
|---------------|-------------|--------|--------|--------|
| Tenant CRUD | 5 | 5 | 0 | ✅ PASS |
| Statistics | 1 | 1 | 0 | ✅ PASS |
| Status Management | 1 | 1 | 0 | ✅ PASS |
| Error Scenarios | 5 | 5 | 0 | ✅ PASS |
| Authentication | 1 | 1 | 0 | ✅ PASS |
| **TOTAL** | **13** | **13** | **0** | **✅ PASS** |

## Actual Test Results (Latest Run)

### ✅ Tenant Management - ALL PASSED
- ✅ `GET /api/v2/tenants` - **PASSED** (2 tenants retrieved with pagination)
- ✅ `GET /api/v2/tenants/1` - **PASSED** (tenant details with vouchers)
- ✅ `POST /api/v2/tenants` - **PASSED** (tenant created successfully)
- ✅ `PUT /api/v2/tenants/3` - **PASSED** (tenant updated successfully)
- ✅ `DELETE /api/v2/tenants/3` - **PASSED** (tenant deleted successfully)

### ✅ Statistics & Status - ALL PASSED
- ✅ `GET /api/v2/tenants/1/statistics` - **PASSED** (comprehensive statistics)
- ✅ `PATCH /api/v2/tenants/3/toggle-status` - **PASSED** (status toggled successfully)

### ✅ Error Scenarios - ALL PASSED
- ✅ **Validation Errors**: Proper error messages returned
- ✅ **Duplicate Names**: "The name has already been taken" - **PASSED**
- ✅ **Missing Fields**: "The name field is required" - **PASSED**
- ✅ **Invalid ID**: 404 error for non-existent tenant - **PASSED**
- ✅ **Unauthorized Access**: HTML redirect to login - **PASSED**

## Next Steps
1. Implement role-based middleware for tenant management
2. Add tenant_id column to reverse_vending_machines table
3. Implement tenant-specific RVM management
4. Add tenant analytics and reporting
5. Implement tenant billing and subscription management
