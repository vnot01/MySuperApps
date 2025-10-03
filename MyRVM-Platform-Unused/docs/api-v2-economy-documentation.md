# API V2 Economy Documentation

## Overview
Dokumentasi lengkap untuk API Ekonomi MyRVM v2.1 yang mencakup User Balance Management dan Voucher Management. API ini menyediakan sistem ekonomi mikro untuk platform RVM (Reverse Vending Machine) dengan fitur saldo user, transaksi, dan voucher redemption.

## Table of Contents
1. [Authentication](#authentication)
2. [User Balance Management](#user-balance-management)
3. [Voucher Management](#voucher-management)
4. [Economy Service](#economy-service)
5. [Error Handling](#error-handling)
6. [Response Format](#response-format)
7. [Rate Limiting](#rate-limiting)
8. [Examples](#examples)

## Authentication

Semua endpoint API Ekonomi memerlukan authentication menggunakan Laravel Sanctum token.

### Headers Required
```http
Authorization: Bearer {token}
Content-Type: application/json
```

### Getting Authentication Token
```bash
curl -X POST "http://localhost:8000/api/v2/auth/login" \
  -H "Content-Type: application/json" \
  -d '{"email": "user@example.com", "password": "password123"}'
```

**Response:**
```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "user": {
      "id": 4,
      "name": "John Doe",
      "email": "john@test.com",
      "role": "No Role"
    },
    "token": "2|eJnDVVbwaoNtfiFYgm7ewEetIHQadfy60252ZSnT70844d92",
    "token_type": "Bearer"
  }
}
```

## User Balance Management

### 1. Get User Balance
Mengambil informasi saldo user beserta statistik dan transaksi terbaru.

**Endpoint:** `GET /api/v2/user/balance`

**Headers:**
```http
Authorization: Bearer {token}
```

**Response:**
```json
{
  "success": true,
  "message": "User balance retrieved successfully",
  "data": {
    "user_id": 4,
    "current_balance": "912.5000",
    "currency": "IDR_POIN",
    "statistics": {
      "total_credits": "1912.5000",
      "total_debits": "1000.0000",
      "total_transactions": 2,
      "net_balance": 912.5
    },
    "recent_transactions": [
      {
        "id": 2,
        "type": "debit",
        "amount": "1000.0000",
        "balance_before": "1912.5000",
        "balance_after": "912.5000",
        "description": "Voucher redemption: Welcome Voucher",
        "source_type": "App\\Models\\VoucherRedemption",
        "source_id": 2,
        "created_at": "2025-09-07T07:27:44.000000Z"
      }
    ]
  }
}
```

**Field Descriptions:**
- `user_id`: ID user yang diminta saldonya
- `current_balance`: Saldo saat ini dalam format decimal
- `currency`: Mata uang (IDR_POIN untuk poin, IDR untuk rupiah)
- `statistics.total_credits`: Total kredit yang pernah diterima
- `statistics.total_debits`: Total debit yang pernah dikeluarkan
- `statistics.total_transactions`: Jumlah total transaksi
- `statistics.net_balance`: Saldo bersih (kredit - debit)
- `recent_transactions`: Array transaksi terbaru (maksimal 10)

### 2. Get Transaction History
Mengambil riwayat transaksi user dengan pagination dan filter.

**Endpoint:** `GET /api/v2/user/balance/transactions`

**Headers:**
```http
Authorization: Bearer {token}
```

**Query Parameters:**
- `type` (optional): Filter berdasarkan tipe transaksi (`credit` atau `debit`)
- `start_date` (optional): Filter mulai tanggal (format: YYYY-MM-DD)
- `end_date` (optional): Filter sampai tanggal (format: YYYY-MM-DD)
- `page` (optional): Halaman untuk pagination (default: 1)
- `per_page` (optional): Jumlah item per halaman (default: 15, max: 100)

**Example Request:**
```bash
curl -X GET "http://localhost:8000/api/v2/user/balance/transactions?type=credit&page=1&per_page=10" \
  -H "Authorization: Bearer {token}"
```

**Response:**
```json
{
  "success": true,
  "message": "Transaction history retrieved successfully",
  "data": [
    {
      "id": 1,
      "type": "credit",
      "amount": "1912.5000",
      "balance_before": "0.0000",
      "balance_after": "1912.5000",
      "description": "Reward for plastic deposit (ID: 3)",
      "source_type": "App\\Models\\Deposit",
      "source_id": 3,
      "created_at": "2025-09-07T06:55:17.000000Z"
    }
  ],
  "pagination": {
    "current_page": 1,
    "per_page": 10,
    "total": 1,
    "last_page": 1,
    "from": 1,
    "to": 1
  }
}
```

### 3. Get Balance Statistics
Mengambil statistik saldo user untuk 30 hari terakhir.

**Endpoint:** `GET /api/v2/user/balance/statistics`

**Headers:**
```http
Authorization: Bearer {token}
```

**Response:**
```json
{
  "success": true,
  "message": "Balance statistics retrieved successfully",
  "data": {
    "current_balance": "912.5000",
    "currency": "IDR_POIN",
    "last_30_days": {
      "total_transactions": 2,
      "total_credits": "1912.5000",
      "total_debits": "1000.0000",
      "credit_count": 1,
      "debit_count": 1,
      "net_change": 912.5
    },
    "daily_changes": [
      {
        "date": "2025-09-07",
        "credits": "1912.5000",
        "debits": "1000.0000",
        "net_change": 912.5
      }
    ]
  }
}
```

### 4. Get Economy Summary
Mengambil ringkasan ekonomi user yang komprehensif.

**Endpoint:** `GET /api/v2/user/economy/summary`

**Headers:**
```http
Authorization: Bearer {token}
```

**Response:**
```json
{
  "success": true,
  "message": "Economy summary retrieved successfully",
  "data": {
    "user_balance": {
      "current_balance": "912.5000",
      "currency": "IDR_POIN"
    },
    "transaction_summary": {
      "total_transactions": 2,
      "total_credits": "1912.5000",
      "total_debits": "1000.0000",
      "credit_count": 1,
      "debit_count": 1,
      "net_balance": 912.5
    },
    "deposit_summary": {
      "total_deposits": 2,
      "completed_deposits": 1,
      "pending_deposits": 0,
      "rejected_deposits": 0,
      "total_rewards": "3837.50",
      "avg_confidence": "76.7500000000000000"
    },
    "voucher_summary": {
      "total_redemptions": 1,
      "total_spent": 1000,
      "by_tenant": {
        "1": {
          "count": 1,
          "total_cost": 1000,
          "vouchers": [
            {
              "id": 2,
              "voucher_title": "Welcome Voucher",
              "redemption_code": "VRM68BD33EFDE1B6",
              "cost": "1000.0000",
              "redeemed_at": "2025-09-07T07:27:43.000000Z"
            }
          ]
        }
      }
    }
  }
}
```

## Voucher Management

### 1. Get Available Vouchers
Mengambil daftar voucher yang tersedia untuk user.

**Endpoint:** `GET /api/v2/vouchers`

**Headers:**
```http
Authorization: Bearer {token}
```

**Query Parameters:**
- `page` (optional): Halaman untuk pagination (default: 1)
- `per_page` (optional): Jumlah item per halaman (default: 15, max: 100)

**Response:**
```json
{
  "success": true,
  "message": "Available vouchers retrieved successfully",
  "data": [
    {
      "id": 2,
      "tenant_id": 1,
      "title": "Welcome Voucher",
      "description": "10% discount for new users",
      "cost": "1000.0000",
      "stock": 100,
      "total_redeemed": 1,
      "remaining_stock": 99,
      "valid_from": "2025-09-07T07:27:00.000000Z",
      "valid_until": "2025-10-07T07:27:00.000000Z",
      "is_redeemed": true,
      "redeemed_at": "2025-09-07T07:27:43.000000Z"
    },
    {
      "id": 4,
      "tenant_id": 1,
      "title": "Fixed Discount Voucher",
      "description": "5k discount for any purchase",
      "cost": "2000.0000",
      "stock": 25,
      "total_redeemed": 0,
      "remaining_stock": 25,
      "valid_from": "2025-09-07T07:27:00.000000Z",
      "valid_until": "2025-09-14T07:27:00.000000Z",
      "is_redeemed": false,
      "redeemed_at": null
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

**Field Descriptions:**
- `id`: ID voucher
- `tenant_id`: ID tenant yang mengeluarkan voucher
- `title`: Judul voucher
- `description`: Deskripsi voucher
- `cost`: Biaya untuk menukar voucher (dalam poin)
- `stock`: Jumlah stok voucher
- `total_redeemed`: Jumlah voucher yang sudah ditukar
- `remaining_stock`: Sisa stok voucher
- `valid_from`: Tanggal mulai berlaku
- `valid_until`: Tanggal berakhir berlaku
- `is_redeemed`: Status apakah user sudah menukar voucher ini
- `redeemed_at`: Tanggal user menukar voucher (null jika belum ditukar)

### 2. Redeem Voucher
Menukar voucher dengan saldo user.

**Endpoint:** `POST /api/v2/vouchers/redeem`

**Headers:**
```http
Authorization: Bearer {token}
Content-Type: application/json
```

**Request Body:**
```json
{
  "voucher_id": 4
}
```

**Response (Success):**
```json
{
  "success": true,
  "message": "Voucher redeemed successfully",
  "data": {
    "redemption_id": 3,
    "redemption_code": "VRM68BD33EFDE1B6",
    "voucher": {
      "id": 4,
      "title": "Fixed Discount Voucher",
      "description": "5k discount for any purchase",
      "cost": "2000.0000"
    },
    "new_balance": "2912.5000",
    "transaction_id": 3
  }
}
```

**Response (Error - Insufficient Balance):**
```json
{
  "success": false,
  "message": "Insufficient balance to redeem voucher",
  "data": {
    "required_balance": "2000.0000",
    "current_balance": "912.5000"
  }
}
```

**Response (Error - Already Redeemed):**
```json
{
  "success": false,
  "message": "You have already redeemed this voucher",
  "data": null
}
```

**Response (Error - Voucher Not Found):**
```json
{
  "success": false,
  "message": "Voucher not found or inactive",
  "data": null
}
```

**Response (Error - Missing Voucher ID):**
```json
{
  "success": false,
  "message": "Voucher ID is required"
}
```

## Economy Service

### Overview
EconomyService adalah service class yang menangani logika bisnis ekonomi, termasuk:

- **Reward Management**: Menghitung dan menambahkan reward dari deposit
- **Voucher Redemption**: Validasi dan proses penukaran voucher
- **Transaction Management**: Mengelola transaksi kredit dan debit
- **Balance Management**: Mengelola saldo user
- **Analytics**: Menyediakan analisis ekonomi

### Key Methods

#### 1. addRewardToUserBalance(Deposit $deposit)
Menambahkan reward ke saldo user dari deposit yang disetujui.

**Parameters:**
- `$deposit`: Model Deposit yang sudah disetujui

**Returns:**
```php
[
    'success' => true,
    'user_balance' => UserBalance,
    'transaction' => Transaction
]
```

#### 2. redeemVoucher(int $userId, int $voucherId)
Menukar voucher dengan validasi lengkap.

**Parameters:**
- `$userId`: ID user yang menukar voucher
- `$voucherId`: ID voucher yang akan ditukar

**Returns:**
```php
[
    'success' => true,
    'redemption' => VoucherRedemption,
    'voucher' => Voucher,
    'user_balance' => UserBalance,
    'transaction' => Transaction
]
```

#### 3. getUserEconomySummary(int $userId)
Mengambil ringkasan ekonomi user yang komprehensif.

**Parameters:**
- `$userId`: ID user

**Returns:**
```php
[
    'success' => true,
    'data' => [
        'user_balance' => [...],
        'transaction_summary' => [...],
        'deposit_summary' => [...],
        'voucher_summary' => [...]
    ]
]
```

#### 4. calculateRewardAmount(string $wasteType, float $weight, string $qualityGrade, float $confidence)
Menghitung jumlah reward berdasarkan tipe sampah, berat, kualitas, dan confidence.

**Parameters:**
- `$wasteType`: Tipe sampah (plastic, glass, metal, paper, organic)
- `$weight`: Berat dalam kg
- `$qualityGrade`: Grade kualitas (A, B, C, D)
- `$confidence`: Tingkat confidence AI (0-100)

**Returns:**
```php
float // Jumlah reward yang dihitung
```

## Error Handling

### HTTP Status Codes
- `200 OK`: Request berhasil
- `400 Bad Request`: Request tidak valid atau parameter salah
- `401 Unauthorized`: Token tidak valid atau tidak ada
- `404 Not Found`: Resource tidak ditemukan
- `422 Unprocessable Entity`: Validasi gagal
- `500 Internal Server Error`: Error server internal

### Error Response Format
```json
{
  "success": false,
  "message": "Error message description",
  "data": {
    "field_name": ["Validation error message"]
  }
}
```

### Common Error Messages

#### Authentication Errors
- `"Unauthenticated"`: Token tidak valid atau tidak ada
- `"Token has expired"`: Token sudah kadaluarsa

#### Validation Errors
- `"Voucher ID is required"`: Parameter voucher_id tidak ada
- `"Invalid transaction type"`: Tipe transaksi tidak valid
- `"Amount must be greater than 0"`: Jumlah harus lebih dari 0

#### Business Logic Errors
- `"Insufficient balance to redeem voucher"`: Saldo tidak cukup
- `"You have already redeemed this voucher"`: Voucher sudah pernah ditukar
- `"Voucher not found or inactive"`: Voucher tidak ditemukan atau tidak aktif
- `"Voucher is out of stock"`: Voucher sudah habis
- `"Voucher is not valid at this time"`: Voucher belum berlaku atau sudah kadaluarsa

## Response Format

### Success Response
```json
{
  "success": true,
  "message": "Operation completed successfully",
  "data": {
    // Response data
  }
}
```

### Error Response
```json
{
  "success": false,
  "message": "Error description",
  "data": null,
  "error": "Detailed error message (optional)"
}
```

### Pagination Response
```json
{
  "success": true,
  "message": "Data retrieved successfully",
  "data": [
    // Array of items
  ],
  "pagination": {
    "current_page": 1,
    "per_page": 15,
    "total": 100,
    "last_page": 7,
    "from": 1,
    "to": 15
  }
}
```

## Rate Limiting

API Ekonomi menggunakan rate limiting untuk mencegah abuse:

- **Default**: 60 requests per minute per user
- **Voucher Redemption**: 10 requests per minute per user
- **Balance Check**: 120 requests per minute per user

### Rate Limit Headers
```http
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 59
X-RateLimit-Reset: 1640995200
```

### Rate Limit Exceeded Response
```json
{
  "success": false,
  "message": "Too many requests",
  "data": {
    "retry_after": 60
  }
}
```

## Examples

### Complete Workflow Example

#### 1. Login dan Get Token
```bash
curl -X POST "http://localhost:8000/api/v2/auth/login" \
  -H "Content-Type: application/json" \
  -d '{"email": "john@test.com", "password": "password123"}'
```

#### 2. Check User Balance
```bash
curl -X GET "http://localhost:8000/api/v2/user/balance" \
  -H "Authorization: Bearer 2|eJnDVVbwaoNtfiFYgm7ewEetIHQadfy60252ZSnT70844d92"
```

#### 3. Get Available Vouchers
```bash
curl -X GET "http://localhost:8000/api/v2/vouchers" \
  -H "Authorization: Bearer 2|eJnDVVbwaoNtfiFYgm7ewEetIHQadfy60252ZSnT70844d92"
```

#### 4. Redeem Voucher
```bash
curl -X POST "http://localhost:8000/api/v2/vouchers/redeem" \
  -H "Authorization: Bearer 2|eJnDVVbwaoNtfiFYgm7ewEetIHQadfy60252ZSnT70844d92" \
  -H "Content-Type: application/json" \
  -d '{"voucher_id": 4}'
```

#### 5. Check Updated Balance
```bash
curl -X GET "http://localhost:8000/api/v2/user/balance" \
  -H "Authorization: Bearer 2|eJnDVVbwaoNtfiFYgm7ewEetIHQadfy60252ZSnT70844d92"
```

#### 6. Get Economy Summary
```bash
curl -X GET "http://localhost:8000/api/v2/user/economy/summary" \
  -H "Authorization: Bearer 2|eJnDVVbwaoNtfiFYgm7ewEetIHQadfy60252ZSnT70844d92"
```

### JavaScript/Node.js Example

```javascript
const axios = require('axios');

class MyRVMEconomyAPI {
  constructor(baseURL, token) {
    this.baseURL = baseURL;
    this.token = token;
    this.headers = {
      'Authorization': `Bearer ${token}`,
      'Content-Type': 'application/json'
    };
  }

  async getUserBalance() {
    try {
      const response = await axios.get(`${this.baseURL}/api/v2/user/balance`, {
        headers: this.headers
      });
      return response.data;
    } catch (error) {
      throw new Error(`Failed to get user balance: ${error.response?.data?.message || error.message}`);
    }
  }

  async getVouchers() {
    try {
      const response = await axios.get(`${this.baseURL}/api/v2/vouchers`, {
        headers: this.headers
      });
      return response.data;
    } catch (error) {
      throw new Error(`Failed to get vouchers: ${error.response?.data?.message || error.message}`);
    }
  }

  async redeemVoucher(voucherId) {
    try {
      const response = await axios.post(`${this.baseURL}/api/v2/vouchers/redeem`, {
        voucher_id: voucherId
      }, {
        headers: this.headers
      });
      return response.data;
    } catch (error) {
      throw new Error(`Failed to redeem voucher: ${error.response?.data?.message || error.message}`);
    }
  }

  async getEconomySummary() {
    try {
      const response = await axios.get(`${this.baseURL}/api/v2/user/economy/summary`, {
        headers: this.headers
      });
      return response.data;
    } catch (error) {
      throw new Error(`Failed to get economy summary: ${error.response?.data?.message || error.message}`);
    }
  }
}

// Usage
const api = new MyRVMEconomyAPI('http://localhost:8000', 'your-token-here');

// Get user balance
api.getUserBalance().then(balance => {
  console.log('Current balance:', balance.data.current_balance);
});

// Redeem voucher
api.redeemVoucher(4).then(result => {
  console.log('Voucher redeemed:', result.data.redemption_code);
});
```

### Python Example

```python
import requests
import json

class MyRVMEconomyAPI:
    def __init__(self, base_url, token):
        self.base_url = base_url
        self.headers = {
            'Authorization': f'Bearer {token}',
            'Content-Type': 'application/json'
        }
    
    def get_user_balance(self):
        try:
            response = requests.get(f'{self.base_url}/api/v2/user/balance', headers=self.headers)
            response.raise_for_status()
            return response.json()
        except requests.exceptions.RequestException as e:
            raise Exception(f'Failed to get user balance: {e}')
    
    def get_vouchers(self):
        try:
            response = requests.get(f'{self.base_url}/api/v2/vouchers', headers=self.headers)
            response.raise_for_status()
            return response.json()
        except requests.exceptions.RequestException as e:
            raise Exception(f'Failed to get vouchers: {e}')
    
    def redeem_voucher(self, voucher_id):
        try:
            data = {'voucher_id': voucher_id}
            response = requests.post(f'{self.base_url}/api/v2/vouchers/redeem', 
                                   headers=self.headers, 
                                   data=json.dumps(data))
            response.raise_for_status()
            return response.json()
        except requests.exceptions.RequestException as e:
            raise Exception(f'Failed to redeem voucher: {e}')
    
    def get_economy_summary(self):
        try:
            response = requests.get(f'{self.base_url}/api/v2/user/economy/summary', headers=self.headers)
            response.raise_for_status()
            return response.json()
        except requests.exceptions.RequestException as e:
            raise Exception(f'Failed to get economy summary: {e}')

# Usage
api = MyRVMEconomyAPI('http://localhost:8000', 'your-token-here')

# Get user balance
balance = api.get_user_balance()
print(f'Current balance: {balance["data"]["current_balance"]}')

# Redeem voucher
result = api.redeem_voucher(4)
print(f'Voucher redeemed: {result["data"]["redemption_code"]}')
```

## Best Practices

### 1. Error Handling
- Selalu handle error responses dengan proper error messages
- Implement retry logic untuk network errors
- Log errors untuk debugging

### 2. Token Management
- Simpan token dengan aman
- Handle token expiration
- Implement token refresh jika diperlukan

### 3. Rate Limiting
- Implement exponential backoff untuk rate limit errors
- Monitor rate limit headers
- Cache responses jika memungkinkan

### 4. Data Validation
- Validasi input sebelum mengirim request
- Handle validation errors dengan user-friendly messages
- Implement client-side validation

### 5. Security
- Jangan hardcode token di client code
- Gunakan HTTPS di production
- Implement proper CORS settings

## Changelog

### Version 2.1.0 (Current)
- ✅ User Balance Management API
- ✅ Voucher Management API
- ✅ EconomyService integration
- ✅ Comprehensive error handling
- ✅ Rate limiting
- ✅ Pagination support
- ✅ Filter support

### Version 2.0.0
- ✅ Basic authentication
- ✅ Deposit management
- ✅ Session management

## Support

Untuk pertanyaan atau bantuan terkait API Ekonomi, silakan hubungi:
- Email: support@myrvm.com
- Documentation: https://docs.myrvm.com/api/v2/economy
- GitHub Issues: https://github.com/myrvm/platform/issues

---

**Last Updated:** 2025-09-07  
**API Version:** 2.1.0  
**Documentation Version:** 1.0.0
