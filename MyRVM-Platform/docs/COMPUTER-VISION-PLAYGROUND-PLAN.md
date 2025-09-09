# Computer Vision Playground v1 - Development Plan

## 📅 **Project Timeline**
- **Start Date**: September 9, 2025
- **Target Completion**: October 2025
- **Status**: 🚀 **PLANNING PHASE**
- **Last Updated**: September 9, 2025

## 🎯 **Project Overview**
Development of a dedicated Computer Vision testing playground for MyRVM Platform, enabling real-time YOLO+SAM inference with model management, visual results, and performance comparison capabilities.

## 🏗️ **Architecture Design**

### **1. Separate Dashboard Structure**
```
/gemini/dashboard              # Gemini Vision Testing (Pending)
/computer-vision/dashboard     # YOLO+SAM Testing (NEW - v1)
/ai-comparison/dashboard       # Combined Testing (Future v2)
```

### **2. Technology Stack**
- **Backend**: Laravel 12 + PHP 8.2+
- **Frontend**: Blade + Vue.js + Tailwind CSS
- **AI Engine**: Python + FastAPI + YOLOv11 + SAM2
- **Database**: PostgreSQL
- **File Storage**: Local Storage (Phase 1) → Cloud Storage (Phase 2)

### **3. Microservice Architecture**
```
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   Laravel App   │    │  Python API     │    │   File Storage  │
│   (Dashboard)   │◄──►│  (Inference)    │◄──►│   (Models)      │
│   Port: 8000    │    │   Port: 8001    │    │   Local/Cloud   │
└─────────────────┘    └─────────────────┘    └─────────────────┘
```

## 📋 **Phase 1: Foundation (Week 1-2)**

### **1.1 Database Schema**
```sql
-- Computer Vision Models
CREATE TABLE computer_vision_models (
    id BIGSERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    model_path VARCHAR(500) NOT NULL,
    model_type ENUM('yolo', 'sam', 'hybrid') NOT NULL,
    version VARCHAR(50) NOT NULL,
    description TEXT,
    accuracy DECIMAL(5,2),
    file_size BIGINT,
    upload_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_active BOOLEAN DEFAULT false,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Inference Results
CREATE TABLE inference_results (
    id BIGSERIAL PRIMARY KEY,
    model_id BIGINT REFERENCES computer_vision_models(id),
    image_path VARCHAR(500) NOT NULL,
    original_filename VARCHAR(255),
    yolo_results JSONB,
    sam_results JSONB,
    processing_time_ms INTEGER,
    confidence_threshold DECIMAL(3,2),
    total_detections INTEGER DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Model Performance Metrics
CREATE TABLE model_performance (
    id BIGSERIAL PRIMARY KEY,
    model_id BIGINT REFERENCES computer_vision_models(id),
    test_image_count INTEGER,
    average_processing_time_ms INTEGER,
    average_confidence DECIMAL(5,2),
    total_tests INTEGER DEFAULT 0,
    last_tested_at TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### **1.2 Laravel Models**
```php
// app/Models/ComputerVisionModel.php
class ComputerVisionModel extends Model
{
    protected $fillable = [
        'name', 'model_path', 'model_type', 'version', 
        'description', 'accuracy', 'file_size', 'is_active'
    ];
    
    protected $casts = [
        'is_active' => 'boolean',
        'accuracy' => 'decimal:2',
        'file_size' => 'integer'
    ];
    
    public function inferenceResults()
    {
        return $this->hasMany(InferenceResult::class);
    }
    
    public function performance()
    {
        return $this->hasOne(ModelPerformance::class);
    }
}

// app/Models/InferenceResult.php
class InferenceResult extends Model
{
    protected $fillable = [
        'model_id', 'image_path', 'original_filename',
        'yolo_results', 'sam_results', 'processing_time_ms',
        'confidence_threshold', 'total_detections'
    ];
    
    protected $casts = [
        'yolo_results' => 'array',
        'sam_results' => 'array',
        'confidence_threshold' => 'decimal:2'
    ];
    
    public function model()
    {
        return $this->belongsTo(ComputerVisionModel::class);
    }
}
```

### **1.3 Python API Service**
```python
# services/inference_service.py
from fastapi import FastAPI, UploadFile, File, HTTPException
from ultralytics import YOLO
from sam2.build_sam import build_sam2
import cv2
import numpy as np
import json
import time
import os
from typing import Dict, List, Optional

app = FastAPI(title="Computer Vision Inference API", version="1.0.0")

class ComputerVisionInference:
    def __init__(self):
        self.yolo_model = None
        self.sam_model = None
        self.model_loaded = False
        
    def load_models(self, yolo_path: str, sam_path: str = "sam2_l.pt") -> Dict:
        """Load YOLO and SAM models"""
        try:
            if not os.path.exists(yolo_path):
                raise FileNotFoundError(f"YOLO model not found: {yolo_path}")
            if not os.path.exists(sam_path):
                raise FileNotFoundError(f"SAM model not found: {sam_path}")
                
            self.yolo_model = YOLO(yolo_path)
            self.sam_model = build_sam2(sam_path)
            self.model_loaded = True
            
            return {
                "success": True,
                "message": "Models loaded successfully",
                "yolo_path": yolo_path,
                "sam_path": sam_path
            }
        except Exception as e:
            self.model_loaded = False
            return {
                "success": False,
                "error": str(e)
            }
    
    def run_inference(self, image_path: str, confidence: float = 0.5) -> Dict:
        """Run YOLO detection + SAM segmentation"""
        if not self.model_loaded:
            raise HTTPException(status_code=400, detail="Models not loaded")
            
        start_time = time.time()
        
        try:
            # YOLO Detection
            yolo_results = self.yolo_model.predict(image_path, conf=confidence, verbose=False)
            
            results = {
                'yolo_detections': [],
                'sam_segmentations': [],
                'processing_time_ms': 0,
                'total_detections': 0,
                'image_dimensions': None,
                'success': True
            }
            
            if len(yolo_results[0].boxes) > 0:
                # Get image dimensions
                img = yolo_results[0].orig_img
                results['image_dimensions'] = {
                    'width': img.shape[1],
                    'height': img.shape[0],
                    'channels': img.shape[2] if len(img.shape) > 2 else 1
                }
                
                boxes = yolo_results[0].boxes.xyxy.cpu().numpy()
                confidences = yolo_results[0].boxes.conf.cpu().numpy()
                classes = yolo_results[0].boxes.cls.cpu().numpy()
                
                # Process each detection
                for i, (box, conf, cls) in enumerate(zip(boxes, confidences, classes)):
                    detection = {
                        'id': i,
                        'bbox': {
                            'x1': float(box[0]),
                            'y1': float(box[1]),
                            'x2': float(box[2]),
                            'y2': float(box[3]),
                            'width': float(box[2] - box[0]),
                            'height': float(box[3] - box[1])
                        },
                        'confidence': float(conf),
                        'class_id': int(cls),
                        'class_name': self.yolo_model.names[int(cls)]
                    }
                    results['yolo_detections'].append(detection)
                
                # SAM Segmentation
                sam_results = self.sam_model.predict(
                    yolo_results[0].orig_img, 
                    bboxes=boxes, 
                    verbose=False
                )
                
                # Process SAM results
                for i, mask in enumerate(sam_results[0].masks.data):
                    mask_array = mask.cpu().numpy()
                    results['sam_segmentations'].append({
                        'id': i,
                        'mask_area': float(mask_array.sum()),
                        'mask_percentage': float(mask_array.sum() / (img.shape[0] * img.shape[1]) * 100),
                        'bounding_box': results['yolo_detections'][i]['bbox'] if i < len(results['yolo_detections']) else None
                    })
            
            results['processing_time_ms'] = int((time.time() - start_time) * 1000)
            results['total_detections'] = len(results['yolo_detections'])
            
            return results
            
        except Exception as e:
            return {
                'success': False,
                'error': str(e),
                'processing_time_ms': int((time.time() - start_time) * 1000)
            }

# Global inference instance
inference_engine = ComputerVisionInference()

@app.get("/")
async def root():
    return {"message": "Computer Vision Inference API", "version": "1.0.0"}

@app.get("/status")
async def get_status():
    return {
        "model_loaded": inference_engine.model_loaded,
        "yolo_model": inference_engine.yolo_model is not None,
        "sam_model": inference_engine.sam_model is not None
    }

@app.post("/load-model")
async def load_model(yolo_path: str, sam_path: str = "sam2_l.pt"):
    """Load YOLO and SAM models"""
    result = inference_engine.load_models(yolo_path, sam_path)
    if not result['success']:
        raise HTTPException(status_code=400, detail=result['error'])
    return result

@app.post("/inference")
async def run_inference(
    image: UploadFile = File(...),
    confidence: float = 0.5
):
    """Run inference on uploaded image"""
    try:
        # Validate image
        if not image.content_type.startswith('image/'):
            raise HTTPException(status_code=400, detail="File must be an image")
        
        # Save uploaded image
        image_path = f"/tmp/{image.filename}"
        with open(image_path, "wb") as buffer:
            buffer.write(await image.read())
        
        # Run inference
        results = inference_engine.run_inference(image_path, confidence)
        
        # Clean up temp file
        os.remove(image_path)
        
        return results
        
    except Exception as e:
        raise HTTPException(status_code=500, detail=str(e))

if __name__ == "__main__":
    import uvicorn
    uvicorn.run(app, host="0.0.0.0", port=8001, reload=True)
```

## 📋 **Phase 2: Laravel Integration (Week 3-4)**

### **2.1 Service Layer**
```php
// app/Services/ComputerVisionService.php
class ComputerVisionService
{
    private $pythonApiUrl;
    private $httpClient;
    
    public function __construct()
    {
        $this->pythonApiUrl = config('computer_vision.python_api_url', 'http://localhost:8001');
        $this->httpClient = Http::timeout(120); // 2 minutes timeout for inference
    }
    
    public function checkServiceStatus(): array
    {
        try {
            $response = $this->httpClient->get("{$this->pythonApiUrl}/status");
            return $response->successful() ? $response->json() : ['error' => 'Service unavailable'];
        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }
    
    public function loadModel(string $modelPath, string $modelType = 'yolo'): array
    {
        try {
            $response = $this->httpClient->post("{$this->pythonApiUrl}/load-model", [
                'yolo_path' => $modelPath,
                'sam_path' => config('computer_vision.sam_model_path', 'sam2_l.pt')
            ]);
            
            if ($response->successful()) {
                return $response->json();
            }
            
            throw new Exception('Failed to load model: ' . $response->body());
        } catch (Exception $e) {
            throw new Exception('Model loading failed: ' . $e->getMessage());
        }
    }
    
    public function runInference(string $imagePath, float $confidence = 0.5): array
    {
        try {
            $response = $this->httpClient->attach(
                'image', file_get_contents($imagePath), basename($imagePath)
            )->post("{$this->pythonApiUrl}/inference", [
                'confidence' => $confidence
            ]);
            
            if ($response->successful()) {
                return $response->json();
            }
            
            throw new Exception('Inference failed: ' . $response->body());
        } catch (Exception $e) {
            throw new Exception('Inference error: ' . $e->getMessage());
        }
    }
}
```

### **2.2 Controllers**
```php
// app/Http/Controllers/ComputerVisionDashboardController.php
class ComputerVisionDashboardController extends Controller
{
    private $computerVisionService;
    
    public function __construct(ComputerVisionService $computerVisionService)
    {
        $this->computerVisionService = $computerVisionService;
    }
    
    public function index()
    {
        $models = ComputerVisionModel::orderBy('created_at', 'desc')->get();
        $recentResults = InferenceResult::with('model')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
        $serviceStatus = $this->computerVisionService->checkServiceStatus();
            
        return view('computer-vision.dashboard', compact('models', 'recentResults', 'serviceStatus'));
    }
    
    public function uploadModel(Request $request)
    {
        $request->validate([
            'model_file' => 'required|file|mimes:pt,pth,onnx|max:102400', // 100MB max
            'model_name' => 'required|string|max:255',
            'model_type' => 'required|in:yolo,sam,hybrid',
            'version' => 'required|string|max:50',
            'description' => 'nullable|string|max:1000'
        ]);
        
        try {
            // Store model file
            $modelPath = $request->file('model_file')->store('models/computer-vision');
            $fullModelPath = storage_path("app/{$modelPath}");
            
            // Save to database
            $model = ComputerVisionModel::create([
                'name' => $request->model_name,
                'model_path' => $modelPath,
                'model_type' => $request->model_type,
                'version' => $request->version,
                'description' => $request->description,
                'file_size' => $request->file('model_file')->getSize(),
                'is_active' => false
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Model uploaded successfully',
                'model_id' => $model->id
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    public function activateModel(Request $request, $id)
    {
        try {
            $model = ComputerVisionModel::findOrFail($id);
            
            // Deactivate all other models
            ComputerVisionModel::where('is_active', true)->update(['is_active' => false]);
            
            // Load model in Python service
            $fullModelPath = storage_path("app/{$model->model_path}");
            $result = $this->computerVisionService->loadModel($fullModelPath, $model->model_type);
            
            if ($result['success']) {
                $model->update(['is_active' => true]);
                return response()->json([
                    'success' => true,
                    'message' => 'Model activated successfully'
                ]);
            } else {
                throw new Exception($result['error'] ?? 'Failed to load model');
            }
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    public function runInference(Request $request)
    {
        $request->validate([
            'model_id' => 'required|exists:computer_vision_models,id',
            'image' => 'required|image|max:10240', // 10MB max
            'confidence' => 'required|numeric|min:0.1|max:1.0'
        ]);
        
        try {
            // Store uploaded image
            $imagePath = $request->file('image')->store('inference-images');
            $fullImagePath = storage_path("app/{$imagePath}");
            
            // Get model
            $model = ComputerVisionModel::findOrFail($request->model_id);
            
            // Ensure model is active
            if (!$model->is_active) {
                throw new Exception('Model is not active. Please activate the model first.');
            }
            
            // Run inference
            $results = $this->computerVisionService->runInference($fullImagePath, $request->confidence);
            
            if (!$results['success']) {
                throw new Exception($results['error'] ?? 'Inference failed');
            }
            
            // Store results
            $inferenceResult = InferenceResult::create([
                'model_id' => $model->id,
                'image_path' => $imagePath,
                'original_filename' => $request->file('image')->getClientOriginalName(),
                'yolo_results' => $results['yolo_detections'] ?? [],
                'sam_results' => $results['sam_segmentations'] ?? [],
                'processing_time_ms' => $results['processing_time_ms'] ?? 0,
                'confidence_threshold' => $request->confidence,
                'total_detections' => $results['total_detections'] ?? 0
            ]);
            
            return response()->json([
                'success' => true,
                'result_id' => $inferenceResult->id,
                'results' => $results
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    public function getResult($id)
    {
        $result = InferenceResult::with('model')->findOrFail($id);
        return response()->json($result);
    }
}
```

## 📋 **Phase 3: Frontend Dashboard (Week 5-6)**

### **3.1 Routes**
```php
// routes/web.php
Route::prefix('computer-vision')->name('computer-vision.')->group(function () {
    Route::get('/dashboard', [ComputerVisionDashboardController::class, 'index'])->name('dashboard');
    Route::post('/upload-model', [ComputerVisionDashboardController::class, 'uploadModel'])->name('upload-model');
    Route::post('/activate-model/{id}', [ComputerVisionDashboardController::class, 'activateModel'])->name('activate-model');
    Route::post('/run-inference', [ComputerVisionDashboardController::class, 'runInference'])->name('run-inference');
    Route::get('/result/{id}', [ComputerVisionDashboardController::class, 'getResult'])->name('get-result');
});
```

### **3.2 Dashboard View**
```blade
{{-- resources/views/computer-vision/dashboard.blade.php --}}
@extends('layouts.app')

@section('title', 'Computer Vision Playground')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-4xl font-bold text-gray-900 mb-2">Computer Vision Playground</h1>
            <p class="text-gray-600">Test and compare YOLO+SAM models with real-time inference</p>
        </div>
        
        <!-- Service Status -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-8">
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold">Service Status</h2>
                <div class="flex items-center space-x-2">
                    <div class="w-3 h-3 rounded-full {{ $serviceStatus['model_loaded'] ?? false ? 'bg-green-500' : 'bg-red-500' }}"></div>
                    <span class="text-sm {{ $serviceStatus['model_loaded'] ?? false ? 'text-green-600' : 'text-red-600' }}">
                        {{ $serviceStatus['model_loaded'] ?? false ? 'Ready' : 'Not Ready' }}
                    </span>
                </div>
            </div>
            @if(isset($serviceStatus['error']))
                <p class="text-red-600 text-sm mt-2">{{ $serviceStatus['error'] }}</p>
            @endif
        </div>
        
        <!-- Model Management -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-8">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-xl font-semibold">Model Management</h2>
                <button id="uploadModelBtn" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                    Upload Model
                </button>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($models as $model)
                <div class="border rounded-lg p-4 {{ $model->is_active ? 'border-green-500 bg-green-50' : 'border-gray-300' }}">
                    <div class="flex items-center justify-between mb-2">
                        <h3 class="font-semibold text-lg">{{ $model->name }}</h3>
                        @if($model->is_active)
                            <span class="bg-green-100 text-green-800 text-xs px-2 py-1 rounded">Active</span>
                        @endif
                    </div>
                    <div class="space-y-1 text-sm text-gray-600">
                        <p><strong>Version:</strong> {{ $model->version }}</p>
                        <p><strong>Type:</strong> {{ ucfirst($model->model_type) }}</p>
                        <p><strong>Size:</strong> {{ number_format($model->file_size / 1024 / 1024, 2) }} MB</p>
                        <p><strong>Uploaded:</strong> {{ $model->created_at->format('M d, Y') }}</p>
                    </div>
                    @if($model->description)
                        <p class="text-sm text-gray-500 mt-2">{{ Str::limit($model->description, 100) }}</p>
                    @endif
                    <div class="mt-4 flex space-x-2">
                        @if(!$model->is_active)
                            <button onclick="activateModel({{ $model->id }})" 
                                    class="bg-green-600 text-white px-3 py-1 rounded text-sm hover:bg-green-700">
                                Activate
                            </button>
                        @endif
                        <button onclick="viewModelDetails({{ $model->id }})" 
                                class="bg-gray-600 text-white px-3 py-1 rounded text-sm hover:bg-gray-700">
                            Details
                        </button>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        
        <!-- Inference Testing -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-8">
            <h2 class="text-xl font-semibold mb-6">Run Inference</h2>
            <form id="inferenceForm" enctype="multipart/form-data">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Select Model</label>
                        <select name="model_id" id="modelSelect" class="w-full border rounded-lg px-3 py-2" required>
                            <option value="">Choose a model...</option>
                            @foreach($models->where('is_active', true) as $model)
                            <option value="{{ $model->id }}">{{ $model->name }} ({{ $model->version }})</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Confidence Threshold</label>
                        <input type="range" name="confidence" id="confidenceSlider" min="0.1" max="1.0" step="0.1" value="0.5" class="w-full">
                        <div class="flex justify-between text-sm text-gray-600">
                            <span>0.1</span>
                            <span id="confidenceValue">0.5</span>
                            <span>1.0</span>
                        </div>
                    </div>
                </div>
                
                <div class="mt-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Upload Image</label>
                    <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center">
                        <input type="file" name="image" id="imageInput" accept="image/*" class="hidden" required>
                        <div id="fileDropZone" class="cursor-pointer">
                            <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            <p class="mt-2 text-sm text-gray-600">Click to upload or drag and drop</p>
                            <p class="text-xs text-gray-500">PNG, JPG, JPEG up to 10MB</p>
                        </div>
                        <div id="filePreview" class="hidden mt-4">
                            <img id="previewImage" class="mx-auto h-32 w-32 object-cover rounded-lg">
                            <p id="fileName" class="mt-2 text-sm text-gray-600"></p>
                        </div>
                    </div>
                </div>
                
                <div class="mt-6">
                    <button type="submit" id="runInferenceBtn" class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed">
                        <span id="btnText">Run Inference</span>
                        <span id="btnLoading" class="hidden">Running Inference...</span>
                    </button>
                </div>
            </form>
        </div>
        
        <!-- Results Section -->
        <div id="resultsSection" class="bg-white rounded-lg shadow-md p-6" style="display: none;">
            <h2 class="text-xl font-semibold mb-4">Inference Results</h2>
            <div id="resultsContent"></div>
        </div>
        
        <!-- Recent Results -->
        @if($recentResults->count() > 0)
        <div class="bg-white rounded-lg shadow-md p-6 mt-8">
            <h2 class="text-xl font-semibold mb-4">Recent Results</h2>
            <div class="space-y-4">
                @foreach($recentResults as $result)
                <div class="border rounded-lg p-4 hover:bg-gray-50 cursor-pointer" onclick="viewResult({{ $result->id }})">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="font-semibold">{{ $result->model->name }}</h3>
                            <p class="text-sm text-gray-600">{{ $result->original_filename }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-medium">{{ $result->total_detections }} detections</p>
                            <p class="text-sm text-gray-600">{{ $result->processing_time_ms }}ms</p>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</div>

<!-- Upload Model Modal -->
<div id="uploadModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg p-6 w-full max-w-md">
            <h3 class="text-lg font-semibold mb-4">Upload New Model</h3>
            <form id="uploadForm" enctype="multipart/form-data">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Model Name</label>
                        <input type="text" name="model_name" class="w-full border rounded-lg px-3 py-2" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Version</label>
                        <input type="text" name="version" class="w-full border rounded-lg px-3 py-2" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Model Type</label>
                        <select name="model_type" class="w-full border rounded-lg px-3 py-2" required>
                            <option value="yolo">YOLO</option>
                            <option value="sam">SAM</option>
                            <option value="hybrid">Hybrid (YOLO+SAM)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                        <textarea name="description" class="w-full border rounded-lg px-3 py-2" rows="3"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Model File (.pt, .pth, .onnx)</label>
                        <input type="file" name="model_file" accept=".pt,.pth,.onnx" class="w-full border rounded-lg px-3 py-2" required>
                    </div>
                </div>
                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" onclick="closeUploadModal()" class="px-4 py-2 text-gray-600 border rounded-lg hover:bg-gray-50">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Upload</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Confidence slider
    const confidenceSlider = document.getElementById('confidenceSlider');
    const confidenceValue = document.getElementById('confidenceValue');
    confidenceSlider.addEventListener('input', function() {
        confidenceValue.textContent = this.value;
    });
    
    // File upload handling
    const imageInput = document.getElementById('imageInput');
    const fileDropZone = document.getElementById('fileDropZone');
    const filePreview = document.getElementById('filePreview');
    const previewImage = document.getElementById('previewImage');
    const fileName = document.getElementById('fileName');
    
    fileDropZone.addEventListener('click', () => imageInput.click());
    fileDropZone.addEventListener('dragover', (e) => {
        e.preventDefault();
        fileDropZone.classList.add('border-blue-500', 'bg-blue-50');
    });
    fileDropZone.addEventListener('dragleave', () => {
        fileDropZone.classList.remove('border-blue-500', 'bg-blue-50');
    });
    fileDropZone.addEventListener('drop', (e) => {
        e.preventDefault();
        fileDropZone.classList.remove('border-blue-500', 'bg-blue-50');
        const files = e.dataTransfer.files;
        if (files.length > 0) {
            handleFileSelect(files[0]);
        }
    });
    
    imageInput.addEventListener('change', (e) => {
        if (e.target.files.length > 0) {
            handleFileSelect(e.target.files[0]);
        }
    });
    
    function handleFileSelect(file) {
        if (file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = (e) => {
                previewImage.src = e.target.result;
                fileName.textContent = file.name;
                filePreview.classList.remove('hidden');
            };
            reader.readAsDataURL(file);
        }
    }
    
    // Inference form submission
    const inferenceForm = document.getElementById('inferenceForm');
    const runInferenceBtn = document.getElementById('runInferenceBtn');
    const btnText = document.getElementById('btnText');
    const btnLoading = document.getElementById('btnLoading');
    
    inferenceForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        runInferenceBtn.disabled = true;
        btnText.classList.add('hidden');
        btnLoading.classList.remove('hidden');
        
        const formData = new FormData(this);
        
        try {
            const response = await fetch('/computer-vision/run-inference', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });
            
            const data = await response.json();
            
            if (data.success) {
                displayResults(data.results);
            } else {
                alert('Inference failed: ' + (data.error || 'Unknown error'));
            }
        } catch (error) {
            alert('Error: ' + error.message);
        } finally {
            runInferenceBtn.disabled = false;
            btnText.classList.remove('hidden');
            btnLoading.classList.add('hidden');
        }
    });
    
    // Upload modal
    const uploadModal = document.getElementById('uploadModal');
    const uploadForm = document.getElementById('uploadForm');
    
    document.getElementById('uploadModelBtn').addEventListener('click', () => {
        uploadModal.classList.remove('hidden');
    });
    
    function closeUploadModal() {
        uploadModal.classList.add('hidden');
        uploadForm.reset();
    }
    
    uploadForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        try {
            const response = await fetch('/computer-vision/upload-model', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });
            
            const data = await response.json();
            
            if (data.success) {
                alert('Model uploaded successfully!');
                closeUploadModal();
                location.reload();
            } else {
                alert('Upload failed: ' + (data.error || 'Unknown error'));
            }
        } catch (error) {
            alert('Error: ' + error.message);
        }
    });
    
    function displayResults(results) {
        const resultsSection = document.getElementById('resultsSection');
        const resultsContent = document.getElementById('resultsContent');
        
        resultsContent.innerHTML = `
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div>
                    <h3 class="text-lg font-semibold mb-3">Detection Results</h3>
                    <div class="space-y-2">
                        <p><strong>Total Detections:</strong> ${results.total_detections}</p>
                        <p><strong>Processing Time:</strong> ${results.processing_time_ms}ms</p>
                        <p><strong>Image Dimensions:</strong> ${results.image_dimensions ? `${results.image_dimensions.width}x${results.image_dimensions.height}` : 'N/A'}</p>
                    </div>
                    
                    <div class="mt-4">
                        <h4 class="font-semibold mb-2">Detected Objects:</h4>
                        <div class="space-y-1 max-h-40 overflow-y-auto">
                            ${results.yolo_detections.map(detection => `
                                <div class="flex justify-between items-center p-2 bg-gray-50 rounded">
                                    <span>${detection.class_name}</span>
                                    <span class="text-sm text-gray-600">${(detection.confidence * 100).toFixed(1)}%</span>
                                </div>
                            `).join('')}
                        </div>
                    </div>
                </div>
                
                <div>
                    <h3 class="text-lg font-semibold mb-3">Segmentation Results</h3>
                    <div class="space-y-2">
                        <p><strong>Total Segments:</strong> ${results.sam_segmentations.length}</p>
                    </div>
                    
                    <div class="mt-4">
                        <h4 class="font-semibold mb-2">Segment Details:</h4>
                        <div class="space-y-1 max-h-40 overflow-y-auto">
                            ${results.sam_segmentations.map((segment, index) => `
                                <div class="flex justify-between items-center p-2 bg-gray-50 rounded">
                                    <span>Segment ${index + 1}</span>
                                    <span class="text-sm text-gray-600">${segment.mask_percentage.toFixed(1)}%</span>
                                </div>
                            `).join('')}
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        resultsSection.style.display = 'block';
        resultsSection.scrollIntoView({ behavior: 'smooth' });
    }
});

// Global functions
function activateModel(modelId) {
    if (confirm('Are you sure you want to activate this model?')) {
        fetch(`/computer-vision/activate-model/${modelId}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Model activated successfully!');
                location.reload();
            } else {
                alert('Activation failed: ' + (data.error || 'Unknown error'));
            }
        })
        .catch(error => {
            alert('Error: ' + error.message);
        });
    }
}

function viewModelDetails(modelId) {
    // TODO: Implement model details modal
    alert('Model details feature coming soon!');
}

function viewResult(resultId) {
    // TODO: Implement result details modal
    alert('Result details feature coming soon!');
}
</script>
@endsection
```

## 📋 **Phase 4: Advanced Features (Week 7-8)**

### **4.1 Performance Monitoring**
- Real-time processing time tracking
- Model accuracy metrics
- Resource usage monitoring
- Batch processing capabilities

### **4.2 Visual Results Enhancement**
- Interactive bounding box visualization
- Segmentation mask overlay
- Side-by-side comparison
- Export capabilities (JSON, images)

### **4.3 Model Comparison Tools**
- A/B testing between models
- Performance benchmarking
- Accuracy comparison charts
- Processing time analysis

## 🎯 **Success Metrics**

### **Phase 1-2 (Foundation)**
- ✅ Python API service running on port 8001
- ✅ Laravel integration with Python service
- ✅ Model upload and management system
- ✅ Basic inference functionality

### **Phase 3-4 (Dashboard & Features)**
- ✅ Complete dashboard interface
- ✅ Real-time inference results
- ✅ Visual result display
- ✅ Performance monitoring
- ✅ Model comparison tools

## 🚀 **Future Enhancements (v2)**

### **Advanced Features**
- 🔄 **Batch Processing**: Multiple image inference
- 🔄 **Model Training**: On-platform model fine-tuning
- 🔄 **Cloud Integration**: AWS/GCP model storage
- 🔄 **API Documentation**: Swagger/OpenAPI docs
- 🔄 **WebSocket Integration**: Real-time updates
- 🔄 **Mobile App**: React Native companion app

### **Production Features**
- 🔄 **Queue System**: Redis-based job queue
- 🔄 **Caching**: Model and result caching
- 🔄 **Monitoring**: Prometheus + Grafana
- 🔄 **Security**: API authentication and rate limiting
- 🔄 **Scaling**: Kubernetes deployment
- 🔄 **Backup**: Automated model and data backup

---

**Status**: 🚀 **PLANNING PHASE COMPLETE**
**Next**: Phase 1 - Foundation Development
**Last Updated**: September 9, 2025
