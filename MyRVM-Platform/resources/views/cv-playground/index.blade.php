<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Computer Vision Playground V2</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-4xl mx-auto">
            <h1 class="text-3xl font-bold text-gray-800 mb-8">Computer Vision Playground V2</h1>
            
            <!-- Upload Form -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-8">
                <h2 class="text-xl font-semibold mb-4">Upload Model & Image</h2>
                
                <form id="cvTestForm" enctype="multipart/form-data">
                    @csrf
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Model Upload -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">YOLO Model (.pt)</label>
                            <input type="file" name="model_file" id="modelFile" accept=".pt,.pth,.onnx" 
                                   class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" required>
                        </div>
                        
                        <!-- Image Upload -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Test Image</label>
                            <input type="file" name="image_file" id="imageFile" accept="image/*" 
                                   class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-green-50 file:text-green-700 hover:file:bg-green-100" required>
                        </div>
                    </div>
                    
                    <!-- Confidence Slider -->
                    <div class="mt-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Confidence Threshold: <span id="confidenceValue">0.5</span>
                        </label>
                        <input type="range" name="confidence" id="confidenceSlider" min="0.1" max="0.9" step="0.1" value="0.5" 
                               class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer">
                    </div>
                    
                    <!-- Submit Button -->
                    <div class="mt-6">
                        <button type="submit" id="submitBtn" 
                                class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition duration-200">
                            Run CV Test
                        </button>
                    </div>
                </form>
            </div>
            
            <!-- Results Area -->
            <div id="resultsArea" class="hidden">
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-semibold mb-4">Test Results</h2>
                    <div id="resultsContent"></div>
                </div>
            </div>
            
            <!-- Loading Indicator -->
            <div id="loadingIndicator" class="hidden text-center py-8">
                <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                <p class="mt-2 text-gray-600">Processing...</p>
            </div>
        </div>
    </div>

    <script>
        // Update confidence value display
        document.getElementById('confidenceSlider').addEventListener('input', function() {
            document.getElementById('confidenceValue').textContent = this.value;
        });

        // Handle form submission
        document.getElementById('cvTestForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const submitBtn = document.getElementById('submitBtn');
            const loadingIndicator = document.getElementById('loadingIndicator');
            const resultsArea = document.getElementById('resultsArea');
            
            // Show loading
            submitBtn.disabled = true;
            submitBtn.textContent = 'Processing...';
            loadingIndicator.classList.remove('hidden');
            resultsArea.classList.add('hidden');
            
            try {
                const response = await fetch('/cv-playground/run-test', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });
                
                // Check if response is ok
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }
                
                // Check content type
                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    const text = await response.text();
                    console.error('Non-JSON response:', text);
                    throw new Error('Server returned non-JSON response');
                }
                
                const result = await response.json();
                
                if (result.success) {
                    displayResults(result.result);
                } else {
                    displayError(result.error || 'Unknown error occurred');
                }
            } catch (error) {
                console.error('Request error:', error);
                displayError('Network error: ' + error.message);
            } finally {
                // Hide loading
                submitBtn.disabled = false;
                submitBtn.textContent = 'Run CV Test';
                loadingIndicator.classList.add('hidden');
            }
        });

        function displayResults(result) {
            const resultsContent = document.getElementById('resultsContent');
            const resultsArea = document.getElementById('resultsArea');
            
            // Get detections from the correct structure
            const detections = result.yolo_results?.detections || [];
            const totalDetections = detections.length;
            
            resultsContent.innerHTML = `
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h3 class="font-semibold mb-2">Detection Summary</h3>
                        <p><strong>Total Detections:</strong> ${totalDetections}</p>
                        <p><strong>Confidence Threshold:</strong> ${result.confidence_threshold}</p>
                        <p><strong>Timestamp:</strong> ${result.timestamp}</p>
                    </div>
                    <div>
                        <h3 class="font-semibold mb-2">Image Info</h3>
                        <p><strong>Dimensions:</strong> ${result.image_info.width} x ${result.image_info.height}</p>
                        <p><strong>Channels:</strong> ${result.image_info.channels}</p>
                    </div>
                </div>
                
                <div class="mt-6">
                    <h3 class="font-semibold mb-2">Detections</h3>
                    <div class="space-y-2">
                        ${detections.map(detection => `
                            <div class="bg-gray-50 p-3 rounded">
                                <p><strong>Class:</strong> ${detection.class_name}</p>
                                <p><strong>Confidence:</strong> ${(detection.confidence * 100).toFixed(1)}%</p>
                                <p><strong>Bounding Box:</strong> (${detection.bbox.x1.toFixed(1)}, ${detection.bbox.y1.toFixed(1)}) to (${detection.bbox.x2.toFixed(1)}, ${detection.bbox.y2.toFixed(1)})</p>                          
                            </div>
                        `).join('')}
                    </div>
                </div>
                
                ${result.output_images.yolo ? `
                    <div class="mt-6">
                        <h3 class="font-semibold mb-2">Output Images</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <h4 class="font-medium mb-2">YOLO Detection</h4>
                                <img src="/cv-playground/result/${encodeURIComponent(result.output_images.yolo)}" 
                                     alt="YOLO Detection" class="w-full rounded border">
                            </div>
                            ${result.output_images.comparison ? `
                                <div>
                                    <h4 class="font-medium mb-2">Comparison</h4>
                                    <img src="/cv-playground/result/${encodeURIComponent(result.output_images.comparison)}" 
                                         alt="Comparison" class="w-full rounded border">
                                </div>
                            ` : ''}
                        </div>
                    </div>
                ` : ''}
            `;
            
            resultsArea.classList.remove('hidden');
        }

        function displayError(error) {
            const resultsContent = document.getElementById('resultsContent');
            const resultsArea = document.getElementById('resultsArea');
            
            resultsContent.innerHTML = `
                <div class="bg-red-50 border border-red-200 rounded-md p-4">
                    <div class="flex">
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-red-800">Error</h3>
                            <div class="mt-2 text-sm text-red-700">
                                <p>${error}</p>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            resultsArea.classList.remove('hidden');
        }
    </script>
</body>
</html>