<?php

namespace App\Services;

use App\Models\GeminiConfig;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class GeminiVisionService
{
    protected string $apiKey;
    protected ?GeminiConfig $config;

    public function __construct()
    {
        $this->apiKey = config('services.gemini.api_key', env('GOOGLE_API_KEY'));
        $this->config = GeminiConfig::getDefault();
    }

    /**
     * Analyze waste image using Gemini Vision
     *
     * @param string $imagePath Path to the image file
     * @param array $options Analysis options
     * @return array
     */
    public function analyzeWasteImage(string $imagePath, array $options = []): array
    {
        try {
            // Load and prepare image
            $imageData = $this->prepareImage($imagePath);
            
            // Build prompt for waste analysis
            $prompt = $this->buildWasteAnalysisPrompt($options);
            
            // Make API request
            $response = $this->makeApiRequest($imageData, $prompt);
            
            // Parse and validate response
            $result = $this->parseResponse($response);
            
            Log::info('Gemini Vision analysis completed', [
                'image_path' => $imagePath,
                'model' => $this->config->name ?? 'default',
                'confidence' => $result['confidence'] ?? 0,
                'waste_type' => $result['waste_type'] ?? 'unknown'
            ]);

            return $result;

        } catch (\Exception $e) {
            Log::error('Gemini Vision analysis failed', [
                'image_path' => $imagePath,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return $this->getDefaultAnalysis();
        }
    }

    /**
     * Analyze multiple waste items in image
     *
     * @param string $imagePath
     * @param array $options
     * @return array
     */
    public function analyzeMultipleWasteItems(string $imagePath, array $options = []): array
    {
        try {
            $imageData = $this->prepareImage($imagePath);
            $prompt = $this->buildMultipleWasteAnalysisPrompt($options);
            
            $response = $this->makeApiRequest($imageData, $prompt);
            $result = $this->parseMultipleItemsResponse($response);
            
            Log::info('Gemini Vision multiple items analysis completed', [
                'image_path' => $imagePath,
                'items_count' => count($result['items'] ?? []),
                'model' => $this->config->name ?? 'default'
            ]);

            return $result;

        } catch (\Exception $e) {
            Log::error('Gemini Vision multiple items analysis failed', [
                'image_path' => $imagePath,
                'error' => $e->getMessage()
            ]);

            return $this->getDefaultMultipleAnalysis();
        }
    }

    /**
     * Get spatial understanding (bounding boxes and masks)
     *
     * @param string $imagePath
     * @param array $options
     * @return array
     */
    public function getSpatialUnderstanding(string $imagePath, array $options = []): array
    {
        try {
            $imageData = $this->prepareImage($imagePath);
            $prompt = $this->buildSpatialAnalysisPrompt($options);
            
            $response = $this->makeApiRequest($imageData, $prompt);
            $result = $this->parseSpatialResponse($response);
            
            Log::info('Gemini Vision spatial analysis completed', [
                'image_path' => $imagePath,
                'bounding_boxes' => count($result['bounding_boxes'] ?? []),
                'masks' => count($result['masks'] ?? []),
                'model' => $this->config->name ?? 'default'
            ]);

            return $result;

        } catch (\Exception $e) {
            Log::error('Gemini Vision spatial analysis failed', [
                'image_path' => $imagePath,
                'error' => $e->getMessage()
            ]);

            return $this->getDefaultSpatialAnalysis();
        }
    }

    /**
     * Prepare image for API request
     */
    protected function prepareImage(string $imagePath): array
    {
        // Check if it's a URL or file path
        if (filter_var($imagePath, FILTER_VALIDATE_URL)) {
            $imageData = base64_encode(file_get_contents($imagePath));
        } else {
            // Handle different path formats
            $fullPath = null;
            
            // Try different path combinations
            $possiblePaths = [
                $imagePath, // Original path
                public_path($imagePath), // Public path
                storage_path('app/public/' . $imagePath), // Storage path
                storage_path('app/public/images/' . basename($imagePath)), // Images directory
                public_path('storage/' . $imagePath), // Public storage symlink
            ];
            
            foreach ($possiblePaths as $path) {
                if (file_exists($path)) {
                    $fullPath = $path;
                    break;
                }
            }
            
            if (!$fullPath) {
                throw new \Exception("Image file not found. Tried paths: " . implode(', ', $possiblePaths));
            }
            
            $imageData = base64_encode(file_get_contents($fullPath));
        }

        // Detect MIME type
        $mimeType = $this->detectMimeType($imageData);
        
        return [
            'mime_type' => $mimeType,
            'data' => $imageData
        ];
    }

    /**
     * Build prompt for waste analysis
     */
    protected function buildWasteAnalysisPrompt(array $options = []): string
    {
        $wasteTypes = $options['waste_types'] ?? ['plastic', 'glass', 'metal', 'paper', 'mixed'];
        $wasteTypesStr = implode(', ', $wasteTypes);
        
        $prompt = "You are an expert waste analysis AI for a Reverse Vending Machine (RVM) system. 
        
Analyze the image and identify the waste item with the following requirements:

1. **Waste Classification**: Classify the item as one of: {$wasteTypesStr}
2. **Condition Assessment**: Determine the condition (good, fair, poor, damaged)
3. **Quality Grade**: Assign a quality grade (A, B, C, D) where:
   - A: Excellent condition, clean, undamaged
   - B: Good condition, minor wear, clean
   - C: Fair condition, some wear or dirt
   - D: Poor condition, damaged, dirty
4. **Weight Estimation**: Estimate weight in grams (be realistic)
5. **Quantity**: Count the number of items (usually 1 for single items)
6. **Confidence**: Provide confidence score (0-100)

**Important**: 
- Focus on recyclable materials only
- Be precise with classifications
- Consider contamination levels
- Provide realistic weight estimates

Respond in JSON format:
{
  \"waste_type\": \"plastic\",
  \"condition\": \"good\",
  \"quality_grade\": \"B\",
  \"estimated_weight_grams\": 25.5,
  \"quantity\": 1,
  \"confidence\": 85,
  \"analysis_details\": {
    \"material\": \"PET plastic bottle\",
    \"color\": \"clear\",
    \"size\": \"500ml\",
    \"contamination_level\": \"low\",
    \"recyclability\": \"high\"
  }
}";

        return $prompt;
    }

    /**
     * Build prompt for multiple waste items analysis
     */
    protected function buildMultipleWasteAnalysisPrompt(array $options = []): string
    {
        $maxItems = $options['max_items'] ?? 10;
        
        $prompt = "You are an expert waste analysis AI for a Reverse Vending Machine (RVM) system.

Analyze the image and identify ALL waste items (maximum {$maxItems} items). For each item, provide:

1. **Waste Type**: plastic, glass, metal, paper, mixed
2. **Condition**: good, fair, poor, damaged
3. **Quality Grade**: A, B, C, D
4. **Estimated Weight**: in grams
5. **Confidence**: 0-100

**Important**: 
- Focus on recyclable materials only
- Be precise with classifications
- Consider contamination levels
- Provide realistic weight estimates

Respond in JSON format:
{
  \"items\": [
    {
      \"id\": 1,
      \"waste_type\": \"plastic\",
      \"condition\": \"good\",
      \"quality_grade\": \"B\",
      \"estimated_weight_grams\": 25.5,
      \"confidence\": 85,
      \"label\": \"PET bottle\"
    }
  ],
  \"total_items\": 1,
  \"analysis_summary\": {
    \"total_weight_grams\": 25.5,
    \"recyclable_items\": 1,
    \"non_recyclable_items\": 0
  }
}";

        return $prompt;
    }

    /**
     * Build prompt for spatial understanding
     */
    protected function buildSpatialAnalysisPrompt(array $options = []): string
    {
        $targetPrompt = $options['target'] ?? 'waste items';
        
        $prompt = "You are an expert waste analysis AI for a Reverse Vending Machine (RVM) system.

Analyze the image and identify waste items with spatial information. For each item, provide:

1. **Waste Type**: plastic, glass, metal, paper, mixed
2. **Condition**: good, fair, poor, damaged
3. **Quality Grade**: A, B, C, D
4. **Estimated Weight**: in grams
5. **Confidence**: 0-100
6. **Position**: Approximate location in image

**Important**: 
- Focus on recyclable materials only
- Be precise with classifications
- Consider contamination levels
- Provide realistic weight estimates

Respond in JSON format:
{
  \"detections\": [
    {
      \"id\": 1,
      \"waste_type\": \"plastic\",
      \"condition\": \"good\",
      \"quality_grade\": \"B\",
      \"estimated_weight_grams\": 25.5,
      \"confidence\": 85,
      \"label\": \"PET plastic bottle\",
      \"position\": \"center\"
    }
  ],
  \"image_analysis\": {
    \"total_detections\": 1,
    \"image_quality\": \"good\",
    \"lighting_conditions\": \"adequate\"
  }
}";

        return $prompt;
    }

    /**
     * Make API request to Gemini
     */
    protected function makeApiRequest(array $imageData, string $prompt): array
    {
        if (!$this->config) {
            throw new \Exception('No active Gemini configuration found');
        }

        $requestData = [
            'contents' => [
                [
                    'parts' => [
                        [
                            'text' => $prompt
                        ],
                        [
                            'inline_data' => [
                                'mime_type' => $imageData['mime_type'],
                                'data' => $imageData['data']
                            ]
                        ]
                    ]
                ]
            ],
            'generationConfig' => $this->config->generation_config,
            'safetySettings' => $this->config->safety_settings
        ];

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->timeout(30)->post($this->config->endpoint_url . '?key=' . $this->apiKey, $requestData);

        if (!$response->successful()) {
            throw new \Exception('Gemini API request failed: ' . $response->body());
        }

        return $response->json();
    }

    /**
     * Parse single item analysis response
     */
    protected function parseResponse(array $response): array
    {
        try {
            // Check if response has candidates
            if (!isset($response['candidates']) || empty($response['candidates'])) {
                Log::warning('No candidates in Gemini response', ['response' => $response]);
                return $this->getDefaultAnalysis();
            }

            $candidate = $response['candidates'][0];
            
            // Check if candidate has content
            if (!isset($candidate['content']['parts'][0]['text'])) {
                Log::warning('No text content in Gemini response', ['candidate' => $candidate]);
                return $this->getDefaultAnalysis();
            }

            $content = $candidate['content']['parts'][0]['text'];
            Log::debug('Gemini response content', ['content' => $content]);
            
            // Try to extract JSON from response
            $jsonMatch = preg_match('/\{.*\}/s', $content, $matches);
            if ($jsonMatch) {
                $data = json_decode($matches[0], true);
                if ($data && json_last_error() === JSON_ERROR_NONE) {
                    return $this->normalizeAnalysisResult($data);
                }
            }

            // Fallback parsing
            return $this->parseTextResponse($content);

        } catch (\Exception $e) {
            Log::error('Error parsing Gemini response', [
                'error' => $e->getMessage(),
                'response' => $response
            ]);
            return $this->getDefaultAnalysis();
        }
    }

    /**
     * Parse multiple items analysis response
     */
    protected function parseMultipleItemsResponse(array $response): array
    {
        try {
            // Check if response has candidates
            if (!isset($response['candidates']) || empty($response['candidates'])) {
                Log::warning('No candidates in Gemini multiple items response', ['response' => $response]);
                return $this->getDefaultMultipleAnalysis();
            }

            $candidate = $response['candidates'][0];
            
            // Check if candidate has content
            if (!isset($candidate['content']['parts'][0]['text'])) {
                Log::warning('No text content in Gemini multiple items response', ['candidate' => $candidate]);
                return $this->getDefaultMultipleAnalysis();
            }

            $content = $candidate['content']['parts'][0]['text'];
            Log::debug('Gemini multiple items response content', ['content' => $content]);
            
            $jsonMatch = preg_match('/\{.*\}/s', $content, $matches);
            if ($jsonMatch) {
                $data = json_decode($matches[0], true);
                if ($data && json_last_error() === JSON_ERROR_NONE) {
                    return $this->normalizeMultipleItemsResult($data);
                }
            }

            return $this->getDefaultMultipleAnalysis();

        } catch (\Exception $e) {
            Log::error('Error parsing Gemini multiple items response', [
                'error' => $e->getMessage(),
                'response' => $response
            ]);
            return $this->getDefaultMultipleAnalysis();
        }
    }

    /**
     * Parse spatial analysis response
     */
    protected function parseSpatialResponse(array $response): array
    {
        try {
            // Check if response has candidates
            if (!isset($response['candidates']) || empty($response['candidates'])) {
                Log::warning('No candidates in Gemini spatial response', ['response' => $response]);
                return $this->getDefaultSpatialAnalysis();
            }

            $candidate = $response['candidates'][0];
            
            // Check if candidate has content
            if (!isset($candidate['content']['parts'][0]['text'])) {
                Log::warning('No text content in Gemini spatial response', ['candidate' => $candidate]);
                return $this->getDefaultSpatialAnalysis();
            }

            $content = $candidate['content']['parts'][0]['text'];
            Log::debug('Gemini spatial response content', ['content' => $content]);
            
            $jsonMatch = preg_match('/\{.*\}/s', $content, $matches);
            if ($jsonMatch) {
                $data = json_decode($matches[0], true);
                if ($data && json_last_error() === JSON_ERROR_NONE) {
                    return $this->normalizeSpatialResult($data);
                }
            }

            return $this->getDefaultSpatialAnalysis();

        } catch (\Exception $e) {
            Log::error('Error parsing Gemini spatial response', [
                'error' => $e->getMessage(),
                'response' => $response
            ]);
            return $this->getDefaultSpatialAnalysis();
        }
    }

    /**
     * Normalize analysis result
     */
    protected function normalizeAnalysisResult(array $data): array
    {
        return [
            'waste_type' => $data['waste_type'] ?? 'unknown',
            'condition' => $data['condition'] ?? 'unknown',
            'quality_grade' => $data['quality_grade'] ?? 'D',
            'estimated_weight_grams' => (float) ($data['estimated_weight_grams'] ?? 0),
            'quantity' => (int) ($data['quantity'] ?? 1),
            'confidence' => (float) ($data['confidence'] ?? 0),
            'analysis_details' => $data['analysis_details'] ?? [],
            'raw_response' => $data
        ];
    }

    /**
     * Normalize multiple items result
     */
    protected function normalizeMultipleItemsResult(array $data): array
    {
        $items = [];
        foreach ($data['items'] ?? [] as $item) {
            $items[] = [
                'id' => $item['id'] ?? 0,
                'box_2d' => $item['box_2d'] ?? [0, 0, 0, 0],
                'waste_type' => $item['waste_type'] ?? 'unknown',
                'condition' => $item['condition'] ?? 'unknown',
                'quality_grade' => $item['quality_grade'] ?? 'D',
                'estimated_weight_grams' => (float) ($item['estimated_weight_grams'] ?? 0),
                'confidence' => (float) ($item['confidence'] ?? 0),
                'label' => $item['label'] ?? 'Unknown item'
            ];
        }

        return [
            'items' => $items,
            'total_items' => count($items),
            'analysis_summary' => $data['analysis_summary'] ?? [],
            'raw_response' => $data
        ];
    }

    /**
     * Normalize spatial result
     */
    protected function normalizeSpatialResult(array $data): array
    {
        $detections = [];
        foreach ($data['detections'] ?? [] as $detection) {
            $detections[] = [
                'id' => $detection['id'] ?? 0,
                'box_2d' => $detection['box_2d'] ?? [0, 0, 0, 0],
                'mask' => $detection['mask'] ?? '',
                'label' => $detection['label'] ?? 'Unknown',
                'waste_type' => $detection['waste_type'] ?? 'unknown',
                'confidence' => (float) ($detection['confidence'] ?? 0),
                'condition' => $detection['condition'] ?? 'unknown'
            ];
        }

        return [
            'detections' => $detections,
            'image_analysis' => $data['image_analysis'] ?? [],
            'raw_response' => $data
        ];
    }

    /**
     * Parse text response (fallback)
     */
    protected function parseTextResponse(string $content): array
    {
        // Simple text parsing as fallback
        $wasteType = 'unknown';
        $confidence = 50;
        
        if (stripos($content, 'plastic') !== false) $wasteType = 'plastic';
        elseif (stripos($content, 'glass') !== false) $wasteType = 'glass';
        elseif (stripos($content, 'metal') !== false) $wasteType = 'metal';
        elseif (stripos($content, 'paper') !== false) $wasteType = 'paper';
        
        if (preg_match('/(\d+)%/', $content, $matches)) {
            $confidence = (int) $matches[1];
        }

        return [
            'waste_type' => $wasteType,
            'condition' => 'unknown',
            'quality_grade' => 'C',
            'estimated_weight_grams' => 0,
            'quantity' => 1,
            'confidence' => $confidence,
            'analysis_details' => ['raw_text' => $content],
            'raw_response' => $content
        ];
    }

    /**
     * Detect MIME type from base64 data
     */
    protected function detectMimeType(string $base64Data): string
    {
        $header = substr($base64Data, 0, 20);
        
        if (strpos($header, '/9j/') === 0) return 'image/jpeg';
        if (strpos($header, 'iVBORw0KGgo') === 0) return 'image/png';
        if (strpos($header, 'R0lGOD') === 0) return 'image/gif';
        if (strpos($header, 'UklGR') === 0) return 'image/webp';
        
        return 'image/jpeg'; // Default
    }

    /**
     * Get default analysis result
     */
    protected function getDefaultAnalysis(): array
    {
        return [
            'waste_type' => 'unknown',
            'condition' => 'unknown',
            'quality_grade' => 'D',
            'estimated_weight_grams' => 0,
            'quantity' => 1,
            'confidence' => 0,
            'analysis_details' => [
                'error' => 'Analysis failed - unable to parse response',
                'status' => 'failed'
            ],
            'raw_response' => null
        ];
    }

    /**
     * Get default multiple analysis result
     */
    protected function getDefaultMultipleAnalysis(): array
    {
        return [
            'items' => [],
            'total_items' => 0,
            'analysis_summary' => [
                'error' => 'Multiple items analysis failed - unable to parse response',
                'status' => 'failed',
                'total_weight_grams' => 0,
                'recyclable_items' => 0,
                'non_recyclable_items' => 0
            ],
            'raw_response' => null
        ];
    }

    /**
     * Get default spatial analysis result
     */
    protected function getDefaultSpatialAnalysis(): array
    {
        return [
            'detections' => [],
            'image_analysis' => [
                'error' => 'Spatial analysis failed - unable to parse response',
                'status' => 'failed',
                'total_detections' => 0,
                'image_quality' => 'unknown',
                'lighting_conditions' => 'unknown'
            ],
            'raw_response' => null
        ];
    }

    /**
     * Set active configuration
     */
    public function setConfig(GeminiConfig $config): void
    {
        $this->config = $config;
    }

    /**
     * Get available configurations
     */
    public function getAvailableConfigs(): array
    {
        return GeminiConfig::getActiveConfigs()->toArray();
    }

    /**
     * Get current configuration
     */
    public function getCurrentConfig(): ?GeminiConfig
    {
        return $this->config;
    }
}
