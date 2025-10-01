# MyRVM-Platform Integration Manual Setup Guide

## 📋 Files to Implement

### 1. Database Migration
**File**: `database/migrations/xxxx_xx_xx_xxxxxx_create_detection_results_table.php`

```php
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('detection_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rvm_id')->constrained('reverse_vending_machines')->onDelete('cascade');
            $table->string('session_id')->index();
            $table->json('detection_data');
            $table->string('image_path')->nullable();
            $table->timestamp('detected_at');
            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
            $table->text('error_message')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            
            $table->index(['rvm_id', 'status']);
            $table->index(['rvm_id', 'detected_at']);
            $table->index('session_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detection_results');
    }
};
```

### 2. Model
**File**: `app/Models/DetectionResult.php`

```php
<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetectionResult extends Model
{
    use HasFactory;

    protected $fillable = [
        'rvm_id',
        'session_id',
        'detection_data',
        'image_path',
        'detected_at',
        'status',
        'error_message',
        'metadata'
    ];

    protected $casts = [
        'detection_data' => 'array',
        'metadata' => 'array',
        'detected_at' => 'datetime'
    ];

    public function rvm(): BelongsTo
    {
        return $this->belongsTo(ReverseVendingMachine::class, 'rvm_id');
    }
}
```

### 3. Controller
**File**: `app/Http/Controllers/Api/RvmIntegrationController.php`

```php
<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ReverseVendingMachine;
use App\Models\DetectionResult;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

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

        $apiKey = $request->input('api_key');
        $rvm = ReverseVendingMachine::where('api_key', $apiKey)
            ->where('status', '!=', 'inactive')
            ->first();

        if (!$rvm) {
            return response()->json(['valid' => false, 'error' => 'Invalid API key'], 401);
        }

        return response()->json([
            'valid' => true,
            'rvm' => [
                'id' => $rvm->id,
                'name' => $rvm->name,
                'location_description' => $rvm->location_description,
                'status' => $rvm->status
            ]
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
            'location_description' => $rvm->location_description,
            'status' => $rvm->status
        ]);
    }

    public function storeDetection(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'rvm_id' => 'required|integer|exists:reverse_vending_machines,id',
            'session_id' => 'required|string',
            'detection_data' => 'required|array',
            'image_path' => 'nullable|string',
            'detected_at' => 'required|date'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => 'Invalid request'], 400);
        }

        $detectionResult = DetectionResult::create([
            'rvm_id' => $request->input('rvm_id'),
            'session_id' => $request->input('session_id'),
            'detection_data' => $request->input('detection_data'),
            'image_path' => $request->input('image_path'),
            'detected_at' => $request->input('detected_at'),
            'status' => 'completed'
        ]);

        return response()->json([
            'success' => true,
            'detection_id' => $detectionResult->id,
            'message' => 'Detection result stored successfully'
        ], 201);
    }

    public function getRvmStats(int $id): JsonResponse
    {
        $rvm = ReverseVendingMachine::find($id);
        if (!$rvm) {
            return response()->json(['error' => 'RVM not found'], 404);
        }

        $stats = [
            'rvm_id' => $rvm->id,
            'name' => $rvm->name,
            'status' => $rvm->status,
            'total_detections' => DetectionResult::where('rvm_id', $id)->count(),
            'detections_today' => DetectionResult::where('rvm_id', $id)
                ->whereDate('created_at', today())
                ->count()
        ];

        return response()->json($stats);
    }
}
```

### 4. Routes
**File**: `routes/api.php` (add these routes)

```php
// RVM Integration API Routes
Route::prefix('rvm')->group(function () {
    Route::post('validate-api-key', [App\Http\Controllers\Api\RvmIntegrationController::class, 'validateApiKey']);
    Route::get('{id}', [App\Http\Controllers\Api\RvmIntegrationController::class, 'getRvm']);
    Route::get('{id}/stats', [App\Http\Controllers\Api\RvmIntegrationController::class, 'getRvmStats']);
});

Route::prefix('detections')->group(function () {
    Route::post('store', [App\Http\Controllers\Api\RvmIntegrationController::class, 'storeDetection']);
});
```

## 🚀 Setup Steps

1. **Create Migration**
   ```bash
   php artisan make:migration create_detection_results_table
   ```

2. **Create Model**
   ```bash
   php artisan make:model DetectionResult
   ```

3. **Create Controller**
   ```bash
   php artisan make:controller Api/RvmIntegrationController
   ```

4. **Run Migration**
   ```bash
   php artisan migrate
   ```

5. **Test Integration**
   ```bash
   # Test RVM validation
   curl -X POST http://your-rvm-platform.com/api/rvm/validate-api-key \
     -H "Content-Type: application/json" \
     -d '{"api_key": "your_rvm_api_key"}'
   ```

## 🔧 Configuration

Update your MyCV-Platform configuration:
```bash
# In rvm_config.env
RVM_API_BASE_URL=http://your-rvm-platform.com/api
RVM_API_KEY=your_master_api_key_here
```

## 📞 Support

For questions or issues, check the integration documentation or contact support.
