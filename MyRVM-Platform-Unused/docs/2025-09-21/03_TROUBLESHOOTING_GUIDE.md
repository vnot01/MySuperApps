# Troubleshooting Guide - RVM-Jetson Integration
**Date:** 2025-09-21  
**Version:** 1.0  
**Integration:** RVM-Jetson ↔ MyRVM-Platform  

## 🚨 Common Issues & Solutions

### 1. CSRF Token Mismatch (419 Error)

#### Problem
```json
{
    "message": "CSRF token mismatch.",
    "exception": "Symfony\\Component\\HttpKernel\\Exception\\HttpException"
}
```

#### Root Cause
- Laravel's default CSRF protection blocking external API requests
- RVM-Jetson cannot provide valid CSRF tokens

#### Solution ✅
**Status:** RESOLVED
- Created custom `RvmCsrfMiddleware` for RVM requests
- Moved RVM endpoints to API routes (no CSRF protection)
- Implemented API token authentication system

#### Verification
```bash
# Test health check (should work without CSRF)
curl -X GET "http://localhost:8001/api/health-check"

# Test API token generation (should work without CSRF)
curl -X POST "http://localhost:8001/api/rvm/generate-token" \
  -H "Content-Type: application/json" \
  -d '{"rvm_id": "4", "ip_address": "172.28.93.97"}'
```

### 2. RVM Not Found Error

#### Problem
```json
{
    "success": false,
    "message": "RVM not found",
    "command_id": 19,
    "data": null
}
```

#### Root Cause
- Logic issue in `EnhancedRemoteCommandsController`
- RVM exists in database but controller returns "not found"

#### Current Status ⚠️
**Status:** DEBUGGING IN PROGRESS
- Log shows RVM found but response still indicates not found
- Need to investigate return statement logic

#### Debug Steps
1. Check RVM exists in database:
```bash
cd /home/my/MySuperApps/MyRVM-Platform
docker compose exec --user 1000:1000 app php -r "
require 'vendor/autoload.php';
\$app = require 'bootstrap/app.php';
\$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
\$rvm = App\Models\ReverseVendingMachine::find(4);
if(\$rvm) { echo 'RVM ID 4: ' . \$rvm->name . ' - ' . \$rvm->ip_address; }
"
```

2. Check controller logic in `EnhancedRemoteCommandsController.php`
3. Verify route parameters are correctly passed

### 3. Database Schema Issues

#### Problem
```json
{
    "error": "SQLSTATE[42703]: Undefined column: 7 ERROR: column \"recorded_at\" of relation \"system_metrics\" violates not-null constraint"
}
```

#### Root Cause
- Database schema inconsistencies
- Code references non-existent columns

#### Solution ✅
**Status:** PARTIALLY RESOLVED
- Fixed `timestamp` column reference in `EnhancedMetricsController`
- Some columns still need alignment

#### Remaining Issues
- `ApplicationMetric` and `NetworkInformation` tables may have similar issues
- Need to verify all column references match actual schema

### 4. API Token Issues

#### Problem
```json
{
    "success": false,
    "error": "Invalid or expired token",
    "message": "API token is invalid or has expired"
}
```

#### Root Cause
- Token expired (30-day expiration)
- Invalid token format
- Token not found in database

#### Solution
1. **Generate new token:**
```bash
curl -X POST "http://localhost:8001/api/rvm/generate-token" \
  -H "Content-Type: application/json" \
  -d '{"rvm_id": "4", "ip_address": "172.28.93.97"}'
```

2. **Validate existing token:**
```bash
curl -X POST "http://localhost:8001/api/rvm/validate-token" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{}'
```

3. **Revoke and regenerate:**
```bash
curl -X POST "http://localhost:8001/api/rvm/revoke-token" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{}'
```

### 5. Connection Issues

#### Problem
- RVM-Jetson cannot reach server
- Network connectivity issues
- Port forwarding problems

#### Debug Steps
1. **Test server accessibility:**
```bash
# From RVM-Jetson
curl -X GET "http://localhost:8001/api/health-check"
```

2. **Check network connectivity:**
```bash
# Ping test
ping localhost

# Port test
telnet localhost 8001
```

3. **Verify port forwarding:**
- Ensure port 8001 is forwarded in VS Code/Cursor
- Check firewall settings
- Verify Docker container is running

## 🔧 Debug Commands

### Server Health Check
```bash
# Check if server is running
curl -X GET "http://localhost:8001/api/health-check"

# Check server status
curl -X GET "http://localhost:8001/api/status"
```

### Database Verification
```bash
# Check RVM exists
cd /home/my/MySuperApps/MyRVM-Platform
docker compose exec --user 1000:1000 app php -r "
require 'vendor/autoload.php';
\$app = require 'bootstrap/app.php';
\$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
\$rvms = App\Models\ReverseVendingMachine::all();
foreach(\$rvms as \$rvm) {
    echo 'ID: ' . \$rvm->id . ' | Name: ' . \$rvm->name . ' | IP: ' . \$rvm->ip_address . PHP_EOL;
}
"
```

### Log Analysis
```bash
# Check Laravel logs
cd /home/my/MySuperApps/MyRVM-Platform
docker compose exec --user 1000:1000 app tail -f storage/logs/laravel.log

# Check specific log entries
docker compose exec --user 1000:1000 app tail -n 50 storage/logs/laravel.log | grep "RVM"
```

### Cache Clearing
```bash
# Clear all caches
cd /home/my/MySuperApps/MyRVM-Platform
docker compose exec --user 1000:1000 app php artisan config:clear
docker compose exec --user 1000:1000 app php artisan route:clear
docker compose exec --user 1000:1000 app php artisan cache:clear
```

## 📊 Status Dashboard

### ✅ Working Components
- [x] CSRF protection bypass
- [x] API token generation
- [x] API token validation
- [x] Health check endpoint
- [x] Metrics storage
- [x] Database connection
- [x] RVM detection logic

### ⚠️ Issues in Progress
- [ ] Command execution debugging
- [ ] Database schema alignment
- [ ] Error handling improvements

### 🔄 Testing Status
- [x] Health check: ✅ Working
- [x] Token generation: ✅ Working
- [x] Token validation: ✅ Working
- [x] Metrics storage: ✅ Working
- [ ] Command execution: ⚠️ Debugging
- [ ] Integration testing: ⏳ Pending

## 🚀 Quick Fixes

### Reset API Token
```bash
# Generate new token for RVM-Orin1
curl -X POST "http://localhost:8001/api/rvm/generate-token" \
  -H "Content-Type: application/json" \
  -d '{"rvm_id": "4", "ip_address": "172.28.93.97"}'
```

### Test Basic Communication
```bash
# 1. Health check
curl -X GET "http://localhost:8001/api/health-check"

# 2. Generate token
TOKEN=$(curl -s -X POST "http://localhost:8001/api/rvm/generate-token" \
  -H "Content-Type: application/json" \
  -d '{"rvm_id": "4", "ip_address": "172.28.93.97"}' | jq -r '.data.api_token')

# 3. Test metrics storage
curl -X POST "http://localhost:8001/api/rvm/4/store-metrics" \
  -H "Authorization: Bearer $TOKEN" \
  -H "X-RVM-ID: 4" \
  -H "Content-Type: application/json" \
  -d '{"system_metrics": {"cpu_usage": 45.2, "memory_usage": 67.8}}'
```

### Emergency Reset
```bash
# Clear all caches and restart
cd /home/my/MySuperApps/MyRVM-Platform
docker compose exec --user 1000:1000 app php artisan config:clear
docker compose exec --user 1000:1000 app php artisan route:clear
docker compose exec --user 1000:1000 app php artisan cache:clear
docker compose restart app
```

## 📞 Support Information

### Log Locations
- **Laravel Logs:** `storage/logs/laravel.log`
- **Docker Logs:** `docker compose logs app`
- **System Logs:** `/var/log/` (on host system)

### Configuration Files
- **Middleware:** `app/Http/Middleware/RvmCsrfMiddleware.php`
- **API Routes:** `routes/api.php`
- **Auth Controller:** `app/Http/Controllers/Api/RvmAuthController.php`
- **Bootstrap:** `bootstrap/app.php`

### Database Tables
- **RVMs:** `reverse_vending_machines`
- **Metrics:** `system_metrics`, `application_metrics`, `network_information`
- **Commands:** `remote_commands`
- **Tokens:** Stored in `reverse_vending_machines.api_token`

---
**Troubleshooting Guide Generated:** 2025-09-21  
**Version:** 1.0  
**Last Updated:** 2025-09-21  
**Next Review:** After command execution debugging completion
