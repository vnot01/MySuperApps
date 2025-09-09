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
            const result = currentResults.find(r => r.id === resultId);
            if (!result) return;
            
            const modalContent = document.getElementById('modal-content');
            modalContent.innerHTML = `
                <div class="space-y-4">
                    <div class="flex items-center space-x-4">
                        <img src="${result.image_url}" alt="Analysis result" class="w-24 h-24 object-cover rounded-lg border">
                        <div>
                            <h4 class="text-lg font-semibold">${result.analysis_type.charAt(0).toUpperCase() + result.analysis_type.slice(1)} Analysis</h4>
                            <p class="text-sm text-gray-600">${new Date(result.timestamp).toLocaleString()}</p>
                            <p class="text-sm text-gray-600">Model: ${result.config_used?.display_name || 'Unknown'}</p>
                            <p class="text-sm text-gray-600">Processing Time: ${result.processing_time_ms}ms</p>
                        </div>
                    </div>
                    
                    <div class="bg-gray-50 rounded-lg p-4">
                        <h5 class="font-medium mb-2">Raw Analysis Result:</h5>
                        <pre class="text-sm text-gray-700 overflow-x-auto">${JSON.stringify(result.result, null, 2)}</pre>
                    </div>
                </div>
            `;
            
            document.getElementById('results-modal').classList.remove('hidden');
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
