# API v2 - RVM Management Testing

## Overview
Dokumentasi testing untuk API RVM Management yang mencakup semua endpoint CRUD dan operasi khusus untuk mengelola Reverse Vending Machines.

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

### 1. Get All RVMs
**GET** `/api/v2/rvms`

#### Test Command
```bash
curl -X GET "http://localhost:8000/api/v2/rvms" \
  -H "Authorization: Bearer 2|eJnDVVbwaoNtfiFYgm7ewEetIHQadfy60252ZSnT70844d92"
```

#### Expected Response
```json
{
  "success": true,
  "message": "RVMs retrieved successfully",
  "data": [
    {
      "id": 1,
      "name": "RVM-001",
      "location_description": "Lobby Gedung A, Lantai 1",
      "status": "active",
      "api_key": "juhFbragzyelllLERikaocYXNHMASnFi",
      "deposits_count": 2,
      "sessions_count": 0,
      "active_sessions_count": 0,
      "created_at": "2025-09-07T04:56:30.000000Z",
      "updated_at": "2025-09-07T04:56:30.000000Z"
    }
  ],
  "pagination": {
    "current_page": 1,
    "per_page": 15,
    "total": 3,
    "last_page": 1,
    "from": 1,
    "to": 3
  }
}
```

#### Test with Filters
```bash
# Filter by status
curl -X GET "http://localhost:8000/api/v2/rvms?status=active" \
  -H "Authorization: Bearer 2|eJnDVVbwaoNtfiFYgm7ewEetIHQadfy60252ZSnT70844d92"

# Search by name
curl -X GET "http://localhost:8000/api/v2/rvms?search=RVM-001" \
  -H "Authorization: Bearer 2|eJnDVVbwaoNtfiFYgm7ewEetIHQadfy60252ZSnT70844d92"

# Sort by name
curl -X GET "http://localhost:8000/api/v2/rvms?sort_by=name&sort_order=asc" \
  -H "Authorization: Bearer 2|eJnDVVbwaoNtfiFYgm7ewEetIHQadfy60252ZSnT70844d92"
```

### 2. Get RVM Details
**GET** `/api/v2/rvms/{id}`

#### Test Command
```bash
curl -X GET "http://localhost:8000/api/v2/rvms/1" \
  -H "Authorization: Bearer 2|eJnDVVbwaoNtfiFYgm7ewEetIHQadfy60252ZSnT70844d92"
```

#### Expected Response
```json
{
  "success": true,
  "message": "RVM details retrieved successfully",
  "data": {
    "id": 1,
    "name": "RVM-001",
    "location_description": "Lobby Gedung A, Lantai 1",
    "status": "active",
    "api_key": "juhFbragzyelllLERikaocYXNHMASnFi",
    "statistics": {
      "deposits_count": 2,
      "sessions_count": 0,
      "active_sessions_count": 0,
      "completed_deposits": 1,
      "pending_deposits": 0,
      "total_rewards_given": "1912.50"
    },
    "recent_deposits": [
      {
        "id": 3,
        "user_id": 4,
        "status": "completed",
        "reward_amount": "1912.50",
        "cv_confidence": null,
        "cv_waste_type": null,
        "created_at": "2025-09-07T06:55:09.000000Z"
      }
    ],
    "recent_sessions": [],
    "created_at": "2025-09-07T04:56:30.000000Z",
    "updated_at": "2025-09-07T04:56:30.000000Z"
  }
}
```

### 3. Create RVM
**POST** `/api/v2/rvms`

#### Test Command
```bash
curl -X POST "http://localhost:8000/api/v2/rvms" \
  -H "Authorization: Bearer 2|eJnDVVbwaoNtfiFYgm7ewEetIHQadfy60252ZSnT70844d92" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "RVM-004",
    "location_description": "Lobby Gedung B, Lantai 1",
    "status": "active"
  }'
```

#### Expected Response
```json
{
  "success": true,
  "message": "RVM created successfully",
  "data": {
    "id": 4,
    "name": "RVM-004",
    "location_description": "Lobby Gedung B, Lantai 1",
    "status": "active",
    "api_key": "rvm_j3QmwC3rKXbrqnH1FNUlLxwwDCfBQivD",
    "created_at": "2025-09-07T09:13:16.000000Z"
  }
}
```

#### Validation Error Test
```bash
curl -X POST "http://localhost:8000/api/v2/rvms" \
  -H "Authorization: Bearer 2|eJnDVVbwaoNtfiFYgm7ewEetIHQadfy60252ZSnT70844d92" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "",
    "status": "invalid_status"
  }'
```

### 4. Update RVM
**PUT** `/api/v2/rvms/{id}`

#### Test Command
```bash
curl -X PUT "http://localhost:8000/api/v2/rvms/4" \
  -H "Authorization: Bearer 2|eJnDVVbwaoNtfiFYgm7ewEetIHQadfy60252ZSnT70844d92" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "RVM-004-Updated",
    "location_description": "Lobby Gedung B, Lantai 1 - Updated",
    "status": "maintenance"
  }'
```

#### Expected Response
```json
{
  "success": true,
  "message": "RVM updated successfully",
  "data": {
    "id": 4,
    "name": "RVM-004-Updated",
    "location_description": "Lobby Gedung B, Lantai 1 - Updated",
    "status": "maintenance",
    "api_key": "rvm_j3QmwC3rKXbrqnH1FNUlLxwwDCfBQivD",
    "updated_at": "2025-09-07T09:13:23.000000Z"
  }
}
```

### 5. Update RVM Status
**PATCH** `/api/v2/rvms/{id}/status`

#### Test Command
```bash
curl -X PATCH "http://localhost:8000/api/v2/rvms/4/status" \
  -H "Authorization: Bearer 2|eJnDVVbwaoNtfiFYgm7ewEetIHQadfy60252ZSnT70844d92" \
  -H "Content-Type: application/json" \
  -d '{"status": "active"}'
```

#### Expected Response
```json
{
  "success": true,
  "message": "RVM status updated successfully",
  "data": {
    "id": 4,
    "name": "RVM-004-Updated",
    "status": "active",
    "updated_at": "2025-09-07T09:13:30.000000Z"
  }
}
```

### 6. Regenerate API Key
**PATCH** `/api/v2/rvms/{id}/regenerate-api-key`

#### Test Command
```bash
curl -X PATCH "http://localhost:8000/api/v2/rvms/4/regenerate-api-key" \
  -H "Authorization: Bearer 2|eJnDVVbwaoNtfiFYgm7ewEetIHQadfy60252ZSnT70844d92"
```

#### Expected Response
```json
{
  "success": true,
  "message": "RVM API key regenerated successfully",
  "data": {
    "id": 4,
    "name": "RVM-004-Updated",
    "api_key": "rvm_AUlTcgoYp98P2u20BlKbvJtDe8xLRgwM",
    "updated_at": "2025-09-07T09:13:37.000000Z"
  }
}
```

### 7. Get RVM Statistics
**GET** `/api/v2/rvms/{id}/statistics`

#### Test Command
```bash
curl -X GET "http://localhost:8000/api/v2/rvms/1/statistics" \
  -H "Authorization: Bearer 2|eJnDVVbwaoNtfiFYgm7ewEetIHQadfy60252ZSnT70844d92"
```

#### Expected Response
```json
{
  "success": true,
  "message": "RVM statistics retrieved successfully",
  "data": {
    "rvm_info": {
      "id": 1,
      "name": "RVM-001",
      "status": "active"
    },
    "deposits": {
      "total": 2,
      "completed": 1,
      "pending": 0,
      "rejected": 0,
      "total_rewards_given": "1912.50",
      "avg_confidence": null
    },
    "sessions": {
      "total": 0,
      "active": 0,
      "completed": 0,
      "expired": 0
    },
    "waste_types": {
      "by_type": []
    },
    "performance": {
      "avg_processing_time": "8.0000000000000000",
      "success_rate": 50
    }
  }
}
```

### 8. Delete RVM
**DELETE** `/api/v2/rvms/{id}`

#### Test Command
```bash
curl -X DELETE "http://localhost:8000/api/v2/rvms/4" \
  -H "Authorization: Bearer 2|eJnDVVbwaoNtfiFYgm7ewEetIHQadfy60252ZSnT70844d92"
```

#### Expected Response
```json
{
  "success": true,
  "message": "RVM deleted successfully"
}
```

#### Test Delete with Associated Data
```bash
# Try to delete RVM with deposits/sessions
curl -X DELETE "http://localhost:8000/api/v2/rvms/1" \
  -H "Authorization: Bearer 2|eJnDVVbwaoNtfiFYgm7ewEetIHQadfy60252ZSnT70844d92"
```

Expected Error Response:
```json
{
  "success": false,
  "message": "Cannot delete RVM with associated data",
  "data": {
    "deposits_count": 2,
    "sessions_count": 0
  }
}
```

## Error Scenarios

### 1. Unauthorized Access
```bash
curl -X GET "http://localhost:8000/api/v2/rvms"
```

Expected Response:
```json
{
  "message": "Unauthenticated."
}
```

### 2. RVM Not Found
```bash
curl -X GET "http://localhost:8000/api/v2/rvms/999" \
  -H "Authorization: Bearer 2|eJnDVVbwaoNtfiFYgm7ewEetIHQadfy60252ZSnT70844d92"
```

Expected Response:
```json
{
  "message": "No query results for model [App\\Models\\ReverseVendingMachine] 999"
}
```

### 3. Validation Errors
```bash
curl -X POST "http://localhost:8000/api/v2/rvms" \
  -H "Authorization: Bearer 2|eJnDVVbwaoNtfiFYgm7ewEetIHQadfy60252ZSnT70844d92" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "",
    "status": "invalid"
  }'
```

Expected Response:
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "name": ["The name field is required."],
    "status": ["The selected status is invalid."]
  }
}
```

## Database Verification

### Check RVM Data
```bash
docker compose exec app php artisan tinker --execute="App\\Models\\ReverseVendingMachine::all()->toArray()"
```

### Check RVM Sessions
```bash
docker compose exec app php artisan tinker --execute="App\\Models\\RvmSession::all()->toArray()"
```

### Check RVM Deposits
```bash
docker compose exec app php artisan tinker --execute="App\\Models\\Deposit::where('rvm_id', 1)->get()->toArray()"
```

## Performance Testing

### Load Test with Multiple Requests
```bash
# Test pagination performance
for i in {1..10}; do
  curl -X GET "http://localhost:8000/api/v2/rvms?page=$i" \
    -H "Authorization: Bearer 2|eJnDVVbwaoNtfiFYgm7ewEetIHQadfy60252ZSnT70844d92" &
done
wait
```

### Concurrent Access Test
```bash
# Test concurrent access to same RVM
for i in {1..5}; do
  curl -X GET "http://localhost:8000/api/v2/rvms/1" \
    -H "Authorization: Bearer 2|eJnDVVbwaoNtfiFYgm7ewEetIHQadfy60252ZSnT70844d92" &
done
wait
```

## Success Criteria

### ✅ All Tests Passed
- [x] GET /api/v2/rvms - List all RVMs with pagination
- [x] GET /api/v2/rvms/{id} - Get RVM details
- [x] POST /api/v2/rvms - Create new RVM
- [x] PUT /api/v2/rvms/{id} - Update RVM
- [x] PATCH /api/v2/rvms/{id}/status - Update RVM status
- [x] PATCH /api/v2/rvms/{id}/regenerate-api-key - Regenerate API key
- [x] GET /api/v2/rvms/{id}/statistics - Get RVM statistics
- [x] DELETE /api/v2/rvms/{id} - Delete RVM
- [x] Error handling for unauthorized access
- [x] Error handling for not found
- [x] Error handling for validation errors
- [x] Error handling for deletion with associated data

### ✅ Features Working
- [x] Pagination and filtering
- [x] Search functionality
- [x] Sorting
- [x] Statistics calculation
- [x] API key generation
- [x] Status management
- [x] Relationship data (deposits, sessions)
- [x] Data validation
- [x] Error responses

### ✅ Database Integration
- [x] RVM data persistence
- [x] Relationship integrity
- [x] Foreign key constraints
- [x] Index optimization
- [x] Data consistency

## Notes
- RVMController berhasil diimplementasikan dengan semua endpoint CRUD
- Model RvmSession dibuat untuk menggantikan Session Laravel standar
- Semua endpoint telah di-test dan berfungsi dengan baik
- Error handling sudah diimplementasikan dengan baik
- Performance testing menunjukkan respons yang baik
- Database schema sudah sesuai dengan dokumentasi
