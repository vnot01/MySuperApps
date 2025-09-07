# 📋 Dokumentasi Testing API v2 - Deposit Management & AI Analysis

## 🎯 Overview

Dokumentasi ini menjelaskan cara testing lengkap untuk API Deposit Management dengan integrasi AI Analysis di MyRVM v2.1.

## 🔧 Prerequisites

### 1. Environment Setup
```bash
# Pastikan Docker container berjalan
docker compose up -d

# Pastikan Laravel server berjalan
docker compose exec app php artisan serve --host=0.0.0.0 --port=8000
```

### 2. Database Setup
```bash
# Jalankan migration
docker compose exec app php artisan migrate

# Seed data untuk testing
docker compose exec app php artisan db:seed --class=RvmSeeder
docker compose exec app php artisan db:seed --class=UserSeeder
```

### 3. Authentication Token
```bash
# Login untuk mendapatkan token
curl -X POST "http://localhost:8000/api/v2/auth/login" \
  -H "Content-Type: application/json" \
  -d '{"email": "john@test.com", "password": "password123"}'

# Response akan memberikan token seperti:
# {"success": true, "data": {"token": "2|eJnDVVbwaoNtfiFYgm7ewEetIHQadfy60252ZSnT70844d92"}}
```

## 📊 Database Schema

### Tabel `deposits`
```sql
-- Field utama
id, user_id, rvm_id, session_token
waste_type, weight, quantity, quality_grade
reward_amount, status, rejection_reason, processed_at

-- Computer Vision (YOLO + SAM)
cv_confidence, cv_analysis, cv_waste_type
cv_weight, cv_quantity, cv_quality_grade

-- AI (Gemini/Agent AI)
ai_confidence, ai_analysis, ai_waste_type
ai_weight, ai_quantity, ai_quality_grade
```

### Tabel `user_balances`
```sql
id, user_id, balance, currency, created_at, updated_at
```

### Tabel `transactions`
```sql
id, user_id, user_balance_id, type, amount
balance_before, balance_after, description
sourceable_type, sourceable_id, created_at, updated_at
```

## 🚀 API Endpoints Testing

### 1. Create Deposit (POST /api/v2/deposits)

**Purpose**: Membuat deposit baru dengan AI analysis otomatis

**Request**:
```bash
curl -X POST "http://localhost:8000/api/v2/deposits" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "rvm_id": 1,
    "waste_type": "plastic",
    "weight": 0.5,
    "quantity": 2
  }'
```

**Expected Response**:
```json
{
  "success": true,
  "message": "Deposit created and analyzed successfully",
  "data": {
    "deposit_id": 3,
    "waste_type": "plastic",
    "quality_grade": "B",
    "ai_confidence": "76.50",
    "reward_amount": "1912.50",
    "status": "processing",
    "ai_analysis": {
      "waste_type": "plastic",
      "confidence_score": 76.5,
      "quality_grade": "B",
      "analysis_timestamp": "2025-09-07T06:55:09.574965Z",
      "detected_features": {
        "material_type": "PET",
        "color": "transparent",
        "condition": "good",
        "labels_present": true
      },
      "recommendations": [
        "suitable_for_recycling",
        "remove_labels_before_processing"
      ]
    }
  }
}
```

**Test Cases**:
- ✅ Valid data dengan rvm_id yang ada
- ✅ Valid data dengan waste_type yang berbeda
- ❌ Invalid rvm_id (tidak ada di database)
- ❌ Missing required fields
- ❌ Invalid data types

### 2. List Deposits (GET /api/v2/deposits)

**Purpose**: Menampilkan daftar deposit user dengan pagination

**Request**:
```bash
curl -X GET "http://localhost:8000/api/v2/deposits?per_page=5&status=processing" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

**Expected Response**:
```json
{
  "success": true,
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": 3,
        "user_id": 4,
        "rvm_id": 1,
        "waste_type": "plastic",
        "weight": "0.500",
        "quantity": 2,
        "quality_grade": "B",
        "ai_confidence": "76.50",
        "reward_amount": "1912.50",
        "status": "processing",
        "created_at": "2025-09-07T06:55:09.000000Z"
      }
    ],
    "first_page_url": "http://localhost:8000/api/v2/deposits?page=1",
    "from": 1,
    "last_page": 1,
    "per_page": 5,
    "to": 1,
    "total": 1
  }
}
```

**Test Cases**:
- ✅ Default pagination (15 items)
- ✅ Custom per_page parameter
- ✅ Filter by status
- ✅ Empty result set
- ❌ Invalid pagination parameters

### 3. Get Deposit Statistics (GET /api/v2/deposits/statistics)

**Purpose**: Menampilkan statistik deposit user

**Request**:
```bash
curl -X GET "http://localhost:8000/api/v2/deposits/statistics" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

**Expected Response**:
```json
{
  "success": true,
  "data": {
    "total_deposits": 3,
    "completed_deposits": 1,
    "pending_deposits": 0,
    "processing_deposits": 2,
    "rejected_deposits": 0,
    "total_rewards": "3837.50",
    "avg_confidence": "76.75",
    "waste_types_count": 1
  }
}
```

**Test Cases**:
- ✅ User dengan deposit
- ✅ User tanpa deposit
- ✅ Statistik dengan berbagai status

### 4. Get Single Deposit (GET /api/v2/deposits/{id})

**Purpose**: Menampilkan detail deposit berdasarkan ID

**Request**:
```bash
curl -X GET "http://localhost:8000/api/v2/deposits/3" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

**Expected Response**:
```json
{
  "success": true,
  "data": {
    "id": 3,
    "user_id": 4,
    "rvm_id": 1,
    "waste_type": "plastic",
    "weight": "0.500",
    "quantity": 2,
    "quality_grade": "B",
    "ai_confidence": "76.50",
    "ai_analysis": {
      "waste_type": "plastic",
      "confidence_score": 76.5,
      "quality_grade": "B",
      "analysis_timestamp": "2025-09-07T06:55:09.574965Z",
      "detected_features": {
        "material_type": "PET",
        "color": "transparent",
        "condition": "good",
        "labels_present": true
      },
      "recommendations": [
        "suitable_for_recycling",
        "remove_labels_before_processing"
      ]
    },
    "reward_amount": "1912.50",
    "status": "processing",
    "created_at": "2025-09-07T06:55:09.000000Z",
    "updated_at": "2025-09-07T06:55:09.000000Z"
  }
}
```

**Test Cases**:
- ✅ Valid deposit ID
- ❌ Invalid deposit ID
- ❌ Deposit milik user lain
- ❌ Deposit yang tidak ada

### 5. Process Deposit (POST /api/v2/deposits/{id}/process)

**Purpose**: Memproses deposit (approve/reject) dan menambahkan reward

**Request**:
```bash
curl -X POST "http://localhost:8000/api/v2/deposits/3/process" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "status": "completed"
  }'
```

**Expected Response**:
```json
{
  "success": true,
  "message": "Deposit processed successfully",
  "data": {
    "deposit_id": 3,
    "status": "completed",
    "reward_amount": "1912.50",
    "processed_at": "2025-09-07T06:55:17.000000Z"
  },
  "debug": {
    "user_id": 4,
    "waste_type": "plastic",
    "ai_confidence": "76.50"
  }
}
```

**Test Cases**:
- ✅ Approve deposit (status: completed)
- ✅ Reject deposit (status: rejected)
- ❌ Invalid status
- ❌ Deposit sudah diproses
- ❌ Deposit dengan status yang tidak valid

## 🔍 AI Analysis Flow

### 1. Computer Vision (YOLO + SAM)
```
Kamera RVM → YOLO v11 + SAM v2 → best.pt → JSON Result
```

**Field yang diisi**:
- `cv_confidence` - Confidence score
- `cv_analysis` - JSON analysis result
- `cv_waste_type` - Detected waste type
- `cv_weight` - Estimated weight
- `cv_quantity` - Detected quantity
- `cv_quality_grade` - Quality assessment

### 2. AI Analysis (Gemini/Agent AI)
```
CV Result → Gemini Vision → Validation → Enhanced Analysis
```

**Field yang diisi**:
- `ai_confidence` - AI confidence score
- `ai_analysis` - Enhanced analysis
- `ai_waste_type` - Validated waste type
- `ai_weight` - Refined weight
- `ai_quantity` - Refined quantity
- `ai_quality_grade` - Final quality grade

## 🧪 Testing Scenarios

### Scenario 1: Complete Deposit Flow
```bash
# 1. Create deposit
curl -X POST "http://localhost:8000/api/v2/deposits" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"rvm_id": 1, "waste_type": "plastic", "weight": 0.5, "quantity": 2}'

# 2. Check deposit status
curl -X GET "http://localhost:8000/api/v2/deposits/3" \
  -H "Authorization: Bearer YOUR_TOKEN"

# 3. Process deposit
curl -X POST "http://localhost:8000/api/v2/deposits/3/process" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"status": "completed"}'

# 4. Verify final status
curl -X GET "http://localhost:8000/api/v2/deposits/3" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### Scenario 2: Error Handling
```bash
# 1. Invalid RVM ID
curl -X POST "http://localhost:8000/api/v2/deposits" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"rvm_id": 999, "waste_type": "plastic", "weight": 0.5, "quantity": 1}'

# 2. Missing required fields
curl -X POST "http://localhost:8000/api/v2/deposits" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"waste_type": "plastic"}'

# 3. Invalid deposit ID
curl -X GET "http://localhost:8000/api/v2/deposits/999" \
  -H "Authorization: Bearer YOUR_TOKEN"

# 4. Process non-existent deposit
curl -X POST "http://localhost:8000/api/v2/deposits/999/process" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"status": "completed"}'
```

## 📝 Logging & Debugging

### 1. Laravel Logs
```bash
# View logs
docker compose exec app tail -f storage/logs/laravel.log

# Filter deposit-related logs
docker compose exec app tail -f storage/logs/laravel.log | grep -i deposit
```

### 2. Debug Information
Setiap response API menyertakan debug information:
```json
{
  "debug": {
    "user_id": 4,
    "waste_type": "plastic",
    "ai_confidence": "76.50"
  }
}
```

### 3. Error Logging
Error ditangkap dan dicatat dengan detail:
```json
{
  "success": false,
  "message": "Failed to process deposit",
  "error": "Error message",
  "debug": {
    "error_file": "/path/to/file.php",
    "error_line": 123,
    "deposit_id": 3,
    "user_id": 4
  }
}
```

## 🔧 Troubleshooting

### Common Issues

1. **Redirect Issue**
   - **Problem**: Response HTML instead of JSON
   - **Solution**: Check route definition and middleware

2. **Authentication Error**
   - **Problem**: 401 Unauthorized
   - **Solution**: Verify token and user permissions

3. **Database Error**
   - **Problem**: SQL errors
   - **Solution**: Check migration status and database connection

4. **Validation Error**
   - **Problem**: 422 Validation failed
   - **Solution**: Check request data format and validation rules

### Debug Commands
```bash
# Check route list
docker compose exec app php artisan route:list --path=api/v2/deposits

# Check migration status
docker compose exec app php artisan migrate:status

# Clear cache
docker compose exec app php artisan cache:clear
docker compose exec app php artisan route:clear
docker compose exec app php artisan config:clear
```

## 📊 Performance Testing

### Load Testing
```bash
# Test multiple concurrent requests
for i in {1..10}; do
  curl -X POST "http://localhost:8000/api/v2/deposits" \
    -H "Authorization: Bearer YOUR_TOKEN" \
    -H "Content-Type: application/json" \
    -d '{"rvm_id": 1, "waste_type": "plastic", "weight": 0.5, "quantity": 1}' &
done
wait
```

### Response Time Testing
```bash
# Measure response time
time curl -X GET "http://localhost:8000/api/v2/deposits" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

## 🎯 Success Criteria

### Functional Testing
- ✅ All endpoints return correct responses
- ✅ AI analysis generates realistic data
- ✅ Deposit processing updates user balance
- ✅ Transaction records are created correctly
- ✅ Error handling works properly

### Performance Testing
- ✅ Response time < 2 seconds
- ✅ Concurrent requests handled properly
- ✅ Database queries optimized
- ✅ Memory usage within limits

### Security Testing
- ✅ Authentication required for protected endpoints
- ✅ User can only access their own deposits
- ✅ Input validation prevents SQL injection
- ✅ Sensitive data not exposed in responses

## 📚 Additional Resources

- [Laravel API Documentation](https://laravel.com/docs/api)
- [Postman Collection](./postman-collection.json)
- [Database Schema](./database-schema.sql)
- [Error Codes Reference](./error-codes.md)

---

**Last Updated**: 2025-09-07  
**Version**: 2.1  
**Author**: MyRVM Development Team
