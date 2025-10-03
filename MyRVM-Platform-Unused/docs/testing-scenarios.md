# 🧪 Testing Scenarios - MyRVM v2.1 API

## 📋 Overview

Dokumentasi ini berisi berbagai skenario testing untuk memastikan API Deposit Management berfungsi dengan baik dalam berbagai kondisi.

## 🎯 Test Categories

### 1. Functional Testing
- Happy path scenarios
- Edge cases
- Error handling
- Data validation

### 2. Integration Testing
- API endpoint integration
- Database integration
- AI service integration
- Authentication integration

### 3. Performance Testing
- Load testing
- Stress testing
- Response time testing
- Concurrent request testing

### 4. Security Testing
- Authentication testing
- Authorization testing
- Input validation testing
- SQL injection testing

## 🚀 Happy Path Scenarios

### Scenario 1: Complete Deposit Flow
**Description**: User melakukan deposit dari awal sampai selesai

**Steps**:
1. **Login** - User login untuk mendapatkan token
2. **Create Deposit** - User membuat deposit baru
3. **Check Status** - User mengecek status deposit
4. **Process Deposit** - Admin memproses deposit
5. **Verify Balance** - User mengecek saldo yang bertambah

**Test Data**:
```json
{
  "rvm_id": 1,
  "waste_type": "plastic",
  "weight": 0.5,
  "quantity": 2
}
```

**Expected Results**:
- Deposit berhasil dibuat dengan status "processing"
- AI analysis berhasil dilakukan
- Deposit berhasil diproses dengan status "completed"
- User balance bertambah sesuai reward amount
- Transaction record berhasil dibuat

### Scenario 2: Multiple Deposits
**Description**: User melakukan multiple deposits dalam satu session

**Steps**:
1. **Login** - User login
2. **Create Deposit 1** - Deposit pertama
3. **Create Deposit 2** - Deposit kedua
4. **Create Deposit 3** - Deposit ketiga
5. **Check Statistics** - Lihat statistik semua deposits
6. **Process All** - Proses semua deposits
7. **Verify Final Balance** - Cek saldo akhir

**Test Data**:
```json
[
  {"rvm_id": 1, "waste_type": "plastic", "weight": 0.5, "quantity": 1},
  {"rvm_id": 1, "waste_type": "glass", "weight": 0.3, "quantity": 2},
  {"rvm_id": 2, "waste_type": "metal", "weight": 0.8, "quantity": 1}
]
```

**Expected Results**:
- Semua deposits berhasil dibuat
- Statistik menampilkan data yang benar
- Semua deposits berhasil diproses
- Balance bertambah sesuai total reward

### Scenario 3: Different Waste Types
**Description**: Testing dengan berbagai jenis sampah

**Test Data**:
```json
[
  {"waste_type": "plastic", "weight": 0.5, "quantity": 1},
  {"waste_type": "glass", "weight": 0.3, "quantity": 2},
  {"waste_type": "metal", "weight": 0.8, "quantity": 1},
  {"waste_type": "paper", "weight": 0.2, "quantity": 5}
]
```

**Expected Results**:
- Setiap waste type memiliki reward rate yang berbeda
- AI analysis menghasilkan confidence score yang berbeda
- Quality grade mempengaruhi reward amount

## ⚠️ Error Scenarios

### Scenario 4: Invalid RVM ID
**Description**: Testing dengan RVM ID yang tidak ada

**Test Data**:
```json
{
  "rvm_id": 999,
  "waste_type": "plastic",
  "weight": 0.5,
  "quantity": 1
}
```

**Expected Results**:
- Response: 422 Validation Error
- Error message: "The selected rvm id is invalid."
- Deposit tidak dibuat

### Scenario 5: Missing Required Fields
**Description**: Testing dengan field yang hilang

**Test Data**:
```json
{
  "waste_type": "plastic"
}
```

**Expected Results**:
- Response: 422 Validation Error
- Error messages untuk field yang hilang
- Deposit tidak dibuat

### Scenario 6: Invalid Data Types
**Description**: Testing dengan tipe data yang salah

**Test Data**:
```json
{
  "rvm_id": "invalid",
  "waste_type": "plastic",
  "weight": "not_a_number",
  "quantity": "not_an_integer"
}
```

**Expected Results**:
- Response: 422 Validation Error
- Error messages untuk tipe data yang salah
- Deposit tidak dibuat

### Scenario 7: Process Non-existent Deposit
**Description**: Testing proses deposit yang tidak ada

**Test Data**:
```json
{
  "status": "completed"
}
```

**Expected Results**:
- Response: 404 Not Found
- Error message: "Deposit not found"
- Tidak ada perubahan di database

### Scenario 8: Process Already Processed Deposit
**Description**: Testing proses deposit yang sudah diproses

**Steps**:
1. Create deposit
2. Process deposit (status: completed)
3. Try to process again

**Expected Results**:
- Response: 400 Bad Request
- Error message: "Deposit is not in pending or processing status"
- Tidak ada perubahan di database

## 🔐 Authentication Scenarios

### Scenario 9: Missing Authentication
**Description**: Testing tanpa token authentication

**Request**:
```bash
curl -X GET "http://localhost:8000/api/v2/deposits"
```

**Expected Results**:
- Response: 401 Unauthorized
- Error message: "Unauthenticated"

### Scenario 10: Invalid Token
**Description**: Testing dengan token yang tidak valid

**Request**:
```bash
curl -X GET "http://localhost:8000/api/v2/deposits" \
  -H "Authorization: Bearer invalid_token"
```

**Expected Results**:
- Response: 401 Unauthorized
- Error message: "Token is invalid or expired"

### Scenario 11: Expired Token
**Description**: Testing dengan token yang sudah expired

**Expected Results**:
- Response: 401 Unauthorized
- Error message: "Token is invalid or expired"

## 🗄️ Database Scenarios

### Scenario 12: Database Connection Error
**Description**: Testing ketika database tidak tersedia

**Steps**:
1. Stop PostgreSQL container
2. Try to create deposit
3. Start PostgreSQL container

**Expected Results**:
- Response: 500 Internal Server Error
- Error message: "Database connection failed"
- Log error dengan detail

### Scenario 13: Concurrent Deposits
**Description**: Testing multiple users membuat deposit bersamaan

**Steps**:
1. User A login
2. User B login
3. Both users create deposit simultaneously
4. Check both deposits created successfully

**Expected Results**:
- Kedua deposits berhasil dibuat
- Tidak ada data corruption
- Response time masih dalam batas normal

### Scenario 14: Large Data Volume
**Description**: Testing dengan volume data yang besar

**Steps**:
1. Create 100 deposits
2. Check performance
3. Process all deposits
4. Check final statistics

**Expected Results**:
- Semua deposits berhasil dibuat
- Response time masih dalam batas normal
- Database performance tetap baik

## 🤖 AI Analysis Scenarios

### Scenario 15: High Confidence AI Analysis
**Description**: Testing dengan AI confidence yang tinggi

**Test Data**:
```json
{
  "rvm_id": 1,
  "waste_type": "plastic",
  "weight": 0.5,
  "quantity": 1
}
```

**Expected Results**:
- AI confidence > 80%
- Reward amount sesuai dengan confidence
- Quality grade tinggi

### Scenario 16: Low Confidence AI Analysis
**Description**: Testing dengan AI confidence yang rendah

**Test Data**:
```json
{
  "rvm_id": 1,
  "waste_type": "unknown",
  "weight": 0.1,
  "quantity": 1
}
```

**Expected Results**:
- AI confidence < 50%
- Reward amount lebih rendah
- Quality grade rendah

### Scenario 17: AI Service Unavailable
**Description**: Testing ketika AI service tidak tersedia

**Steps**:
1. Simulate AI service down
2. Try to create deposit
3. Check error handling

**Expected Results**:
- Response: 500 Internal Server Error
- Error message: "AI analysis service is currently unavailable"
- Deposit tidak dibuat

## 📊 Performance Scenarios

### Scenario 18: Response Time Testing
**Description**: Testing response time untuk setiap endpoint

**Expected Results**:
- GET endpoints: < 500ms
- POST endpoints: < 2000ms
- Complex queries: < 3000ms

### Scenario 19: Load Testing
**Description**: Testing dengan multiple concurrent requests

**Steps**:
1. Send 10 concurrent requests
2. Measure response time
3. Check error rate
4. Verify data consistency

**Expected Results**:
- Response time tetap dalam batas normal
- Error rate < 1%
- Data consistency terjaga

### Scenario 20: Stress Testing
**Description**: Testing dengan load yang sangat tinggi

**Steps**:
1. Send 100 concurrent requests
2. Monitor system resources
3. Check for memory leaks
4. Verify system recovery

**Expected Results**:
- System tetap stabil
- Tidak ada memory leaks
- System recovery cepat

## 🔒 Security Scenarios

### Scenario 21: SQL Injection Testing
**Description**: Testing dengan input yang berpotensi SQL injection

**Test Data**:
```json
{
  "rvm_id": "1; DROP TABLE deposits; --",
  "waste_type": "plastic",
  "weight": 0.5,
  "quantity": 1
}
```

**Expected Results**:
- Response: 422 Validation Error
- Database tetap aman
- Tidak ada data yang terhapus

### Scenario 22: XSS Testing
**Description**: Testing dengan input yang berpotensi XSS

**Test Data**:
```json
{
  "rvm_id": 1,
  "waste_type": "<script>alert('XSS')</script>",
  "weight": 0.5,
  "quantity": 1
}
```

**Expected Results**:
- Input di-sanitize dengan benar
- Tidak ada script yang dieksekusi
- Response aman

### Scenario 23: Authorization Testing
**Description**: Testing akses ke resource user lain

**Steps**:
1. User A login
2. User B login
3. User A try to access User B's deposits

**Expected Results**:
- User A tidak bisa akses deposit User B
- Response: 403 Forbidden atau 404 Not Found
- Data privacy terjaga

## 🧪 Test Automation

### Automated Test Scripts

#### 1. Basic Functionality Test
```bash
#!/bin/bash
# test_basic_functionality.sh

echo "Testing basic functionality..."

# Login
TOKEN=$(curl -s -X POST "http://localhost:8000/api/v2/auth/login" \
  -H "Content-Type: application/json" \
  -d '{"email": "john@test.com", "password": "password123"}' | \
  jq -r '.data.token')

# Create deposit
DEPOSIT_ID=$(curl -s -X POST "http://localhost:8000/api/v2/deposits" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"rvm_id": 1, "waste_type": "plastic", "weight": 0.5, "quantity": 1}' | \
  jq -r '.data.deposit_id')

# Process deposit
curl -s -X POST "http://localhost:8000/api/v2/deposits/$DEPOSIT_ID/process" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"status": "completed"}'

echo "Basic functionality test completed"
```

#### 2. Error Handling Test
```bash
#!/bin/bash
# test_error_handling.sh

echo "Testing error handling..."

# Test invalid RVM ID
curl -s -X POST "http://localhost:8000/api/v2/deposits" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"rvm_id": 999, "waste_type": "plastic", "weight": 0.5, "quantity": 1}'

# Test missing fields
curl -s -X POST "http://localhost:8000/api/v2/deposits" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"waste_type": "plastic"}'

echo "Error handling test completed"
```

#### 3. Performance Test
```bash
#!/bin/bash
# test_performance.sh

echo "Testing performance..."

# Test response time
time curl -s -X GET "http://localhost:8000/api/v2/deposits" \
  -H "Authorization: Bearer $TOKEN"

# Test concurrent requests
for i in {1..10}; do
  curl -s -X POST "http://localhost:8000/api/v2/deposits" \
    -H "Authorization: Bearer $TOKEN" \
    -H "Content-Type: application/json" \
    -d '{"rvm_id": 1, "waste_type": "plastic", "weight": 0.5, "quantity": 1}' &
done
wait

echo "Performance test completed"
```

## 📈 Test Results Tracking

### Test Metrics
- **Pass Rate**: Percentage of tests that pass
- **Response Time**: Average response time per endpoint
- **Error Rate**: Percentage of requests that fail
- **Coverage**: Percentage of code paths tested

### Test Reports
```json
{
  "test_run_id": "2025-09-07-001",
  "timestamp": "2025-09-07T06:55:17.000000Z",
  "total_tests": 23,
  "passed_tests": 22,
  "failed_tests": 1,
  "pass_rate": "95.65%",
  "average_response_time": "1.2s",
  "error_rate": "0.5%",
  "coverage": "85%"
}
```

## 🎯 Success Criteria

### Functional Requirements
- ✅ All endpoints return correct responses
- ✅ AI analysis generates realistic data
- ✅ Deposit processing updates user balance
- ✅ Transaction records are created correctly
- ✅ Error handling works properly

### Performance Requirements
- ✅ Response time < 2 seconds
- ✅ Concurrent requests handled properly
- ✅ Database queries optimized
- ✅ Memory usage within limits

### Security Requirements
- ✅ Authentication required for protected endpoints
- ✅ User can only access their own deposits
- ✅ Input validation prevents SQL injection
- ✅ Sensitive data not exposed in responses

---

**Last Updated**: 2025-09-07  
**Version**: 2.1  
**Author**: MyRVM Development Team
