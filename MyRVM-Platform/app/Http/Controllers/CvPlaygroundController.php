<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class CvPlaygroundController extends Controller
{
    /**
     * Display the CV Playground dashboard
     */
    public function index()
    {
        return view('cv-playground.index');
    }
    
    /**
     * Run CV model test
     */
    public function runTest(Request $request)
    {
        // Manual validation to handle .pt files properly
        if (!$request->hasFile('model_file')) {
            return response()->json([
                'success' => false,
                'error' => 'Model file is required'
            ], 422);
        }
        
        if (!$request->hasFile('image_file')) {
            return response()->json([
                'success' => false,
                'error' => 'Image file is required'
            ], 422);
        }
        
        $modelFile = $request->file('model_file');
        $imageFile = $request->file('image_file');
        
        // Check file extensions manually
        $modelExtension = strtolower($modelFile->getClientOriginalExtension());
        if (!in_array($modelExtension, ['pt', 'pth', 'onnx'])) {
            return response()->json([
                'success' => false,
                'error' => 'Model file must be .pt, .pth, or .onnx format'
            ], 422);
        }
        
        // Check file sizes
        if ($modelFile->getSize() > 102400 * 1024) { // 100MB
            return response()->json([
                'success' => false,
                'error' => 'Model file is too large (max 100MB)'
            ], 422);
        }
        
        if ($imageFile->getSize() > 10240 * 1024) { // 10MB
            return response()->json([
                'success' => false,
                'error' => 'Image file is too large (max 10MB)'
            ], 422);
        }
        
        // Validate confidence
        $confidence = $request->input('confidence');
        if (!is_numeric($confidence) || $confidence < 0.1 || $confidence > 1.0) {
            return response()->json([
                'success' => false,
                'error' => 'Confidence must be a number between 0.1 and 1.0'
            ], 422);
        }
        
        try {
            Log::info('CV Playground: Starting test', [
                'model_file' => $request->file('model_file')->getClientOriginalName(),
                'image_file' => $request->file('image_file')->getClientOriginalName(),
                'confidence' => $request->confidence
            ]);
            
            // Store uploaded files with original extensions
            $modelFileName = $modelFile->getClientOriginalName();
            $imageFileName = $imageFile->getClientOriginalName();
            
            $modelPath = $request->file('model_file')->storeAs('cv_models', $modelFileName, 'local');
            $imagePath = $request->file('image_file')->storeAs('cv_test_images', $imageFileName, 'local');
            
            // Get absolute paths
            $absoluteModelPath = Storage::disk('local')->path($modelPath);
            $absoluteImagePath = Storage::disk('local')->path($imagePath);
            
            // Create output directory
            $outputDirName = 'cv_test_results/' . uniqid();
            Storage::disk('local')->makeDirectory($outputDirName);
            $absoluteOutputDir = Storage::disk('local')->path($outputDirName);
            
            Log::info('CV Playground: Files stored', [
                'model_path' => $absoluteModelPath,
                'image_path' => $absoluteImagePath,
                'output_dir' => $absoluteOutputDir
            ]);
            
            // Run Python script
            $scriptPath = storage_path('app/cv_scripts/cv_tester.py');
            
            // Execute Python script using shell_exec (like in the reference articles)
            $command = "python3 " . escapeshellarg($scriptPath) . " " . escapeshellarg($absoluteModelPath) . " " . escapeshellarg($absoluteImagePath) . " " . escapeshellarg($absoluteOutputDir) . " " . escapeshellarg($request->confidence);
            $output = shell_exec($command);
            
            Log::info('CV Playground: Python script executed', [
                'output_length' => strlen($output),
                'command' => $command
            ]);
            
            if (!$output) {
                throw new \Exception('Python script execution failed or returned no output');
            }
            
            $outputData = json_decode($output, true);
            
            // Debug JSON parsing
            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error('CV Playground: JSON decode error', [
                    'error' => json_last_error_msg(),
                    'output_preview' => substr($output, 0, 500)
                ]);
                throw new \Exception('Failed to parse JSON from Python script: ' . json_last_error_msg());
            }
            
            if ($outputData && $outputData['status'] === 'success') {
                // Convert file paths to URLs
                $outputData['output_image_urls'] = $this->generateImageUrls($outputData['output_images'], $outputDirName);
                
                // Store result in session for display
                session(['cv_test_result' => $outputData]);
                
                Log::info('CV Playground: Test completed successfully', [
                    'total_detections' => count($outputData['yolo_results']['detections']),
                    'timestamp' => $outputData['timestamp']
                ]);
                
                return response()->json([
                    'success' => true,
                    'result' => $outputData
                ]);
            } else {
                $errorMsg = $outputData['message'] ?? 'Unknown error in Python script';
                Log::error('CV Playground: Python script error', ['error' => $errorMsg]);
                throw new \Exception($errorMsg);
            }
            
        } catch (\Exception $e) {
            Log::error("CV Playground Error", [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Serve result files (images)
     */
    public function serveResult($filepath)
    {
        $decodedPath = base64_decode($filepath);
        
        // Security validation
        if (Str::contains($decodedPath, '..') || !Str::startsWith($decodedPath, 'cv_test_results/')) {
            abort(404);
        }
        
        if (!Storage::disk('local')->exists($decodedPath)) {
            abort(404);
        }
        
        return response()->file(Storage::disk('local')->path($decodedPath));
    }
    
    /**
     * Generate image URLs for frontend
     */
    private function generateImageUrls($outputImages, $outputDirName)
    {
        $urls = [];
        
        foreach ($outputImages as $key => $imagePath) {
            if ($imagePath && file_exists($imagePath)) {
                $relativePath = Str::after($imagePath, Storage::disk('local')->path(''));
                $urls[$key] = route('cv-playground.serve-result', [
                    'filepath' => base64_encode($relativePath)
                ]);
            }
        }
        
        return $urls;
    }
    
    /**
     * Get recent test results
     */
    private function getRecentResults()
    {
        // Get recent test results from storage
        $resultsDir = storage_path('app/cv_test_results');
        
        if (!is_dir($resultsDir)) {
            return [];
        }
        
        $results = [];
        $directories = array_slice(scandir($resultsDir), 2); // Remove . and ..
        
        foreach (array_slice($directories, -10) as $dir) { // Last 10 results
            $resultPath = $resultsDir . '/' . $dir . '/result.json';
            if (file_exists($resultPath)) {
                $result = json_decode(file_get_contents($resultPath), true);
                if ($result) {
                    $results[] = $result;
                }
            }
        }
        
        return array_reverse($results); // Most recent first
    }
}