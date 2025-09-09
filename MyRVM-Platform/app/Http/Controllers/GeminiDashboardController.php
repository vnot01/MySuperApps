<?php

namespace App\Http\Controllers;

use App\Services\GeminiVisionService;
use App\Models\GeminiConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class GeminiDashboardController extends Controller
{
    protected GeminiVisionService $geminiService;

    public function __construct(GeminiVisionService $geminiService)
    {
        $this->geminiService = $geminiService;
    }

    /**
     * Show Gemini Vision Dashboard
     */
    public function index(Request $request)
    {
        $configs = GeminiConfig::active()->orderBy('priority', 'desc')->get();
        $defaultConfig = GeminiConfig::getDefault();
        
        // Get sample images
        $sampleImages = $this->getSampleImages();
        
        // Get recent test results with pagination
        $allResults = session('gemini_test_results', []);
        $perPage = 5; // Show 5 results per page
        $currentPage = $request->get('page', 1);
        $offset = ($currentPage - 1) * $perPage;
        $recentResults = array_slice($allResults, $offset, $perPage);
        
        // Calculate pagination info
        $totalResults = count($allResults);
        $totalPages = ceil($totalResults / $perPage);
        $hasNextPage = $currentPage < $totalPages;
        $hasPrevPage = $currentPage > 1;
        
        return view('gemini.dashboard', compact(
            'configs', 
            'defaultConfig', 
            'sampleImages', 
            'recentResults',
            'currentPage',
            'totalPages',
            'hasNextPage',
            'hasPrevPage',
            'totalResults'
        ));
    }

    /**
     * Process image analysis
     */
    public function analyzeImage(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
            'analysis_type' => 'required|in:single,multiple,spatial',
            'config_id' => 'nullable|exists:gemini_configs,id',
            'options' => 'nullable|array'
        ]);

        try {
            // Store uploaded image
            $image = $request->file('image');
            $imagePath = $image->store('gemini-test-images', 'public');
            
            $analysisType = $request->input('analysis_type');
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
            $processingTime = round(($endTime - $startTime) * 1000, 2);

            // Store result in session for dashboard display
            $testResult = [
                'id' => uniqid(),
                'timestamp' => now()->toISOString(),
                'image_path' => $imagePath,
                'image_url' => asset('storage/' . $imagePath),
                'analysis_type' => $analysisType,
                'config_used' => $this->geminiService->getCurrentConfig(),
                'result' => $result,
                'processing_time_ms' => $processingTime,
                'success' => $this->determineAnalysisSuccess($result, $analysisType)
            ];

            // Store in session (keep last 10 results)
            $recentResults = session('gemini_test_results', []);
            array_unshift($recentResults, $testResult);
            $recentResults = array_slice($recentResults, 0, 10);
            session(['gemini_test_results' => $recentResults]);

            return response()->json([
                'success' => true,
                'data' => $testResult
            ]);

        } catch (\Exception $e) {
            Log::error('Dashboard image analysis failed', [
                'error' => $e->getMessage(),
                'request' => $request->except(['image'])
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Analysis failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Test with sample image
     */
    public function testSampleImage(Request $request)
    {
        $request->validate([
            'image_name' => 'required|string',
            'analysis_type' => 'required|in:single,multiple,spatial',
            'config_id' => 'nullable|exists:gemini_configs,id',
            'options' => 'nullable|array'
        ]);

        try {
            $imageName = $request->input('image_name');
            $analysisType = $request->input('analysis_type');
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
            $startTime = microtime(true);
            
            switch ($analysisType) {
                case 'multiple':
                    $result = $this->geminiService->analyzeMultipleWasteItems($imageName, $options);
                    break;
                case 'spatial':
                    $result = $this->geminiService->getSpatialUnderstanding($imageName, $options);
                    break;
                default:
                    $result = $this->geminiService->analyzeWasteImage($imageName, $options);
                    break;
            }
            
            $endTime = microtime(true);
            $processingTime = round(($endTime - $startTime) * 1000, 2);

            // Store result in session
            $testResult = [
                'id' => uniqid(),
                'timestamp' => now()->toISOString(),
                'image_path' => $imageName,
                'image_url' => asset("storage/images/{$imageName}"),
                'analysis_type' => $analysisType,
                'config_used' => $this->geminiService->getCurrentConfig(),
                'result' => $result,
                'processing_time_ms' => $processingTime,
                'success' => $this->determineAnalysisSuccess($result, $analysisType)
            ];

            // Store in session
            $recentResults = session('gemini_test_results', []);
            array_unshift($recentResults, $testResult);
            $recentResults = array_slice($recentResults, 0, 10);
            session(['gemini_test_results' => $recentResults]);

            return response()->json([
                'success' => true,
                'data' => $testResult
            ]);

        } catch (\Exception $e) {
            Log::error('Dashboard sample image test failed', [
                'error' => $e->getMessage(),
                'request' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Test failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Compare models with sample image
     */
    public function compareModels(Request $request)
    {
        $request->validate([
            'image_name' => 'required|string',
            'analysis_type' => 'required|in:single,multiple,spatial',
            'options' => 'nullable|array'
        ]);

        try {
            $imageName = $request->input('image_name');
            $analysisType = $request->input('analysis_type');
            $options = $request->input('options', []);

            $configs = GeminiConfig::active()->orderBy('priority', 'desc')->get();
            $results = [];

            foreach ($configs as $config) {
                $this->geminiService->setConfig($config);
                
                $startTime = microtime(true);
                
                switch ($analysisType) {
                    case 'multiple':
                        $result = $this->geminiService->analyzeMultipleWasteItems($imageName, $options);
                        break;
                    case 'spatial':
                        $result = $this->geminiService->getSpatialUnderstanding($imageName, $options);
                        break;
                    default:
                        $result = $this->geminiService->analyzeWasteImage($imageName, $options);
                        break;
                }
                
                $endTime = microtime(true);
                $processingTime = round(($endTime - $startTime) * 1000, 2);

                $results[] = [
                    'config' => $config,
                    'result' => $result,
                    'processing_time_ms' => $processingTime,
                    'success' => $this->determineAnalysisSuccess($result, $analysisType)
                ];
            }

            return response()->json([
                'success' => true,
                'data' => $results,
                'image_name' => $imageName,
                'analysis_type' => $analysisType
            ]);

        } catch (\Exception $e) {
            Log::error('Dashboard model comparison failed', [
                'error' => $e->getMessage(),
                'request' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Comparison failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get system status
     */
    public function getStatus()
    {
        try {
            $configs = GeminiConfig::active()->count();
            $defaultConfig = GeminiConfig::getDefault();
            $apiKey = config('services.gemini.api_key', env('GOOGLE_API_KEY'));
            $sampleImages = $this->getSampleImages();
            
            return response()->json([
                'success' => true,
                'data' => [
                    'api_key_configured' => !empty($apiKey),
                    'active_configurations' => $configs,
                    'default_configuration' => $defaultConfig,
                    'sample_images_count' => count($sampleImages),
                    'recent_tests_count' => count(session('gemini_test_results', [])),
                    'timestamp' => now()->toISOString()
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Clear test results
     */
    public function clearResults()
    {
        session()->forget('gemini_test_results');
        
        return response()->json([
            'success' => true,
            'message' => 'Test results cleared successfully'
        ]);
    }

    /**
     * Get sample images
     */
    protected function getSampleImages(): array
    {
        $sampleImages = [
            'test_image1.jpg',
            'test_image2.png',
            'test_image3.jpg',
            'test_image4.png'
        ];

        $images = [];
        foreach ($sampleImages as $imageName) {
            $images[] = [
                'name' => $imageName,
                'url' => asset("storage/images/{$imageName}"),
                'path' => $imageName
            ];
        }

        return $images;
    }

    /**
     * Determine analysis success based on type
     */
    protected function determineAnalysisSuccess(array $result, string $analysisType): bool
    {
        if (empty($result)) {
            return false;
        }

        switch ($analysisType) {
            case 'multiple':
                return isset($result['total_items']) && $result['total_items'] > 0;
            case 'spatial':
                return isset($result['detections']) && count($result['detections']) > 0;
            default:
                return isset($result['confidence']) && $result['confidence'] > 0;
        }
    }
}
