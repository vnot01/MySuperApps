<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Gemini Vision Dashboard - MyRVM Platform</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .card-hover {
            transition: all 0.3s ease;
        }
        
        .card-hover:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
        }
        
        .loading-spinner {
            border: 2px solid #f3f3f3;
            border-top: 2px solid #3498db;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .result-card {
            border-left: 4px solid #10b981;
        }
        
        .result-card.error {
            border-left-color: #ef4444;
        }
        
        .confidence-bar {
            height: 8px;
            background: linear-gradient(90deg, #ef4444 0%, #f59e0b 50%, #10b981 100%);
            border-radius: 4px;
        }
        
        .confidence-fill {
            height: 100%;
            background: #10b981;
            border-radius: 4px;
            transition: width 0.3s ease;
        }
    </style>
</head>
<body class="h-full bg-gray-50">
    <!-- Header -->
    <header class="gradient-bg shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-6">
                <div class="flex items-center">
                    <i class="fas fa-robot text-white text-3xl mr-4"></i>
                    <div>
                        <h1 class="text-3xl font-bold text-white">Gemini Vision Dashboard</h1>
                        <p class="text-blue-100 mt-1">AI-Powered Waste Analysis Testing Platform</p>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <div class="text-white text-sm">
                        <div class="font-medium" id="status-indicator">🟢 System Online</div>
                        <div class="text-blue-100" id="last-updated">Last updated: {{ now()->format('H:i:s') }}</div>
                    </div>
                    <button onclick="refreshStatus()" class="bg-white bg-opacity-20 hover:bg-opacity-30 text-white px-4 py-2 rounded-lg transition-all">
                        <i class="fas fa-sync-alt mr-2"></i>Refresh
                    </button>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- System Status Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow-md p-6 card-hover">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                        <i class="fas fa-cogs text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Active Models</p>
                        <p class="text-2xl font-bold text-gray-900" id="active-models">{{ $configs->count() }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6 card-hover">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-green-100 text-green-600">
                        <i class="fas fa-key text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">API Status</p>
                        <p class="text-2xl font-bold text-gray-900" id="api-status">
                            @if(env('GOOGLE_API_KEY'))
                                <span class="text-green-600">Connected</span>
                            @else
                                <span class="text-red-600">Not Set</span>
                            @endif
                        </p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6 card-hover">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                        <i class="fas fa-images text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Sample Images</p>
                        <p class="text-2xl font-bold text-gray-900">{{ count($sampleImages) }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6 card-hover">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                        <i class="fas fa-history text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Recent Tests</p>
                        <p class="text-2xl font-bold text-gray-900" id="recent-tests">{{ count($recentResults) }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Testing Interface -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
            <!-- Image Upload & Analysis -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">
                    <i class="fas fa-upload mr-2"></i>Upload & Analyze Image
                </h3>
                
                <form id="upload-form" enctype="multipart/form-data">
                    @csrf
                    <div class="space-y-4">
                        <!-- File Upload -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Select Image</label>
                            <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-gray-400 transition-colors">
                                <input type="file" id="image-upload" name="image" accept="image/*" class="hidden" required>
                                <label for="image-upload" class="cursor-pointer">
                                    <i class="fas fa-cloud-upload-alt text-3xl text-gray-400 mb-2"></i>
                                    <p class="text-sm text-gray-600">Click to upload or drag and drop</p>
                                    <p class="text-xs text-gray-500 mt-1">PNG, JPG, GIF up to 10MB</p>
                                </label>
                            </div>
                            <div id="image-preview" class="mt-4 hidden">
                                <img id="preview-img" class="max-w-full h-48 object-contain rounded-lg border">
                            </div>
                        </div>

                        <!-- Analysis Type -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Analysis Type</label>
                            <select name="analysis_type" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="single">Single Item Analysis</option>
                                <option value="multiple">Multiple Items Analysis</option>
                                <option value="spatial">Spatial Understanding</option>
                            </select>
                        </div>

                        <!-- Model Selection -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Gemini Model</label>
                            <select name="config_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Use Default ({{ $defaultConfig->display_name ?? 'None' }})</option>
                                @foreach($configs as $config)
                                    <option value="{{ $config->id }}">{{ $config->display_name }} ({{ $config->name }})</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Analyze Button -->
                        <button type="submit" id="analyze-btn" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-3 px-4 rounded-lg transition-colors">
                            <i class="fas fa-search mr-2"></i>Analyze Image
                        </button>
                    </div>
                </form>
            </div>

            <!-- Sample Images Testing -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">
                    <i class="fas fa-images mr-2"></i>Test with Sample Images
                </h3>
                
                <div class="space-y-4">
                    <!-- Sample Images Grid -->
                    <div class="grid grid-cols-2 gap-4">
                        @foreach($sampleImages as $image)
                            <div class="relative group cursor-pointer" onclick="selectSampleImage('{{ $image['name'] }}')">
                                <img src="{{ $image['url'] }}" alt="{{ $image['name'] }}" class="w-full h-24 object-cover rounded-lg border-2 border-gray-200 group-hover:border-blue-500 transition-colors">
                                <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-20 transition-all rounded-lg flex items-center justify-center">
                                    <i class="fas fa-play text-white text-xl opacity-0 group-hover:opacity-100 transition-opacity"></i>
                                </div>
                                <p class="text-xs text-gray-600 mt-1 text-center">{{ $image['name'] }}</p>
                            </div>
                        @endforeach
                    </div>

                    <!-- Analysis Options -->
                    <div class="space-y-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Analysis Type</label>
                            <select id="sample-analysis-type" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="single">Single Item Analysis</option>
                                <option value="multiple">Multiple Items Analysis</option>
                                <option value="spatial">Spatial Understanding</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Gemini Model</label>
                            <select id="sample-config-id" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Use Default ({{ $defaultConfig->display_name ?? 'None' }})</option>
                                @foreach($configs as $config)
                                    <option value="{{ $config->id }}">{{ $config->display_name }} ({{ $config->name }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="flex space-x-2">
                            <button onclick="testSampleImage()" id="test-sample-btn" class="flex-1 bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded-lg transition-colors">
                                <i class="fas fa-play mr-2"></i>Test Sample
                            </button>
                            <button onclick="compareModels()" id="compare-btn" class="flex-1 bg-purple-600 hover:bg-purple-700 text-white font-medium py-2 px-4 rounded-lg transition-colors">
                                <i class="fas fa-balance-scale mr-2"></i>Compare Models
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Results Section -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-lg font-semibold text-gray-900">
                    <i class="fas fa-chart-line mr-2"></i>Analysis Results
                </h3>
                <div class="flex space-x-2">
                    <button onclick="clearResults()" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition-colors">
                        <i class="fas fa-trash mr-2"></i>Clear Results
                    </button>
                    <button onclick="refreshResults()" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition-colors">
                        <i class="fas fa-sync-alt mr-2"></i>Refresh
                    </button>
                </div>
            </div>

            <!-- Results Container -->
            <div id="results-container" class="space-y-4">
                @if(count($recentResults) > 0)
                    @foreach($recentResults as $result)
                        @include('gemini.partials.result-card', ['result' => $result])
                    @endforeach
                    
                    <!-- Pagination -->
                    @if($totalPages > 1)
                        <div class="flex items-center justify-between mt-6 pt-4 border-t border-gray-200">
                            <div class="text-sm text-gray-700">
                                Showing {{ (($currentPage - 1) * 5) + 1 }} to {{ min($currentPage * 5, $totalResults) }} of {{ $totalResults }} results
                            </div>
                            
                            <div class="flex items-center space-x-2">
                                <!-- Previous Button -->
                                @if($hasPrevPage)
                                    <a href="{{ route('gemini.dashboard', ['page' => $currentPage - 1]) }}" 
                                       class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-md hover:bg-gray-50 hover:text-gray-700 transition-colors">
                                        <i class="fas fa-chevron-left mr-1"></i>Previous
                                    </a>
                                @else
                                    <span class="px-3 py-2 text-sm font-medium text-gray-300 bg-gray-100 border border-gray-200 rounded-md cursor-not-allowed">
                                        <i class="fas fa-chevron-left mr-1"></i>Previous
                                    </span>
                                @endif
                                
                                <!-- Page Numbers -->
                                @for($i = max(1, $currentPage - 2); $i <= min($totalPages, $currentPage + 2); $i++)
                                    @if($i == $currentPage)
                                        <span class="px-3 py-2 text-sm font-medium text-white bg-blue-600 border border-blue-600 rounded-md">
                                            {{ $i }}
                                        </span>
                                    @else
                                        <a href="{{ route('gemini.dashboard', ['page' => $i]) }}" 
                                           class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-md hover:bg-gray-50 hover:text-gray-700 transition-colors">
                                            {{ $i }}
                                        </a>
                                    @endif
                                @endfor
                                
                                <!-- Next Button -->
                                @if($hasNextPage)
                                    <a href="{{ route('gemini.dashboard', ['page' => $currentPage + 1]) }}" 
                                       class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-md hover:bg-gray-50 hover:text-gray-700 transition-colors">
                                        Next<i class="fas fa-chevron-right ml-1"></i>
                                    </a>
                                @else
                                    <span class="px-3 py-2 text-sm font-medium text-gray-300 bg-gray-100 border border-gray-200 rounded-md cursor-not-allowed">
                                        Next<i class="fas fa-chevron-right ml-1"></i>
                                    </span>
                                @endif
                            </div>
                        </div>
                    @endif
                @else
                    <div class="text-center py-12 text-gray-500">
                        <i class="fas fa-search text-4xl mb-4"></i>
                        <p class="text-lg">No analysis results yet</p>
                        <p class="text-sm">Upload an image or test with sample images to see results</p>
                    </div>
                @endif
            </div>
        </div>
    </main>

    <!-- Loading Overlay -->
    <div id="loading-overlay" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-lg p-6 flex items-center space-x-4">
            <div class="loading-spinner"></div>
            <span class="text-lg font-medium">Analyzing image...</span>
        </div>
    </div>

    <!-- Results Modal -->
    <div id="results-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-lg max-w-4xl w-full mx-4 max-h-[90vh] overflow-y-auto">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-xl font-semibold">Analysis Results</h3>
                    <button onclick="closeResultsModal()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                <div id="modal-content"></div>
            </div>
        </div>
    </div>

    <script>
        let selectedSampleImage = null;
        let currentResults = @json($recentResults);

        // File upload preview
        document.getElementById('image-upload').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('preview-img').src = e.target.result;
                    document.getElementById('image-preview').classList.remove('hidden');
                };
                reader.readAsDataURL(file);
            }
        });

        // Upload form submission
        document.getElementById('upload-form').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            showLoading();
            
            try {
                const response = await fetch('/gemini/dashboard/analyze', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });
                
                const result = await response.json();
                hideLoading();
                
                if (result.success) {
                    addResultToContainer(result.data);
                    showNotification('Analysis completed successfully!', 'success');
                } else {
                    showNotification('Analysis failed: ' + result.message, 'error');
                }
            } catch (error) {
                hideLoading();
                showNotification('Network error: ' + error.message, 'error');
            }
        });

        // Sample image selection
        function selectSampleImage(imageName) {
            selectedSampleImage = imageName;
            document.querySelectorAll('.group').forEach(el => el.classList.remove('ring-2', 'ring-blue-500'));
            event.currentTarget.classList.add('ring-2', 'ring-blue-500');
        }

        // Test sample image
        async function testSampleImage() {
            if (!selectedSampleImage) {
                showNotification('Please select a sample image first', 'warning');
                return;
            }
            
            showLoading();
            
            try {
                const response = await fetch('/gemini/dashboard/test-sample', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        image_name: selectedSampleImage,
                        analysis_type: document.getElementById('sample-analysis-type').value,
                        config_id: document.getElementById('sample-config-id').value || null
                    })
                });
                
                const result = await response.json();
                hideLoading();
                
                if (result.success) {
                    addResultToContainer(result.data);
                    showNotification('Sample test completed successfully!', 'success');
                } else {
                    showNotification('Test failed: ' + result.message, 'error');
                }
            } catch (error) {
                hideLoading();
                showNotification('Network error: ' + error.message, 'error');
            }
        }

        // Compare models
        async function compareModels() {
            if (!selectedSampleImage) {
                showNotification('Please select a sample image first', 'warning');
                return;
            }
            
            showLoading();
            
            try {
                const response = await fetch('/gemini/dashboard/compare-models', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        image_name: selectedSampleImage,
                        analysis_type: document.getElementById('sample-analysis-type').value
                    })
                });
                
                const result = await response.json();
                hideLoading();
                
                if (result.success) {
                    showComparisonResults(result.data);
                    showNotification('Model comparison completed!', 'success');
                } else {
                    showNotification('Comparison failed: ' + result.message, 'error');
                }
            } catch (error) {
                hideLoading();
                showNotification('Network error: ' + error.message, 'error');
            }
        }

        // Add result to container
        function addResultToContainer(result) {
            const container = document.getElementById('results-container');
            const resultHtml = createResultCard(result);
            container.insertAdjacentHTML('afterbegin', resultHtml);
            updateRecentTestsCount();
        }

        // Create result card HTML
        function createResultCard(result) {
            // Get confidence based on analysis type (same logic as PHP)
            let confidence = 0;
            
            if (result.result.confidence) {
                // Single analysis
                confidence = result.result.confidence;
            } else if (result.result.detections && result.result.detections.length > 0) {
                // Spatial analysis - get confidence from first detection
                confidence = result.result.detections[0].confidence || 0;
            } else if (result.result.items && result.result.items.length > 0) {
                // Multiple analysis - get average confidence from items
                const confidences = result.result.items.map(item => item.confidence || 0);
                confidence = confidences.length > 0 ? Math.round((confidences.reduce((a, b) => a + b, 0) / confidences.length) * 100) / 100 : 0;
            }
            
            const successClass = result.success ? 'result-card' : 'result-card error';
            const confidenceColor = confidence >= 80 ? 'bg-green-500' : confidence >= 60 ? 'bg-yellow-500' : 'bg-red-500';
            
            return `
                <div class="${successClass} bg-white border border-gray-200 rounded-lg p-4 shadow-sm">
                    <div class="flex justify-between items-start mb-3">
                        <div class="flex items-center space-x-3">
                            <img src="${result.image_url}" alt="Analysis result" class="w-16 h-16 object-cover rounded-lg border">
                            <div>
                                <h4 class="font-medium text-gray-900">${result.analysis_type.charAt(0).toUpperCase() + result.analysis_type.slice(1)} Analysis</h4>
                                <p class="text-sm text-gray-600">${new Date(result.timestamp).toLocaleString()}</p>
                                <p class="text-xs text-gray-500">Model: ${result.config_used?.display_name || 'Unknown'}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-sm font-medium ${result.success ? 'text-green-600' : 'text-red-600'}">
                                ${result.success ? 'Success' : 'Failed'}
                            </div>
                            <div class="text-xs text-gray-500">${result.processing_time_ms}ms</div>
                        </div>
                    </div>
                    
                    ${result.success ? `
                        <div class="space-y-2">
                            <div class="flex justify-between text-sm">
                                <span>Confidence:</span>
                                <span class="font-medium">${confidence}%</span>
                            </div>
                            <div class="confidence-bar">
                                <div class="confidence-fill ${confidenceColor}" style="width: ${confidence}%"></div>
                            </div>
                            
                            ${result.result.waste_type ? `
                                <div class="grid grid-cols-2 gap-4 text-sm">
                                    <div><span class="font-medium">Waste Type:</span> ${result.result.waste_type}</div>
                                    <div><span class="font-medium">Quality Grade:</span> ${result.result.quality_grade || 'N/A'}</div>
                                    <div><span class="font-medium">Weight:</span> ${result.result.estimated_weight_grams || 0}g</div>
                                    <div><span class="font-medium">Quantity:</span> ${result.result.quantity || 1}</div>
                                </div>
                            ` : ''}
                            
                            ${result.result.items ? `
                                <div class="text-sm">
                                    <span class="font-medium">Items Detected:</span> ${result.result.total_items || 0}
                                </div>
                            ` : ''}
                        </div>
                    ` : `
                        <div class="text-red-600 text-sm">
                            Error: ${result.result.analysis_details?.error || 'Unknown error'}
                        </div>
                    `}
                    
                    <div class="mt-3 flex justify-end">
                        <button onclick="viewDetailedResults('${result.id}')" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                            View Details <i class="fas fa-arrow-right ml-1"></i>
                        </button>
                    </div>
                </div>
            `;
        }

        // Show comparison results
        function showComparisonResults(results) {
            const modalContent = document.getElementById('modal-content');
            let html = '<div class="space-y-4">';
            
            results.forEach((result, index) => {
                // Get confidence based on analysis type (same logic as PHP)
                let confidence = 0;
                
                if (result.result.confidence) {
                    // Single analysis
                    confidence = result.result.confidence;
                } else if (result.result.detections && result.result.detections.length > 0) {
                    // Spatial analysis - get confidence from first detection
                    confidence = result.result.detections[0].confidence || 0;
                } else if (result.result.items && result.result.items.length > 0) {
                    // Multiple analysis - get average confidence from items
                    const confidences = result.result.items.map(item => item.confidence || 0);
                    confidence = confidences.length > 0 ? Math.round((confidences.reduce((a, b) => a + b, 0) / confidences.length) * 100) / 100 : 0;
                }
                
                const successClass = result.success ? 'border-green-200 bg-green-50' : 'border-red-200 bg-red-50';
                
                html += `
                    <div class="border rounded-lg p-4 ${successClass}">
                        <div class="flex justify-between items-start mb-3">
                            <h4 class="font-semibold text-lg">${result.config.display_name}</h4>
                            <div class="text-right">
                                <div class="text-sm font-medium ${result.success ? 'text-green-600' : 'text-red-600'}">
                                    ${result.success ? 'Success' : 'Failed'}
                                </div>
                                <div class="text-xs text-gray-500">${result.processing_time_ms}ms</div>
                            </div>
                        </div>
                        
                        ${result.success ? `
                            <div class="space-y-2">
                                <div class="flex justify-between text-sm">
                                    <span>Confidence:</span>
                                    <span class="font-medium">${confidence}%</span>
                                </div>
                                <div class="confidence-bar">
                                    <div class="confidence-fill ${confidence >= 80 ? 'bg-green-500' : confidence >= 60 ? 'bg-yellow-500' : 'bg-red-500'}" style="width: ${confidence}%"></div>
                                </div>
                                
                                ${result.result.waste_type ? `
                                    <div class="grid grid-cols-2 gap-4 text-sm">
                                        <div><span class="font-medium">Waste Type:</span> ${result.result.waste_type}</div>
                                        <div><span class="font-medium">Quality Grade:</span> ${result.result.quality_grade || 'N/A'}</div>
                                        <div><span class="font-medium">Weight:</span> ${result.result.estimated_weight_grams || 0}g</div>
                                        <div><span class="font-medium">Quantity:</span> ${result.result.quantity || 1}</div>
                                    </div>
                                ` : ''}
                            </div>
                        ` : `
                            <div class="text-red-600 text-sm">
                                Error: ${result.result.analysis_details?.error || 'Unknown error'}
                            </div>
                        `}
                    </div>
                `;
            });
            
            html += '</div>';
            modalContent.innerHTML = html;
            document.getElementById('results-modal').classList.remove('hidden');
        }

        // View detailed results
        function viewDetailedResults(resultId) {
            console.log('View Details clicked for result:', resultId);
            
            // Find result in current results
            let result = currentResults.find(r => r.id === resultId);
            
            // If not found in current results, try to find in all results from session
            if (!result) {
                console.log('Result not found in currentResults, checking session...');
                // Try to get from session storage or make a request
                fetch(`/gemini/dashboard/result/${resultId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success && data.result) {
                            result = data.result;
                            showModalWithResult(result);
                        } else {
                            console.error('Result not found in session:', resultId);
                            alert('Analysis result not found. Please refresh the page.');
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching result:', error);
                        alert('Error loading analysis result. Please refresh the page.');
                    });
                return;
            }
            
            showModalWithResult(result);
        }
        
        function showModalWithResult(result) {
            console.log('Showing modal with result:', result);
            const modalContent = document.getElementById('modal-content');
            const modal = document.getElementById('results-modal');
            
            if (!modalContent || !modal) {
                console.error('Modal elements not found');
                return;
            }
            
            // Show loading state immediately
            modalContent.innerHTML = `
                <div class="flex items-center justify-center py-8">
                    <div class="loading-spinner"></div>
                    <span class="ml-3 text-gray-600">Loading analysis details...</span>
                </div>
            `;
            
            // Show modal immediately
            modal.classList.remove('hidden');
            console.log('Modal shown');
            
            // Create analysis result image with bounding boxes asynchronously
            createAnalysisResultImage(result).then(analysisImageHtml => {
                console.log('Analysis image created successfully');
                modalContent.innerHTML = `
                    <div class="space-y-6">
                        <!-- Analysis Info -->
                        <div class="flex items-center space-x-4 p-4 bg-gray-50 rounded-lg">
                            <img src="${result.image_url}" alt="Analysis result" class="w-20 h-20 object-cover rounded-lg border">
                            <div>
                                <h4 class="text-lg font-semibold">${result.analysis_type.charAt(0).toUpperCase() + result.analysis_type.slice(1)} Analysis</h4>
                                <p class="text-sm text-gray-600">${new Date(result.timestamp).toLocaleString()}</p>
                                <p class="text-sm text-gray-600">Model: ${result.config_used?.display_name || 'Unknown'}</p>
                                <p class="text-sm text-gray-600">Processing Time: ${result.processing_time_ms}ms</p>
                            </div>
                        </div>
                        
                        <!-- Image Comparison -->
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                            <!-- Original Image -->
                            <div class="space-y-2">
                                <h5 class="font-medium text-gray-900">Original Image</h5>
                                <div class="relative border rounded-lg overflow-hidden">
                                    <img src="${result.image_url}" alt="Original image" class="w-full h-auto">
                                </div>
                            </div>
                            
                            <!-- Analysis Result Image -->
                            <div class="space-y-2">
                                <h5 class="font-medium text-gray-900">Analysis Result</h5>
                                <div class="relative border rounded-lg overflow-hidden">
                                    ${analysisImageHtml}
                                </div>
                            </div>
                        </div>
                        
                        <!-- Analysis Details -->
                        <div class="bg-gray-50 rounded-lg p-4">
                            <h5 class="font-medium mb-3 text-gray-900">Analysis Details</h5>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                ${createAnalysisDetails(result)}
                            </div>
                        </div>
                    </div>
                `;
            }).catch(error => {
                console.error('Error creating analysis image:', error);
                // Fallback to original image if canvas fails
                modalContent.innerHTML = `
                    <div class="space-y-6">
                        <!-- Analysis Info -->
                        <div class="flex items-center space-x-4 p-4 bg-gray-50 rounded-lg">
                            <img src="${result.image_url}" alt="Analysis result" class="w-20 h-20 object-cover rounded-lg border">
                            <div>
                                <h4 class="text-lg font-semibold">${result.analysis_type.charAt(0).toUpperCase() + result.analysis_type.slice(1)} Analysis</h4>
                                <p class="text-sm text-gray-600">${new Date(result.timestamp).toLocaleString()}</p>
                                <p class="text-sm text-gray-600">Model: ${result.config_used?.display_name || 'Unknown'}</p>
                                <p class="text-sm text-gray-600">Processing Time: ${result.processing_time_ms}ms</p>
                            </div>
                        </div>
                        
                        <!-- Image Comparison -->
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                            <!-- Original Image -->
                            <div class="space-y-2">
                                <h5 class="font-medium text-gray-900">Original Image</h5>
                                <div class="relative border rounded-lg overflow-hidden">
                                    <img src="${result.image_url}" alt="Original image" class="w-full h-auto">
                                </div>
                            </div>
                            
                            <!-- Analysis Result Image -->
                            <div class="space-y-2">
                                <h5 class="font-medium text-gray-900">Analysis Result</h5>
                                <div class="relative border rounded-lg overflow-hidden">
                                    <img src="${result.image_url}" alt="Analysis result" class="w-full h-auto">
                                    <div class="absolute top-2 left-2 bg-red-500 text-white px-2 py-1 text-sm rounded">
                                        Canvas rendering failed - showing original image
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Analysis Details -->
                        <div class="bg-gray-50 rounded-lg p-4">
                            <h5 class="font-medium mb-3 text-gray-900">Analysis Details</h5>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                ${createAnalysisDetails(result)}
                            </div>
                        </div>
                    </div>
                `;
            });
        }
        
        function createAnalysisResultImage(result) {
            console.log('Creating analysis result image for:', result);
            // Create a canvas to draw bounding boxes on the original image
            const canvas = document.createElement('canvas');
            const ctx = canvas.getContext('2d');
            const img = new Image();
            
            return new Promise((resolve) => {
                img.onload = function() {
                    canvas.width = img.width;
                    canvas.height = img.height;
                    
                    // Draw original image
                    ctx.drawImage(img, 0, 0);
                    
                    console.log('Canvas dimensions:', canvas.width, 'x', canvas.height);
                    
                    // Draw bounding boxes and masks based on analysis type
                    console.log('Full result object:', result);
                    console.log('Result.result:', result.result);
                    
                    if (result.analysis_type === 'spatial') {
                        const detections = result.result?.detections || [];
                        console.log('Processing spatial detections:', detections);
                        
                        if (detections.length === 0) {
                            console.log('No detections found, checking raw_response...');
                            const rawDetections = result.result?.raw_response?.detections || [];
                            console.log('Raw detections:', rawDetections);
                            
                            rawDetections.forEach((detection, index) => {
                                if (detection.box_2d && detection.box_2d.length === 4) {
                                    const [x, y, width, height] = detection.box_2d;
                                    console.log(`Drawing raw detection ${index}:`, {x, y, width, height, label: detection.label});
                                    drawBoundingBox(ctx, x, y, width, height, detection.label, index);
                                    
                                    // Draw segmentation mask if available
                                    if (detection.mask && detection.mask.trim() !== '') {
                                        console.log(`Drawing raw mask for detection ${index}, mask length:`, detection.mask.length);
                                        drawSegmentationMask(ctx, x, y, width, height, detection.mask, index);
                                    } else {
                                        console.log(`No raw mask data for detection ${index}`);
                                    }
                                }
                            });
                        } else {
                            detections.forEach((detection, index) => {
                                if (detection.box_2d && detection.box_2d.length === 4) {
                                    const [x, y, width, height] = detection.box_2d;
                                    console.log(`Drawing detection ${index}:`, {x, y, width, height, label: detection.label});
                                    drawBoundingBox(ctx, x, y, width, height, detection.label, index);
                                    
                                    // Draw segmentation mask if available
                                    if (detection.mask && detection.mask.trim() !== '') {
                                        console.log(`Drawing mask for detection ${index}, mask length:`, detection.mask.length);
                                        drawSegmentationMask(ctx, x, y, width, height, detection.mask, index);
                                    } else {
                                        console.log(`No mask data for detection ${index}`);
                                    }
                                }
                            });
                        }
                    } else if (result.analysis_type === 'multiple') {
                        const items = result.result?.items || [];
                        console.log('Processing multiple items:', items);
                        
                        if (items.length === 0) {
                            console.log('No items found, checking raw_response...');
                            const rawItems = result.result?.raw_response?.items || [];
                            console.log('Raw items:', rawItems);
                            
                            if (rawItems.length > 0) {
                                rawItems.forEach((item, index) => {
                                    if (item.box_2d && item.box_2d.length === 4) {
                                        const [x, y, width, height] = item.box_2d;
                                        console.log(`Drawing raw item ${index}:`, {x, y, width, height, label: item.label});
                                        drawBoundingBox(ctx, x, y, width, height, item.label, index);
                                        
                                        // Draw segmentation mask if available
                                        if (item.mask && item.mask.trim() !== '') {
                                            console.log(`Drawing raw mask for item ${index}, mask length:`, item.mask.length);
                                            drawSegmentationMask(ctx, x, y, width, height, item.mask, index);
                                        } else {
                                            console.log(`No raw mask data for item ${index}`);
                                        }
                                    }
                                });
                            } else {
                                console.log('No raw items found either, checking if we can extract from raw_response directly...');
                                // Try to extract from the raw response structure
                                const rawResponse = result.result?.raw_response;
                                if (rawResponse && rawResponse.items) {
                                    console.log('Found items in raw_response:', rawResponse.items);
                                    rawResponse.items.forEach((item, index) => {
                                        if (item.box_2d && item.box_2d.length === 4) {
                                            const [x, y, width, height] = item.box_2d;
                                            console.log(`Drawing extracted item ${index}:`, {x, y, width, height, label: item.label});
                                            drawBoundingBox(ctx, x, y, width, height, item.label, index);
                                            
                                            if (item.mask && item.mask.trim() !== '') {
                                                console.log(`Drawing extracted mask for item ${index}, mask length:`, item.mask.length);
                                                drawSegmentationMask(ctx, x, y, width, height, item.mask, index);
                                            }
                                        }
                                    });
                                }
                            }
                        } else {
                            items.forEach((item, index) => {
                                if (item.box_2d && item.box_2d.length === 4) {
                                    const [x, y, width, height] = item.box_2d;
                                    console.log(`Drawing item ${index}:`, {x, y, width, height, label: item.label});
                                    drawBoundingBox(ctx, x, y, width, height, item.label, index);
                                    
                                    // Draw segmentation mask if available
                                    if (item.mask && item.mask.trim() !== '') {
                                        console.log(`Drawing mask for item ${index}, mask length:`, item.mask.length);
                                        drawSegmentationMask(ctx, x, y, width, height, item.mask, index);
                                    } else {
                                        console.log(`No mask data for item ${index}`);
                                    }
                                }
                            });
                        }
                    }
                    
                    // Convert canvas to data URL
                    const dataUrl = canvas.toDataURL();
                    console.log('Canvas rendered successfully');
                    resolve(`<img src="${dataUrl}" alt="Analysis result with bounding boxes and masks" class="w-full h-auto">`);
                };
                
                img.onerror = function() {
                    console.error('Failed to load image:', result.image_url);
                    resolve(`<img src="${result.image_url}" alt="Analysis result" class="w-full h-auto">`);
                };
                
                img.src = result.image_url;
            }).then(html => html).catch((error) => {
                console.error('Error creating analysis image:', error);
                // Fallback to original image if canvas fails
                return `<img src="${result.image_url}" alt="Analysis result" class="w-full h-auto">`;
            });
        }
        
        function drawBoundingBox(ctx, x, y, width, height, label, index) {
            const colors = ['#FF6B6B', '#4ECDC4', '#45B7D1', '#96CEB4', '#FFEAA7', '#DDA0DD'];
            const color = colors[index % colors.length];
            
            // Draw bounding box
            ctx.strokeStyle = color;
            ctx.lineWidth = 3;
            ctx.strokeRect(x, y, width, height);
            
            // Draw label background
            ctx.fillStyle = color;
            ctx.fillRect(x, y - 25, label.length * 8 + 10, 25);
            
            // Draw label text
            ctx.fillStyle = 'white';
            ctx.font = '14px Arial';
            ctx.fillText(label, x + 5, y - 8);
        }
        
        function drawSegmentationMask(ctx, x, y, width, height, maskData, index) {
            console.log('Drawing segmentation mask:', {x, y, width, height, maskLength: maskData.length, index});
            
            const segmentationColors = [
                [230, 25, 75],   // #E6194B
                [60, 137, 208],  // #3C89D0
                [60, 180, 75],   // #3CB44B
                [255, 225, 25],  // #FFE119
                [145, 30, 180],  // #911EB4
                [66, 212, 244],  // #42D4F4
                [245, 130, 49],  // #F58231
                [240, 50, 230],  // #F032E6
                [191, 239, 69],  // #BFEF45
                [70, 153, 144],  // #469990
            ];
            
            const rgb = segmentationColors[index % segmentationColors.length];
            
            try {
                // If maskData is a base64 image
                if (typeof maskData === 'string' && maskData.startsWith('data:image')) {
                    console.log('Processing base64 mask image');
                    const maskImg = new Image();
                    maskImg.onload = function() {
                        console.log('Mask image loaded, dimensions:', maskImg.width, 'x', maskImg.height);
                        
                        // Create a temporary canvas for the mask
                        const maskCanvas = document.createElement('canvas');
                        const maskCtx = maskCanvas.getContext('2d');
                        maskCanvas.width = maskImg.width;
                        maskCanvas.height = maskImg.height;
                        
                        // Draw the mask image
                        maskCtx.drawImage(maskImg, 0, 0);
                        
                        // Get image data
                        const imageData = maskCtx.getImageData(0, 0, maskImg.width, maskImg.height);
                        const data = imageData.data;
                        
                        // Process pixels to create colored mask
                        for (let i = 0; i < data.length; i += 4) {
                            // Use alpha channel from mask as opacity
                            const alpha = data[i + 3];
                            if (alpha > 0) {
                                data[i] = rgb[0];     // Red
                                data[i + 1] = rgb[1]; // Green
                                data[i + 2] = rgb[2]; // Blue
                                data[i + 3] = Math.floor(alpha * 0.5); // Semi-transparent
                            }
                        }
                        
                        // Put the processed image data back
                        maskCtx.putImageData(imageData, 0, 0);
                        
                        // Draw the colored mask onto the main canvas
                        ctx.globalAlpha = 0.5;
                        ctx.drawImage(maskCanvas, x, y, width, height);
                        ctx.globalAlpha = 1.0;
                        console.log('Mask drawn successfully');
                    };
                    maskImg.onerror = function() {
                        console.error('Failed to load mask image');
                        // Fallback: draw a simple colored overlay
                        ctx.globalAlpha = 0.3;
                        ctx.fillStyle = `rgb(${rgb[0]}, ${rgb[1]}, ${rgb[2]})`;
                        ctx.fillRect(x, y, width, height);
                        ctx.globalAlpha = 1.0;
                    };
                    maskImg.src = maskData;
                }
                // If maskData is a simple mask array or other format
                else if (Array.isArray(maskData)) {
                    console.log('Processing array-based mask');
                    // Draw a simple colored overlay for array-based masks
                    ctx.globalAlpha = 0.3;
                    ctx.fillStyle = `rgb(${rgb[0]}, ${rgb[1]}, ${rgb[2]})`;
                    ctx.fillRect(x, y, width, height);
                    ctx.globalAlpha = 1.0;
                }
                // If maskData is just a string (might be base64 without data:image prefix)
                else if (typeof maskData === 'string' && maskData.length > 100) {
                    console.log('Processing string mask data, adding data:image prefix');
                    // Try to add the data:image prefix
                    const base64Data = maskData.startsWith('data:') ? maskData : `data:image/png;base64,${maskData}`;
                    const maskImg = new Image();
                    maskImg.onload = function() {
                        console.log('String mask image loaded, dimensions:', maskImg.width, 'x', maskImg.height);
                        
                        // Create a temporary canvas for the mask
                        const maskCanvas = document.createElement('canvas');
                        const maskCtx = maskCanvas.getContext('2d');
                        maskCanvas.width = maskImg.width;
                        maskCanvas.height = maskImg.height;
                        
                        // Draw the mask image
                        maskCtx.drawImage(maskImg, 0, 0);
                        
                        // Get image data
                        const imageData = maskCtx.getImageData(0, 0, maskImg.width, maskImg.height);
                        const data = imageData.data;
                        
                        // Process pixels to create colored mask
                        for (let i = 0; i < data.length; i += 4) {
                            // Use alpha channel from mask as opacity
                            const alpha = data[i + 3];
                            if (alpha > 0) {
                                data[i] = rgb[0];     // Red
                                data[i + 1] = rgb[1]; // Green
                                data[i + 2] = rgb[2]; // Blue
                                data[i + 3] = Math.floor(alpha * 0.5); // Semi-transparent
                            }
                        }
                        
                        // Put the processed image data back
                        maskCtx.putImageData(imageData, 0, 0);
                        
                        // Draw the colored mask onto the main canvas
                        ctx.globalAlpha = 0.5;
                        ctx.drawImage(maskCanvas, x, y, width, height);
                        ctx.globalAlpha = 1.0;
                        console.log('String mask drawn successfully');
                    };
                    maskImg.onerror = function() {
                        console.error('Failed to load string mask image');
                        // Fallback: draw a simple colored overlay
                        ctx.globalAlpha = 0.3;
                        ctx.fillStyle = `rgb(${rgb[0]}, ${rgb[1]}, ${rgb[2]})`;
                        ctx.fillRect(x, y, width, height);
                        ctx.globalAlpha = 1.0;
                    };
                    maskImg.src = base64Data;
                }
                else {
                    console.log('Unknown mask data format, using fallback overlay');
                    // Fallback: draw a simple colored overlay
                    ctx.globalAlpha = 0.3;
                    ctx.fillStyle = `rgb(${rgb[0]}, ${rgb[1]}, ${rgb[2]})`;
                    ctx.fillRect(x, y, width, height);
                    ctx.globalAlpha = 1.0;
                }
            } catch (error) {
                console.warn('Error drawing segmentation mask:', error);
                // Fallback: draw a simple colored overlay
                ctx.globalAlpha = 0.3;
                ctx.fillStyle = `rgb(${rgb[0]}, ${rgb[1]}, ${rgb[2]})`;
                ctx.fillRect(x, y, width, height);
                ctx.globalAlpha = 1.0;
            }
        }
        
        function createAnalysisDetails(result) {
            let details = '';
            
            if (result.analysis_type === 'single') {
                details = `
                    <div><span class="font-medium">Waste Type:</span> ${result.result.waste_type || 'N/A'}</div>
                    <div><span class="font-medium">Condition:</span> ${result.result.condition || 'N/A'}</div>
                    <div><span class="font-medium">Quality Grade:</span> ${result.result.quality_grade || 'N/A'}</div>
                    <div><span class="font-medium">Weight:</span> ${result.result.estimated_weight_grams || 0}g</div>
                    <div><span class="font-medium">Confidence:</span> ${result.result.confidence || 0}%</div>
                `;
            } else if (result.analysis_type === 'multiple') {
                // Check if analysis failed
                const analysisSummary = result.result?.analysis_summary || {};
                const hasError = analysisSummary.error || analysisSummary.status === 'failed';
                
                if (hasError) {
                    details = `
                        <div class="text-red-600"><span class="font-medium">Status:</span> ${analysisSummary.status || 'failed'}</div>
                        <div class="text-red-600"><span class="font-medium">Error:</span> ${analysisSummary.error || 'Analysis failed'}</div>
                        <div><span class="font-medium">Total Items:</span> 0</div>
                        <div><span class="font-medium">Total Weight:</span> 0g</div>
                        <div><span class="font-medium">Recyclable Items:</span> 0</div>
                        <div><span class="font-medium">Non-Recyclable Items:</span> 0</div>
                    `;
                } else {
                    // Check both normalized and raw response for accurate counts
                    const normalizedItems = result.result?.items || [];
                    const rawItems = result.result?.raw_response?.items || [];
                    const totalItems = Math.max(
                        result.result?.total_items || 0,
                        normalizedItems.length,
                        rawItems.length
                    );
                    
                    const totalWeight = result.result?.analysis_summary?.total_weight_grams || 
                                       result.result?.raw_response?.analysis_summary?.total_weight_grams || 0;
                    const recyclableItems = result.result?.analysis_summary?.recyclable_items || 
                                           result.result?.raw_response?.analysis_summary?.recyclable_items || 0;
                    const nonRecyclableItems = result.result?.analysis_summary?.non_recyclable_items || 
                                              result.result?.raw_response?.analysis_summary?.non_recyclable_items || 0;
                    
                    console.log('Multiple analysis details:', {
                        normalizedItems: normalizedItems.length,
                        rawItems: rawItems.length,
                        totalItems: totalItems,
                        totalWeight: totalWeight,
                        recyclableItems: recyclableItems,
                        nonRecyclableItems: nonRecyclableItems
                    });
                    
                    details = `
                        <div><span class="font-medium">Total Items:</span> ${totalItems}</div>
                        <div><span class="font-medium">Total Weight:</span> ${totalWeight}g</div>
                        <div><span class="font-medium">Recyclable Items:</span> ${recyclableItems}</div>
                        <div><span class="font-medium">Non-Recyclable Items:</span> ${nonRecyclableItems}</div>
                        <div><span class="font-medium">Bounding Boxes:</span> ${totalItems}</div>
                        <div><span class="font-medium">Segmentation Masks:</span> ${rawItems.filter(item => item.mask).length}</div>
                    `;
                }
            } else if (result.analysis_type === 'spatial') {
                // Check if analysis failed
                const imageAnalysis = result.result?.image_analysis || {};
                const hasError = imageAnalysis.error || imageAnalysis.status === 'failed';
                
                if (hasError) {
                    details = `
                        <div class="text-red-600"><span class="font-medium">Status:</span> ${imageAnalysis.status || 'failed'}</div>
                        <div class="text-red-600"><span class="font-medium">Error:</span> ${imageAnalysis.error || 'Analysis failed'}</div>
                        <div><span class="font-medium">Total Detections:</span> 0</div>
                        <div><span class="font-medium">Image Quality:</span> unknown</div>
                        <div><span class="font-medium">Lighting Conditions:</span> unknown</div>
                    `;
                } else {
                    // Check both normalized and raw response for detections
                    const normalizedDetections = result.result?.detections || [];
                    const rawDetections = result.result?.raw_response?.detections || [];
                    const totalDetections = Math.max(normalizedDetections.length, rawDetections.length);
                    
                    console.log('Spatial analysis details:', {
                        normalizedDetections: normalizedDetections.length,
                        rawDetections: rawDetections.length,
                        totalDetections: totalDetections
                    });
                    
                    details = `
                        <div><span class="font-medium">Total Detections:</span> ${totalDetections}</div>
                        <div><span class="font-medium">Image Quality:</span> ${result.result.image_analysis?.image_quality || result.result.raw_response?.image_analysis?.image_quality || 'N/A'}</div>
                        <div><span class="font-medium">Lighting Conditions:</span> ${result.result.image_analysis?.lighting_conditions || result.result.raw_response?.image_analysis?.lighting_conditions || 'N/A'}</div>
                        <div><span class="font-medium">Bounding Boxes:</span> ${totalDetections}</div>
                        <div><span class="font-medium">Segmentation Masks:</span> ${rawDetections.filter(d => d.mask).length}</div>
                    `;
                }
            }
            
            return details;
        }

        // Utility functions
        function showLoading() {
            document.getElementById('loading-overlay').classList.remove('hidden');
        }

        function hideLoading() {
            document.getElementById('loading-overlay').classList.add('hidden');
        }

        function closeResultsModal() {
            document.getElementById('results-modal').classList.add('hidden');
        }

        function showNotification(message, type = 'info') {
            const colors = {
                success: 'bg-green-500',
                error: 'bg-red-500',
                warning: 'bg-yellow-500',
                info: 'bg-blue-500'
            };
            
            const notification = document.createElement('div');
            notification.className = `fixed top-4 right-4 ${colors[type]} text-white px-6 py-3 rounded-lg shadow-lg z-50`;
            notification.textContent = message;
            
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.remove();
            }, 3000);
        }

        function updateRecentTestsCount() {
            const count = document.querySelectorAll('#results-container .result-card').length;
            document.getElementById('recent-tests').textContent = count;
        }

        function clearResults() {
            if (confirm('Are you sure you want to clear all results?')) {
                fetch('/gemini/dashboard/clear-results', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                }).then(() => {
                    document.getElementById('results-container').innerHTML = `
                        <div class="text-center py-12 text-gray-500">
                            <i class="fas fa-search text-4xl mb-4"></i>
                            <p class="text-lg">No analysis results yet</p>
                            <p class="text-sm">Upload an image or test with sample images to see results</p>
                        </div>
                    `;
                    updateRecentTestsCount();
                    showNotification('Results cleared successfully', 'success');
                });
            }
        }

        function refreshResults() {
            // Preserve current page when refreshing
            const urlParams = new URLSearchParams(window.location.search);
            const currentPage = urlParams.get('page') || 1;
            window.location.href = `{{ route('gemini.dashboard') }}?page=${currentPage}`;
        }

        function refreshStatus() {
            fetch('/gemini/dashboard/status')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('active-models').textContent = data.data.active_configurations;
                        document.getElementById('last-updated').textContent = `Last updated: ${new Date().toLocaleTimeString()}`;
                        showNotification('Status refreshed', 'success');
                    }
                });
        }

        // Auto-refresh status every 30 seconds
        setInterval(refreshStatus, 30000);
    </script>
</body>
</html>
