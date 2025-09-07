# API v2 - User Management Testing

## Overview
Dokumentasi testing untuk API User Management yang mencakup semua endpoint CRUD dan operasi khusus untuk mengelola users, roles, dan balance management.

## Prerequisites
- Server Laravel berjalan di `http://localhost:8000`
- Token autentikasi yang valid
- Database sudah di-migrate dan di-seed

## Authentication
Semua endpoint memerlukan autentikasi Bearer Token:
```bash
Authorization: Bearer YOUR_TOKEN_HERE
```

## Endpoints Testing

### 1. Get All Users
**GET** `/api/v2/users`

#### Test Command
```bash
curl -X GET "http://localhost:8000/api/v2/users" \
  -H "Authorization: Bearer 2|eJnDVVbwaoNtfiFYgm7ewEetIHQadfy60252ZSnT70844d92"
```

#### Expected Response
```json
{
  "success": true,
  "message": "Users retrieved successfully",
  "data": [
    {
      "id": 4,
      "name": "John Doe",
      "email": "john@test.com",
      "phone_number": null,
      "email_verified_at": "2025-09-07T05:15:04.000000Z",
      "role": "User",
      "balance": "912.5000",
      "deposits_count": 2,
      "voucher_redemptions_count": 1,
      "created_at": "2025-09-07T05:15:04.000000Z",
      "updated_at": "2025-09-07T05:15:04.000000Z"
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

#### Test with Filters
```bash
# Filter by role
curl -X GET "http://localhost:8000/api/v2/users?role_id=4" \
  -H "Authorization: Bearer 2|eJnDVVbwaoNtfiFYgm7ewEetIHQadfy60252ZSnT70844d92"

# Search by name/email
curl -X GET "http://localhost:8000/api/v2/users?search=John" \
  -H "Authorization: Bearer 2|eJnDVVbwaoNtfiFYgm7ewEetIHQadfy60252ZSnT70844d92"

# Filter by status
curl -X GET "http://localhost:8000/api/v2/users?status=active" \
  -H "Authorization: Bearer 2|eJnDVVbwaoNtfiFYgm7ewEetIHQadfy60252ZSnT70844d92"

# Sort by name
curl -X GET "http://localhost:8000/api/v2/users?sort_by=name&sort_order=asc" \
  -H "Authorization: Bearer 2|eJnDVVbwaoNtfiFYgm7ewEetIHQadfy60252ZSnT70844d92"
```

### 2. Get Available Roles
**GET** `/api/v2/users/roles`

#### Test Command
```bash
curl -X GET "http://localhost:8000/api/v2/users/roles" \
  -H "Authorization: Bearer 2|eJnDVVbwaoNtfiFYgm7ewEetIHQadfy60252ZSnT70844d92"
```

#### Expected Response
```json
{
  "success": true,
  "message": "Roles retrieved successfully",
  "data": [
    {
      "id": 1,
      "name": "Super Admin",
      "slug": "super-admin"
    },
    {
      "id": 2,
      "name": "Admin",
      "slug": "admin"
    },
    {
      "id": 3,
      "name": "Tenant",
      "slug": "tenant"
    },
    {
      "id": 4,
      "name": "User",
      "slug": "user"
    }
  ]
}
```

### 3. Get User Details
**GET** `/api/v2/users/{id}`

#### Test Command
```bash
curl -X GET "http://localhost:8000/api/v2/users/4" \
  -H "Authorization: Bearer 2|eJnDVVbwaoNtfiFYgm7ewEetIHQadfy60252ZSnT70844d92"
```

#### Expected Response
```json
{
  "success": true,
  "message": "User details retrieved successfully",
  "data": {
    "id": 4,
    "name": "John Doe",
    "email": "john@test.com",
    "phone_number": null,
    "email_verified_at": "2025-09-07T05:15:04.000000Z",
    "role": {
      "id": 4,
      "name": "User"
    },
    "balance": {
      "balance": "912.5000",
      "updated_at": "2025-09-07T07:27:44.000000Z"
    },
    "statistics": {
      "deposits_count": 2,
      "completed_deposits": 1,
      "total_rewards_earned": "1912.50",
      "voucher_redemptions_count": 1,
      "total_vouchers_redeemed": "1000.0000"
    },
    "recent_deposits": [
      {
        "id": 3,
        "rvm_id": 1,
        "status": "completed",
        "reward_amount": "1912.50",
        "cv_confidence": null,
        "cv_waste_type": null,
        "created_at": "2025-09-07T06:55:09.000000Z"
      }
    ],
    "recent_voucher_redemptions": [
      {
        "id": 2,
        "voucher_title": "Welcome Voucher",
        "redemption_code": "VRM68BD33EFDE1B6",
        "cost_at_redemption": "1000.0000",
        "redeemed_at": "2025-09-07T07:27:43.000000Z",
        "used_at": null
      }
    ],
    "created_at": "2025-09-07T05:15:04.000000Z",
    "updated_at": "2025-09-07T05:15:04.000000Z"
  }
}
```

### 4. Create User
**POST** `/api/v2/users`

#### Test Command
```bash
curl -X POST "http://localhost:8000/api/v2/users" \
  -H "Authorization: Bearer 2|eJnDVVbwaoNtfiFYgm7ewEetIHQadfy60252ZSnT70844d92" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Test User Management",
    "email": "testusermgmt@example.com",
    "password": "password123",
    "password_confirmation": "password123",
    "phone_number": "081234567890",
    "role_id": 4
  }'
```

#### Expected Response
```json
{
  "success": true,
  "message": "User created successfully",
  "data": {
    "id": 9,
    "name": "Test User Management",
    "email": "testusermgmt@example.com",
    "phone_number": "081234567890",
    "role_id": 4,
    "email_verified_at": null,
    "created_at": "2025-09-07T09:20:29.000000Z"
  }
}
```

#### Validation Error Test
```bash
curl -X POST "http://localhost:8000/api/v2/users" \
  -H "Authorization: Bearer 2|eJnDVVbwaoNtfiFYgm7ewEetIHQadfy60252ZSnT70844d92" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "",
    "email": "invalid-email",
    "password": "123",
    "role_id": 999
  }'
```

### 5. Update User
**PUT** `/api/v2/users/{id}`

#### Test Command
```bash
curl -X PUT "http://localhost:8000/api/v2/users/9" \
  -H "Authorization: Bearer 2|eJnDVVbwaoNtfiFYgm7ewEetIHQadfy60252ZSnT70844d92" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Test User Management Updated",
    "phone_number": "081234567891",
    "role_id": 3
  }'
```

#### Expected Response
```json
{
  "success": true,
  "message": "User updated successfully",
  "data": {
    "id": 9,
    "name": "Test User Management Updated",
    "email": "testusermgmt@example.com",
    "phone_number": "081234567891",
    "role_id": 3,
    "updated_at": "2025-09-07T09:20:38.000000Z"
  }
}
```

### 6. Get User Statistics
**GET** `/api/v2/users/{id}/statistics`

#### Test Command
```bash
curl -X GET "http://localhost:8000/api/v2/users/4/statistics" \
  -H "Authorization: Bearer 2|eJnDVVbwaoNtfiFYgm7ewEetIHQadfy60252ZSnT70844d92"
```

#### Expected Response
```json
{
  "success": true,
  "message": "User statistics retrieved successfully",
  "data": {
    "user_info": {
      "id": 4,
      "name": "John Doe",
      "email": "john@test.com",
      "role": "User"
    },
    "balance": {
      "current_balance": "912.5000",
      "total_earned": "1912.50",
      "total_spent": "1000.0000"
    },
    "deposits": {
      "total": 2,
      "completed": 1,
      "pending": 0,
      "processing": 1,
      "rejected": 0,
      "avg_reward": "1912.5000000000000000"
    },
    "voucher_redemptions": {
      "total": 1,
      "used": 0,
      "unused": 1,
      "total_cost": "1000.0000"
    },
    "activity": {
      "first_deposit": "2025-09-07T05:49:24.000000Z",
      "last_deposit": "2025-09-07T06:55:09.000000Z",
      "first_redemption": "2025-09-07T07:27:43.000000Z",
      "last_redemption": "2025-09-07T07:27:43.000000Z"
    }
  }
}
```

### 7. Update User Balance
**PATCH** `/api/v2/users/{id}/balance`

#### Test Command
```bash
curl -X PATCH "http://localhost:8000/api/v2/users/9/balance" \
  -H "Authorization: Bearer 2|eJnDVVbwaoNtfiFYgm7ewEetIHQadfy60252ZSnT70844d92" \
  -H "Content-Type: application/json" \
  -d '{
    "balance": 5000,
    "reason": "Initial balance setup"
  }'
```

#### Expected Response
```json
{
  "success": true,
  "message": "User balance updated successfully",
  "data": {
    "user_id": 9,
    "old_balance": "5000.0000",
    "new_balance": 5000,
    "difference": 0,
    "reason": "Initial balance setup",
    "updated_at": "2025-09-07T09:20:55.000000Z"
  }
}
```

### 8. Delete User
**DELETE** `/api/v2/users/{id}`

#### Test Command
```bash
curl -X DELETE "http://localhost:8000/api/v2/users/9" \
  -H "Authorization: Bearer 2|eJnDVVbwaoNtfiFYgm7ewEetIHQadfy60252ZSnT70844d92"
```

#### Expected Response
```json
{
  "success": true,
  "message": "User deleted successfully"
}
```

#### Test Delete with Associated Data
```bash
# Try to delete user with deposits/voucher redemptions
curl -X DELETE "http://localhost:8000/api/v2/users/4" \
  -H "Authorization: Bearer 2|eJnDVVbwaoNtfiFYgm7ewEetIHQadfy60252ZSnT70844d92"
```

Expected Error Response:
```json
{
  "success": false,
  "message": "Cannot delete user with associated data",
  "data": {
    "deposits_count": 2,
    "voucher_redemptions_count": 1
  }
}
```

## Error Scenarios

### 1. Unauthorized Access
```bash
curl -X GET "http://localhost:8000/api/v2/users"
```

Expected Response:
```json
{
  "message": "Unauthenticated."
}
```

### 2. User Not Found
```bash
curl -X GET "http://localhost:8000/api/v2/users/999" \
  -H "Authorization: Bearer 2|eJnDVVbwaoNtfiFYgm7ewEetIHQadfy60252ZSnT70844d92"
```

Expected Response:
```json
{
  "message": "No query results for model [App\\Models\\User] 999"
}
```

### 3. Validation Errors
```bash
curl -X POST "http://localhost:8000/api/v2/users" \
  -H "Authorization: Bearer 2|eJnDVVbwaoNtfiFYgm7ewEetIHQadfy60252ZSnT70844d92" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "",
    "email": "invalid-email",
    "password": "123",
    "role_id": 999
  }'
```

Expected Response:
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "name": ["The name field is required."],
    "email": ["The email must be a valid email address."],
    "password": ["The password must be at least 8 characters."],
    "role_id": ["The selected role id is invalid."]
  }
}
```

## Database Verification

### Check User Data
```bash
docker compose exec app php artisan tinker --execute="App\\Models\\User::with(['balance', 'role'])->get()->toArray()"
```

### Check User Balance
```bash
docker compose exec app php artisan tinker --execute="App\\Models\\UserBalance::all()->toArray()"
```

### Check User Transactions
```bash
docker compose exec app php artisan tinker --execute="App\\Models\\Transaction::where('user_id', 4)->get()->toArray()"
```

### Check User Deposits
```bash
docker compose exec app php artisan tinker --execute="App\\Models\\Deposit::where('user_id', 4)->get()->toArray()"
```

### Check User Voucher Redemptions
```bash
docker compose exec app php artisan tinker --execute="App\\Models\\VoucherRedemption::where('user_id', 4)->get()->toArray()"
```

## Performance Testing

### Load Test with Multiple Requests
```bash
# Test pagination performance
for i in {1..10}; do
  curl -X GET "http://localhost:8000/api/v2/users?page=$i" \
    -H "Authorization: Bearer 2|eJnDVVbwaoNtfiFYgm7ewEetIHQadfy60252ZSnT70844d92" &
done
wait
```

### Concurrent Access Test
```bash
# Test concurrent access to same user
for i in {1..5}; do
  curl -X GET "http://localhost:8000/api/v2/users/4" \
    -H "Authorization: Bearer 2|eJnDVVbwaoNtfiFYgm7ewEetIHQadfy60252ZSnT70844d92" &
done
wait
```

## Success Criteria

### ✅ All Tests Passed
- [x] GET /api/v2/users - List all users with pagination
- [x] GET /api/v2/users/roles - Get available roles
- [x] GET /api/v2/users/{id} - Get user details
- [x] POST /api/v2/users - Create new user
- [x] PUT /api/v2/users/{id} - Update user
- [x] GET /api/v2/users/{id}/statistics - Get user statistics
- [x] PATCH /api/v2/users/{id}/balance - Update user balance
- [x] DELETE /api/v2/users/{id} - Delete user
- [x] Error handling for unauthorized access
- [x] Error handling for not found
- [x] Error handling for validation errors
- [x] Error handling for deletion with associated data

### ✅ Features Working
- [x] Pagination and filtering
- [x] Search functionality
- [x] Sorting
- [x] Statistics calculation
- [x] Balance management
- [x] Role management
- [x] Relationship data (deposits, voucher redemptions, transactions)
- [x] Data validation
- [x] Error responses

### ✅ Database Integration
- [x] User data persistence
- [x] User balance management
- [x] Transaction recording
- [x] Relationship integrity
- [x] Foreign key constraints
- [x] Data consistency

## Notes
- UserManagementController berhasil diimplementasikan dengan semua endpoint CRUD
- Semua endpoint telah di-test dan berfungsi dengan baik
- Error handling sudah diimplementasikan dengan baik
- Performance testing menunjukkan respons yang baik
- Database schema sudah sesuai dengan dokumentasi
- Balance management dengan transaction recording berfungsi dengan baik
