# API v2 Analytics Testing Documentation

## Overview
Dokumentasi ini menjelaskan cara testing untuk API Analytics & Reporting yang menyediakan analisis komprehensif sistem MyRVM v2.1.

## Prerequisites
- Docker container running
- Database seeded dengan data test
- Authentication token (Bearer token)
- API v2 routes registered

## Authentication
Semua endpoint memerlukan authentication dengan Bearer token:
```bash
Authorization: Bearer {token}
```

## API Endpoints

### 1. Dashboard Analytics
**GET** `/api/v2/analytics/dashboard`

#### Parameters
- `period` (optional): `7d`, `30d`, `90d`, `1y` (default: `30d`)

#### Test Commands
```bash
# Test dengan period 7 hari
curl -X GET "http://localhost:8000/api/v2/analytics/dashboard?period=7d" \
  -H "Authorization: Bearer 2|eJnDVVbwaoNtfiFYgm7ewEetIHQadfy60252ZSnT70844d92"

# Test dengan period 30 hari (default)
curl -X GET "http://localhost:8000/api/v2/analytics/dashboard" \
  -H "Authorization: Bearer 2|eJnDVVbwaoNtfiFYgm7ewEetIHQadfy60252ZSnT70844d92"

# Test dengan period 90 hari
curl -X GET "http://localhost:8000/api/v2/analytics/dashboard?period=90d" \
  -H "Authorization: Bearer 2|eJnDVVbwaoNtfiFYgm7ewEetIHQadfy60252ZSnT70844d92"
```

#### Expected Response
```json
{
  "success": true,
  "message": "Dashboard analytics retrieved successfully",
  "data": {
    "period": "7d",
    "date_range": {
      "start": "2025-08-31",
      "end": "2025-09-07"
    },
    "overview": {
      "total_users": 6,
      "active_users": 5,
      "total_deposits": 2,
      "completed_deposits": 1,
      "total_rewards_given": "1912.50",
      "total_rvms": 3,
      "active_rvms": 2,
      "total_voucher_redemptions": 1,
      "total_balance": "912.5000"
    },
    "users": {
      "new_users": 6,
      "active_users": 1,
      "total_users": 6,
      "user_growth_rate": 100
    },
    "deposits": {
      "total_deposits": 2,
      "completed_deposits": 1,
      "pending_deposits": 0,
      "processing_deposits": 1,
      "rejected_deposits": 0,
      "total_rewards_given": "1912.50",
      "avg_reward_per_deposit": 1912.5,
      "completion_rate": 50
    },
    "economy": {
      "total_transactions": 2,
      "total_credits": "1912.5000",
      "total_debits": "1000.0000",
      "net_flow": 912.5,
      "voucher_redemptions": 1,
      "avg_transaction_amount": 1456.25
    },
    "rvms": {
      "total_rvms": 3,
      "active_rvms": 2,
      "inactive_rvms": 0,
      "maintenance_rvms": 1,
      "rvms_with_activity": 1,
      "utilization_rate": 33.33333333333333
    },
    "trends": {
      "daily_deposits": [...],
      "daily_rewards": [...],
      "daily_users": [...]
    }
  }
}
```

### 2. Deposit Analytics
**GET** `/api/v2/analytics/deposits`

#### Parameters
- `period` (optional): `7d`, `30d`, `90d`, `1y` (default: `30d`)

#### Test Commands
```bash
# Test deposit analytics
curl -X GET "http://localhost:8000/api/v2/analytics/deposits?period=30d" \
  -H "Authorization: Bearer 2|eJnDVVbwaoNtfiFYgm7ewEetIHQadfy60252ZSnT70844d92"
```

#### Expected Response
```json
{
  "success": true,
  "message": "Deposit analytics retrieved successfully",
  "data": {
    "period": "30d",
    "date_range": {
      "start": "2025-08-08",
      "end": "2025-09-07"
    },
    "summary": {
      "total_deposits": 2,
      "completed_deposits": 1,
      "pending_deposits": 0,
      "processing_deposits": 1,
      "rejected_deposits": 0,
      "total_rewards_given": "1912.50",
      "avg_reward_per_deposit": 1912.5,
      "completion_rate": 50
    },
    "by_status": {
      "processing": 1,
      "completed": 1
    },
    "by_waste_type": [],
    "by_rvm": [
      {
        "rvm_id": 1,
        "rvm_name": "RVM-001",
        "count": 2
      }
    ],
    "daily_trends": [...],
    "top_users": [...]
  }
}
```

### 3. Economy Analytics
**GET** `/api/v2/analytics/economy`

#### Parameters
- `period` (optional): `7d`, `30d`, `90d`, `1y` (default: `30d`)

#### Test Commands
```bash
# Test economy analytics
curl -X GET "http://localhost:8000/api/v2/analytics/economy?period=30d" \
  -H "Authorization: Bearer 2|eJnDVVbwaoNtfiFYgm7ewEetIHQadfy60252ZSnT70844d92"
```

#### Expected Response
```json
{
  "success": true,
  "message": "Economy analytics retrieved successfully",
  "data": {
    "period": "30d",
    "date_range": {
      "start": "2025-08-08",
      "end": "2025-09-07"
    },
    "summary": {
      "total_transactions": 2,
      "total_credits": "1912.5000",
      "total_debits": "1000.0000",
      "net_flow": 912.5,
      "voucher_redemptions": 1,
      "avg_transaction_amount": 1456.25
    },
    "transactions": {
      "total_transactions": 2,
      "credit_transactions": 1,
      "debit_transactions": 1
    },
    "voucher_redemptions": {
      "total_redemptions": 1,
      "used_vouchers": 0,
      "unused_vouchers": 1
    },
    "balance_distribution": {
      "zero_balance": 5,
      "low_balance": 1,
      "medium_balance": 0,
      "high_balance": 0
    },
    "revenue_trends": [...]
  }
}
```

### 4. User Analytics
**GET** `/api/v2/analytics/users`

#### Parameters
- `period` (optional): `7d`, `30d`, `90d`, `1y` (default: `30d`)

#### Test Commands
```bash
# Test user analytics
curl -X GET "http://localhost:8000/api/v2/analytics/users?period=30d" \
  -H "Authorization: Bearer 2|eJnDVVbwaoNtfiFYgm7ewEetIHQadfy60252ZSnT70844d92"
```

#### Expected Response
```json
{
  "success": true,
  "message": "User analytics retrieved successfully",
  "data": {
    "period": "30d",
    "date_range": {
      "start": "2025-08-08",
      "end": "2025-09-07"
    },
    "summary": {
      "new_users": 6,
      "active_users": 1,
      "total_users": 6,
      "user_growth_rate": 100
    },
    "registration_trends": [...],
    "activity_levels": {
      "highly_active": 0,
      "moderately_active": 1,
      "low_activity": 0,
      "inactive": 5
    },
    "by_role": [
      {
        "role_id": null,
        "role_name": "No Role",
        "count": 1
      },
      {
        "role_id": 1,
        "role_name": "Super Admin",
        "count": 1
      },
      {
        "role_id": 4,
        "role_name": "User",
        "count": 3
      },
      {
        "role_id": 2,
        "role_name": "Admin",
        "count": 1
      }
    ],
    "top_contributors": [...]
  }
}
```

### 5. RVM Analytics
**GET** `/api/v2/analytics/rvms`

#### Parameters
- `period` (optional): `7d`, `30d`, `90d`, `1y` (default: `30d`)

#### Test Commands
```bash
# Test RVM analytics
curl -X GET "http://localhost:8000/api/v2/analytics/rvms?period=30d" \
  -H "Authorization: Bearer 2|eJnDVVbwaoNtfiFYgm7ewEetIHQadfy60252ZSnT70844d92"
```

#### Expected Response
```json
{
  "success": true,
  "message": "RVM analytics retrieved successfully",
  "data": {
    "period": "30d",
    "date_range": {
      "start": "2025-08-08",
      "end": "2025-09-07"
    },
    "summary": {
      "total_rvms": 3,
      "active_rvms": 2,
      "inactive_rvms": 0,
      "maintenance_rvms": 1,
      "rvms_with_activity": 1,
      "utilization_rate": 33.33333333333333
    },
    "performance_ranking": [
      {
        "id": 1,
        "name": "RVM-001",
        "location_description": "Lobby Gedung A, Lantai 1",
        "status": "active",
        "api_key": "juhFbragzyelllLERikaocYXNHMASnFi",
        "created_at": "2025-09-07T04:56:30.000000Z",
        "updated_at": "2025-09-07T04:56:30.000000Z",
        "deposits_count": 2,
        "deposits_sum_reward_amount": "1912.50"
      }
    ],
    "utilization_rates": [...],
    "maintenance_insights": {
      "maintenance_rvms": 1,
      "inactive_rvms": 0,
      "rvms_needing_attention": [
        {
          "id": 3,
          "name": "RVM-003",
          "status": "maintenance"
        }
      ]
    }
  }
}
```

### 6. Generate Custom Report
**POST** `/api/v2/analytics/reports`

#### Parameters
- `report_type` (required): `deposits`, `economy`, `users`, `rvms`, `comprehensive`
- `start_date` (required): Date in YYYY-MM-DD format
- `end_date` (required): Date in YYYY-MM-DD format
- `format` (optional): `json`, `csv`, `pdf` (default: `json`)
- `filters` (optional): Array of filters

#### Test Commands
```bash
# Test comprehensive report
curl -X POST "http://localhost:8000/api/v2/analytics/reports" \
  -H "Authorization: Bearer 2|eJnDVVbwaoNtfiFYgm7ewEetIHQadfy60252ZSnT70844d92" \
  -H "Content-Type: application/json" \
  -d '{
    "report_type": "comprehensive",
    "start_date": "2025-09-01",
    "end_date": "2025-09-07",
    "format": "json"
  }'

# Test deposits report
curl -X POST "http://localhost:8000/api/v2/analytics/reports" \
  -H "Authorization: Bearer 2|eJnDVVbwaoNtfiFYgm7ewEetIHQadfy60252ZSnT70844d92" \
  -H "Content-Type: application/json" \
  -d '{
    "report_type": "deposits",
    "start_date": "2025-09-01",
    "end_date": "2025-09-07",
    "format": "json"
  }'

# Test economy report
curl -X POST "http://localhost:8000/api/v2/analytics/reports" \
  -H "Authorization: Bearer 2|eJnDVVbwaoNtfiFYgm7ewEetIHQadfy60252ZSnT70844d92" \
  -H "Content-Type: application/json" \
  -d '{
    "report_type": "economy",
    "start_date": "2025-09-01",
    "end_date": "2025-09-07",
    "format": "json"
  }'
```

#### Expected Response
```json
{
  "success": true,
  "message": "Report generated successfully",
  "data": {
    "report_type": "comprehensive",
    "date_range": {
      "start": "2025-09-01",
      "end": "2025-09-07"
    },
    "format": "json",
    "generated_at": "2025-09-07T10:04:03.652380Z",
    "report": {
      "overview": {...},
      "users": {...},
      "deposits": {...},
      "economy": {...},
      "rvms": {...}
    }
  }
}
```

## Error Scenarios

### 1. Unauthorized Access
```bash
# Test tanpa token
curl -X GET "http://localhost:8000/api/v2/analytics/dashboard"
```

**Expected Response:**
```json
{
  "message": "Unauthenticated."
}
```

### 2. Invalid Period Parameter
```bash
# Test dengan period invalid
curl -X GET "http://localhost:8000/api/v2/analytics/dashboard?period=invalid" \
  -H "Authorization: Bearer 2|eJnDVVbwaoNtfiFYgm7ewEetIHQadfy60252ZSnT70844d92"
```

**Expected Response:** (Menggunakan default period 30d)

### 3. Invalid Report Parameters
```bash
# Test dengan parameter report yang salah
curl -X POST "http://localhost:8000/api/v2/analytics/reports" \
  -H "Authorization: Bearer 2|eJnDVVbwaoNtfiFYgm7ewEetIHQadfy60252ZSnT70844d92" \
  -H "Content-Type: application/json" \
  -d '{
    "report_type": "invalid",
    "start_date": "invalid-date",
    "end_date": "2025-09-07"
  }'
```

**Expected Response:**
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "report_type": ["The selected report type is invalid."],
    "start_date": ["The start date does not match the format Y-m-d."]
  }
}
```

## Performance Testing

### 1. Load Testing dengan Multiple Periods
```bash
# Test dengan berbagai period secara bersamaan
for period in 7d 30d 90d 1y; do
  curl -X GET "http://localhost:8000/api/v2/analytics/dashboard?period=$period" \
    -H "Authorization: Bearer 2|eJnDVVbwaoNtfiFYgm7ewEetIHQadfy60252ZSnT70844d92" &
done
wait
```

### 2. Concurrent Report Generation
```bash
# Test generate multiple reports secara bersamaan
for type in deposits economy users rvms comprehensive; do
  curl -X POST "http://localhost:8000/api/v2/analytics/reports" \
    -H "Authorization: Bearer 2|eJnDVVbwaoNtfiFYgm7ewEetIHQadfy60252ZSnT70844d92" \
    -H "Content-Type: application/json" \
    -d "{\"report_type\": \"$type\", \"start_date\": \"2025-09-01\", \"end_date\": \"2025-09-07\"}" &
done
wait
```

## Database Verification

### 1. Verify Analytics Data Accuracy
```bash
# Cek data di database
docker compose exec app php artisan tinker --execute="
echo 'Total Users: ' . App\Models\User::count() . PHP_EOL;
echo 'Total Deposits: ' . App\Models\Deposit::count() . PHP_EOL;
echo 'Total RVMs: ' . App\Models\ReverseVendingMachine::count() . PHP_EOL;
echo 'Total Transactions: ' . App\Models\Transaction::count() . PHP_EOL;
"
```

### 2. Verify Date Range Filtering
```bash
# Cek data dalam range tanggal tertentu
docker compose exec app php artisan tinker --execute="
\$startDate = '2025-09-01';
\$endDate = '2025-09-07';
echo 'Users in range: ' . App\Models\User::whereBetween('created_at', [\$startDate, \$endDate])->count() . PHP_EOL;
echo 'Deposits in range: ' . App\Models\Deposit::whereBetween('created_at', [\$startDate, \$endDate])->count() . PHP_EOL;
"
```

## Success Criteria

### ✅ Functional Requirements
- [x] Dashboard analytics menampilkan overview sistem
- [x] Deposit analytics menampilkan breakdown detail
- [x] Economy analytics menampilkan data ekonomi
- [x] User analytics menampilkan data pengguna
- [x] RVM analytics menampilkan performa RVM
- [x] Custom report generation berfungsi
- [x] Period filtering (7d, 30d, 90d, 1y) berfungsi
- [x] Date range filtering berfungsi
- [x] Error handling yang proper

### ✅ Performance Requirements
- [x] Response time < 2 detik untuk dashboard analytics
- [x] Response time < 3 detik untuk detailed analytics
- [x] Response time < 5 detik untuk comprehensive reports
- [x] Concurrent requests handling

### ✅ Security Requirements
- [x] Authentication required untuk semua endpoints
- [x] Input validation untuk semua parameters
- [x] SQL injection protection
- [x] XSS protection

### ✅ Data Accuracy
- [x] Analytics data sesuai dengan data database
- [x] Date range filtering akurat
- [x] Aggregation calculations benar
- [x] Growth rate calculations akurat

## Troubleshooting

### Common Issues

1. **Slow Response Time**
   - Cek database indexes
   - Optimize queries dengan eager loading
   - Consider caching untuk data yang jarang berubah

2. **Memory Issues**
   - Limit data yang diambil per query
   - Use pagination untuk large datasets
   - Optimize memory usage di helper methods

3. **Data Inconsistency**
   - Verify database relationships
   - Check for data integrity issues
   - Ensure proper date handling

### Debug Commands

```bash
# Cek log errors
docker compose exec app tail -f storage/logs/laravel.log

# Cek database connections
docker compose exec app php artisan tinker --execute="
echo 'DB Connection: ' . (DB::connection()->getPdo() ? 'OK' : 'FAILED') . PHP_EOL;
"

# Cek route registration
docker compose exec app php artisan route:list --path=analytics
```

## Notes

- AnalyticsController menggunakan helper methods untuk modularity
- Semua calculations dilakukan di database level untuk performance
- Date handling menggunakan Carbon untuk consistency
- Error handling menggunakan try-catch dengan proper HTTP status codes
- Response format konsisten dengan API v2 standards
