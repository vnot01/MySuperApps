# 🔐 API Security & Authentication - MyRVM-Ecosystem v2.0

## 📍 Security Overview

### Security Architecture
- **Authentication**: API Key-based authentication
- **Authorization**: Role-based access control (RBAC)
- **Encryption**: HTTPS/TLS 1.3 for all communications
- **Rate Limiting**: Per-IP and per-API-key limits
- **Input Validation**: Comprehensive input sanitization
- **CORS**: Configured for cross-origin requests
- **CSRF**: CSRF token protection for web routes

---

## 🔑 Authentication System

### API Key Authentication
```php
<?php
// app/Http/Middleware/ApiKeyAuth.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\ReverseVendingMachine;

class ApiKeyAuth
{
    public function handle(Request $request, Closure $next)
    {
        $apiKey = $request->header('Authorization');
        
        if (!$apiKey) {
            return response()->json([
                'message' => 'API key required',
                'error' => 'UNAUTHORIZED'
            ], 401);
        }
        
        // Extract API key from Bearer token
        if (strpos($apiKey, 'Bearer ') === 0) {
            $apiKey = substr($apiKey, 7);
        }
        
        // Validate API key
        $user = User::where('api_key', $apiKey)->first();
        $rvm = ReverseVendingMachine::where('api_key', $apiKey)->first();
        
        if (!$user && !$rvm) {
            return response()->json([
                'message' => 'Invalid API key',
                'error' => 'UNAUTHORIZED'
            ], 401);
        }
        
        // Check API key expiration
        if ($user && $user->api_key_expires_at && $user->api_key_expires_at->isPast()) {
            return response()->json([
                'message' => 'API key expired',
                'error' => 'API_KEY_EXPIRED'
            ], 401);
        }
        
        if ($rvm && $rvm->api_key_expires_at && $rvm->api_key_expires_at->isPast()) {
            return response()->json([
                'message' => 'API key expired',
                'error' => 'API_KEY_EXPIRED'
            ], 401);
        }
        
        // Set authenticated entity
        if ($user) {
            $request->merge(['authenticated_user' => $user]);
        } else {
            $request->merge(['authenticated_rvm' => $rvm]);
        }
        
        return $next($request);
    }
}
```

### API Key Generation
```php
<?php
// app/Services/ApiKeyService.php

namespace App\Services;

use Illuminate\Support\Str;
use App\Models\User;
use App\Models\ReverseVendingMachine;

class ApiKeyService
{
    public function generateApiKey(): string
    {
        return hash('sha256', Str::random(64) . time());
    }
    
    public function createUserApiKey(User $user, int $expirationDays = 30): string
    {
        $apiKey = $this->generateApiKey();
        
        $user->update([
            'api_key' => $apiKey,
            'api_key_expires_at' => now()->addDays($expirationDays)
        ]);
        
        return $apiKey;
    }
    
    public function createRvmApiKey(ReverseVendingMachine $rvm, int $expirationDays = 30): string
    {
        $apiKey = $this->generateApiKey();
        
        $rvm->update([
            'api_key' => $apiKey,
            'api_key_expires_at' => now()->addDays($expirationDays)
        ]);
        
        return $apiKey;
    }
    
    public function rotateApiKey(string $currentApiKey): ?string
    {
        $user = User::where('api_key', $currentApiKey)->first();
        $rvm = ReverseVendingMachine::where('api_key', $currentApiKey)->first();
        
        if ($user) {
            return $this->createUserApiKey($user);
        }
        
        if ($rvm) {
            return $this->createRvmApiKey($rvm);
        }
        
        return null;
    }
    
    public function revokeApiKey(string $apiKey): bool
    {
        $user = User::where('api_key', $apiKey)->first();
        $rvm = ReverseVendingMachine::where('api_key', $apiKey)->first();
        
        if ($user) {
            $user->update([
                'api_key' => null,
                'api_key_expires_at' => null
            ]);
            return true;
        }
        
        if ($rvm) {
            $rvm->update([
                'api_key' => null,
                'api_key_expires_at' => null
            ]);
            return true;
        }
        
        return false;
    }
}
```

---

## 🛡️ Authorization System

### Role-Based Access Control
```php
<?php
// app/Http/Middleware/RoleMiddleware.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $user = $request->get('authenticated_user');
        
        if (!$user) {
            return response()->json([
                'message' => 'Authentication required',
                'error' => 'UNAUTHORIZED'
            ], 401);
        }
        
        if (!in_array($user->role, $roles)) {
            return response()->json([
                'message' => 'Insufficient permissions',
                'error' => 'FORBIDDEN'
            ], 403);
        }
        
        return $next($request);
    }
}
```

### Permission System
```php
<?php
// app/Models/Permission.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    protected $fillable = [
        'name',
        'description',
        'resource',
        'action'
    ];
    
    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }
}

// app/Models/Role.php
class Role extends Model
{
    protected $fillable = [
        'name',
        'description'
    ];
    
    public function users()
    {
        return $this->hasMany(User::class);
    }
    
    public function permissions()
    {
        return $this->belongsToMany(Permission::class);
    }
}
```

---

## 🔒 Input Validation & Sanitization

### Request Validation
```php
<?php
// app/Http/Requests/StoreRvmRequest.php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRvmRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }
    
    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'location' => 'required|string|max:500',
            'ip_address' => 'required|ip',
            'capacity' => 'required|integer|min:1|max:1000',
            'current_load' => 'integer|min:0|max:1000',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180'
        ];
    }
    
    public function messages()
    {
        return [
            'name.required' => 'RVM name is required',
            'name.max' => 'RVM name cannot exceed 255 characters',
            'location.required' => 'Location is required',
            'ip_address.required' => 'IP address is required',
            'ip_address.ip' => 'Invalid IP address format',
            'capacity.required' => 'Capacity is required',
            'capacity.min' => 'Capacity must be at least 1',
            'capacity.max' => 'Capacity cannot exceed 1000',
            'latitude.between' => 'Latitude must be between -90 and 90',
            'longitude.between' => 'Longitude must be between -180 and 180'
        ];
    }
}
```

### Input Sanitization
```php
<?php
// app/Http/Middleware/SanitizeInput.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SanitizeInput
{
    public function handle(Request $request, Closure $next)
    {
        $input = $request->all();
        
        // Sanitize string inputs
        foreach ($input as $key => $value) {
            if (is_string($value)) {
                $input[$key] = $this->sanitizeString($value);
            }
        }
        
        $request->merge($input);
        
        return $next($request);
    }
    
    private function sanitizeString(string $input): string
    {
        // Remove null bytes
        $input = str_replace("\0", '', $input);
        
        // Remove control characters except newlines and tabs
        $input = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $input);
        
        // Trim whitespace
        $input = trim($input);
        
        // Limit length
        $input = substr($input, 0, 10000);
        
        return $input;
    }
}
```

---

## 🚦 Rate Limiting

### Rate Limiting Configuration
```php
<?php
// app/Http/Kernel.php

protected $middlewareGroups = [
    'api' => [
        'throttle:api',
        \Illuminate\Routing\Middleware\SubstituteBindings::class,
    ],
];

protected $routeMiddleware = [
    'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
    'throttle:api' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
    'throttle:api-key' => \App\Http\Middleware\ThrottleApiKey::class,
];
```

### Custom Rate Limiting
```php
<?php
// app/Http/Middleware/ThrottleApiKey.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Cache\RateLimiter;
use Illuminate\Http\Exceptions\ThrottleRequestsException;

class ThrottleApiKey
{
    protected $limiter;
    
    public function __construct(RateLimiter $limiter)
    {
        $this->limiter = $limiter;
    }
    
    public function handle(Request $request, Closure $next, $maxAttempts = 60, $decayMinutes = 1)
    {
        $apiKey = $request->header('Authorization');
        
        if ($apiKey) {
            $key = 'api-key:' . $apiKey;
        } else {
            $key = 'ip:' . $request->ip();
        }
        
        if ($this->limiter->tooManyAttempts($key, $maxAttempts)) {
            throw new ThrottleRequestsException;
        }
        
        $this->limiter->hit($key, $decayMinutes * 60);
        
        $response = $next($request);
        
        return $this->addHeaders(
            $response,
            $maxAttempts,
            $this->calculateRemainingAttempts($key, $maxAttempts)
        );
    }
    
    protected function addHeaders($response, $maxAttempts, $remainingAttempts)
    {
        $response->headers->add([
            'X-RateLimit-Limit' => $maxAttempts,
            'X-RateLimit-Remaining' => $remainingAttempts,
        ]);
        
        return $response;
    }
    
    protected function calculateRemainingAttempts($key, $maxAttempts)
    {
        return max(0, $maxAttempts - $this->limiter->attempts($key));
    }
}
```

---

## 🔐 Encryption & Hashing

### Data Encryption
```php
<?php
// app/Services/EncryptionService.php

namespace App\Services;

use Illuminate\Support\Facades\Crypt;

class EncryptionService
{
    public function encryptSensitiveData($data): string
    {
        return Crypt::encryptString($data);
    }
    
    public function decryptSensitiveData($encryptedData): string
    {
        return Crypt::decryptString($encryptedData);
    }
    
    public function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_ARGON2ID);
    }
    
    public function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }
    
    public function generateSecureToken(int $length = 32): string
    {
        return bin2hex(random_bytes($length));
    }
}
```

### API Key Hashing
```php
<?php
// app/Services/ApiKeyService.php

namespace App\Services;

use Illuminate\Support\Facades\Hash;

class ApiKeyService
{
    public function hashApiKey(string $apiKey): string
    {
        return Hash::make($apiKey);
    }
    
    public function verifyApiKey(string $apiKey, string $hash): bool
    {
        return Hash::check($apiKey, $hash);
    }
}
```

---

## 🌐 CORS Configuration

### CORS Middleware
```php
<?php
// app/Http/Middleware/Cors.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class Cors
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);
        
        $response->headers->set('Access-Control-Allow-Origin', '*');
        $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
        $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With');
        $response->headers->set('Access-Control-Max-Age', '86400');
        
        if ($request->isMethod('OPTIONS')) {
            $response->setStatusCode(200);
        }
        
        return $response;
    }
}
```

---

## 🛡️ Security Headers

### Security Headers Middleware
```php
<?php
// app/Http/Middleware/SecurityHeaders.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);
        
        // Security headers
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Content-Security-Policy', "default-src 'self'");
        $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        
        return $response;
    }
}
```

---

## 🔍 Security Monitoring

### Security Event Logging
```php
<?php
// app/Http/Middleware/SecurityLogger.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SecurityLogger
{
    public function handle(Request $request, Closure $next)
    {
        $startTime = microtime(true);
        
        $response = $next($request);
        
        $endTime = microtime(true);
        $duration = $endTime - $startTime;
        
        // Log security events
        $this->logSecurityEvent($request, $response, $duration);
        
        return $response;
    }
    
    private function logSecurityEvent(Request $request, $response, float $duration)
    {
        $logData = [
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'status_code' => $response->getStatusCode(),
            'duration' => $duration,
            'timestamp' => now()->toISOString()
        ];
        
        // Log failed authentication attempts
        if ($response->getStatusCode() === 401) {
            Log::warning('Failed authentication attempt', $logData);
        }
        
        // Log rate limit exceeded
        if ($response->getStatusCode() === 429) {
            Log::warning('Rate limit exceeded', $logData);
        }
        
        // Log suspicious activity
        if ($this->isSuspiciousActivity($request)) {
            Log::warning('Suspicious activity detected', $logData);
        }
    }
    
    private function isSuspiciousActivity(Request $request): bool
    {
        // Check for SQL injection attempts
        $sqlPatterns = [
            '/union\s+select/i',
            '/drop\s+table/i',
            '/insert\s+into/i',
            '/delete\s+from/i',
            '/update\s+set/i'
        ];
        
        foreach ($sqlPatterns as $pattern) {
            if (preg_match($pattern, $request->getContent())) {
                return true;
            }
        }
        
        // Check for XSS attempts
        $xssPatterns = [
            '/<script/i',
            '/javascript:/i',
            '/on\w+\s*=/i'
        ];
        
        foreach ($xssPatterns as $pattern) {
            if (preg_match($pattern, $request->getContent())) {
                return true;
            }
        }
        
        return false;
    }
}
```

---

## 🔐 API Key Management

### API Key Rotation
```php
<?php
// app/Console/Commands/RotateApiKeys.php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ApiKeyService;
use App\Models\User;
use App\Models\ReverseVendingMachine;

class RotateApiKeys extends Command
{
    protected $signature = 'api:rotate-keys {--force : Force rotation even if not expired}';
    protected $description = 'Rotate expired API keys';
    
    public function handle(ApiKeyService $apiKeyService)
    {
        $this->info('Starting API key rotation...');
        
        $force = $this->option('force');
        $rotatedCount = 0;
        
        // Rotate user API keys
        $users = User::whereNotNull('api_key')
            ->where(function($query) use ($force) {
                if (!$force) {
                    $query->where('api_key_expires_at', '<', now());
                }
            })
            ->get();
        
        foreach ($users as $user) {
            $apiKeyService->createUserApiKey($user);
            $rotatedCount++;
            $this->line("Rotated API key for user: {$user->name}");
        }
        
        // Rotate RVM API keys
        $rvms = ReverseVendingMachine::whereNotNull('api_key')
            ->where(function($query) use ($force) {
                if (!$force) {
                    $query->where('api_key_expires_at', '<', now());
                }
            })
            ->get();
        
        foreach ($rvms as $rvm) {
            $apiKeyService->createRvmApiKey($rvm);
            $rotatedCount++;
            $this->line("Rotated API key for RVM: {$rvm->name}");
        }
        
        $this->info("API key rotation completed. Rotated {$rotatedCount} keys.");
    }
}
```

### API Key Validation
```php
<?php
// app/Console/Commands/ValidateApiKeys.php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\ReverseVendingMachine;

class ValidateApiKeys extends Command
{
    protected $signature = 'api:validate-keys';
    protected $description = 'Validate all API keys';
    
    public function handle()
    {
        $this->info('Validating API keys...');
        
        $issues = [];
        
        // Check user API keys
        $users = User::whereNotNull('api_key')->get();
        foreach ($users as $user) {
            if (!$user->api_key_expires_at) {
                $issues[] = "User {$user->name} has API key without expiration date";
            } elseif ($user->api_key_expires_at->isPast()) {
                $issues[] = "User {$user->name} has expired API key";
            }
        }
        
        // Check RVM API keys
        $rvms = ReverseVendingMachine::whereNotNull('api_key')->get();
        foreach ($rvms as $rvm) {
            if (!$rvm->api_key_expires_at) {
                $issues[] = "RVM {$rvm->name} has API key without expiration date";
            } elseif ($rvm->api_key_expires_at->isPast()) {
                $issues[] = "RVM {$rvm->name} has expired API key";
            }
        }
        
        if (empty($issues)) {
            $this->info('All API keys are valid!');
        } else {
            $this->error('Found ' . count($issues) . ' issues:');
            foreach ($issues as $issue) {
                $this->line("  - {$issue}");
            }
        }
    }
}
```

---

## 🚨 Security Alerts

### Security Alert System
```php
<?php
// app/Services/SecurityAlertService.php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\SecurityAlert;

class SecurityAlertService
{
    public function sendSecurityAlert(string $type, array $data)
    {
        $alert = [
            'type' => $type,
            'data' => $data,
            'timestamp' => now()->toISOString(),
            'severity' => $this->getSeverity($type)
        ];
        
        // Log the alert
        Log::warning('Security alert triggered', $alert);
        
        // Send email notification
        if ($alert['severity'] === 'high') {
            Mail::to('admin@myrvm.com')->send(new SecurityAlert($alert));
        }
        
        // Store in database for dashboard
        $this->storeAlert($alert);
    }
    
    private function getSeverity(string $type): string
    {
        $highSeverity = [
            'multiple_failed_logins',
            'suspicious_activity',
            'api_key_compromise',
            'sql_injection_attempt',
            'xss_attempt'
        ];
        
        return in_array($type, $highSeverity) ? 'high' : 'medium';
    }
    
    private function storeAlert(array $alert)
    {
        // Store in database for security dashboard
        \App\Models\SecurityAlert::create($alert);
    }
}
```

---

## 📊 Security Dashboard

### Security Metrics
```php
<?php
// app/Http/Controllers/Api/SecurityController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SecurityAlert;
use App\Models\User;
use App\Models\ReverseVendingMachine;

class SecurityController extends Controller
{
    public function dashboard()
    {
        $metrics = [
            'total_alerts' => SecurityAlert::count(),
            'high_severity_alerts' => SecurityAlert::where('severity', 'high')->count(),
            'failed_logins_24h' => SecurityAlert::where('type', 'failed_login')
                ->where('created_at', '>=', now()->subDay())
                ->count(),
            'active_api_keys' => User::whereNotNull('api_key')->count() + 
                               ReverseVendingMachine::whereNotNull('api_key')->count(),
            'expired_api_keys' => User::where('api_key_expires_at', '<', now())->count() +
                                 ReverseVendingMachine::where('api_key_expires_at', '<', now())->count(),
            'suspicious_activity_24h' => SecurityAlert::where('type', 'suspicious_activity')
                ->where('created_at', '>=', now()->subDay())
                ->count()
        ];
        
        return response()->json([
            'data' => $metrics
        ]);
    }
    
    public function alerts(Request $request)
    {
        $alerts = SecurityAlert::orderBy('created_at', 'desc')
            ->limit($request->get('limit', 50))
            ->get();
        
        return response()->json([
            'data' => $alerts
        ]);
    }
}
```

---

## 🔧 Security Configuration

### Environment Security
```bash
# .env security settings
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:your-32-character-secret-key

# Database security
DB_CONNECTION=pgsql
DB_HOST=postgres
DB_PORT=5432
DB_DATABASE=myrvm_ecosystem
DB_USERNAME=myrvm_user
DB_PASSWORD=your-secure-password

# Redis security
REDIS_PASSWORD=your-redis-password

# Session security
SESSION_DRIVER=redis
SESSION_LIFETIME=120
SESSION_ENCRYPT=true
SESSION_PATH=/
SESSION_DOMAIN=null
SESSION_SECURE_COOKIE=true
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=strict

# Cache security
CACHE_DRIVER=redis
CACHE_PREFIX=myrvm

# Queue security
QUEUE_CONNECTION=redis
QUEUE_PREFIX=myrvm

# Mail security
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@myrvm.com
MAIL_FROM_NAME="MyRVM System"

# API security
API_RATE_LIMIT=60
API_RATE_LIMIT_WINDOW=60
API_KEY_LENGTH=64
API_KEY_EXPIRATION_DAYS=30
```

---

**Last Updated**: 2025-01-02  
**Version**: 2.0.0  
**Status**: ✅ COMPLETE SECURITY & AUTHENTICATION DOCUMENTATION
