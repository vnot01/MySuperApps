# 🏆 API Best Practices & Guidelines - MyRVM-Ecosystem v2.0

## 📍 Best Practices Overview

### Core Principles
- **RESTful Design**: Follow REST conventions and HTTP standards
- **Consistent Naming**: Use clear, descriptive, and consistent naming
- **Error Handling**: Provide meaningful error messages and status codes
- **Security First**: Implement proper authentication and authorization
- **Performance**: Optimize for speed and efficiency
- **Documentation**: Maintain comprehensive and up-to-date documentation

---

## 🎯 API Design Best Practices

### 1. RESTful Resource Design
```php
<?php
// ✅ Good: RESTful resource endpoints
Route::apiResource('rvms', RvmController::class);
Route::apiResource('detection-results', DetectionResultController::class);
Route::apiResource('users', UserController::class);

// ✅ Good: Nested resources
Route::apiResource('rvms.detection-results', RvmDetectionResultController::class);
Route::apiResource('users.balances', UserBalanceController::class);

// ❌ Bad: Non-RESTful endpoints
Route::get('/getAllRvms', [RvmController::class, 'getAll']);
Route::post('/createNewRvm', [RvmController::class, 'createNew']);
Route::delete('/removeRvm/{id}', [RvmController::class, 'remove']);
```

### 2. HTTP Methods and Status Codes
```php
<?php
// ✅ Good: Proper HTTP methods and status codes
class RvmController extends Controller
{
    public function index()
    {
        $rvms = Rvm::paginate(15);
        return response()->json([
            'data' => $rvms->items(),
            'pagination' => [
                'current_page' => $rvms->currentPage(),
                'last_page' => $rvms->lastPage(),
                'per_page' => $rvms->perPage(),
                'total' => $rvms->total()
            ]
        ], 200); // 200 OK
    }
    
    public function store(Request $request)
    {
        $rvm = Rvm::create($request->validated());
        return response()->json([
            'data' => $rvm
        ], 201); // 201 Created
    }
    
    public function show($id)
    {
        $rvm = Rvm::findOrFail($id);
        return response()->json([
            'data' => $rvm
        ], 200); // 200 OK
    }
    
    public function update(Request $request, $id)
    {
        $rvm = Rvm::findOrFail($id);
        $rvm->update($request->validated());
        return response()->json([
            'data' => $rvm
        ], 200); // 200 OK
    }
    
    public function destroy($id)
    {
        $rvm = Rvm::findOrFail($id);
        $rvm->delete();
        return response()->json(null, 204); // 204 No Content
    }
}
```

### 3. Consistent Response Format
```php
<?php
// ✅ Good: Consistent response format
class ApiResponse
{
    public static function success($data = null, string $message = null, int $statusCode = 200)
    {
        $response = [
            'success' => true,
            'data' => $data,
            'timestamp' => now()->toISOString()
        ];
        
        if ($message) {
            $response['message'] = $message;
        }
        
        return response()->json($response, $statusCode);
    }
    
    public static function error(string $message, string $errorCode = null, int $statusCode = 400, array $details = null)
    {
        $response = [
            'success' => false,
            'error' => $errorCode ?? 'API_ERROR',
            'message' => $message,
            'timestamp' => now()->toISOString()
        ];
        
        if ($details) {
            $response['details'] = $details;
        }
        
        return response()->json($response, $statusCode);
    }
    
    public static function validationError(array $errors, string $message = 'Validation failed')
    {
        return self::error($message, 'VALIDATION_ERROR', 422, $errors);
    }
}

// Usage in controllers
class RvmController extends Controller
{
    public function index()
    {
        $rvms = Rvm::paginate(15);
        return ApiResponse::success($rvms);
    }
    
    public function store(Request $request)
    {
        $rvm = Rvm::create($request->validated());
        return ApiResponse::success($rvm, 'RVM created successfully', 201);
    }
}
```

---

## 🔐 Security Best Practices

### 1. Authentication and Authorization
```php
<?php
// ✅ Good: Proper authentication middleware
class ApiKeyAuth
{
    public function handle(Request $request, Closure $next)
    {
        $apiKey = $request->header('Authorization');
        
        if (!$apiKey) {
            return ApiResponse::error('API key required', 'UNAUTHORIZED', 401);
        }
        
        // Extract API key from Bearer token
        if (strpos($apiKey, 'Bearer ') === 0) {
            $apiKey = substr($apiKey, 7);
        }
        
        // Validate API key
        $user = User::where('api_key', $apiKey)->first();
        if (!$user) {
            return ApiResponse::error('Invalid API key', 'INVALID_API_KEY', 401);
        }
        
        // Check API key expiration
        if ($user->api_key_expires_at && $user->api_key_expires_at->isPast()) {
            return ApiResponse::error('API key expired', 'API_KEY_EXPIRED', 401);
        }
        
        $request->merge(['authenticated_user' => $user]);
        return $next($request);
    }
}

// ✅ Good: Role-based authorization
class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $user = $request->get('authenticated_user');
        
        if (!$user) {
            return ApiResponse::error('Authentication required', 'UNAUTHORIZED', 401);
        }
        
        if (!in_array($user->role, $roles)) {
            return ApiResponse::error('Insufficient permissions', 'FORBIDDEN', 403);
        }
        
        return $next($request);
    }
}
```

### 2. Input Validation and Sanitization
```php
<?php
// ✅ Good: Comprehensive input validation
class StoreRvmRequest extends FormRequest
{
    public function rules()
    {
        return [
            'name' => 'required|string|max:255|min:3',
            'location' => 'required|string|max:500|min:5',
            'ip_address' => 'required|ip|unique:reverse_vending_machines,ip_address',
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
            'name.min' => 'RVM name must be at least 3 characters',
            'name.max' => 'RVM name cannot exceed 255 characters',
            'ip_address.required' => 'IP address is required',
            'ip_address.ip' => 'Invalid IP address format',
            'ip_address.unique' => 'IP address is already in use',
            'capacity.required' => 'Capacity is required',
            'capacity.min' => 'Capacity must be at least 1',
            'capacity.max' => 'Capacity cannot exceed 1000',
            'latitude.between' => 'Latitude must be between -90 and 90',
            'longitude.between' => 'Longitude must be between -180 and 180'
        ];
    }
}

// ✅ Good: Input sanitization
class SanitizeInput
{
    public function handle(Request $request, Closure $next)
    {
        $input = $request->all();
        
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

### 3. Rate Limiting and Throttling
```php
<?php
// ✅ Good: Rate limiting implementation
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
            return ApiResponse::error(
                'Too many requests. Please try again later.',
                'RATE_LIMIT_EXCEEDED',
                429,
                [
                    'retry_after' => $this->limiter->availableIn($key)
                ]
            );
        }
        
        $this->limiter->hit($key, $decayMinutes * 60);
        
        $response = $next($request);
        
        return $this->addHeaders(
            $response,
            $maxAttempts,
            $this->calculateRemainingAttempts($key, $maxAttempts)
        );
    }
}
```

---

## ⚡ Performance Best Practices

### 1. Database Optimization
```php
<?php
// ✅ Good: Optimized database queries
class RvmController extends Controller
{
    public function index(Request $request)
    {
        // Use select to limit columns
        $query = Rvm::select([
            'id', 'name', 'location', 'status', 'ip_address', 
            'capacity', 'current_load', 'created_at'
        ]);
        
        // Add eager loading for relationships
        $query->with([
            'detectionResults' => function($query) {
                $query->select('id', 'rvm_id', 'created_at')
                      ->latest()
                      ->limit(1);
            }
        ]);
        
        // Apply filters efficiently
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->has('search')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('location', 'like', "%{$request->search}%");
            });
        }
        
        // Use pagination
        $rvms = $query->paginate($request->get('per_page', 15));
        
        return ApiResponse::success($rvms);
    }
}

// ✅ Good: Database indexing
// Migration: Add indexes for frequently queried columns
Schema::table('reverse_vending_machines', function (Blueprint $table) {
    $table->index('status');
    $table->index('created_at');
    $table->index('ip_address');
    $table->index(['status', 'created_at']);
});
```

### 2. Caching Strategy
```php
<?php
// ✅ Good: Intelligent caching
class RvmService
{
    public function getRvmList(array $filters = [])
    {
        $cacheKey = 'rvms_list_' . md5(serialize($filters));
        
        return Cache::remember($cacheKey, 300, function () use ($filters) {
            return Rvm::with(['detectionResults' => function($query) {
                $query->select('rvm_id', 'created_at')->latest()->limit(1);
            }])
            ->when($filters['status'] ?? null, function($query, $status) {
                $query->where('status', $status);
            })
            ->when($filters['search'] ?? null, function($query, $search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->orderBy('created_at', 'desc')
            ->paginate(15);
        });
    }
    
    public function getRvmAnalytics(int $rvmId)
    {
        $cacheKey = "rvm_analytics_{$rvmId}";
        
        return Cache::remember($cacheKey, 600, function () use ($rvmId) {
            return DB::table('detection_results')
                ->where('rvm_id', $rvmId)
                ->selectRaw('
                    COUNT(*) as total_detections,
                    AVG(processing_time) as avg_processing_time,
                    MAX(created_at) as last_detection
                ')
                ->first();
        });
    }
    
    public function invalidateRvmCache(int $rvmId)
    {
        Cache::forget("rvm_analytics_{$rvmId}");
        Cache::forget("rvm_details_{$rvmId}");
        
        // Clear list cache
        $this->clearListCache();
    }
}
```

### 3. Response Optimization
```php
<?php
// ✅ Good: Optimized response handling
class RvmController extends Controller
{
    public function index(Request $request)
    {
        $rvms = $this->rvmService->getRvmList($request->all());
        
        // Transform data for API response
        $transformedData = $rvms->getCollection()->map(function ($rvm) {
            return [
                'id' => $rvm->id,
                'name' => $rvm->name,
                'location' => $rvm->location,
                'status' => $rvm->status,
                'ip_address' => $rvm->ip_address,
                'capacity' => $rvm->capacity,
                'current_load' => $rvm->current_load,
                'load_percentage' => ($rvm->current_load / $rvm->capacity) * 100,
                'last_detection' => $rvm->detectionResults->first()?->created_at,
                'created_at' => $rvm->created_at
            ];
        });
        
        return ApiResponse::success([
            'data' => $transformedData,
            'pagination' => [
                'current_page' => $rvms->currentPage(),
                'last_page' => $rvms->lastPage(),
                'per_page' => $rvms->perPage(),
                'total' => $rvms->total()
            ]
        ]);
    }
}
```

---

## 📝 Documentation Best Practices

### 1. API Documentation Standards
```php
<?php
// ✅ Good: Comprehensive API documentation
/**
 * @OA\Tag(
 *     name="RVMs",
 *     description="Reverse Vending Machine management endpoints"
 * )
 */
class RvmController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/rvms",
     *     summary="Get list of RVMs",
     *     description="Retrieve a paginated list of reverse vending machines",
     *     tags={"RVMs"},
     *     security={{"api_key": {}}},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number",
     *         required=false,
     *         @OA\Schema(type="integer", minimum=1, default=1)
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of items per page",
     *         required=false,
     *         @OA\Schema(type="integer", minimum=1, maximum=100, default=15)
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filter by RVM status",
     *         required=false,
     *         @OA\Schema(type="string", enum={"active", "inactive", "offline"})
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search by name or location",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/RVM")
     *             ),
     *             @OA\Property(
     *                 property="pagination",
     *                 ref="#/components/schemas/Pagination"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=429,
     *         description="Rate limit exceeded",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function index(Request $request)
    {
        // Implementation
    }
}
```

### 2. Error Documentation
```php
<?php
// ✅ Good: Comprehensive error documentation
/**
 * @OA\Schema(
 *     schema="ErrorResponse",
 *     type="object",
 *     @OA\Property(property="success", type="boolean", example=false),
 *     @OA\Property(property="error", type="string", example="VALIDATION_ERROR"),
 *     @OA\Property(property="message", type="string", example="Validation failed"),
 *     @OA\Property(property="details", type="object", example={"name": ["The name field is required."]}),
 *     @OA\Property(property="timestamp", type="string", format="date-time", example="2025-01-02T10:30:00Z")
 * )
 */
class ErrorResponse
{
    // Error response implementation
}

/**
 * @OA\Schema(
 *     schema="ValidationError",
 *     type="object",
 *     @OA\Property(property="success", type="boolean", example=false),
 *     @OA\Property(property="error", type="string", example="VALIDATION_ERROR"),
 *     @OA\Property(property="message", type="string", example="Validation failed"),
 *     @OA\Property(
 *         property="details",
 *         type="object",
 *         @OA\Property(
 *             property="name",
 *             type="array",
 *             @OA\Items(type="string"),
 *             example={"The name field is required.", "The name must be at least 3 characters."}
 *         ),
 *         @OA\Property(
 *             property="ip_address",
 *             type="array",
 *             @OA\Items(type="string"),
 *             example={"The ip address field is required.", "The ip address must be a valid IP address."}
 *         )
 *     ),
 *     @OA\Property(property="timestamp", type="string", format="date-time", example="2025-01-02T10:30:00Z")
 * )
 */
class ValidationError
{
    // Validation error implementation
}
```

---

## 🧪 Testing Best Practices

### 1. Unit Testing
```php
<?php
// ✅ Good: Comprehensive unit tests
class RvmControllerTest extends TestCase
{
    use RefreshDatabase;
    
    protected $user;
    protected $apiKey;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->apiKey = $this->user->createApiKey();
    }
    
    public function test_can_list_rvms()
    {
        Rvm::factory()->count(3)->create();
        
        $response = $this->getJson('/api/rvms', [
            'Authorization' => 'Bearer ' . $this->apiKey
        ]);
        
        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        '*' => [
                            'id',
                            'name',
                            'location',
                            'status',
                            'created_at'
                        ]
                    ],
                    'pagination' => [
                        'current_page',
                        'last_page',
                        'per_page',
                        'total'
                    ]
                ]);
    }
    
    public function test_can_create_rvm()
    {
        $rvmData = [
            'name' => 'Test RVM',
            'location' => 'Test Location',
            'ip_address' => '192.168.1.100',
            'capacity' => 100
        ];
        
        $response = $this->postJson('/api/rvms', $rvmData, [
            'Authorization' => 'Bearer ' . $this->apiKey
        ]);
        
        $response->assertStatus(201)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'id',
                        'name',
                        'location',
                        'ip_address',
                        'capacity',
                        'created_at'
                    ]
                ]);
        
        $this->assertDatabaseHas('reverse_vending_machines', [
            'name' => 'Test RVM',
            'location' => 'Test Location',
            'ip_address' => '192.168.1.100',
            'capacity' => 100
        ]);
    }
    
    public function test_cannot_create_rvm_without_authentication()
    {
        $rvmData = [
            'name' => 'Test RVM',
            'location' => 'Test Location',
            'ip_address' => '192.168.1.100',
            'capacity' => 100
        ];
        
        $response = $this->postJson('/api/rvms', $rvmData);
        
        $response->assertStatus(401)
                ->assertJsonStructure([
                    'success',
                    'error',
                    'message',
                    'timestamp'
                ]);
    }
    
    public function test_cannot_create_rvm_with_invalid_data()
    {
        $rvmData = [
            'name' => '', // Invalid: empty name
            'location' => 'Test Location',
            'ip_address' => 'invalid-ip', // Invalid: not a valid IP
            'capacity' => -1 // Invalid: negative capacity
        ];
        
        $response = $this->postJson('/api/rvms', $rvmData, [
            'Authorization' => 'Bearer ' . $this->apiKey
        ]);
        
        $response->assertStatus(422)
                ->assertJsonStructure([
                    'success',
                    'error',
                    'message',
                    'details' => [
                        'name',
                        'ip_address',
                        'capacity'
                    ],
                    'timestamp'
                ]);
    }
}
```

### 2. Integration Testing
```php
<?php
// ✅ Good: Integration tests
class RvmIntegrationTest extends TestCase
{
    use RefreshDatabase;
    
    public function test_complete_rvm_lifecycle()
    {
        $user = User::factory()->create();
        $apiKey = $user->createApiKey();
        
        // 1. Create RVM
        $rvmData = [
            'name' => 'Integration Test RVM',
            'location' => 'Test Location',
            'ip_address' => '192.168.1.100',
            'capacity' => 100
        ];
        
        $createResponse = $this->postJson('/api/rvms', $rvmData, [
            'Authorization' => 'Bearer ' . $apiKey
        ]);
        
        $createResponse->assertStatus(201);
        $rvmId = $createResponse->json('data.id');
        
        // 2. Get RVM details
        $getResponse = $this->getJson("/api/rvms/{$rvmId}", [
            'Authorization' => 'Bearer ' . $apiKey
        ]);
        
        $getResponse->assertStatus(200)
                  ->assertJsonPath('data.name', 'Integration Test RVM');
        
        // 3. Update RVM
        $updateData = [
            'name' => 'Updated RVM Name',
            'location' => 'Updated Location'
        ];
        
        $updateResponse = $this->putJson("/api/rvms/{$rvmId}", $updateData, [
            'Authorization' => 'Bearer ' . $apiKey
        ]);
        
        $updateResponse->assertStatus(200)
                      ->assertJsonPath('data.name', 'Updated RVM Name');
        
        // 4. Delete RVM
        $deleteResponse = $this->deleteJson("/api/rvms/{$rvmId}", [], [
            'Authorization' => 'Bearer ' . $apiKey
        ]);
        
        $deleteResponse->assertStatus(204);
        
        // 5. Verify deletion
        $getResponse = $this->getJson("/api/rvms/{$rvmId}", [
            'Authorization' => 'Bearer ' . $apiKey
        ]);
        
        $getResponse->assertStatus(404);
    }
}
```

---

## 🔄 Versioning Best Practices

### 1. API Versioning Strategy
```php
<?php
// ✅ Good: API versioning
// routes/api.php
Route::prefix('v1')->group(function () {
    Route::apiResource('rvms', V1\RvmController::class);
    Route::apiResource('detection-results', V1\DetectionResultController::class);
});

Route::prefix('v2')->group(function () {
    Route::apiResource('rvms', V2\RvmController::class);
    Route::apiResource('detection-results', V2\DetectionResultController::class);
    Route::apiResource('analytics', V2\AnalyticsController::class);
});

// ✅ Good: Version-specific controllers
namespace App\Http\Controllers\Api\V2;

class RvmController extends Controller
{
    public function index(Request $request)
    {
        // V2 implementation with additional features
        $rvms = Rvm::with(['detectionResults', 'analytics'])
                   ->paginate($request->get('per_page', 15));
        
        return response()->json([
            'success' => true,
            'data' => $rvms->items(),
            'pagination' => [
                'current_page' => $rvms->currentPage(),
                'last_page' => $rvms->lastPage(),
                'per_page' => $rvms->perPage(),
                'total' => $rvms->total()
            ],
            'version' => '2.0.0'
        ]);
    }
}
```

### 2. Backward Compatibility
```php
<?php
// ✅ Good: Backward compatibility
class RvmController extends Controller
{
    public function index(Request $request)
    {
        $rvms = Rvm::paginate($request->get('per_page', 15));
        
        // Check API version
        $version = $request->header('API-Version', '1.0');
        
        if (version_compare($version, '2.0', '>=')) {
            // V2 response format
            return response()->json([
                'success' => true,
                'data' => $rvms->items(),
                'pagination' => [
                    'current_page' => $rvms->currentPage(),
                    'last_page' => $rvms->lastPage(),
                    'per_page' => $rvms->perPage(),
                    'total' => $rvms->total()
                ],
                'version' => '2.0.0'
            ]);
        } else {
            // V1 response format (backward compatibility)
            return response()->json([
                'data' => $rvms->items(),
                'pagination' => [
                    'current_page' => $rvms->currentPage(),
                    'last_page' => $rvms->lastPage(),
                    'per_page' => $rvms->perPage(),
                    'total' => $rvms->total()
                ]
            ]);
        }
    }
}
```

---

## 📊 Monitoring and Logging Best Practices

### 1. Structured Logging
```php
<?php
// ✅ Good: Structured logging
class ApiRequestLogger
{
    public function handle(Request $request, Closure $next)
    {
        $startTime = microtime(true);
        
        $response = $next($request);
        
        $endTime = microtime(true);
        $duration = $endTime - $startTime;
        
        $this->logApiRequest($request, $response, $duration);
        
        return $response;
    }
    
    private function logApiRequest(Request $request, $response, float $duration): void
    {
        $logData = [
            'type' => 'api_request',
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'status_code' => $response->getStatusCode(),
            'duration' => $duration,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'user_id' => $request->user()?->id,
            'api_key' => $this->maskApiKey($request->header('Authorization')),
            'request_size' => strlen($request->getContent()),
            'response_size' => strlen($response->getContent()),
            'timestamp' => now()->toISOString()
        ];
        
        Log::channel('api')->info('API request processed', $logData);
    }
}
```

### 2. Performance Monitoring
```php
<?php
// ✅ Good: Performance monitoring
class PerformanceMonitor
{
    public function handle(Request $request, Closure $next)
    {
        $startTime = microtime(true);
        $startMemory = memory_get_usage();
        
        $response = $next($request);
        
        $endTime = microtime(true);
        $endMemory = memory_get_usage();
        
        $duration = $endTime - $startTime;
        $memoryUsed = $endMemory - $startMemory;
        
        // Log performance metrics
        $this->logPerformanceMetrics($request, $response, $duration, $memoryUsed);
        
        // Update metrics cache
        $this->updateMetricsCache($duration, $response->getStatusCode());
        
        return $response;
    }
}
```

---

## 🚀 Deployment Best Practices

### 1. Environment Configuration
```bash
# ✅ Good: Environment-specific configuration
# .env.production
APP_ENV=production
APP_DEBUG=false
APP_URL=https://api.myrvm.com

DB_CONNECTION=pgsql
DB_HOST=postgres
DB_PORT=5432
DB_DATABASE=myrvm_ecosystem
DB_USERNAME=myrvm_user
DB_PASSWORD=secure_password

REDIS_HOST=redis
REDIS_PASSWORD=secure_redis_password

LOG_LEVEL=warning
LOG_CHANNEL=daily

API_RATE_LIMIT=1000
API_RATE_LIMIT_WINDOW=60
```

### 2. Health Checks
```php
<?php
// ✅ Good: Health check endpoint
class HealthController extends Controller
{
    public function check()
    {
        $checks = [
            'database' => $this->checkDatabase(),
            'cache' => $this->checkCache(),
            'queue' => $this->checkQueue(),
            'storage' => $this->checkStorage()
        ];
        
        $overallHealth = collect($checks)->every(fn($check) => $check['status'] === 'healthy');
        
        return response()->json([
            'status' => $overallHealth ? 'healthy' : 'unhealthy',
            'checks' => $checks,
            'timestamp' => now()->toISOString()
        ], $overallHealth ? 200 : 503);
    }
}
```

---

**Last Updated**: 2025-01-02  
**Version**: 2.0.0  
**Status**: ✅ COMPLETE BEST PRACTICES & GUIDELINES DOCUMENTATION
