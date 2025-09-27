@php
    // Get confidence based on analysis type
    $confidence = 0;
    
    // Check for single analysis confidence
    if (isset($result['result']['confidence'])) {
        $confidence = $result['result']['confidence'];
    }
    // Check for spatial analysis confidence
    elseif (isset($result['result']['detections']) && is_array($result['result']['detections']) && count($result['result']['detections']) > 0) {
        $confidence = $result['result']['detections'][0]['confidence'] ?? 0;
    }
    // Check for multiple analysis confidence
    elseif (isset($result['result']['items']) && is_array($result['result']['items']) && count($result['result']['items']) > 0) {
        $confidences = array_column($result['result']['items'], 'confidence');
        $confidence = count($confidences) > 0 ? round(array_sum($confidences) / count($confidences), 2) : 0;
    }
    
    $successClass = $result['success'] ? 'result-card' : 'result-card error';
    $confidenceColor = $confidence >= 80 ? 'bg-green-500' : ($confidence >= 60 ? 'bg-yellow-500' : 'bg-red-500');
@endphp

<div class="{{ $successClass }} bg-white border border-gray-200 rounded-lg p-4 shadow-sm">
    <div class="flex justify-between items-start mb-3">
        <div class="flex items-center space-x-3">
            <img src="{{ $result['image_url'] }}" alt="Analysis result" class="w-16 h-16 object-cover rounded-lg border">
            <div>
                <h4 class="font-medium text-gray-900">{{ ucfirst($result['analysis_type']) }} Analysis</h4>
                <p class="text-sm text-gray-600">{{ \Carbon\Carbon::parse($result['timestamp'])->format('M d, Y H:i:s') }}</p>
                <p class="text-xs text-gray-500">Model: {{ $result['config_used']['display_name'] ?? 'Unknown' }}</p>
            </div>
        </div>
        <div class="text-right">
            <div class="text-sm font-medium {{ $result['success'] ? 'text-green-600' : 'text-red-600' }}">
                {{ $result['success'] ? 'Success' : 'Failed' }}
            </div>
            <div class="text-xs text-gray-500">{{ $result['processing_time_ms'] }}ms</div>
        </div>
    </div>
    
    @if($result['success'])
        <div class="space-y-2">
            <div class="flex justify-between text-sm">
                <span>Confidence:</span>
                <span class="font-medium">{{ $confidence }}%</span>
            </div>
            <div class="confidence-bar">
                <div class="confidence-fill {{ $confidenceColor }}" style="width: {{ $confidence }}%"></div>
            </div>
            
            @if(isset($result['result']['waste_type']))
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div><span class="font-medium">Waste Type:</span> {{ $result['result']['waste_type'] }}</div>
                    <div><span class="font-medium">Quality Grade:</span> {{ $result['result']['quality_grade'] ?? 'N/A' }}</div>
                    <div><span class="font-medium">Weight:</span> {{ $result['result']['estimated_weight_grams'] ?? 0 }}g</div>
                    <div><span class="font-medium">Quantity:</span> {{ $result['result']['quantity'] ?? 1 }}</div>
                </div>
            @endif
            
            @if(isset($result['result']['items']))
                <div class="text-sm">
                    <span class="font-medium">Items Detected:</span> {{ $result['result']['total_items'] ?? 0 }}
                </div>
            @endif
            
            @if(isset($result['result']['detections']))
                <div class="text-sm">
                    <span class="font-medium">Detections:</span> {{ count($result['result']['detections']) }}
                </div>
                @if(count($result['result']['detections']) > 0)
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div><span class="font-medium">Waste Type:</span> {{ $result['result']['detections'][0]['waste_type'] ?? 'N/A' }}</div>
                        <div><span class="font-medium">Quality Grade:</span> {{ $result['result']['detections'][0]['quality_grade'] ?? 'N/A' }}</div>
                        <div><span class="font-medium">Weight:</span> {{ $result['result']['detections'][0]['estimated_weight_grams'] ?? 0 }}g</div>
                        <div><span class="font-medium">Condition:</span> {{ $result['result']['detections'][0]['condition'] ?? 'N/A' }}</div>
                    </div>
                @endif
            @endif
        </div>
    @else
        <div class="text-red-600 text-sm">
            Error: {{ $result['result']['analysis_details']['error'] ?? 'Unknown error' }}
        </div>
    @endif
    
    <div class="mt-3 flex justify-end">
        <button onclick="viewDetailedResults('{{ $result['id'] }}')" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
            View Details <i class="fas fa-arrow-right ml-1"></i>
        </button>
    </div>
</div>
