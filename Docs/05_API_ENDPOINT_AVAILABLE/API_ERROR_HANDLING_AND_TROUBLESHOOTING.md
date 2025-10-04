# 🚨 API Error Handling & Troubleshooting - MyRVM-Ecosystem v2.0

## 📍 Error Handling Overview

### Error Categories
- **Client Errors (4xx)**: Invalid requests, authentication issues, validation errors
- **Server Errors (5xx)**: Internal server errors, database issues, service unavailability
- **Network Errors**: Connection timeouts, DNS resolution failures
- **Business Logic Errors**: Custom application-specific errors

---

## 🔧 Server Error Handling

### Global Exception Handler
```php
<?php
// app/Exceptions/Handler.php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    protected $dontReport = [
        AuthenticationException::class,
        ValidationException::class,
    ];

    public function render($request, Throwable $exception)
    {
        if ($request->expectsJson() || $request->is('api/*')) {
            return $this->handleApiException($request, $exception);
        }

        return parent::render($request, $exception);
    }

    private function handleApiException(Request $request, Throwable $exception): JsonResponse
    {
        $statusCode = 500;
        $errorCode = 'INTERNAL_SERVER_ERROR';
        $message = 'An unexpected error occurred';
        $details = null;

        if ($exception instanceof ValidationException) {
            $statusCode = 422;
            $errorCode = 'VALIDATION_ERROR';
            $message = 'Validation failed';
            $details = $exception->errors();
        } elseif ($exception instanceof AuthenticationException) {
            $statusCode = 401;
            $errorCode = 'UNAUTHORIZED';
            $message = 'Authentication required';
        } elseif ($exception instanceof ModelNotFoundException) {
            $statusCode = 404;
            $errorCode = 'NOT_FOUND';
            $message = 'Resource not found';
        } elseif ($exception instanceof NotFoundHttpException) {
            $statusCode = 404;
            $errorCode = 'ENDPOINT_NOT_FOUND';
            $message = 'API endpoint not found';
        } elseif ($exception instanceof MethodNotAllowedHttpException) {
            $statusCode = 405;
            $errorCode = 'METHOD_NOT_ALLOWED';
            $message = 'HTTP method not allowed for this endpoint';
        } elseif ($exception instanceof TooManyRequestsHttpException) {
            $statusCode = 429;
            $errorCode = 'RATE_LIMIT_EXCEEDED';
            $message = 'Too many requests. Please try again later.';
        } elseif ($exception instanceof \Illuminate\Database\QueryException) {
            $statusCode = 500;
            $errorCode = 'DATABASE_ERROR';
            $message = 'Database operation failed';
            
            // Log detailed error for debugging
            \Log::error('Database error', [
                'exception' => $exception->getMessage(),
                'sql' => $exception->getSql(),
                'bindings' => $exception->getBindings()
            ]);
        } elseif ($exception instanceof \Illuminate\Cache\Exception\CacheException) {
            $statusCode = 500;
            $errorCode = 'CACHE_ERROR';
            $message = 'Cache operation failed';
        } elseif ($exception instanceof \Illuminate\Queue\MaxAttemptsExceededException) {
            $statusCode = 500;
            $errorCode = 'QUEUE_ERROR';
            $message = 'Background job failed after maximum attempts';
        }

        // Log error for monitoring
        $this->logError($exception, $statusCode, $errorCode);

        return response()->json([
            'error' => $errorCode,
            'message' => $message,
            'details' => $details,
            'timestamp' => now()->toISOString(),
            'request_id' => $request->header('X-Request-ID', uniqid())
        ], $statusCode);
    }

    private function logError(Throwable $exception, int $statusCode, string $errorCode): void
    {
        $logData = [
            'error_code' => $errorCode,
            'status_code' => $statusCode,
            'exception' => get_class($exception),
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString(),
            'timestamp' => now()->toISOString()
        ];

        if ($statusCode >= 500) {
            \Log::error('Server error occurred', $logData);
        } else {
            \Log::warning('Client error occurred', $logData);
        }
    }
}
```

### Custom API Exceptions
```php
<?php
// app/Exceptions/ApiException.php

namespace App\Exceptions;

use Exception;

class ApiException extends Exception
{
    protected $errorCode;
    protected $statusCode;
    protected $details;

    public function __construct(
        string $message,
        string $errorCode = 'API_ERROR',
        int $statusCode = 400,
        array $details = null
    ) {
        parent::__construct($message);
        $this->errorCode = $errorCode;
        $this->statusCode = $statusCode;
        $this->details = $details;
    }

    public function getErrorCode(): string
    {
        return $this->errorCode;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getDetails(): ?array
    {
        return $this->details;
    }

    public function render()
    {
        return response()->json([
            'error' => $this->errorCode,
            'message' => $this->getMessage(),
            'details' => $this->details,
            'timestamp' => now()->toISOString()
        ], $this->statusCode);
    }
}

// app/Exceptions/RvmNotFoundException.php
class RvmNotFoundException extends ApiException
{
    public function __construct(int $rvmId)
    {
        parent::__construct(
            "RVM with ID {$rvmId} not found",
            'RVM_NOT_FOUND',
            404,
            ['rvm_id' => $rvmId]
        );
    }
}

// app/Exceptions/InvalidApiKeyException.php
class InvalidApiKeyException extends ApiException
{
    public function __construct()
    {
        parent::__construct(
            'Invalid or expired API key',
            'INVALID_API_KEY',
            401
        );
    }
}

// app/Exceptions/InsufficientPermissionsException.php
class InsufficientPermissionsException extends ApiException
{
    public function __construct(string $requiredPermission)
    {
        parent::__construct(
            'Insufficient permissions',
            'INSUFFICIENT_PERMISSIONS',
            403,
            ['required_permission' => $requiredPermission]
        );
    }
}
```

### Validation Error Handling
```php
<?php
// app/Http/Requests/BaseApiRequest.php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

abstract class BaseApiRequest extends FormRequest
{
    protected function failedValidation(Validator $validator)
    {
        $errors = $validator->errors();
        
        $formattedErrors = [];
        foreach ($errors->all() as $field => $messages) {
            $formattedErrors[$field] = is_array($messages) ? $messages : [$messages];
        }

        throw new HttpResponseException(
            response()->json([
                'error' => 'VALIDATION_ERROR',
                'message' => 'Validation failed',
                'details' => $formattedErrors,
                'timestamp' => now()->toISOString()
            ], 422)
        );
    }
}
```

---

## 🤖 Jetson Error Handling

### Python Error Handling
```python
# utils/error_handler.py

import logging
import traceback
from functools import wraps
from flask import jsonify, request
import time

# Configure logging
logging.basicConfig(
    level=logging.INFO,
    format='%(asctime)s - %(name)s - %(levelname)s - %(message)s'
)
logger = logging.getLogger(__name__)

class APIError(Exception):
    """Base API exception class"""
    def __init__(self, message, error_code='API_ERROR', status_code=400, details=None):
        self.message = message
        self.error_code = error_code
        self.status_code = status_code
        self.details = details or {}
        super().__init__(self.message)

class ValidationError(APIError):
    """Validation error"""
    def __init__(self, message, details=None):
        super().__init__(message, 'VALIDATION_ERROR', 422, details)

class AuthenticationError(APIError):
    """Authentication error"""
    def __init__(self, message='Authentication required'):
        super().__init__(message, 'UNAUTHORIZED', 401)

class NotFoundError(APIError):
    """Resource not found error"""
    def __init__(self, message='Resource not found'):
        super().__init__(message, 'NOT_FOUND', 404)

class RateLimitError(APIError):
    """Rate limit exceeded error"""
    def __init__(self, message='Rate limit exceeded'):
        super().__init__(message, 'RATE_LIMIT_EXCEEDED', 429)

class InternalServerError(APIError):
    """Internal server error"""
    def __init__(self, message='Internal server error'):
        super().__init__(message, 'INTERNAL_SERVER_ERROR', 500)

def handle_api_errors(f):
    """Decorator for handling API errors"""
    @wraps(f)
    def decorated_function(*args, **kwargs):
        try:
            return f(*args, **kwargs)
        except APIError as e:
            logger.warning(f"API Error: {e.message}", extra={
                'error_code': e.error_code,
                'status_code': e.status_code,
                'details': e.details
            })
            
            return jsonify({
                'error': e.error_code,
                'message': e.message,
                'details': e.details,
                'timestamp': time.time()
            }), e.status_code
            
        except Exception as e:
            logger.error(f"Unexpected error: {str(e)}", extra={
                'traceback': traceback.format_exc()
            })
            
            return jsonify({
                'error': 'INTERNAL_SERVER_ERROR',
                'message': 'An unexpected error occurred',
                'timestamp': time.time()
            }), 500
    
    return decorated_function

def validate_request_data(required_fields=None, optional_fields=None):
    """Decorator for validating request data"""
    def decorator(f):
        @wraps(f)
        def decorated_function(*args, **kwargs):
            if not request.is_json:
                raise ValidationError('Request must be JSON')
            
            data = request.get_json()
            if not data:
                raise ValidationError('Request body is required')
            
            # Check required fields
            if required_fields:
                missing_fields = [field for field in required_fields if field not in data]
                if missing_fields:
                    raise ValidationError(
                        f'Missing required fields: {", ".join(missing_fields)}',
                        {'missing_fields': missing_fields}
                    )
            
            # Check for unknown fields
            if optional_fields:
                allowed_fields = set(required_fields or []) | set(optional_fields or [])
                unknown_fields = [field for field in data.keys() if field not in allowed_fields]
                if unknown_fields:
                    raise ValidationError(
                        f'Unknown fields: {", ".join(unknown_fields)}',
                        {'unknown_fields': unknown_fields}
                    )
            
            return f(*args, **kwargs)
        return decorated_function
    return decorator

def rate_limit(max_requests=60, window_seconds=60):
    """Decorator for rate limiting"""
    def decorator(f):
        @wraps(f)
        def decorated_function(*args, **kwargs):
            # Simple in-memory rate limiting
            # In production, use Redis or similar
            client_ip = request.remote_addr
            current_time = time.time()
            
            # Check rate limit (simplified implementation)
            if hasattr(rate_limit, 'requests'):
                # Clean old requests
                rate_limit.requests = {
                    ip: times for ip, times in rate_limit.requests.items()
                    if any(t > current_time - window_seconds for t in times)
                }
                
                # Check current IP
                if client_ip in rate_limit.requests:
                    recent_requests = [
                        t for t in rate_limit.requests[client_ip]
                        if t > current_time - window_seconds
                    ]
                    if len(recent_requests) >= max_requests:
                        raise RateLimitError()
                
                # Add current request
                if client_ip not in rate_limit.requests:
                    rate_limit.requests[client_ip] = []
                rate_limit.requests[client_ip].append(current_time)
            else:
                rate_limit.requests = {client_ip: [current_time]}
            
            return f(*args, **kwargs)
        return decorated_function
    return decorator

# Initialize rate limit storage
rate_limit.requests = {}
```

### Error Response Format
```python
# app.py - Error handling in Flask app

from utils.error_handler import (
    handle_api_errors, validate_request_data, rate_limit,
    ValidationError, AuthenticationError, NotFoundError
)

@app.errorhandler(404)
def not_found(error):
    return jsonify({
        'error': 'ENDPOINT_NOT_FOUND',
        'message': 'API endpoint not found',
        'timestamp': time.time()
    }), 404

@app.errorhandler(405)
def method_not_allowed(error):
    return jsonify({
        'error': 'METHOD_NOT_ALLOWED',
        'message': 'HTTP method not allowed for this endpoint',
        'timestamp': time.time()
    }), 405

@app.errorhandler(500)
def internal_server_error(error):
    logger.error(f"Internal server error: {str(error)}")
    return jsonify({
        'error': 'INTERNAL_SERVER_ERROR',
        'message': 'An unexpected error occurred',
        'timestamp': time.time()
    }), 500

@app.route('/api/detection', methods=['POST'])
@handle_api_errors
@validate_request_data(
    required_fields=['rvm_id', 'image_path', 'detection_results'],
    optional_fields=['processing_time']
)
@rate_limit(max_requests=30, window_seconds=60)
def detection():
    """Detection endpoint with error handling"""
    data = request.get_json()
    
    # Validate RVM ID
    rvm_id = data.get('rvm_id')
    if not isinstance(rvm_id, int) or rvm_id <= 0:
        raise ValidationError('Invalid RVM ID', {'rvm_id': 'Must be a positive integer'})
    
    # Validate image path
    image_path = data.get('image_path')
    if not image_path or not isinstance(image_path, str):
        raise ValidationError('Invalid image path', {'image_path': 'Must be a non-empty string'})
    
    # Validate detection results
    detection_results = data.get('detection_results')
    if not isinstance(detection_results, list):
        raise ValidationError('Invalid detection results', {'detection_results': 'Must be an array'})
    
    # Process detection
    try:
        result = process_detection(data)
        return jsonify({
            'id': result['id'],
            'rvm_id': rvm_id,
            'detection_results': result['detection_results'],
            'processing_time': result['processing_time'],
            'timestamp': time.time()
        }), 201
    except Exception as e:
        logger.error(f"Detection processing failed: {str(e)}")
        raise InternalServerError('Detection processing failed')
```

---

## 🔍 Troubleshooting Guide

### Common Issues and Solutions

#### 1. Authentication Issues
```bash
# Problem: 401 Unauthorized
curl -X GET http://100.123.143.87:8001/api/rvms
# Response: {"error":"UNAUTHORIZED","message":"Authentication required"}

# Solution: Include API key in header
curl -X GET http://100.123.143.87:8001/api/rvms \
  -H "Authorization: Bearer 38bbe1d2ecf75df21546b05340f5878a24e74ed0d8b88d75db1ddeff198380c1"
```

#### 2. Validation Errors
```bash
# Problem: 422 Validation Error
curl -X POST http://100.123.143.87:8001/api/rvms \
  -H "Authorization: Bearer 38bbe1d2ecf75df21546b05340f5878a24e74ed0d8b88d75db1ddeff198380c1" \
  -H "Content-Type: application/json" \
  -d '{"name": ""}'
# Response: {"error":"VALIDATION_ERROR","message":"Validation failed","details":{"name":["The name field is required."]}}

# Solution: Provide valid data
curl -X POST http://100.123.143.87:8001/api/rvms \
  -H "Authorization: Bearer 38bbe1d2ecf75df21546b05340f5878a24e74ed0d8b88d75db1ddeff198380c1" \
  -H "Content-Type: application/json" \
  -d '{"name": "Test RVM", "location": "Test Location", "ip_address": "192.168.1.100", "capacity": 100}'
```

#### 3. Rate Limiting
```bash
# Problem: 429 Rate Limit Exceeded
# Response: {"error":"RATE_LIMIT_EXCEEDED","message":"Too many requests. Please try again later."}

# Solution: Wait and retry with exponential backoff
sleep 60  # Wait 1 minute
curl -X GET http://100.123.143.87:8001/api/rvms \
  -H "Authorization: Bearer 38bbe1d2ecf75df21546b05340f5878a24e74ed0d8b88d75db1ddeff198380c1"
```

#### 4. Database Connection Issues
```bash
# Problem: 500 Database Error
# Response: {"error":"DATABASE_ERROR","message":"Database operation failed"}

# Solution: Check database connection
docker-compose exec postgres psql -U myrvm_user -d myrvm_ecosystem -c "SELECT 1;"

# Check database logs
docker-compose logs postgres
```

#### 5. Cache Issues
```bash
# Problem: Stale data in responses

# Solution: Clear cache
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan config:cache
docker-compose exec app php artisan route:cache
```

### Debugging Tools

#### 1. API Health Check
```bash
#!/bin/bash
# api_health_check.sh

echo "🏥 API Health Check"
echo "=================="

# Server health
echo "🖥️ Server health..."
SERVER_RESPONSE=$(curl -s -w "%{http_code}" -o /dev/null http://100.123.143.87:8001/api/health)
if [ "$SERVER_RESPONSE" = "200" ]; then
    echo "✅ Server: Healthy"
else
    echo "❌ Server: Unhealthy (HTTP $SERVER_RESPONSE)"
fi

# Jetson health
echo "🤖 Jetson health..."
JETSON_RESPONSE=$(curl -s -w "%{http_code}" -o /dev/null http://100.117.234.2:5000/api/health)
if [ "$JETSON_RESPONSE" = "200" ]; then
    echo "✅ Jetson: Healthy"
else
    echo "❌ Jetson: Unhealthy (HTTP $JETSON_RESPONSE)"
fi

# Database health
echo "🗄️ Database health..."
DB_RESPONSE=$(curl -s -w "%{http_code}" -o /dev/null http://100.123.143.87:8001/api/health)
if [ "$DB_RESPONSE" = "200" ]; then
    echo "✅ Database: Healthy"
else
    echo "❌ Database: Unhealthy (HTTP $DB_RESPONSE)"
fi
```

#### 2. Error Log Analysis
```bash
#!/bin/bash
# analyze_errors.sh

echo "🔍 Error Log Analysis"
echo "===================="

# Server errors
echo "🖥️ Server errors (last 24h):"
docker-compose exec app tail -n 100 /var/www/html/storage/logs/laravel.log | grep -i error | tail -10

# Jetson errors
echo "🤖 Jetson errors (last 24h):"
ssh my@orin1 "cd /home/my/MySuperApps/MyCV-Platform/direct/app/api-hybrid-detection-jetson && tail -n 100 app.log | grep -i error | tail -10"

# Database errors
echo "🗄️ Database errors:"
docker-compose logs postgres | grep -i error | tail -10

# Nginx errors
echo "🌐 Nginx errors:"
docker-compose logs nginx | grep -i error | tail -10
```

#### 3. Performance Analysis
```bash
#!/bin/bash
# performance_analysis.sh

echo "📊 Performance Analysis"
echo "======================"

# Response times
echo "⏱️ Response times:"
curl -w "@curl-format.txt" -o /dev/null -s http://100.123.143.87:8001/api/rvms
curl -w "@curl-format.txt" -o /dev/null -s http://100.117.234.2:5000/api/health

# Memory usage
echo "💾 Memory usage:"
docker stats --no-stream

# Database performance
echo "🗄️ Database performance:"
docker-compose exec postgres psql -U myrvm_user -d myrvm_ecosystem -c "
SELECT 
    query,
    mean_time,
    calls,
    total_time
FROM pg_stat_statements 
ORDER BY mean_time DESC 
LIMIT 5;
"
```

### Monitoring and Alerting

#### 1. Error Rate Monitoring
```php
<?php
// app/Console/Commands/MonitorErrorRate.php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class MonitorErrorRate extends Command
{
    protected $signature = 'monitor:error-rate';
    protected $description = 'Monitor API error rates and send alerts';

    public function handle()
    {
        $errorRate = $this->calculateErrorRate();
        
        if ($errorRate > 0.05) { // 5% error rate threshold
            $this->sendAlert($errorRate);
        }
        
        $this->info("Current error rate: " . ($errorRate * 100) . "%");
    }
    
    private function calculateErrorRate(): float
    {
        $totalRequests = Cache::get('total_requests_1h', 0);
        $errorRequests = Cache::get('error_requests_1h', 0);
        
        return $totalRequests > 0 ? $errorRequests / $totalRequests : 0;
    }
    
    private function sendAlert(float $errorRate): void
    {
        Log::critical('High error rate detected', [
            'error_rate' => $errorRate,
            'threshold' => 0.05
        ]);
        
        // Send email/SMS alert
        // Implementation depends on your notification system
    }
}
```

#### 2. Automated Recovery
```bash
#!/bin/bash
# auto_recovery.sh

echo "🔄 Automated Recovery"
echo "===================="

# Check if services are running
if ! curl -f http://100.123.143.87:8001/api/health > /dev/null 2>&1; then
    echo "🖥️ Server is down, attempting recovery..."
    docker-compose restart app
    sleep 30
    
    if curl -f http://100.123.143.87:8001/api/health > /dev/null 2>&1; then
        echo "✅ Server recovered"
    else
        echo "❌ Server recovery failed"
    fi
fi

if ! curl -f http://100.117.234.2:5000/api/health > /dev/null 2>&1; then
    echo "🤖 Jetson is down, attempting recovery..."
    ssh my@orin1 "cd /home/my/MySuperApps/MyCV-Platform/direct/app/api-hybrid-detection-jetson && pkill -f python3 && nohup python3 app.py > app.log 2>&1 &"
    sleep 30
    
    if curl -f http://100.117.234.2:5000/api/health > /dev/null 2>&1; then
        echo "✅ Jetson recovered"
    else
        echo "❌ Jetson recovery failed"
    fi
fi
```

---

## 📋 Error Codes Reference

### HTTP Status Codes
- **200**: OK - Request successful
- **201**: Created - Resource created successfully
- **400**: Bad Request - Invalid request format
- **401**: Unauthorized - Authentication required
- **403**: Forbidden - Insufficient permissions
- **404**: Not Found - Resource not found
- **405**: Method Not Allowed - HTTP method not supported
- **422**: Unprocessable Entity - Validation failed
- **429**: Too Many Requests - Rate limit exceeded
- **500**: Internal Server Error - Server error
- **502**: Bad Gateway - Upstream server error
- **503**: Service Unavailable - Service temporarily unavailable

### Custom Error Codes
- **API_ERROR**: General API error
- **VALIDATION_ERROR**: Input validation failed
- **UNAUTHORIZED**: Authentication required
- **INVALID_API_KEY**: API key is invalid or expired
- **INSUFFICIENT_PERMISSIONS**: User lacks required permissions
- **RVM_NOT_FOUND**: RVM resource not found
- **DETECTION_FAILED**: Detection processing failed
- **RATE_LIMIT_EXCEEDED**: Too many requests
- **DATABASE_ERROR**: Database operation failed
- **CACHE_ERROR**: Cache operation failed
- **QUEUE_ERROR**: Background job failed
- **NETWORK_ERROR**: Network communication failed
- **TIMEOUT_ERROR**: Request timeout
- **SERVICE_UNAVAILABLE**: Service temporarily unavailable

---

## 🔧 Debugging Commands

### Server Debugging
```bash
# Check application status
docker-compose exec app php artisan about

# Check configuration
docker-compose exec app php artisan config:show

# Check routes
docker-compose exec app php artisan route:list

# Check database connection
docker-compose exec app php artisan tinker --execute="DB::connection()->getPdo();"

# Check cache status
docker-compose exec app php artisan cache:table

# Check queue status
docker-compose exec app php artisan queue:work --once

# Check logs
docker-compose exec app tail -f storage/logs/laravel.log
```

### Jetson Debugging
```bash
# Check Python environment
ssh my@orin1 "cd /home/my/MySuperApps/MyCV-Platform/direct/app/api-hybrid-detection-jetson && python3 -c 'import sys; print(sys.version)'"

# Check dependencies
ssh my@orin1 "cd /home/my/MySuperApps/MyCV-Platform/direct/app/api-hybrid-detection-jetson && pip list"

# Check API status
ssh my@orin1 "cd /home/my/MySuperApps/MyCV-Platform/direct/app/api-hybrid-detection-jetson && python3 -c 'import requests; print(requests.get(\"http://localhost:5000/api/health\").json())'"

# Check logs
ssh my@orin1 "cd /home/my/MySuperApps/MyCV-Platform/direct/app/api-hybrid-detection-jetson && tail -f app.log"

# Check system resources
ssh my@orin1 "htop"
ssh my@orin1 "nvidia-smi"
```

---

**Last Updated**: 2025-01-02  
**Version**: 2.0.0  
**Status**: ✅ COMPLETE ERROR HANDLING & TROUBLESHOOTING DOCUMENTATION
