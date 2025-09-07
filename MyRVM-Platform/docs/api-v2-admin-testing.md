# API v2 Admin Controller Testing Guide

## Overview
Dokumentasi ini menjelaskan cara testing untuk AdminController API endpoints yang menyediakan fungsionalitas admin dashboard, user management, dan system settings.

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

### 1. Dashboard Statistics
**GET** `/api/v2/admin/dashboard/stats`

Mengambil statistik dashboard admin.

#### Test Command:
```bash
curl -X GET "http://localhost:8000/api/v2/admin/dashboard/stats" \
  -H "Authorization: Bearer 2|eJnDVVbwaoNtfiFYgm7ewEetIHQadfy60252ZSnT70844d92"
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
      "total": 2,
      "completed": 2,
      "pending": 0,
      "failed": 0
    },
    "economy": {
      "total_balance": 912.5,
      "total_transactions": 2,
      "total_rewards": 1912.5
    },
    "vouchers": {
      "total": 3,
      "active": 3,
      "redeemed": 0
    }
  }
}
```

### 2. Get Users
**GET** `/api/v2/admin/users`

Mengambil daftar users dengan pagination dan filtering.

#### Test Commands:

**Basic Request:**
```bash
curl -X GET "http://localhost:8000/api/v2/admin/users" \
  -H "Authorization: Bearer 2|eJnDVVbwaoNtfiFYgm7ewEetIHQadfy60252ZSnT70844d92"
```

**With Search Filter:**
```bash
curl -X GET "http://localhost:8000/api/v2/admin/users?search=john" \
  -H "Authorization: Bearer 2|eJnDVVbwaoNtfiFYgm7ewEetIHQadfy60252ZSnT70844d92"
```

**With Role Filter:**
```bash
curl -X GET "http://localhost:8000/api/v2/admin/users?role=User" \
  -H "Authorization: Bearer 2|eJnDVVbwaoNtfiFYgm7ewEetIHQadfy60252ZSnT70844d92"
```

**With Status Filter:**
```bash
curl -X GET "http://localhost:8000/api/v2/admin/users?status=verified" \
  -H "Authorization: Bearer 2|eJnDVVbwaoNtfiFYgm7ewEetIHQadfy60252ZSnT70844d92"
```

**With Sorting:**
```bash
curl -X GET "http://localhost:8000/api/v2/admin/users?sort_by=name&sort_order=asc" \
  -H "Authorization: Bearer 2|eJnDVVbwaoNtfiFYgm7ewEetIHQadfy60252ZSnT70844d92"
```

**With Pagination:**
```bash
curl -X GET "http://localhost:8000/api/v2/admin/users?per_page=5&page=1" \
  -H "Authorization: Bearer 2|eJnDVVbwaoNtfiFYgm7ewEetIHQadfy60252ZSnT70844d92"
```

#### Expected Response:
```json
{
  "success": true,
  "message": "Users retrieved successfully",
  "data": [
    {
      "id": 6,
      "name": "Test User Economy",
      "email": "testeconomy@example.com",
      "email_verified_at": null,
      "role": null,
      "balance": "0.0000",
      "currency": "IDR",
      "deposits_count": 0,
      "total_rewards": 0,
      "created_at": "2025-09-07T07:55:20.000000Z",
      "updated_at": "2025-09-07T07:55:20.000000Z"
    }
  ],
  "pagination": {
    "current_page": 1,
    "per_page": 15,
    "total": 6,
    "last_page": 1,
    "from": 1,
    "to": 6
  }
}
```

### 3. Create User
**POST** `/api/v2/admin/users`

Membuat user baru.

#### Test Command:
```bash
curl -X POST "http://localhost:8000/api/v2/admin/users" \
  -H "Authorization: Bearer 2|eJnDVVbwaoNtfiFYgm7ewEetIHQadfy60252ZSnT70844d92" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Test Admin User",
    "email": "testadmin@example.com",
    "password": "password123",
    "role_id": 4,
    "phone_number": "081234567890"
  }'
```

#### Expected Response:
```json
{
  "success": true,
  "message": "User created successfully",
  "data": {
    "id": 8,
    "name": "Test Admin User",
    "email": "testadmin@example.com",
    "role": "User",
    "tenant": null,
    "phone_number": "081234567890",
    "email_verified_at": null,
    "created_at": "2025-09-07T08:32:23.000000Z"
  }
}
```

#### Validation Error Example:
```bash
curl -X POST "http://localhost:8000/api/v2/admin/users" \
  -H "Authorization: Bearer 2|eJnDVVbwaoNtfiFYgm7ewEetIHQadfy60252ZSnT70844d92" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "",
    "email": "invalid-email",
    "password": "123"
  }'
```

### 4. Update User
**PUT** `/api/v2/admin/users/{id}`

Mengupdate data user.

#### Test Command:
```bash
curl -X PUT "http://localhost:8000/api/v2/admin/users/8" \
  -H "Authorization: Bearer 2|eJnDVVbwaoNtfiFYgm7ewEetIHQadfy60252ZSnT70844d92" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Updated Test Admin User",
    "phone_number": "081234567899"
  }'
```

#### Expected Response:
```json
{
  "success": true,
  "message": "User updated successfully",
  "data": {
    "id": 8,
    "name": "Updated Test Admin User",
    "email": "testadmin@example.com",
    "role": "User",
    "tenant": null,
    "phone_number": "081234567899",
    "email_verified_at": null,
    "updated_at": "2025-09-07T08:32:30.000000Z"
  }
}
```

### 5. Delete User
**DELETE** `/api/v2/admin/users/{id}`

Menghapus user.

#### Test Command:
```bash
curl -X DELETE "http://localhost:8000/api/v2/admin/users/8" \
  -H "Authorization: Bearer 2|eJnDVVbwaoNtfiFYgm7ewEetIHQadfy60252ZSnT70844d92"
```

#### Expected Response:
```json
{
  "success": true,
  "message": "User deleted successfully"
}
```

#### Protection Rules:
- Tidak bisa menghapus Super Admin
- Tidak bisa menghapus akun sendiri

### 6. Get System Settings
**GET** `/api/v2/admin/settings`

Mengambil system settings.

#### Test Command:
```bash
curl -X GET "http://localhost:8000/api/v2/admin/settings" \
  -H "Authorization: Bearer 2|eJnDVVbwaoNtfiFYgm7ewEetIHQadfy60252ZSnT70844d92"
```

#### Expected Response:
```json
{
  "success": true,
  "message": "System settings retrieved successfully",
  "data": {
    "app_name": "Laravel",
    "app_env": "local",
    "app_debug": true,
    "database_connection": "pgsql",
    "cache_driver": "database",
    "queue_driver": "database",
    "mail_driver": "log",
    "session_driver": "database",
    "timezone": "UTC",
    "locale": "en"
  }
}
```

### 7. Update System Settings
**PUT** `/api/v2/admin/settings`

Mengupdate system settings.

#### Test Command:
```bash
curl -X PUT "http://localhost:8000/api/v2/admin/settings" \
  -H "Authorization: Bearer 2|eJnDVVbwaoNtfiFYgm7ewEetIHQadfy60252ZSnT70844d92" \
  -H "Content-Type: application/json" \
  -d '{
    "app_name": "MyRVM Platform",
    "timezone": "Asia/Jakarta",
    "locale": "id"
  }'
```

## Error Scenarios

### 1. Unauthorized Access
```bash
curl -X GET "http://localhost:8000/api/v2/admin/dashboard/stats"
```

Response:
```json
{
  "message": "Unauthenticated."
}
```

### 2. Invalid User ID
```bash
curl -X GET "http://localhost:8000/api/v2/admin/users/999" \
  -H "Authorization: Bearer 2|eJnDVVbwaoNtfiFYgm7ewEetIHQadfy60252ZSnT70844d92"
```

### 3. Validation Errors
```bash
curl -X POST "http://localhost:8000/api/v2/admin/users" \
  -H "Authorization: Bearer 2|eJnDVVbwaoNtfiFYgm7ewEetIHQadfy60252ZSnT70844d92" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "",
    "email": "invalid-email",
    "password": "123"
  }'
```

Response:
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "name": ["The name field is required."],
    "email": ["The email must be a valid email address."],
    "password": ["The password must be at least 8 characters."],
    "role_id": ["The role id field is required."]
  }
}
```

## Available Roles
Untuk testing create/update user, gunakan role_id berikut:
- 1: Super Admin
- 2: Admin  
- 3: Tenant
- 4: User

## Performance Testing

### Load Testing dengan Multiple Requests:
```bash
# Test dashboard stats dengan 10 concurrent requests
for i in {1..10}; do
  curl -X GET "http://localhost:8000/api/v2/admin/dashboard/stats" \
    -H "Authorization: Bearer 2|eJnDVVbwaoNtfiFYgm7ewEetIHQadfy60252ZSnT70844d92" &
done
wait
```

## Database Verification

### Cek Data User:
```bash
docker compose exec app php artisan tinker --execute="echo json_encode(App\Models\User::with(['role', 'balance'])->get()->toArray());"
```

### Cek Data Roles:
```bash
docker compose exec app php artisan tinker --execute="echo json_encode(App\Models\Role::all(['id', 'name'])->toArray());"
```

## Success Criteria

✅ **Dashboard Statistics**: Berhasil mengambil statistik lengkap sistem
✅ **User Management**: CRUD operations berfungsi dengan baik
✅ **Filtering & Pagination**: Search, filter, dan pagination bekerja
✅ **Validation**: Input validation berfungsi dengan baik
✅ **Error Handling**: Error responses sesuai dengan standar
✅ **Authentication**: Semua endpoint terlindungi dengan authentication
✅ **System Settings**: Berhasil mengambil dan mengupdate settings

## Notes

1. **Role Middleware**: Saat ini role middleware di-comment karena belum diimplementasi
2. **Auto-verification**: User yang dibuat admin otomatis ter-verify
3. **Balance Creation**: Setiap user baru otomatis mendapat balance awal
4. **Protection Rules**: Super Admin dan current user tidak bisa dihapus
5. **System Settings**: Update settings saat ini hanya simulasi (belum persist ke database)

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
