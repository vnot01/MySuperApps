<?php

namespace App\Http\Controllers;

use App\Services\GeminiVisionService;
use App\Models\GeminiConfig;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class GeminiVisionController extends Controller
{
    protected GeminiVisionService $geminiService;

    public function __construct(GeminiVisionService $geminiService)
    {
        $this->geminiService = $geminiService;
    }

    /**
     * Test Gemini Vision with sample images
     */
    public function testAnalysis(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'image_path' => 'required|string',
                'analysis_type' => 'in:single,multiple,spatial',
                'config_id' => 'nullable|exists:gemini_configs,id',
                'options' => 'nullable|array'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $imagePath = $request->input('image_path');
            $analysisType = $request->input('analysis_type', 'single');
            $configId = $request->input('config_id');
            $options = $request->input('options', []);

            // Set specific configuration if provided
            if ($configId) {
                $config = GeminiConfig::find($configId);
                if ($config && $config->is_active) {
                    $this->geminiService->setConfig($config);
                }
            }

            // Perform analysis based on type
            switch ($analysisType) {
                case 'multiple':
                    $result = $this->geminiService->analyzeMultipleWasteItems($imagePath, $options);
                    break;
                case 'spatial':
                    $result = $this->geminiService->getSpatialUnderstanding($imagePath, $options);
                    break;
                default:
                    $result = $this->geminiService->analyzeWasteImage($imagePath, $options);
                    break;
            }

            return response()->json([
                'success' => true,
                'data' => $result,
                'analysis_type' => $analysisType,
                'config_used' => $this->geminiService->getAvailableConfigs()[0] ?? null,
                'timestamp' => now()->toISOString()
            ]);

        } catch (\Exception $e) {
            Log::error('Gemini Vision test failed', [
                'error' => $e->getMessage(),
                'request' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Analysis failed: ' . $e->getMessage(),
                'timestamp' => now()->toISOString()
            ], 500);
        }
    }

    /**
     * Upload and analyze image
     */
    public function uploadAndAnalyze(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:10240', // 10MB max
                'analysis_type' => 'in:single,multiple,spatial',
                'config_id' => 'nullable|exists:gemini_configs,id',
                'options' => 'nullable|array'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Store uploaded image
            $image = $request->file('image');
            $imagePath = $image->store('gemini-test-images', 'public');
            
            $analysisType = $request->input('analysis_type', 'single');
            $configId = $request->input('config_id');
            $options = $request->input('options', []);

            // Set specific configuration if provided
            if ($configId) {
                $config = GeminiConfig::find($configId);
                if ($config && $config->is_active) {
                    $this->geminiService->setConfig($config);
                }
            }

            // Perform analysis
            switch ($analysisType) {
                case 'multiple':
                    $result = $this->geminiService->analyzeMultipleWasteItems($imagePath, $options);
                    break;
                case 'spatial':
                    $result = $this->geminiService->getSpatialUnderstanding($imagePath, $options);
                    break;
                default:
                    $result = $this->geminiService->analyzeWasteImage($imagePath, $options);
                    break;
            }

            return response()->json([
                'success' => true,
                'data' => $result,
                'image_path' => $imagePath,
                'image_url' => Storage::url($imagePath),
                'analysis_type' => $analysisType,
                'config_used' => $this->geminiService->getAvailableConfigs()[0] ?? null,
                'timestamp' => now()->toISOString()
            ]);

        } catch (\Exception $e) {
            Log::error('Gemini Vision upload and analyze failed', [
                'error' => $e->getMessage(),
                'request' => $request->except(['image'])
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Upload and analysis failed: ' . $e->getMessage(),
                'timestamp' => now()->toISOString()
            ], 500);
        }
    }

    /**
     * Get available Gemini configurations
     */
    public function getConfigurations(): JsonResponse
    {
        try {
            $configs = GeminiConfig::active()->orderBy('priority', 'desc')->get();
            
            return response()->json([
                'success' => true,
                'data' => $configs,
                'default_config' => GeminiConfig::getDefault(),
                'timestamp' => now()->toISOString()
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to get Gemini configurations', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to get configurations: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Test with sample images
     */
    public function testSampleImages(): JsonResponse
    {
        try {
            $sampleImages = [
                'test_image1.jpg',
                'test_image2.png',
                'test_image3.jpg',
                'test_image4.png'
            ];

            $results = [];
            $configs = GeminiConfig::active()->orderBy('priority', 'desc')->get();

            foreach ($sampleImages as $imageName) {
                $imagePath = "images/{$imageName}";
                
                // Test with default configuration
                $result = $this->geminiService->analyzeWasteImage($imagePath);
                
                $results[] = [
                    'image_name' => $imageName,
                    'image_path' => $imagePath,
                    'analysis' => $result,
                    'config_used' => $this->geminiService->getAvailableConfigs()[0] ?? null
                ];
            }

            return response()->json([
                'success' => true,
                'data' => $results,
                'total_images' => count($sampleImages),
                'available_configs' => $configs,
                'timestamp' => now()->toISOString()
            ]);

        } catch (\Exception $e) {
            Log::error('Sample images test failed', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Sample images test failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Compare different Gemini models
     */
    public function compareModels(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'image_path' => 'required|string',
                'analysis_type' => 'in:single,multiple,spatial',
                'options' => 'nullable|array'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $imagePath = $request->input('image_path');
            $analysisType = $request->input('analysis_type', 'single');
            $options = $request->input('options', []);

            $configs = GeminiConfig::active()->orderBy('priority', 'desc')->get();
            $results = [];

            foreach ($configs as $config) {
                $this->geminiService->setConfig($config);
                
                $startTime = microtime(true);
                
                switch ($analysisType) {
                    case 'multiple':
                        $result = $this->geminiService->analyzeMultipleWasteItems($imagePath, $options);
                        break;
                    case 'spatial':
                        $result = $this->geminiService->getSpatialUnderstanding($imagePath, $options);
                        break;
                    default:
                        $result = $this->geminiService->analyzeWasteImage($imagePath, $options);
                        break;
                }
                
                $endTime = microtime(true);
                $processingTime = round(($endTime - $startTime) * 1000, 2); // milliseconds

                // Determine success based on analysis type
                $success = false;
                if (!empty($result)) {
                    switch ($analysisType) {
                        case 'multiple':
                            $success = isset($result['total_items']) && $result['total_items'] > 0;
                            break;
                        case 'spatial':
                            $success = isset($result['detections']) && count($result['detections']) > 0;
                            break;
                        default:
                            $success = isset($result['confidence']) && $result['confidence'] > 0;
                            break;
                    }
                }

                $results[] = [
                    'config' => $config,
                    'result' => $result,
                    'processing_time_ms' => $processingTime,
                    'success' => $success
                ];
            }

            return response()->json([
                'success' => true,
                'data' => $results,
                'image_path' => $imagePath,
                'analysis_type' => $analysisType,
                'total_configs_tested' => count($configs),
                'timestamp' => now()->toISOString()
            ]);

        } catch (\Exception $e) {
            Log::error('Model comparison failed', [
                'error' => $e->getMessage(),
                'request' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Model comparison failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get system status
     */
    public function getStatus(): JsonResponse
    {
        try {
            $configs = GeminiConfig::active()->count();
            $defaultConfig = GeminiConfig::getDefault();
            $apiKey = config('services.gemini.api_key', env('GOOGLE_API_KEY'));
            
            return response()->json([
                'success' => true,
                'data' => [
                    'api_key_configured' => !empty($apiKey),
                    'active_configurations' => $configs,
                    'default_configuration' => $defaultConfig,
                    'available_endpoints' => [
                        'test_analysis' => '/api/v2/gemini/test-analysis',
                        'upload_analyze' => '/api/v2/gemini/upload-analyze',
                        'sample_images' => '/api/v2/gemini/sample-images',
                        'compare_models' => '/api/v2/gemini/compare-models',
                        'configurations' => '/api/v2/gemini/configurations'
                    ],
                    'sample_images_available' => [
                        'test_image1.jpg',
                        'test_image2.png', 
                        'test_image3.jpg',
                        'test_image4.png'
                    ]
                ],
                'timestamp' => now()->toISOString()
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to get Gemini status', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to get status: ' . $e->getMessage()
            ], 500);
        }
    }
}
