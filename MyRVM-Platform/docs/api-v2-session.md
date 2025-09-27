# API v2 - RVM Session Management

## Overview
API untuk mengelola sesi RVM (Reverse Vending Machine) dengan dukungan mode pengguna terdaftar dan mode tamu/donasi.

## Base URL
```
/api/v2/rvm/session
```

## Endpoints

### 1. Create Session
**POST** `/api/v2/rvm/session/create`

Membuat token sesi baru untuk RVM.

#### Request Body
```json
{
    "rvm_id": 1
}
```

#### Response (Success)
```json
{
    "success": true,
    "message": "Session token created successfully",
    "data": {
        "session_token": "550e8400-e29b-41d4-a716-446655440000",
        "rvm_id": 1,
        "expires_at": "2025-01-22T10:30:00.000Z"
    }
}
```

#### Response (Error)
```json
{
    "success": false,
    "message": "Validation failed",
    "errors": {
        "rvm_id": ["RVM ID is required"]
    }
}
```

### 2. Claim Session (Authenticated)
**POST** `/api/v2/rvm/session/claim`

**Authentication Required:** Bearer Token (Sanctum)

Mengklaim sesi untuk pengguna yang sudah login.

#### Request Body
```json
{
    "session_token": "550e8400-e29b-41d4-a716-446655440000"
}
```

#### Response (Success)
```json
{
    "success": true,
    "message": "Session claimed successfully",
    "data": {
        "session_token": "550e8400-e29b-41d4-a716-446655440000",
        "user_name": "John Doe",
        "rvm_id": 1
    }
}
```

### 3. Activate Guest Session
**POST** `/api/v2/rvm/session/activate-guest`

Mengaktifkan sesi dalam mode tamu/donasi.

#### Request Body
```json
{
    "session_token": "550e8400-e29b-41d4-a716-446655440000"
}
```

#### Response (Success)
```json
{
    "success": true,
    "message": "Guest session activated successfully",
    "data": {
        "session_token": "550e8400-e29b-41d4-a716-446655440000",
        "rvm_id": 1,
        "mode": "guest_donation"
    }
}
```

### 4. Get Session Status
**GET** `/api/v2/rvm/session/status`

Mengecek status sesi saat ini.

#### Query Parameters
- `session_token` (required): Token sesi

#### Response (Success)
```json
{
    "success": true,
    "data": {
        "session_token": "550e8400-e29b-41d4-a716-446655440000",
        "status": "diotorisasi",
        "rvm_id": 1,
        "user_id": 123,
        "created_at": "2025-01-22T10:20:00.000Z",
        "expires_at": "2025-01-22T10:30:00.000Z"
    }
}
```

## Session Status Values
- `menunggu_otorisasi`: Menunggu klaim dari pengguna
- `diotorisasi`: Sudah diklaim oleh pengguna terdaftar
- `aktif_sebagai_tamu`: Aktif dalam mode tamu/donasi

## WebSocket Events

### SesiDiotorisasi
Dikirim ketika sesi berhasil diklaim oleh pengguna terdaftar.

**Channel:** `private-rvm.{rvm_id}`

**Event Data:**
```json
{
    "rvm_id": 1,
    "user_name": "John Doe",
    "session_token": "550e8400-e29b-41d4-a716-446655440000",
    "timestamp": "2025-01-22T10:25:00.000Z",
    "message": "Selamat datang, John Doe! Silakan masukkan item Anda."
}
```

### SesiTamuAktif
Dikirim ketika sesi tamu diaktifkan.

**Channel:** `private-rvm.{rvm_id}`

**Event Data:**
```json
{
    "rvm_id": 1,
    "session_token": "550e8400-e29b-41d4-a716-446655440000",
    "timestamp": "2025-01-22T10:25:00.000Z",
    "message": "Mode Donasi Aktif. Silakan masukkan item Anda."
}
```

## Error Codes
- `422`: Validation Error
- `404`: Session Not Found
- `409`: Session Conflict (already claimed/activated)
- `401`: Unauthorized (for protected endpoints)

## Session Expiration
- Session token berlaku selama **10 menit**
- Session otomatis expired jika tidak ada aktivitas
- Cache menggunakan TTL untuk cleanup otomatis

## **🔑 Cara Mendapatkan SANCTUM_TOKEN - SOLVED!**

### **✅ Masalah yang Sudah Diperbaiki:**
1. **Routes API v2 tidak terdaftar** → Fixed dengan membuat `routes/api.php` dan mengupdate `bootstrap/app.php`
2. **Laravel Sanctum belum diinstal** → Fixed dengan `composer require laravel/sanctum`
3. **Model User belum menggunakan HasApiTokens trait** → Fixed dengan menambahkan trait

---

## **📋 Langkah-langkah Mendapatkan Token:**

### **1. Login untuk Mendapatkan Token**

**POST** `http://localhost:8000/api/v2/auth/login`

**Request Body:**
```json
{
    "email": "superadmin@example.com",
    "password": "password"
}
```

**Response:**
```json
{
    "success": true,
    "message": "Login successful",
    "data": {
        "user": {
            "id": 1,
            "name": "Super Admin",
            "email": "superadmin@example.com",
            "role": "Super Admin"
        },
        "token": "1|OmpZdm1W0mZUX2g3vmv4RKJW4c3jccn3SGtmoT3Obbc0f7b0",
        "token_type": "Bearer"
    }
}
```

### **2. Copy Token dari Response**

Copy nilai `token` dari response di atas. Ini adalah **SANCTUM_TOKEN** Anda.

---

## **🧪 Testing Lengkap dengan Token:**

### **Step 1: Login (Get Token)**
```bash
curl -X POST http://localhost:8000/api/v2/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email": "superadmin@example.com", "password": "password"}'
```

### **Step 2: Create Session**
```bash
curl -X POST http://localhost:8000/api/v2/rvm/session/create \
  -H "Content-Type: application/json" \
  -d '{"rvm_id": 1}'
```

### **Step 3: Subscribe WebSocket di Postman**
```json
{
    "event": "pusher:subscribe",
    "data": {
        "channel": "private-rvm.1"
    }
}
```

### **Step 4: Claim Session (dengan Token)**
```bash
curl -X POST http://localhost:8000/api/v2/rvm/session/claim \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN_FROM_STEP_1" \
  -d '{"session_token": "SESSION_TOKEN_FROM_STEP_2"}'
```

### **Step 5: Lihat WebSocket Event**
Anda akan menerima event `sesi.diotorisasi` di WebSocket connection!

---

## **👥 Test Users yang Tersedia:**

- **Email:** `superadmin@example.com` | **Password:** `password` | **Role:** Super Admin
- **Email:** `admin@example.com` | **Password:** `password` | **Role:** Admin  
- **Email:** `user@example.com` | **Password:** `password` | **Role:** User
- **Email:** `john@test.com` | **Password:** `password123` | **Role:** User
- **Email:** `jane@test.com` | **Password:** `password123` | **Role:** User

---

## **🎯 Quick Test dengan Postman:**

1. **Login** → Copy token dari response
2. **Create Session** → Copy session_token dari response  
3. **Subscribe WebSocket** → Channel `private-rvm.1`
4. **Claim Session** → Gunakan token + session_token
5. **Lihat Event** → WebSocket akan menerima `sesi.diotorisasi`

**Sekarang Anda sudah bisa mendapatkan SANCTUM_TOKEN dan melakukan testing lengkap!** 🚀
