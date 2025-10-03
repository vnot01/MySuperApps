# Technical Implementation Guide - RVM-Jetson Integration
**Date:** 2025-09-21  
**Version:** 1.0  
**Target:** Developers & System Administrators  

## 🏗️ Architecture Overview

### System Architecture
```
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   RVM-Jetson    │    │  MyRVM-Platform │    │    Database     │
│                 │    │                 │    │                 │
│ ┌─────────────┐ │    │ ┌─────────────┐ │    │ ┌─────────────┐ │
│ │API Client   │◄┼────┼►│API Routes   │ │    │ │PostgreSQL   │ │
│ └─────────────┘ │    │ └─────────────┘ │    │ └─────────────┘ │
│ ┌─────────────┐ │    │ ┌─────────────┐ │    │ ┌─────────────┐ │
│ │Metrics      │◄┼────┼►│Controllers  │ │    │ │Tables       │ │
│ │Sender       │ │    │ └─────────────┘ │    │ └─────────────┘ │
│ └─────────────┘ │    │ ┌─────────────┐ │    │                 │
│ ┌─────────────┐ │    │ │Middleware   │ │    │                 │
│ │Command      │◄┼────┼►│Stack        │ │    │                 │
│ │Executor     │ │    │ └─────────────┘ │    │                 │
│ └─────────────┘ │    │                 │    │                 │
└─────────────────┘    └─────────────────┘    └─────────────────┘
```

## 🔧 Server-Side Implementation

### 1. Custom CSRF Middleware

**File:** `app/Http/Middleware/RvmCsrfMiddleware.php`

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class RvmCsrfMiddleware extends Middleware
{
    public function handle($request, Closure $next): Response
    {
        // Check if this is a request from RVM-Jetson
        if ($this->isRvmRequest($request)) {
            return $this->handleRvmRequest($request, $next);
        }

        // For non-RVM requests, use standard CSRF verification
        return parent::handle($request, $next);
    }

    private function isRvmRequest(Request $request): bool
    {
        $rvmId = $request->header('X-RVM-ID');
        $clientIp = $request->ip();
        $rvmIps = ['172.28.233.83', '10.3.52.161', '127.0.0.1', 'localhost'];
        
        return $rvmId || 
               str_contains($rvmUserAgent ?? '', 'RVM') ||
               in_array($clientIp, $rvmIps);
    }

    private function handleRvmRequest(Request $request, Closure $next): Response
    {
        $apiKey = $request->header('Authorization');
        
        if ($apiKey && str_starts_with($apiKey, 'Bearer ')) {
            // RVM is using API key authentication, skip CSRF
            return $next($request);
        }

        if ($this->validateRvmCsrfToken($request)) {
            return $next($request);
        }

        return response()->json([
            'success' => false,
            'error' => 'CSRF token mismatch for RVM request'
        ], 419);
    }
}
```

### 2. API Token Authentication

**File:** `app/Http/Controllers/Api/RvmAuthController.php`

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ReverseVendingMachine;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Carbon\Carbon;

class RvmAuthController extends Controller
{
    public function generateToken(Request $request): JsonResponse
    {
        $data = $request->validate([
            'rvm_id' => 'required|string',
            'ip_address' => 'required|ip',
        ]);

        $rvm = ReverseVendingMachine::where('id', $data['rvm_id'])
            ->orWhere('ip_address', $data['ip_address'])
            ->first();

        if (!$rvm) {
            return response()->json([
                'success' => false,
                'error' => 'RVM not found'
            ], 404);
        }

        $apiToken = Str::random(64);
        
        $rvm->update([
            'api_token' => hash('sha256', $apiToken),
            'api_token_expires_at' => Carbon::now()->addDays(30),
            'last_api_access' => Carbon::now()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'API token generated successfully',
            'data' => [
                'rvm_id' => $rvm->id,
                'rvm_name' => $rvm->name,
                'api_token' => $apiToken,
                'expires_at' => $rvm->api_token_expires_at->toISOString()
            ]
        ]);
    }
}
```

### 3. API Routes Configuration

**File:** `routes/api.php`

```php
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HealthController;
use App\Http\Controllers\Api\RvmAuthController;
use App\Http\Controllers\Admin\EnhancedMetricsController;
use App\Http\Controllers\Admin\EnhancedRemoteCommandsController;

// RVM-Jetson API Routes (No CSRF protection needed)
Route::get('/health-check', [HealthController::class, 'check']);
Route::get('/status', [HealthController::class, 'status']);

// RVM Authentication Routes
Route::post('/rvm/generate-token', [RvmAuthController::class, 'generateToken']);
Route::post('/rvm/validate-token', [RvmAuthController::class, 'validateToken']);
Route::post('/rvm/revoke-token', [RvmAuthController::class, 'revokeToken']);

// RVM-Jetson API Routes
Route::get('/rvm/{id}/metrics', [EnhancedMetricsController::class, 'getComprehensiveMetrics']);
Route::post('/rvm/{id}/store-metrics', [EnhancedMetricsController::class, 'storeMetrics']);
Route::post('/rvm/{id}/execute-command', [EnhancedRemoteCommandsController::class, 'executeCommand']);
Route::get('/rvm/{id}/command/{commandId}/status', [EnhancedRemoteCommandsController::class, 'getCommandStatus']);
Route::get('/rvm/{id}/recent-commands', [EnhancedRemoteCommandsController::class, 'getRecentCommands']);
```

### 4. Middleware Registration

**File:** `bootstrap/app.php`

```php
<?php

return Application::configure(basePath: dirname(__DIR__))
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'rvm.csrf' => \App\Http\Middleware\RvmCsrfMiddleware::class,
        ]);
        
        // Replace default CSRF middleware with RVM-aware CSRF middleware
        $middleware->web(replace: [
            \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class => 
            \App\Http\Middleware\RvmCsrfMiddleware::class,
        ]);
    })
    ->create();
```

## 🗄️ Database Schema

### 1. RVM Table Updates

**Migration:** `2025_09_21_163300_add_api_token_fields_to_reverse_vending_machines_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reverse_vending_machines', function (Blueprint $table) {
            $table->string('api_token', 255)->nullable()->after('api_key');
            $table->timestamp('api_token_expires_at')->nullable()->after('api_token');
            $table->timestamp('last_api_access')->nullable()->after('api_token_expires_at');
        });
    }

    public function down(): void
    {
        Schema::table('reverse_vending_machines', function (Blueprint $table) {
            $table->dropColumn(['api_token', 'api_token_expires_at', 'last_api_access']);
        });
    }
};
```

### 2. Model Updates

**File:** `app/Models/ReverseVendingMachine.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReverseVendingMachine extends Model
{
    protected $fillable = [
        'name',
        'location_description',
        'location',
        'address',
        'status',
        'capacity',
        'special_status',
        'api_key',
        'api_token',
        'api_token_expires_at',
        'last_api_access',
        'ip_address',
        'port',
        'timezone',
        'timezone_offset',
        'last_timezone_sync',
        'last_ping',
        'connection_status',
    ];

    protected $casts = [
        'api_token_expires_at' => 'datetime',
        'last_api_access' => 'datetime',
        'last_timezone_sync' => 'datetime',
        'last_ping' => 'datetime',
        'capacity' => 'integer',
        'port' => 'integer',
    ];
}
```

## 🔌 RVM-Jetson Implementation

### 1. Enhanced API Client

**File:** `myrvm-integration/api_client/enhanced_myrvm_api_client.py`

```python
import requests
import json
from typing import Dict, Tuple, Optional
from datetime import datetime, timedelta

class EnhancedMyRVMAPIClient:
    def __init__(self, server_url: str, rvm_id: str, api_key: str = None):
        self.server_url = server_url
        self.rvm_id = rvm_id
        self.api_key = api_key
        self.session = requests.Session()
        self.current_url = server_url
        
    def health_check(self) -> Tuple[bool, Dict]:
        """Check server health"""
        try:
            response = self.session.get(f"{self.current_url}/api/health-check", timeout=10)
            if response.status_code == 200:
                return True, response.json()
            else:
                return False, {'error': f'Health check failed: {response.status_code}'}
        except Exception as e:
            return False, {'error': str(e)}
    
    def send_metrics(self, metrics_data: Dict) -> Tuple[bool, Dict]:
        """Send metrics to server"""
        if not self.rvm_id:
            return False, {'error': 'RVM ID not provided'}
        
        # Refresh CSRF token before POST request
        self._initialize_csrf_token()
        
        try:
            response = self.session.post(
                f"{self.current_url}/admin/rvm/{self.rvm_id}/store-metrics",
                json=metrics_data,
                headers={
                    'Content-Type': 'application/json',
                    'Authorization': f'Bearer {self.api_key}',
                    'X-RVM-ID': str(self.rvm_id),
                    'X-Requested-With': 'XMLHttpRequest'
                },
                timeout=30
            )
            
            if response.status_code == 200:
                return True, response.json()
            else:
                return False, {'error': f'Metrics send failed: {response.status_code}'}
        except Exception as e:
            return False, {'error': str(e)}
    
    def _initialize_csrf_token(self):
        """Initialize CSRF token for requests"""
        try:
            response = self.session.get(f"{self.current_url}/sanctum/csrf-cookie")
            if response.status_code == 204:
                for cookie in response.cookies:
                    if cookie.name == 'XSRF-TOKEN':
                        self.csrf_token = cookie.value
                        break
        except Exception as e:
            print(f"CSRF token initialization failed: {e}")
```

### 2. Metrics Sender

**File:** `myrvm-integration/monitoring/metrics_sender.py`

```python
import requests
import json
import time
from datetime import datetime
from typing import Dict, Optional

class MetricsSender:
    def __init__(self, server_url: str, rvm_id: str, api_key: str):
        self.server_url = server_url
        self.rvm_id = rvm_id
        self.api_key = api_key
        
    def send_metrics(self, system_metrics: Dict, application_metrics: Dict = None, network_info: Dict = None) -> bool:
        """Send metrics to server"""
        try:
            payload = {
                'system_metrics': system_metrics,
                'application_metrics': application_metrics or {},
                'network_info': network_info or {}
            }
            
            # Get CSRF token first
            csrf_response = requests.get(f"{self.server_url}/sanctum/csrf-cookie")
            csrf_token = None
            if csrf_response.status_code == 204:
                for cookie in csrf_response.cookies:
                    if cookie.name == 'XSRF-TOKEN':
                        csrf_token = cookie.value
                        break
            
            # Prepare headers
            headers = {
                'Content-Type': 'application/json',
                'Authorization': f'Bearer {self.api_key}',
                'X-RVM-ID': str(self.rvm_id),
                'X-Requested-With': 'XMLHttpRequest'
            }
            
            # Add CSRF token if available
            if csrf_token:
                headers['X-XSRF-TOKEN'] = csrf_token
            
            # Send to server
            response = requests.post(
                f"{self.server_url}/admin/rvm/{self.rvm_id}/store-metrics",
                json=payload,
                headers=headers,
                cookies=csrf_response.cookies,
                timeout=30
            )
            
            if response.status_code == 200:
                print(f"✅ Metrics sent successfully: {response.json()}")
                return True
            else:
                print(f"❌ Metrics send failed: {response.status_code} - {response.text}")
                return False
                
        except Exception as e:
            print(f"❌ Error sending metrics: {e}")
            return False
```

## 🚀 Deployment Guide

### 1. Server Deployment

```bash
# 1. Clone repository
git clone https://github.com/vnot01/MySuperApps.git
cd MySuperApps/MyRVM-Platform

# 2. Install dependencies
composer install

# 3. Configure environment
cp .env.example .env
# Edit .env with your database and server settings

# 4. Run migrations
php artisan migrate

# 5. Start services
docker compose up -d

# 6. Clear caches
php artisan config:clear
php artisan route:clear
php artisan cache:clear
```

### 2. RVM-Jetson Deployment

```bash
# 1. Clone repository
git clone https://github.com/vnot01/test-cv-yolo11-sam2-camera.git
cd test-cv-yolo11-sam2-camera

# 2. Install dependencies
pip install -r requirements.txt

# 3. Configure RVM settings
# Edit myrvm-integration/config/production_config.json

# 4. Start services
python myrvm-integration/main_application.py
```

### 3. Testing Deployment

```bash
# 1. Test server health
curl -X GET "http://localhost:8001/api/health-check"

# 2. Generate API token
curl -X POST "http://localhost:8001/api/rvm/generate-token" \
  -H "Content-Type: application/json" \
  -d '{"rvm_id": "4", "ip_address": "172.28.93.97"}'

# 3. Test metrics storage
curl -X POST "http://localhost:8001/api/rvm/4/store-metrics" \
  -H "Authorization: Bearer {token}" \
  -H "X-RVM-ID: 4" \
  -H "Content-Type: application/json" \
  -d '{"system_metrics": {"cpu_usage": 45.2, "memory_usage": 67.8}}'
```

## 🔧 Configuration

### 1. Server Configuration

**File:** `.env`

```env
APP_NAME=MyRVM-Platform
APP_ENV=production
APP_DEBUG=false
APP_URL=http://localhost:8001

DB_CONNECTION=pgsql
DB_HOST=postgres
DB_PORT=5432
DB_DATABASE=myrvm_platform
DB_USERNAME=myrvm_user
DB_PASSWORD=secure_password

SANCTUM_STATEFUL_DOMAINS=localhost:8001,172.28.233.83,10.3.52.161
```

### 2. RVM Configuration

**File:** `myrvm-integration/config/production_config.json`

```json
{
    "rvm": {
        "id": "4",
        "name": "RVM-Orin1",
        "ip_address": "172.28.93.97"
    },
    "server": {
        "url": "http://localhost:8001",
        "api_key": "your_api_key_here"
    },
    "remote_access": {
        "server_url": "http://localhost:8001",
        "api_key": "your_api_key_here",
        "rvm_id": "4",
        "metrics_interval": 60,
        "command_timeout": 30
    }
}
```

## 📊 Monitoring & Maintenance

### 1. Health Monitoring

```bash
# Check server status
curl -X GET "http://localhost:8001/api/health-check"

# Check database connection
php artisan tinker
>>> DB::connection()->getPdo();

# Check RVM connectivity
curl -X GET "http://localhost:8001/api/rvm/4/metrics" \
  -H "Authorization: Bearer {token}"
```

### 2. Log Monitoring

```bash
# Check Laravel logs
tail -f storage/logs/laravel.log

# Check Docker logs
docker compose logs -f app

# Check system logs
journalctl -u docker -f
```

### 3. Performance Monitoring

```bash
# Check system resources
htop

# Check database performance
php artisan tinker
>>> DB::select('SELECT * FROM pg_stat_activity');

# Check API response times
curl -w "@curl-format.txt" -o /dev/null -s "http://localhost:8001/api/health-check"
```

## 🔒 Security Considerations

### 1. API Token Security
- Tokens expire after 30 days
- Tokens are hashed in database
- IP address validation
- Rate limiting (to be implemented)

### 2. Network Security
- HTTPS in production
- Firewall configuration
- VPN for remote access
- Regular security updates

### 3. Data Protection
- Encrypted database connections
- Secure API endpoints
- Audit logging
- Backup procedures

---
**Technical Implementation Guide Generated:** 2025-09-21  
**Version:** 1.0  
**Target Audience:** Developers & System Administrators  
**Next Review:** After production deployment
