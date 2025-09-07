# API V2 Economy Reference

## Quick Reference

### Base URL
```
http://localhost:8000/api/v2
```

### Authentication
```http
Authorization: Bearer {token}
```

## Endpoints

### User Balance Management

| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET` | `/user/balance` | Get user balance with statistics |
| `GET` | `/user/balance/transactions` | Get transaction history |
| `GET` | `/user/balance/statistics` | Get balance statistics (30 days) |
| `GET` | `/user/economy/summary` | Get comprehensive economy summary |

### Voucher Management

| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET` | `/vouchers` | Get available vouchers |
| `POST` | `/vouchers/redeem` | Redeem a voucher |

## Request/Response Examples

### GET /user/balance
```bash
curl -X GET "http://localhost:8000/api/v2/user/balance" \
  -H "Authorization: Bearer {token}"
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
    "recent_transactions": [...]
  }
}
```

### GET /user/balance/transactions
```bash
curl -X GET "http://localhost:8000/api/v2/user/balance/transactions?type=credit&page=1&per_page=10" \
  -H "Authorization: Bearer {token}"
```

**Query Parameters:**
- `type` (optional): `credit` | `debit`
- `start_date` (optional): `YYYY-MM-DD`
- `end_date` (optional): `YYYY-MM-DD`
- `page` (optional): `1` (default)
- `per_page` (optional): `15` (default, max: 100)

### GET /vouchers
```bash
curl -X GET "http://localhost:8000/api/v2/vouchers" \
  -H "Authorization: Bearer {token}"
```

**Response:**
```json
{
  "success": true,
  "message": "Available vouchers retrieved successfully",
  "data": [
    {
      "id": 2,
      "title": "Welcome Voucher",
      "description": "10% discount for new users",
      "cost": "1000.0000",
      "stock": 100,
      "total_redeemed": 1,
      "remaining_stock": 99,
      "is_redeemed": true,
      "redeemed_at": "2025-09-07T07:27:43.000000Z"
    }
  ],
  "pagination": {...}
}
```

### POST /vouchers/redeem
```bash
curl -X POST "http://localhost:8000/api/v2/vouchers/redeem" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{"voucher_id": 4}'
```

**Request Body:**
```json
{
  "voucher_id": 4
}
```

**Success Response:**
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
      "cost": "2000.0000"
    },
    "new_balance": "2912.5000",
    "transaction_id": 3
  }
}
```

## Error Responses

### 400 Bad Request
```json
{
  "success": false,
  "message": "Voucher ID is required"
}
```

### 401 Unauthorized
```html
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="refresh" content="0;url='http://localhost:8000/login'" />
        <title>Redirecting to http://localhost:8000/login</title>
    </head>
</html>
```

### 400 Insufficient Balance
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

### 400 Already Redeemed
```json
{
  "success": false,
  "message": "You have already redeemed this voucher",
  "data": null
}
```

### 404 Voucher Not Found
```json
{
  "success": false,
  "message": "Voucher not found or inactive",
  "data": null
}
```

## Status Codes

| Code | Description |
|------|-------------|
| `200` | Success |
| `400` | Bad Request / Validation Error |
| `401` | Unauthorized |
| `404` | Not Found |
| `422` | Unprocessable Entity |
| `500` | Internal Server Error |

## Rate Limits

| Endpoint | Limit |
|----------|-------|
| General | 60 requests/minute |
| Voucher Redemption | 10 requests/minute |
| Balance Check | 120 requests/minute |

## Data Types

### Currency
- `IDR_POIN`: Poin dalam sistem
- `IDR`: Rupiah

### Transaction Types
- `credit`: Masuk (reward dari deposit)
- `debit`: Keluar (voucher redemption)

### Voucher Status
- `is_redeemed`: `true` jika user sudah menukar
- `redeemed_at`: Tanggal penukaran (null jika belum)

## Common Patterns

### Pagination
```json
{
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

### Success Response
```json
{
  "success": true,
  "message": "Operation completed successfully",
  "data": {...}
}
```

### Error Response
```json
{
  "success": false,
  "message": "Error description",
  "data": null
}
```

## Testing

### Postman Collection
Import file: `docs/api-v2-economy-postman-collection.json`

### Newman (Command Line)
```bash
newman run docs/api-v2-economy-postman-collection.json \
  --environment docs/api-v2-economy-environment.json \
  --reporters cli,json \
  --reporter-json-export results.json
```

### Load Testing
```bash
newman run docs/api-v2-economy-postman-collection.json \
  --folder "Performance Testing" \
  --iteration-count 10 \
  --delay-request 100
```

## SDK Examples

### JavaScript/Node.js
```javascript
const api = new MyRVMEconomyAPI('http://localhost:8000', 'your-token');

// Get balance
const balance = await api.getUserBalance();
console.log('Balance:', balance.data.current_balance);

// Redeem voucher
const result = await api.redeemVoucher(4);
console.log('Redemption code:', result.data.redemption_code);
```

### Python
```python
api = MyRVMEconomyAPI('http://localhost:8000', 'your-token')

# Get balance
balance = api.get_user_balance()
print(f'Balance: {balance["data"]["current_balance"]}')

# Redeem voucher
result = api.redeem_voucher(4)
print(f'Redemption code: {result["data"]["redemption_code"]}')
```

### PHP
```php
$api = new MyRVMEconomyAPI('http://localhost:8000', 'your-token');

// Get balance
$balance = $api->getUserBalance();
echo 'Balance: ' . $balance['data']['current_balance'];

// Redeem voucher
$result = $api->redeemVoucher(4);
echo 'Redemption code: ' . $result['data']['redemption_code'];
```

## Changelog

### v2.1.0 (Current)
- ✅ User Balance Management
- ✅ Voucher Management
- ✅ EconomyService integration
- ✅ Comprehensive error handling
- ✅ Rate limiting
- ✅ Pagination & filtering

### v2.0.0
- ✅ Basic authentication
- ✅ Deposit management
- ✅ Session management

---

**Last Updated:** 2025-09-07  
**API Version:** 2.1.0
