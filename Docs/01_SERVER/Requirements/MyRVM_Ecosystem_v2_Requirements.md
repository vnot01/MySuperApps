# 📋 **MyRVM-Ecosystem-v2 REQUIREMENTS**

## 📋 **OVERVIEW**

Requirements lengkap untuk implementasi MyRVM-Ecosystem-v2 berdasarkan analisis mendalam MyCV-Platform dan kebutuhan integrasi Jetson.

## 🎯 **PHASE 1: CORE IMPLEMENTATION (Week 1)**

### **1.0 RVM Status System (NEW)**
- ✅ **3-Tier Status System**: Operational, Connection, API Status
- ✅ **Database Schema**: Added status columns and indexes
- ✅ **Model Methods**: Status checking and management
- ✅ **Frontend Display**: Multi-status badges in dashboard
- ✅ **Console Commands**: Status checking utilities
- ✅ **API Key Management**: 1-month expiration
- 📋 **Documentation**: [RVM_Status_System.md](./RVM_Status_System.md)

### **1.1 RVM Details Page (NEW)**
- ✅ **Comprehensive View**: Full-page RVM details with complete information
- ✅ **Action Buttons**: Enter Maintenance and Enter Playground functionality
- ✅ **Status Indicators**: Visual status display with real-time data
- ✅ **API Management**: Copy API key and expiration information
- ✅ **Responsive Design**: Grid layout with sidebar for optimal viewing
- ✅ **Navigation**: Back to dashboard with breadcrumb navigation
- 📋 **Documentation**: [RVM_Details_Page.md](./RVM_Details_Page.md)

## 🎯 **PHASE 1: CORE IMPLEMENTATION (Week 1)**

### **1.1 Database Components**

#### **Detection Results Table**
```sql
-- File: database/migrations/xxxx_create_detection_results_table.php
CREATE TABLE detection_results (
    id BIGSERIAL PRIMARY KEY,
    rvm_id BIGINT NOT NULL,
    session_id VARCHAR(255) NOT NULL,
    user_id VARCHAR(255) NULL,
    detection_data JSON NOT NULL,
    image_path VARCHAR(500) NULL,
    detected_at TIMESTAMP NOT NULL,
    status ENUM('pending', 'processing', 'completed', 'failed') DEFAULT 'pending',
    error_message TEXT NULL,
    metadata JSON NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (rvm_id) REFERENCES reverse_vending_machines(id)
);

-- Indexes for performance
CREATE INDEX idx_detection_results_rvm_id ON detection_results(rvm_id);
CREATE INDEX idx_detection_results_detected_at ON detection_results(detected_at);
CREATE INDEX idx_detection_results_session_id ON detection_results(session_id);
CREATE INDEX idx_detection_results_status ON detection_results(status);
```

#### **RVM API Key Enhancement**
```sql
-- File: database/migrations/xxxx_add_api_key_to_rvms.php
ALTER TABLE reverse_vending_machines 
ADD COLUMN api_key VARCHAR(255) NULL UNIQUE,
ADD COLUMN api_key_expires_at TIMESTAMP NULL,
ADD COLUMN last_api_access TIMESTAMP NULL;

-- Index for API key lookups
CREATE INDEX idx_rvms_api_key ON reverse_vending_machines(api_key);
```

### **1.2 Eloquent Models**

#### **DetectionResult Model**
```php
// File: app/Models/DetectionResult.php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class DetectionResult extends Model
{
    use HasFactory;

    protected $fillable = [
        'rvm_id', 'session_id', 'user_id', 'detection_data',
        'image_path', 'detected_at', 'status', 'error_message', 'metadata'
    ];

    protected $casts = [
        'detection_data' => 'array',
        'metadata' => 'array',
        'detected_at' => 'datetime'
    ];

    // Relationships
    public function rvm(): BelongsTo
    {
        return $this->belongsTo(ReverseVendingMachine::class);
    }

    // Scopes
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function scopeByRvm($query, $rvmId)
    {
        return $query->where('rvm_id', $rvmId);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('detected_at', today());
    }

    // Accessors
    public function getDetectionSummaryAttribute()
    {
        if (!$this->detection_data) return 'No data';
        
        $count = count($this->detection_data['detections'] ?? []);
        return "{$count} objects detected";
    }

    // Static methods
    public static function getRvmStatistics($rvmId)
    {
        return [
            'total_detections' => self::byRvm($rvmId)->count(),
            'today_detections' => self::byRvm($rvmId)->today()->count(),
            'completed_detections' => self::byRvm($rvmId)->completed()->count(),
            'failed_detections' => self::byRvm($rvmId)->failed()->count(),
            'last_detection' => self::byRvm($rvmId)->latest('detected_at')->first()
        ];
    }
}
```

#### **RVM Model Enhancement**
```php
// File: app/Models/ReverseVendingMachine.php (additions)
// Add to existing ReverseVendingMachine model

protected $fillable = [
    // ... existing fields ...
    'api_key', 'api_key_expires_at', 'last_api_access'
];

protected $casts = [
    // ... existing casts ...
    'api_key_expires_at' => 'datetime',
    'last_api_access' => 'datetime'
];

// New methods
public function generateApiKey()
{
    $apiKey = bin2hex(random_bytes(32));
    $this->update([
        'api_key' => $apiKey,
        'api_key_expires_at' => now()->addYear()
    ]);
    return $apiKey;
}

public function detectionResults()
{
    return $this->hasMany(DetectionResult::class);
}

public function recentDetections($limit = 10)
{
    return $this->detectionResults()
        ->latest('detected_at')
        ->limit($limit);
}

public function isApiKeyValid()
{
    return $this->api_key && 
           $this->api_key_expires_at && 
           $this->api_key_expires_at->isFuture();
}
```

### **1.3 API Controller**

#### **RVM Integration Controller**
```php
// File: app/Http/Controllers/Api/RvmIntegrationController.php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ReverseVendingMachine;
use App\Models\DetectionResult;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class RvmIntegrationController extends Controller
{
    public function validateApiKey(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'api_key' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => 'Invalid request'], 400);
        }

        $rvm = ReverseVendingMachine::where('api_key', $request->api_key)
            ->where('status', 'active')
            ->first();

        if (!$rvm || !$rvm->isApiKeyValid()) {
            return response()->json(['error' => 'Invalid API key'], 401);
        }

        // Update last API access
        $rvm->update(['last_api_access' => now()]);

        return response()->json([
            'valid' => true,
            'rvm_id' => $rvm->id,
            'rvm_name' => $rvm->name,
            'status' => $rvm->status
        ]);
    }

    public function getRvm(int $id): JsonResponse
    {
        $rvm = ReverseVendingMachine::find($id);
        
        if (!$rvm) {
            return response()->json(['error' => 'RVM not found'], 404);
        }

        return response()->json([
            'id' => $rvm->id,
            'name' => $rvm->name,
            'location' => $rvm->location,
            'ip_address' => $rvm->ip_address,
            'status' => $rvm->status,
            'capacity' => $rvm->capacity,
            'current_load' => $rvm->current_load,
            'last_online_at' => $rvm->last_online_at,
            'api_key_valid' => $rvm->isApiKeyValid()
        ]);
    }

    public function storeDetection(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'rvm_id' => 'required|exists:reverse_vending_machines,id',
            'session_id' => 'required|string',
            'detection_data' => 'required|array',
            'image_path' => 'nullable|string',
            'user_id' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        try {
            $detection = DetectionResult::create([
                'rvm_id' => $request->rvm_id,
                'session_id' => $request->session_id,
                'user_id' => $request->user_id,
                'detection_data' => $request->detection_data,
                'image_path' => $request->image_path,
                'detected_at' => now(),
                'status' => 'completed'
            ]);

            // Update RVM last online
            ReverseVendingMachine::find($request->rvm_id)
                ->update(['last_online_at' => now()]);

            return response()->json([
                'success' => true,
                'detection_id' => $detection->id
            ]);

        } catch (\Exception $e) {
            Log::error('Detection storage failed', [
                'error' => $e->getMessage(),
                'request' => $request->all()
            ]);

            return response()->json(['error' => 'Storage failed'], 500);
        }
    }

    public function getRvmStats(int $id): JsonResponse
    {
        $rvm = ReverseVendingMachine::find($id);
        
        if (!$rvm) {
            return response()->json(['error' => 'RVM not found'], 404);
        }

        $stats = Cache::remember("rvm_stats_{$id}", 300, function () use ($id) {
            return DetectionResult::getRvmStatistics($id);
        });

        return response()->json($stats);
    }

    public function getRvmDetections(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'status' => 'nullable|in:pending,processing,completed,failed',
            'limit' => 'nullable|integer|min:1|max:100',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $query = DetectionResult::byRvm($id);

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->date_from) {
            $query->whereDate('detected_at', '>=', $request->date_from);
        }

        if ($request->date_to) {
            $query->whereDate('detected_at', '<=', $request->date_to);
        }

        $detections = $query->latest('detected_at')
            ->limit($request->limit ?? 50)
            ->get();

        return response()->json($detections);
    }

    public function updateRvmStatus(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:active,inactive,maintenance,error',
            'current_load' => 'nullable|integer|min:0',
            'metrics' => 'nullable|array'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $rvm = ReverseVendingMachine::find($id);
        
        if (!$rvm) {
            return response()->json(['error' => 'RVM not found'], 404);
        }

        $updateData = ['status' => $request->status];

        if ($request->current_load !== null) {
            $updateData['current_load'] = $request->current_load;
        }

        if ($request->metrics) {
            $updateData['last_metrics'] = $request->metrics;
        }

        $rvm->update($updateData);

        return response()->json(['success' => true]);
    }
}
```

### **1.4 API Routes**

#### **RVM Integration Routes**
```php
// File: routes/api.php (additions)
use App\Http\Controllers\Api\RvmIntegrationController;

// RVM Integration Routes
Route::prefix('rvm')->group(function () {
    Route::post('/validate', [RvmIntegrationController::class, 'validateApiKey'])
        ->name('rvm.validate-api-key');
    
    Route::get('/{id}', [RvmIntegrationController::class, 'getRvm'])
        ->name('rvm.show')
        ->where('id', '[0-9]+');
    
    Route::get('/{id}/stats', [RvmIntegrationController::class, 'getRvmStats'])
        ->name('rvm.stats')
        ->where('id', '[0-9]+');
    
    Route::get('/{id}/detections', [RvmIntegrationController::class, 'getRvmDetections'])
        ->name('rvm.detections')
        ->where('id', '[0-9]+');
    
    Route::patch('/{id}/status', [RvmIntegrationController::class, 'updateRvmStatus'])
        ->name('rvm.update-status')
        ->where('id', '[0-9]+');
});

// Detection Management Routes
Route::prefix('detections')->group(function () {
    Route::post('/store', [RvmIntegrationController::class, 'storeDetection'])
        ->name('detections.store');
});

// Health Check Routes
Route::prefix('rvm-health')->group(function () {
    Route::post('/ping', [RvmIntegrationController::class, 'ping'])
        ->name('rvm-health.ping');
    
    Route::post('/status', [RvmIntegrationController::class, 'updateStatus'])
        ->name('rvm-health.status');
    
    Route::post('/metrics', [RvmIntegrationController::class, 'storeMetrics'])
        ->name('rvm-health.metrics');
});
```

## 🎯 **PHASE 2: DASHBOARD INTEGRATION (Week 2)**

### **2.1 Dashboard Controller Update**

#### **Enhanced Dashboard Controller**
```php
// File: app/Http/Controllers/DashboardController.php (additions)
use App\Models\DetectionResult;

public function index(Request $request)
{
    $rvms = ReverseVendingMachine::all();

    $activeRvms = $rvms->where('status', 'active')->count();
    $maintenanceRvms = $rvms->where('status', 'maintenance')->count();
    $totalRvms = $rvms->count();

    // Add detection statistics
    $totalDetections = DetectionResult::count();
    $todayDetections = DetectionResult::today()->count();
    $failedDetections = DetectionResult::failed()->count();

    // Recent detections
    $recentDetections = DetectionResult::with('rvm')
        ->latest('detected_at')
        ->limit(10)
        ->get()
        ->map(function ($detection) {
            return [
                'id' => $detection->id,
                'rvm_name' => $detection->rvm->name,
                'session_id' => $detection->session_id,
                'detected_at' => $detection->detected_at->diffForHumans(),
                'status' => $detection->status,
                'detection_summary' => $detection->detection_summary
            ];
        });

    return Inertia::render('Dashboard', [
        'auth' => [
            'user' => $request->user()
        ],
        'stats' => [
            'activeRvms' => $activeRvms,
            'totalDeposits' => 1247, // Placeholder
            'revenueToday' => 2450000, // Placeholder
            'maintenanceRvms' => $maintenanceRvms,
            'totalRvms' => $totalRvms,
            'totalDetections' => $totalDetections,
            'todayDetections' => $todayDetections,
            'failedDetections' => $failedDetections
        ],
        'rvms' => $rvms->map(function ($rvm) {
            $isOnline = $rvm->last_online_at && $rvm->last_online_at->diffInMinutes(now()) < 5;
            return [
                'id' => $rvm->id,
                'name' => $rvm->name,
                'location' => $rvm->location,
                'ip_address' => $rvm->ip_address,
                'status' => $rvm->status,
                'current_load' => $rvm->current_load,
                'capacity' => $rvm->capacity,
                'last_online_at' => $rvm->last_online_at ? $rvm->last_online_at->diffForHumans() : 'N/A',
                'last_metrics' => $rvm->last_metrics,
                'is_online' => $isOnline,
                'api_key_valid' => $rvm->isApiKeyValid()
            ];
        }),
        'recentDetections' => $recentDetections
    ]);
}
```

### **2.2 Dashboard Vue Component Update**

#### **Enhanced Dashboard Component**
```vue
<!-- File: resources/js/Pages/Dashboard.vue (additions) -->
<template>
  <div class="min-h-screen bg-gray-100 text-gray-900">
    <!-- ... existing nav ... -->
    
    <main class="p-6">
      <!-- ... existing stats grid ... -->
      
      <!-- New Detection Statistics -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="dashboard-card">
          <h2 class="text-lg font-medium text-gray-600">Total Detections</h2>
          <p class="text-3xl font-bold text-blue-600 mt-2">{{ stats.totalDetections }}</p>
        </div>
        <div class="dashboard-card">
          <h2 class="text-lg font-medium text-gray-600">Today's Detections</h2>
          <p class="text-3xl font-bold text-green-600 mt-2">{{ stats.todayDetections }}</p>
        </div>
        <div class="dashboard-card">
          <h2 class="text-lg font-medium text-gray-600">Failed Detections</h2>
          <p class="text-3xl font-bold text-red-600 mt-2">{{ stats.failedDetections }}</p>
        </div>
      </div>

      <!-- Recent Detections Section -->
      <div class="mb-8">
        <h2 class="text-2xl font-semibold text-gray-800 mb-4">Recent Detections</h2>
        <div class="bg-white rounded-lg shadow-sm p-6">
          <div v-if="recentDetections.length === 0" class="text-center text-gray-500 py-8">
            No recent detections
          </div>
          <div v-else class="space-y-4">
            <div v-for="detection in recentDetections" :key="detection.id" 
                 class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
              <div class="flex items-center space-x-4">
                <div :class="['w-3 h-3 rounded-full', getDetectionStatusColor(detection.status)]"></div>
                <div>
                  <p class="font-medium">{{ detection.rvm_name }}</p>
                  <p class="text-sm text-gray-600">Session: {{ detection.session_id }}</p>
                </div>
              </div>
              <div class="text-right">
                <p class="text-sm text-gray-600">{{ detection.detected_at }}</p>
                <p class="text-sm font-medium">{{ detection.detection_summary }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- ... existing RVM status overview ... -->
    </main>
  </div>
</template>

<script setup>
// ... existing script ...

const props = defineProps({
  stats: Object,
  rvms: Array,
  auth: Object,
  recentDetections: Array, // New prop
});

// ... existing methods ...

const getDetectionStatusColor = (status) => {
  const colors = {
    completed: "bg-green-500",
    processing: "bg-yellow-500",
    failed: "bg-red-500",
    pending: "bg-gray-500"
  };
  return colors[status] || "bg-gray-500";
};
</script>
```

## 🎯 **PHASE 3: CONFIGURATION & SECURITY (Week 2)**

### **3.1 Environment Configuration**

#### **Environment Variables**
```bash
# File: .env (additions)
# Jetson Integration
RVM_JETSON_IP=100.117.234.2
RVM_CV_SERVER_IP=100.98.142.94
RVM_API_KEY_CACHE_TTL=300
RVM_DETECTION_STORAGE_PATH=/var/www/html/storage/detections

# CORS Configuration
CORS_ALLOWED_ORIGINS=http://100.117.234.2:5000,http://100.98.142.94:5000
```

### **3.2 CORS Configuration**

#### **CORS Middleware**
```php
// File: config/cors.php (update)
'allowed_origins' => [
    'http://100.117.234.2:5000',
    'http://100.98.142.94:5000',
    'http://localhost:3000', // Development
],

'allowed_methods' => ['*'],
'allowed_headers' => ['*'],
'exposed_headers' => [],
'max_age' => 0,
'supports_credentials' => false,
```

### **3.3 Rate Limiting**

#### **API Rate Limiting**
```php
// File: routes/api.php (additions)
Route::middleware(['throttle:60,1'])->group(function () {
    // RVM integration routes
    Route::prefix('rvm')->group(function () {
        // ... RVM routes ...
    });
});

Route::middleware(['throttle:10,1'])->group(function () {
    // Detection storage routes (more restrictive)
    Route::post('/detections/store', [RvmIntegrationController::class, 'storeDetection']);
});
```

## 🎯 **PHASE 4: TESTING & VALIDATION (Week 3)**

### **4.1 Unit Tests**

#### **DetectionResult Model Test**
```php
// File: tests/Unit/DetectionResultTest.php
<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\DetectionResult;
use App\Models\ReverseVendingMachine;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DetectionResultTest extends TestCase
{
    use RefreshDatabase;

    public function test_detection_result_creation()
    {
        $rvm = ReverseVendingMachine::factory()->create();
        
        $detection = DetectionResult::create([
            'rvm_id' => $rvm->id,
            'session_id' => 'test_session_123',
            'detection_data' => ['detections' => []],
            'detected_at' => now()
        ]);

        $this->assertDatabaseHas('detection_results', [
            'rvm_id' => $rvm->id,
            'session_id' => 'test_session_123'
        ]);
    }

    public function test_rvm_relationship()
    {
        $rvm = ReverseVendingMachine::factory()->create();
        $detection = DetectionResult::factory()->create(['rvm_id' => $rvm->id]);

        $this->assertEquals($rvm->id, $detection->rvm->id);
    }

    public function test_scopes()
    {
        $rvm = ReverseVendingMachine::factory()->create();
        
        DetectionResult::factory()->create([
            'rvm_id' => $rvm->id,
            'status' => 'completed'
        ]);
        
        DetectionResult::factory()->create([
            'rvm_id' => $rvm->id,
            'status' => 'failed'
        ]);

        $this->assertEquals(1, DetectionResult::completed()->count());
        $this->assertEquals(1, DetectionResult::failed()->count());
        $this->assertEquals(2, DetectionResult::byRvm($rvm->id)->count());
    }
}
```

#### **RVM Integration Controller Test**
```php
// File: tests/Feature/RvmIntegrationTest.php
<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\ReverseVendingMachine;
use App\Models\DetectionResult;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RvmIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_key_validation()
    {
        $rvm = ReverseVendingMachine::factory()->create([
            'api_key' => 'test_api_key_123',
            'status' => 'active'
        ]);

        $response = $this->postJson('/api/rvm/validate', [
            'api_key' => 'test_api_key_123'
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'valid' => true,
                'rvm_id' => $rvm->id
            ]);
    }

    public function test_detection_storage()
    {
        $rvm = ReverseVendingMachine::factory()->create();

        $response = $this->postJson('/api/detections/store', [
            'rvm_id' => $rvm->id,
            'session_id' => 'test_session_123',
            'detection_data' => ['detections' => []]
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('detection_results', [
            'rvm_id' => $rvm->id,
            'session_id' => 'test_session_123'
        ]);
    }

    public function test_rvm_stats()
    {
        $rvm = ReverseVendingMachine::factory()->create();
        
        DetectionResult::factory()->count(5)->create(['rvm_id' => $rvm->id]);

        $response = $this->getJson("/api/rvm/{$rvm->id}/stats");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'total_detections',
                'today_detections',
                'completed_detections',
                'failed_detections'
            ]);
    }
}
```

### **4.2 Integration Tests**

#### **Jetson Integration Test**
```php
// File: tests/Integration/JetsonIntegrationTest.php
<?php

namespace Tests\Integration;

use Tests\TestCase;
use App\Models\ReverseVendingMachine;
use Illuminate\Foundation\Testing\RefreshDatabase;

class JetsonIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_full_detection_workflow()
    {
        // 1. Create RVM with API key
        $rvm = ReverseVendingMachine::factory()->create([
            'api_key' => 'jetson_test_key_123',
            'status' => 'active'
        ]);

        // 2. Validate API key
        $validateResponse = $this->postJson('/api/rvm/validate', [
            'api_key' => 'jetson_test_key_123'
        ]);
        $validateResponse->assertStatus(200);

        // 3. Store detection
        $detectionResponse = $this->postJson('/api/detections/store', [
            'rvm_id' => $rvm->id,
            'session_id' => 'jetson_session_456',
            'detection_data' => [
                'detections' => [
                    ['class' => 'bottle', 'confidence' => 0.95],
                    ['class' => 'can', 'confidence' => 0.87]
                ]
            ]
        ]);
        $detectionResponse->assertStatus(200);

        // 4. Verify detection stored
        $this->assertDatabaseHas('detection_results', [
            'rvm_id' => $rvm->id,
            'session_id' => 'jetson_session_456'
        ]);

        // 5. Get RVM stats
        $statsResponse = $this->getJson("/api/rvm/{$rvm->id}/stats");
        $statsResponse->assertStatus(200)
            ->assertJson([
                'total_detections' => 1,
                'completed_detections' => 1
            ]);
    }
}
```

## 🎯 **PHASE 5: DEPLOYMENT & MONITORING (Week 3)**

### **5.1 Database Seeding**

#### **API Key Generation Seeder**
```php
// File: database/seeders/ApiKeySeeder.php
<?php

namespace Database\Seeders;

use App\Models\ReverseVendingMachine;
use Illuminate\Database\Seeder;

class ApiKeySeeder extends Seeder
{
    public function run(): void
    {
        ReverseVendingMachine::all()->each(function ($rvm) {
            if (!$rvm->api_key) {
                $rvm->generateApiKey();
            }
        });
    }
}
```

### **5.2 Performance Monitoring**

#### **Monitoring Controller**
```php
// File: app/Http/Controllers/Api/MonitoringController.php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DetectionResult;
use App\Models\ReverseVendingMachine;
use Illuminate\Http\JsonResponse;

class MonitoringController extends Controller
{
    public function performance(): JsonResponse
    {
        $stats = [
            'total_detections' => DetectionResult::count(),
            'today_detections' => DetectionResult::today()->count(),
            'failed_detections' => DetectionResult::failed()->count(),
            'active_rvms' => ReverseVendingMachine::where('status', 'active')->count(),
            'offline_rvms' => ReverseVendingMachine::where('last_online_at', '<', now()->subMinutes(5))->count()
        ];

        return response()->json($stats);
    }

    public function systemHealth(): JsonResponse
    {
        $health = [
            'database' => $this->checkDatabase(),
            'cache' => $this->checkCache(),
            'storage' => $this->checkStorage(),
            'api_responses' => $this->checkApiResponses()
        ];

        $overall = collect($health)->every(fn($status) => $status === 'healthy') ? 'healthy' : 'degraded';

        return response()->json([
            'overall' => $overall,
            'checks' => $health,
            'timestamp' => now()
        ]);
    }

    private function checkDatabase(): string
    {
        try {
            \DB::connection()->getPdo();
            return 'healthy';
        } catch (\Exception $e) {
            return 'unhealthy';
        }
    }

    private function checkCache(): string
    {
        try {
            \Cache::put('health_check', 'ok', 1);
            return \Cache::get('health_check') === 'ok' ? 'healthy' : 'unhealthy';
        } catch (\Exception $e) {
            return 'unhealthy';
        }
    }

    private function checkStorage(): string
    {
        try {
            $path = storage_path('app');
            return is_writable($path) ? 'healthy' : 'unhealthy';
        } catch (\Exception $e) {
            return 'unhealthy';
        }
    }

    private function checkApiResponses(): string
    {
        // Check if API endpoints are responding
        try {
            $response = \Http::timeout(5)->get(url('/api/health'));
            return $response->successful() ? 'healthy' : 'unhealthy';
        } catch (\Exception $e) {
            return 'unhealthy';
        }
    }
}
```

## 📊 **SUCCESS METRICS**

### **Technical Metrics**
- ✅ **API Response Time**: < 200ms
- ✅ **Detection Processing**: < 5 seconds per image
- ✅ **Database Query Time**: < 50ms
- ✅ **System Uptime**: 99.9%
- ✅ **Error Rate**: < 0.1%

### **Functional Metrics**
- ✅ **RVM Authentication**: 100% success rate
- ✅ **Detection Storage**: 100% reliability
- ✅ **Data Integrity**: 100% consistency
- ✅ **Multi-RVM Support**: Unlimited scalability
- ✅ **Security**: API key authentication

## 🚀 **IMPLEMENTATION CHECKLIST**

### **Week 1: Core Implementation**
- [ ] Create detection_results migration
- [ ] Implement DetectionResult model
- [ ] Create RvmIntegrationController
- [ ] Register API routes
- [ ] Update RVM model with API key methods
- [ ] Run database migrations
- [ ] Generate API keys for existing RVMs

### **Week 2: Dashboard & Configuration**
- [ ] Update DashboardController with detection data
- [ ] Enhance Dashboard Vue component
- [ ] Configure CORS settings
- [ ] Setup rate limiting
- [ ] Configure environment variables
- [ ] Test basic integration

### **Week 3: Testing & Production**
- [ ] Write unit tests
- [ ] Write integration tests
- [ ] Performance testing
- [ ] Security testing
- [ ] Deploy to production
- [ ] Setup monitoring
- [ ] Documentation updates

---

**Created**: 2025-10-02  
**Version**: 1.0.0  
**Status**: ✅ REQUIREMENTS COMPLETED - READY FOR IMPLEMENTATION
