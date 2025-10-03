# 🚨 Error Codes Reference - MyRVM v2.1 API

## 📋 Overview

Dokumentasi ini menjelaskan semua error codes yang mungkin dikembalikan oleh API MyRVM v2.1 beserta penjelasan dan solusinya.

## 🔧 HTTP Status Codes

### 200 OK
**Description**: Request berhasil diproses
```json
{
  "success": true,
  "message": "Operation completed successfully",
  "data": { ... }
}
```

### 201 Created
**Description**: Resource berhasil dibuat
```json
{
  "success": true,
  "message": "Resource created successfully",
  "data": { ... }
}
```

### 400 Bad Request
**Description**: Request tidak valid atau ada kesalahan dalam data yang dikirim

#### 400.1 - Validation Error
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "rvm_id": ["The selected rvm id is invalid."],
    "waste_type": ["The waste type field is required."]
  }
}
```

#### 400.2 - Invalid Status
```json
{
  "success": false,
  "message": "Deposit is not in pending or processing status",
  "debug": {
    "current_status": "completed",
    "allowed_statuses": ["pending", "processing"]
  }
}
```

### 401 Unauthorized
**Description**: Token tidak valid atau tidak ada

#### 401.1 - Missing Token
```json
{
  "success": false,
  "message": "Unauthenticated"
}
```

#### 401.2 - Invalid Token
```json
{
  "success": false,
  "message": "Token is invalid or expired"
}
```

### 403 Forbidden
**Description**: User tidak memiliki permission untuk akses resource

#### 403.1 - Access Denied
```json
{
  "success": false,
  "message": "Access denied. You don't have permission to access this resource"
}
```

### 404 Not Found
**Description**: Resource tidak ditemukan

#### 404.1 - Deposit Not Found
```json
{
  "success": false,
  "message": "Deposit not found",
  "debug": {
    "deposit_id": 999,
    "user_id": 4
  }
}
```

#### 404.2 - RVM Not Found
```json
{
  "success": false,
  "message": "RVM not found",
  "debug": {
    "rvm_id": 999
  }
}
```

### 422 Unprocessable Entity
**Description**: Data tidak dapat diproses karena validasi gagal

#### 422.1 - Validation Failed
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "rvm_id": ["The selected rvm id is invalid."],
    "waste_type": ["The waste type field is required."],
    "weight": ["The weight must be a number."],
    "quantity": ["The quantity must be an integer."]
  }
}
```

### 500 Internal Server Error
**Description**: Error internal server

#### 500.1 - Database Error
```json
{
  "success": false,
  "message": "Failed to process deposit",
  "error": "SQLSTATE[23502]: Not null violation: 7 ERROR: null value in column \"user_id\" violates not-null constraint",
  "debug": {
    "error_file": "/var/www/html/app/Services/DepositService.php",
    "error_line": 142,
    "deposit_id": 3,
    "user_id": 4
  }
}
```

#### 500.2 - Service Error
```json
{
  "success": false,
  "message": "Failed to process deposit",
  "error": "Call to undefined method App\\Models\\UserBalance::create()",
  "debug": {
    "error_file": "/var/www/html/app/Services/DepositService.php",
    "error_line": 140,
    "deposit_id": 3,
    "user_id": 4
  }
}
```

## 🔍 Validation Error Codes

### Deposit Creation Errors

#### V001 - RVM ID Invalid
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "rvm_id": ["The selected rvm id is invalid."]
  }
}
```
**Solution**: Pastikan RVM ID ada di database

#### V002 - Waste Type Required
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "waste_type": ["The waste type field is required."]
  }
}
```
**Solution**: Sertakan waste_type dalam request

#### V003 - Weight Must Be Number
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "weight": ["The weight must be a number."]
  }
}
```
**Solution**: Pastikan weight berupa angka

#### V004 - Quantity Must Be Integer
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "quantity": ["The quantity must be an integer."]
  }
}
```
**Solution**: Pastikan quantity berupa integer

### Deposit Processing Errors

#### V005 - Status Required
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "status": ["The status field is required."]
  }
}
```
**Solution**: Sertakan status dalam request

#### V006 - Invalid Status
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "status": ["The selected status is invalid."]
  }
}
```
**Solution**: Gunakan status yang valid: "completed" atau "rejected"

#### V007 - Rejection Reason Required
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "rejection_reason": ["The rejection reason field is required when status is rejected."]
  }
}
```
**Solution**: Sertakan rejection_reason ketika status "rejected"

## 🗄️ Database Error Codes

### D001 - Connection Error
```json
{
  "success": false,
  "message": "Database connection failed",
  "error": "SQLSTATE[08006] [7] could not connect to server: Connection refused"
}
```
**Solution**: Periksa koneksi database dan pastikan PostgreSQL berjalan

### D002 - Table Not Found
```json
{
  "success": false,
  "message": "Table not found",
  "error": "SQLSTATE[42P01]: Undefined table: 7 ERROR: relation \"deposits\" does not exist"
}
```
**Solution**: Jalankan migration: `php artisan migrate`

### D003 - Column Not Found
```json
{
  "success": false,
  "message": "Column not found",
  "error": "SQLSTATE[42703]: Undefined column: 7 ERROR: column \"cv_confidence\" of relation \"deposits\" does not exist"
}
```
**Solution**: Jalankan migration terbaru: `php artisan migrate`

### D004 - Constraint Violation
```json
{
  "success": false,
  "message": "Constraint violation",
  "error": "SQLSTATE[23502]: Not null violation: 7 ERROR: null value in column \"user_id\" violates not-null constraint"
}
```
**Solution**: Pastikan semua field required diisi

### D005 - Foreign Key Violation
```json
{
  "success": false,
  "message": "Foreign key violation",
  "error": "SQLSTATE[23503]: Foreign key violation: 7 ERROR: insert or update on table \"deposits\" violates foreign key constraint \"deposits_user_id_foreign\""
}
```
**Solution**: Pastikan user_id ada di tabel users

## 🔐 Authentication Error Codes

### A001 - Token Missing
```json
{
  "success": false,
  "message": "Unauthenticated"
}
```
**Solution**: Sertakan Authorization header dengan Bearer token

### A002 - Token Invalid
```json
{
  "success": false,
  "message": "Token is invalid or expired"
}
```
**Solution**: Login ulang untuk mendapatkan token baru

### A003 - User Not Found
```json
{
  "success": false,
  "message": "User not found",
  "debug": {
    "user_id": 999
  }
}
```
**Solution**: Pastikan user ada di database

### A004 - Insufficient Permissions
```json
{
  "success": false,
  "message": "Access denied. You don't have permission to access this resource"
}
```
**Solution**: Pastikan user memiliki role yang sesuai

## 🧪 AI Analysis Error Codes

### AI001 - AI Service Unavailable
```json
{
  "success": false,
  "message": "AI analysis service is currently unavailable",
  "error": "Connection timeout to AI service"
}
```
**Solution**: Periksa koneksi ke AI service

### AI002 - Invalid AI Response
```json
{
  "success": false,
  "message": "Invalid AI analysis response",
  "error": "AI service returned invalid JSON format"
}
```
**Solution**: Periksa format response dari AI service

### AI003 - AI Analysis Failed
```json
{
  "success": false,
  "message": "AI analysis failed",
  "error": "Image processing failed"
}
```
**Solution**: Periksa kualitas gambar dan coba lagi

## 🔧 Troubleshooting Guide

### Common Solutions

1. **Token Issues**
   ```bash
   # Login ulang untuk mendapatkan token baru
   curl -X POST "http://localhost:8000/api/v2/auth/login" \
     -H "Content-Type: application/json" \
     -d '{"email": "john@test.com", "password": "password123"}'
   ```

2. **Database Issues**
   ```bash
   # Jalankan migration
   docker compose exec app php artisan migrate
   
   # Seed data
   docker compose exec app php artisan db:seed
   ```

3. **Cache Issues**
   ```bash
   # Clear cache
   docker compose exec app php artisan cache:clear
   docker compose exec app php artisan route:clear
   docker compose exec app php artisan config:clear
   ```

4. **Permission Issues**
   ```bash
   # Check file permissions
   docker compose exec app chmod -R 755 storage/
   docker compose exec app chmod -R 755 bootstrap/cache/
   ```

### Debug Commands

```bash
# Check logs
docker compose exec app tail -f storage/logs/laravel.log

# Check route list
docker compose exec app php artisan route:list --path=api/v2/deposits

# Check migration status
docker compose exec app php artisan migrate:status

# Test database connection
docker compose exec app php artisan tinker --execute="DB::connection()->getPdo();"
```

## 📊 Error Monitoring

### Log Levels
- **ERROR**: Critical errors yang menyebabkan request gagal
- **WARNING**: Warning yang tidak menghentikan request
- **INFO**: Informasi umum tentang operasi
- **DEBUG**: Informasi debugging untuk development

### Log Format
```json
{
  "timestamp": "2025-09-07T06:55:17.000000Z",
  "level": "ERROR",
  "message": "Failed to process deposit",
  "context": {
    "deposit_id": 3,
    "user_id": 4,
    "error": "SQLSTATE[23502]: Not null violation",
    "file": "/var/www/html/app/Services/DepositService.php",
    "line": 142
  }
}
```

## 🎯 Best Practices

1. **Error Handling**
   - Selalu handle error dengan try-catch
   - Log error dengan detail yang cukup
   - Return error response yang informatif

2. **Validation**
   - Validasi input di level request
   - Gunakan Form Request untuk validasi
   - Return error message yang jelas

3. **Debugging**
   - Sertakan debug info dalam response
   - Log operasi penting
   - Gunakan logging level yang sesuai

4. **Testing**
   - Test semua error scenarios
   - Test dengan data yang tidak valid
   - Test dengan permission yang berbeda

---

**Last Updated**: 2025-09-07  
**Version**: 2.1  
**Author**: MyRVM Development Team
