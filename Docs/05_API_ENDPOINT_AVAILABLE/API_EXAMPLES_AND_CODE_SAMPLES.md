# 💻 API Examples & Code Samples - MyRVM-Ecosystem v2.0

## 📍 Code Examples Overview

### Programming Languages
- **JavaScript/Node.js**: Frontend and backend integration
- **Python**: Jetson integration and data processing
- **PHP**: Server-side API development
- **cURL**: Command-line API testing
- **Postman**: API testing and documentation

---

## 🚀 JavaScript/Node.js Examples

### Basic API Client
```javascript
// api-client.js
class MyRVMAPIClient {
    constructor(baseURL, apiKey) {
        this.baseURL = baseURL;
        this.apiKey = apiKey;
        this.headers = {
            'Authorization': `Bearer ${apiKey}`,
            'Content-Type': 'application/json'
        };
    }

    async request(endpoint, options = {}) {
        const url = `${this.baseURL}${endpoint}`;
        const config = {
            headers: this.headers,
            ...options
        };

        try {
            const response = await fetch(url, config);
            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.message || `HTTP ${response.status}`);
            }

            return data;
        } catch (error) {
            console.error('API request failed:', error);
            throw error;
        }
    }

    // RVM Management
    async getRvms(params = {}) {
        const queryString = new URLSearchParams(params).toString();
        return this.request(`/api/v2/rvms?${queryString}`);
    }

    async getRvm(id) {
        return this.request(`/api/v2/rvms/${id}`);
    }

    async createRvm(rvmData) {
        return this.request('/api/v2/rvms', {
            method: 'POST',
            body: JSON.stringify(rvmData)
        });
    }

    async updateRvm(id, rvmData) {
        return this.request(`/api/v2/rvms/${id}`, {
            method: 'PUT',
            body: JSON.stringify(rvmData)
        });
    }

    async deleteRvm(id) {
        return this.request(`/api/v2/rvms/${id}`, {
            method: 'DELETE'
        });
    }

    // Detection Results
    async getDetectionResults(params = {}) {
        const queryString = new URLSearchParams(params).toString();
        return this.request(`/api/v2/detection-results?${queryString}`);
    }

    async createDetectionResult(detectionData) {
        return this.request('/api/v2/detection-results', {
            method: 'POST',
            body: JSON.stringify(detectionData)
        });
    }

    // Analytics
    async getAnalytics(params = {}) {
        const queryString = new URLSearchParams(params).toString();
        return this.request(`/api/v2/analytics/dashboard?${queryString}`);
    }

    // Economy
    async getBalance() {
        return this.request('/api/v2/economy/balance');
    }

    async createTransaction(transactionData) {
        return this.request('/api/v2/economy/transactions', {
            method: 'POST',
            body: JSON.stringify(transactionData)
        });
    }

    // Monitoring
    async getHealth() {
        return this.request('/api/v2/monitoring/health');
    }

    async getMetrics() {
        return this.request('/api/v2/monitoring/metrics');
    }
}

// Usage example
const api = new MyRVMAPIClient(
    'http://100.123.143.87:8001',
    '38bbe1d2ecf75df21546b05340f5878a24e74ed0d8b88d75db1ddeff198380c1'
);

// Get all RVMs
api.getRvms()
    .then(data => console.log('RVMs:', data))
    .catch(error => console.error('Error:', error));

// Create a new RVM
api.createRvm({
    name: 'Test RVM',
    location: 'Test Location',
    ip_address: '192.168.1.100',
    capacity: 100
})
    .then(data => console.log('Created RVM:', data))
    .catch(error => console.error('Error:', error));
```

### Vue.js Integration
```vue
<!-- RvmList.vue -->
<template>
  <div class="rvm-list">
    <h2>RVM Management</h2>
    
    <!-- Add RVM Form -->
    <form @submit.prevent="createRvm" class="mb-6">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <input
          v-model="newRvm.name"
          type="text"
          placeholder="RVM Name"
          class="px-3 py-2 border rounded-lg"
          required
        />
        <input
          v-model="newRvm.location"
          type="text"
          placeholder="Location"
          class="px-3 py-2 border rounded-lg"
          required
        />
        <input
          v-model="newRvm.ip_address"
          type="text"
          placeholder="IP Address"
          class="px-3 py-2 border rounded-lg"
          required
        />
        <input
          v-model.number="newRvm.capacity"
          type="number"
          placeholder="Capacity"
          class="px-3 py-2 border rounded-lg"
          required
        />
      </div>
      <button
        type="submit"
        :disabled="isCreating"
        class="mt-4 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50"
      >
        {{ isCreating ? 'Creating...' : 'Create RVM' }}
      </button>
    </form>

    <!-- RVM List -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <div
        v-for="rvm in rvms"
        :key="rvm.id"
        class="bg-white rounded-lg shadow p-6"
      >
        <h3 class="text-lg font-semibold">{{ rvm.name }}</h3>
        <p class="text-gray-600">{{ rvm.location }}</p>
        <p class="text-sm text-gray-500">IP: {{ rvm.ip_address }}</p>
        <p class="text-sm text-gray-500">Capacity: {{ rvm.capacity }}</p>
        <p class="text-sm text-gray-500">Load: {{ rvm.current_load }} ({{ rvm.load_percentage }}%)</p>
        
        <div class="mt-4 flex space-x-2">
          <button
            @click="editRvm(rvm)"
            class="px-3 py-1 bg-yellow-600 text-white rounded text-sm hover:bg-yellow-700"
          >
            Edit
          </button>
          <button
            @click="deleteRvm(rvm.id)"
            class="px-3 py-1 bg-red-600 text-white rounded text-sm hover:bg-red-700"
          >
            Delete
          </button>
        </div>
      </div>
    </div>

    <!-- Pagination -->
    <div v-if="pagination" class="mt-6 flex justify-center">
      <button
        v-if="pagination.current_page > 1"
        @click="loadRvms(pagination.current_page - 1)"
        class="px-3 py-1 bg-gray-600 text-white rounded mr-2"
      >
        Previous
      </button>
      <span class="px-3 py-1 bg-gray-200 rounded">
        Page {{ pagination.current_page }} of {{ pagination.last_page }}
      </span>
      <button
        v-if="pagination.current_page < pagination.last_page"
        @click="loadRvms(pagination.current_page + 1)"
        class="px-3 py-1 bg-gray-600 text-white rounded ml-2"
      >
        Next
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { MyRVMAPIClient } from '@/utils/api-client'

const api = new MyRVMAPIClient(
  'http://100.123.143.87:8001',
  '38bbe1d2ecf75df21546b05340f5878a24e74ed0d8b88d75db1ddeff198380c1'
)

const rvms = ref([])
const pagination = ref(null)
const isCreating = ref(false)
const newRvm = ref({
  name: '',
  location: '',
  ip_address: '',
  capacity: 100
})

const loadRvms = async (page = 1) => {
  try {
    const response = await api.getRvms({ page })
    rvms.value = response.data
    pagination.value = response.pagination
  } catch (error) {
    console.error('Failed to load RVMs:', error)
  }
}

const createRvm = async () => {
  isCreating.value = true
  try {
    await api.createRvm(newRvm.value)
    newRvm.value = { name: '', location: '', ip_address: '', capacity: 100 }
    await loadRvms()
  } catch (error) {
    console.error('Failed to create RVM:', error)
  } finally {
    isCreating.value = false
  }
}

const editRvm = (rvm) => {
  // Implement edit functionality
  console.log('Edit RVM:', rvm)
}

const deleteRvm = async (id) => {
  if (confirm('Are you sure you want to delete this RVM?')) {
    try {
      await api.deleteRvm(id)
      await loadRvms()
    } catch (error) {
      console.error('Failed to delete RVM:', error)
    }
  }
}

onMounted(() => {
  loadRvms()
})
</script>
```

### Real-time Updates with WebSocket
```javascript
// websocket-client.js
import Echo from 'laravel-echo'
import Pusher from 'pusher-js'

window.Pusher = Pusher

const echo = new Echo({
    broadcaster: 'pusher',
    key: process.env.MIX_PUSHER_APP_KEY,
    cluster: process.env.MIX_PUSHER_APP_CLUSTER,
    forceTLS: true
})

// RVM updates
echo.channel('rvm-updates')
    .listen('rvm.updated', (data) => {
        console.log('RVM updated:', data)
        // Update RVM data in your application
        updateRvmInList(data)
    })

// Detection updates
echo.channel('detection-updates')
    .listen('detection.created', (data) => {
        console.log('New detection:', data)
        // Add new detection to your application
        addDetectionToList(data)
    })

// System alerts
echo.channel('system-alerts')
    .listen('alert.created', (data) => {
        console.log('System alert:', data)
        // Show alert notification
        showAlert(data)
    })

function updateRvmInList(rvmData) {
    // Update RVM in your list
    const rvmIndex = rvms.value.findIndex(rvm => rvm.id === rvmData.id)
    if (rvmIndex !== -1) {
        rvms.value[rvmIndex] = { ...rvms.value[rvmIndex], ...rvmData }
    }
}

function addDetectionToList(detectionData) {
    // Add new detection to your list
    detections.value.unshift(detectionData)
}

function showAlert(alertData) {
    // Show alert notification
    if (alertData.severity === 'high') {
        // Show critical alert
        showCriticalAlert(alertData.message)
    } else {
        // Show normal alert
        showNormalAlert(alertData.message)
    }
}
```

---

## 🐍 Python Examples

### Jetson API Client
```python
# jetson_api_client.py
import requests
import time
import logging
from typing import Dict, Any, Optional, List

logger = logging.getLogger(__name__)

class JetsonAPIClient:
    def __init__(self, base_url: str, api_key: str):
        self.base_url = base_url
        self.api_key = api_key
        self.headers = {
            'Authorization': f'Bearer {api_key}',
            'Content-Type': 'application/json'
        }
        self.session = requests.Session()
        self.session.headers.update(self.headers)

    def _make_request(self, method: str, endpoint: str, **kwargs) -> Dict[str, Any]:
        """Make HTTP request with error handling"""
        url = f"{self.base_url}{endpoint}"
        
        try:
            response = self.session.request(method, url, **kwargs)
            response.raise_for_status()
            return response.json()
        except requests.exceptions.RequestException as e:
            logger.error(f"Request failed: {e}")
            raise

    def health_check(self) -> Dict[str, Any]:
        """Check API health"""
        return self._make_request('GET', '/api/health')

    def get_status(self) -> Dict[str, Any]:
        """Get system status"""
        return self._make_request('GET', '/api/status')

    def validate_rvm(self, api_key: str) -> Dict[str, Any]:
        """Validate RVM API key"""
        return self._make_request('POST', '/api/rvm/validate', 
                                json={'api_key': api_key})

    def send_detection_result(self, detection_data: Dict[str, Any]) -> Dict[str, Any]:
        """Send detection result to server"""
        return self._make_request('POST', '/api/detection', 
                                json=detection_data)

    def update_status(self, status_data: Dict[str, Any]) -> Dict[str, Any]:
        """Update RVM status"""
        return self._make_request('POST', '/api/rvm/status', 
                                json=status_data)

    def get_monitoring_status(self) -> Dict[str, Any]:
        """Get monitoring status"""
        return self._make_request('GET', '/api/monitoring/status')

    def get_monitoring_summary(self) -> Dict[str, Any]:
        """Get monitoring summary"""
        return self._make_request('GET', '/api/monitoring/summary')

    def get_alerts(self) -> Dict[str, Any]:
        """Get system alerts"""
        return self._make_request('GET', '/api/monitoring/alerts')

# Usage example
def main():
    # Initialize client
    client = JetsonAPIClient(
        base_url='http://100.117.234.2:5000',
        api_key='38bbe1d2ecf75df21546b05340f5878a24e74ed0d8b88d75db1ddeff198380c1'
    )

    # Health check
    try:
        health = client.health_check()
        print(f"API Health: {health['status']}")
    except Exception as e:
        print(f"Health check failed: {e}")

    # Get system status
    try:
        status = client.get_status()
        print(f"System Status: {status}")
    except Exception as e:
        print(f"Status check failed: {e}")

    # Send detection result
    detection_data = {
        'rvm_id': 1,
        'image_path': '/path/to/image.jpg',
        'detection_results': [
            {
                'class': 'bottle',
                'confidence': 0.95,
                'bbox': [100, 100, 200, 200]
            }
        ],
        'processing_time': 1.5
    }

    try:
        result = client.send_detection_result(detection_data)
        print(f"Detection result sent: {result}")
    except Exception as e:
        print(f"Failed to send detection result: {e}")

if __name__ == '__main__':
    main()
```

### Detection Processing
```python
# detection_processor.py
import cv2
import numpy as np
from typing import List, Dict, Any
import time

class DetectionProcessor:
    def __init__(self, api_client: JetsonAPIClient):
        self.api_client = api_client
        self.detection_count = 0

    def process_image(self, image_path: str, rvm_id: int) -> Dict[str, Any]:
        """Process image and return detection results"""
        start_time = time.time()
        
        # Load image
        image = cv2.imread(image_path)
        if image is None:
            raise ValueError(f"Could not load image: {image_path}")

        # Perform detection (simplified example)
        detections = self._detect_objects(image)
        
        processing_time = time.time() - start_time
        
        # Prepare detection data
        detection_data = {
            'rvm_id': rvm_id,
            'image_path': image_path,
            'detection_results': detections,
            'processing_time': processing_time
        }

        # Send to server
        try:
            result = self.api_client.send_detection_result(detection_data)
            self.detection_count += 1
            return result
        except Exception as e:
            logger.error(f"Failed to send detection result: {e}")
            raise

    def _detect_objects(self, image: np.ndarray) -> List[Dict[str, Any]]:
        """Detect objects in image (simplified example)"""
        # This is a simplified example
        # In real implementation, you would use YOLO + SAM2
        
        # Simulate detection results
        detections = [
            {
                'class': 'bottle',
                'confidence': 0.95,
                'bbox': [100, 100, 200, 200]
            },
            {
                'class': 'can',
                'confidence': 0.87,
                'bbox': [300, 150, 400, 250]
            }
        ]
        
        return detections

    def process_batch(self, image_paths: List[str], rvm_id: int) -> List[Dict[str, Any]]:
        """Process multiple images in batch"""
        results = []
        
        for image_path in image_paths:
            try:
                result = self.process_image(image_path, rvm_id)
                results.append(result)
            except Exception as e:
                logger.error(f"Failed to process image {image_path}: {e}")
                results.append({'error': str(e)})
        
        return results

# Usage example
def main():
    # Initialize API client
    api_client = JetsonAPIClient(
        base_url='http://100.117.234.2:5000',
        api_key='38bbe1d2ecf75df21546b05340f5878a24e74ed0d8b88d75db1ddeff198380c1'
    )

    # Initialize detection processor
    processor = DetectionProcessor(api_client)

    # Process single image
    result = processor.process_image('/path/to/image.jpg', rvm_id=1)
    print(f"Detection result: {result}")

    # Process batch of images
    image_paths = ['/path/to/image1.jpg', '/path/to/image2.jpg']
    results = processor.process_batch(image_paths, rvm_id=1)
    print(f"Batch results: {results}")

if __name__ == '__main__':
    main()
```

### Monitoring System
```python
# monitoring_system.py
import psutil
import time
import logging
from typing import Dict, Any
from dataclasses import dataclass

logger = logging.getLogger(__name__)

@dataclass
class SystemMetrics:
    cpu_usage: float
    memory_usage: float
    disk_usage: float
    gpu_usage: float
    timestamp: float

class MonitoringSystem:
    def __init__(self, api_client: JetsonAPIClient):
        self.api_client = api_client
        self.metrics_history = []
        self.max_history = 1000

    def collect_metrics(self) -> SystemMetrics:
        """Collect system metrics"""
        # CPU usage
        cpu_usage = psutil.cpu_percent(interval=1)
        
        # Memory usage
        memory = psutil.virtual_memory()
        memory_usage = memory.percent
        
        # Disk usage
        disk = psutil.disk_usage('/')
        disk_usage = (disk.used / disk.total) * 100
        
        # GPU usage (if available)
        try:
            import GPUtil
            gpus = GPUtil.getGPUs()
            gpu_usage = gpus[0].load * 100 if gpus else 0
        except ImportError:
            gpu_usage = 0
        
        metrics = SystemMetrics(
            cpu_usage=cpu_usage,
            memory_usage=memory_usage,
            disk_usage=disk_usage,
            gpu_usage=gpu_usage,
            timestamp=time.time()
        )
        
        # Store in history
        self.metrics_history.append(metrics)
        if len(self.metrics_history) > self.max_history:
            self.metrics_history.pop(0)
        
        return metrics

    def check_alerts(self, metrics: SystemMetrics) -> List[Dict[str, Any]]:
        """Check for alerts based on metrics"""
        alerts = []
        
        if metrics.cpu_usage > 90:
            alerts.append({
                'type': 'high_cpu_usage',
                'message': f'High CPU usage: {metrics.cpu_usage:.1f}%',
                'severity': 'high',
                'value': metrics.cpu_usage,
                'threshold': 90
            })
        
        if metrics.memory_usage > 90:
            alerts.append({
                'type': 'high_memory_usage',
                'message': f'High memory usage: {metrics.memory_usage:.1f}%',
                'severity': 'high',
                'value': metrics.memory_usage,
                'threshold': 90
            })
        
        if metrics.disk_usage > 90:
            alerts.append({
                'type': 'high_disk_usage',
                'message': f'High disk usage: {metrics.disk_usage:.1f}%',
                'severity': 'high',
                'value': metrics.disk_usage,
                'threshold': 90
            })
        
        if metrics.gpu_usage > 90:
            alerts.append({
                'type': 'high_gpu_usage',
                'message': f'High GPU usage: {metrics.gpu_usage:.1f}%',
                'severity': 'high',
                'value': metrics.gpu_usage,
                'threshold': 90
            })
        
        return alerts

    def send_metrics(self, metrics: SystemMetrics) -> bool:
        """Send metrics to server"""
        try:
            # This would be implemented based on your server API
            # For now, we'll just log the metrics
            logger.info(f"Metrics: CPU={metrics.cpu_usage:.1f}%, "
                       f"Memory={metrics.memory_usage:.1f}%, "
                       f"Disk={metrics.disk_usage:.1f}%, "
                       f"GPU={metrics.gpu_usage:.1f}%")
            return True
        except Exception as e:
            logger.error(f"Failed to send metrics: {e}")
            return False

    def run_monitoring(self, interval: int = 60):
        """Run monitoring loop"""
        logger.info("Starting monitoring system...")
        
        while True:
            try:
                # Collect metrics
                metrics = self.collect_metrics()
                
                # Check for alerts
                alerts = self.check_alerts(metrics)
                
                # Send alerts
                for alert in alerts:
                    logger.warning(f"Alert: {alert['message']}")
                
                # Send metrics
                self.send_metrics(metrics)
                
                # Wait for next interval
                time.sleep(interval)
                
            except KeyboardInterrupt:
                logger.info("Monitoring stopped by user")
                break
            except Exception as e:
                logger.error(f"Monitoring error: {e}")
                time.sleep(interval)

# Usage example
def main():
    # Initialize API client
    api_client = JetsonAPIClient(
        base_url='http://100.117.234.2:5000',
        api_key='38bbe1d2ecf75df21546b05340f5878a24e74ed0d8b88d75db1ddeff198380c1'
    )

    # Initialize monitoring system
    monitoring = MonitoringSystem(api_client)

    # Run monitoring
    monitoring.run_monitoring(interval=60)

if __name__ == '__main__':
    main()
```

---

## 🔧 cURL Examples

### Basic API Operations
```bash
#!/bin/bash
# api_examples.sh

# Set variables
API_BASE_URL="http://100.123.143.87:8001"
API_KEY="38bbe1d2ecf75df21546b05340f5878a24e74ed0d8b88d75db1ddeff198380c1"
JETSON_URL="http://100.117.234.2:5000"

echo "🚀 MyRVM API Examples"
echo "===================="

# 1. Health Check
echo "1. Health Check"
curl -s "$API_BASE_URL/api/v2/health" | jq '.'
echo ""

# 2. Get RVMs
echo "2. Get RVMs"
curl -s -H "Authorization: Bearer $API_KEY" \
     "$API_BASE_URL/api/v2/rvms" | jq '.'
echo ""

# 3. Create RVM
echo "3. Create RVM"
curl -s -X POST "$API_BASE_URL/api/v2/rvms" \
     -H "Authorization: Bearer $API_KEY" \
     -H "Content-Type: application/json" \
     -d '{
       "name": "Test RVM",
       "location": "Test Location",
       "ip_address": "192.168.1.100",
       "capacity": 100
     }' | jq '.'
echo ""

# 4. Get RVM Details
echo "4. Get RVM Details"
RVM_ID=$(curl -s -H "Authorization: Bearer $API_KEY" \
              "$API_BASE_URL/api/v2/rvms" | jq -r '.data[0].id')
curl -s -H "Authorization: Bearer $API_KEY" \
     "$API_BASE_URL/api/v2/rvms/$RVM_ID" | jq '.'
echo ""

# 5. Update RVM
echo "5. Update RVM"
curl -s -X PUT "$API_BASE_URL/api/v2/rvms/$RVM_ID" \
     -H "Authorization: Bearer $API_KEY" \
     -H "Content-Type: application/json" \
     -d '{
       "name": "Updated RVM",
       "location": "Updated Location"
     }' | jq '.'
echo ""

# 6. Get Detection Results
echo "6. Get Detection Results"
curl -s -H "Authorization: Bearer $API_KEY" \
     "$API_BASE_URL/api/v2/detection-results" | jq '.'
echo ""

# 7. Create Detection Result
echo "7. Create Detection Result"
curl -s -X POST "$API_BASE_URL/api/v2/detection-results" \
     -H "Authorization: Bearer $API_KEY" \
     -H "Content-Type: application/json" \
     -d '{
       "rvm_id": '$RVM_ID',
       "image_path": "/path/to/test/image.jpg",
       "detection_results": [
         {
           "class": "bottle",
           "confidence": 0.95,
           "bbox": [100, 100, 200, 200]
         }
       ],
       "processing_time": 1.5
     }' | jq '.'
echo ""

# 8. Get Analytics
echo "8. Get Analytics"
curl -s -H "Authorization: Bearer $API_KEY" \
     "$API_BASE_URL/api/v2/analytics/dashboard" | jq '.'
echo ""

# 9. Get Economy Balance
echo "9. Get Economy Balance"
curl -s -H "Authorization: Bearer $API_KEY" \
     "$API_BASE_URL/api/v2/economy/balance" | jq '.'
echo ""

# 10. Get Monitoring Metrics
echo "10. Get Monitoring Metrics"
curl -s -H "Authorization: Bearer $API_KEY" \
     "$API_BASE_URL/api/v2/monitoring/metrics" | jq '.'
echo ""

# 11. Jetson Health Check
echo "11. Jetson Health Check"
curl -s "$JETSON_URL/api/health" | jq '.'
echo ""

# 12. Jetson Status
echo "12. Jetson Status"
curl -s "$JETSON_URL/api/status" | jq '.'
echo ""

# 13. Jetson Monitoring
echo "13. Jetson Monitoring"
curl -s "$JETSON_URL/api/monitoring/status" | jq '.'
echo ""

# 14. Delete RVM
echo "14. Delete RVM"
curl -s -X DELETE "$API_BASE_URL/api/v2/rvms/$RVM_ID" \
     -H "Authorization: Bearer $API_KEY" | jq '.'
echo ""

echo "✅ API Examples completed!"
```

### Error Handling Examples
```bash
#!/bin/bash
# error_handling_examples.sh

API_BASE_URL="http://100.123.143.87:8001"
API_KEY="38bbe1d2ecf75df21546b05340f5878a24e74ed0d8b88d75db1ddeff198380c1"

echo "🚨 Error Handling Examples"
echo "========================="

# 1. Missing API Key
echo "1. Missing API Key (401 Unauthorized)"
curl -s "$API_BASE_URL/api/v2/rvms" | jq '.'
echo ""

# 2. Invalid API Key
echo "2. Invalid API Key (401 Unauthorized)"
curl -s -H "Authorization: Bearer invalid_key" \
     "$API_BASE_URL/api/v2/rvms" | jq '.'
echo ""

# 3. Invalid Endpoint
echo "3. Invalid Endpoint (404 Not Found)"
curl -s -H "Authorization: Bearer $API_KEY" \
     "$API_BASE_URL/api/v2/invalid-endpoint" | jq '.'
echo ""

# 4. Invalid RVM ID
echo "4. Invalid RVM ID (404 Not Found)"
curl -s -H "Authorization: Bearer $API_KEY" \
     "$API_BASE_URL/api/v2/rvms/99999" | jq '.'
echo ""

# 5. Validation Error
echo "5. Validation Error (422 Unprocessable Entity)"
curl -s -X POST "$API_BASE_URL/api/v2/rvms" \
     -H "Authorization: Bearer $API_KEY" \
     -H "Content-Type: application/json" \
     -d '{
       "name": "",
       "location": "Test Location",
       "ip_address": "invalid-ip",
       "capacity": -1
     }' | jq '.'
echo ""

# 6. Rate Limit Exceeded
echo "6. Rate Limit Exceeded (429 Too Many Requests)"
for i in {1..10}; do
    curl -s -H "Authorization: Bearer $API_KEY" \
         "$API_BASE_URL/api/v2/rvms" > /dev/null
    echo "Request $i completed"
done
echo ""

echo "✅ Error handling examples completed!"
```

---

## 📊 Postman Collection

### Postman Collection JSON
```json
{
  "info": {
    "name": "MyRVM Ecosystem API v2.0",
    "description": "Complete API collection for MyRVM Ecosystem v2.0",
    "version": "2.0.0",
    "schema": "https://schema.getpostman.com/json/collection/v2.1.0/collection.json"
  },
  "variable": [
    {
      "key": "base_url",
      "value": "http://100.123.143.87:8001",
      "type": "string"
    },
    {
      "key": "jetson_url",
      "value": "http://100.117.234.2:5000",
      "type": "string"
    },
    {
      "key": "api_key",
      "value": "38bbe1d2ecf75df21546b05340f5878a24e74ed0d8b88d75db1ddeff198380c1",
      "type": "string"
    }
  ],
  "auth": {
    "type": "bearer",
    "bearer": [
      {
        "key": "token",
        "value": "{{api_key}}",
        "type": "string"
      }
    ]
  },
  "item": [
    {
      "name": "Health Check",
      "request": {
        "method": "GET",
        "header": [],
        "url": {
          "raw": "{{base_url}}/api/v2/health",
          "host": ["{{base_url}}"],
          "path": ["api", "v2", "health"]
        }
      }
    },
    {
      "name": "RVM Management",
      "item": [
        {
          "name": "Get RVMs",
          "request": {
            "method": "GET",
            "header": [],
            "url": {
              "raw": "{{base_url}}/api/v2/rvms?page=1&per_page=15",
              "host": ["{{base_url}}"],
              "path": ["api", "v2", "rvms"],
              "query": [
                {
                  "key": "page",
                  "value": "1"
                },
                {
                  "key": "per_page",
                  "value": "15"
                }
              ]
            }
          }
        },
        {
          "name": "Create RVM",
          "request": {
            "method": "POST",
            "header": [
              {
                "key": "Content-Type",
                "value": "application/json"
              }
            ],
            "body": {
              "mode": "raw",
              "raw": "{\n  \"name\": \"Test RVM\",\n  \"location\": \"Test Location\",\n  \"ip_address\": \"192.168.1.100\",\n  \"capacity\": 100\n}"
            },
            "url": {
              "raw": "{{base_url}}/api/v2/rvms",
              "host": ["{{base_url}}"],
              "path": ["api", "v2", "rvms"]
            }
          }
        },
        {
          "name": "Get RVM Details",
          "request": {
            "method": "GET",
            "header": [],
            "url": {
              "raw": "{{base_url}}/api/v2/rvms/1",
              "host": ["{{base_url}}"],
              "path": ["api", "v2", "rvms", "1"]
            }
          }
        },
        {
          "name": "Update RVM",
          "request": {
            "method": "PUT",
            "header": [
              {
                "key": "Content-Type",
                "value": "application/json"
              }
            ],
            "body": {
              "mode": "raw",
              "raw": "{\n  \"name\": \"Updated RVM\",\n  \"location\": \"Updated Location\"\n}"
            },
            "url": {
              "raw": "{{base_url}}/api/v2/rvms/1",
              "host": ["{{base_url}}"],
              "path": ["api", "v2", "rvms", "1"]
            }
          }
        },
        {
          "name": "Delete RVM",
          "request": {
            "method": "DELETE",
            "header": [],
            "url": {
              "raw": "{{base_url}}/api/v2/rvms/1",
              "host": ["{{base_url}}"],
              "path": ["api", "v2", "rvms", "1"]
            }
          }
        }
      ]
    },
    {
      "name": "Detection Results",
      "item": [
        {
          "name": "Get Detection Results",
          "request": {
            "method": "GET",
            "header": [],
            "url": {
              "raw": "{{base_url}}/api/v2/detection-results?page=1&per_page=15",
              "host": ["{{base_url}}"],
              "path": ["api", "v2", "detection-results"],
              "query": [
                {
                  "key": "page",
                  "value": "1"
                },
                {
                  "key": "per_page",
                  "value": "15"
                }
              ]
            }
          }
        },
        {
          "name": "Create Detection Result",
          "request": {
            "method": "POST",
            "header": [
              {
                "key": "Content-Type",
                "value": "application/json"
              }
            ],
            "body": {
              "mode": "raw",
              "raw": "{\n  \"rvm_id\": 1,\n  \"image_path\": \"/path/to/test/image.jpg\",\n  \"detection_results\": [\n    {\n      \"class\": \"bottle\",\n      \"confidence\": 0.95,\n      \"bbox\": [100, 100, 200, 200]\n    }\n  ],\n  \"processing_time\": 1.5\n}"
            },
            "url": {
              "raw": "{{base_url}}/api/v2/detection-results",
              "host": ["{{base_url}}"],
              "path": ["api", "v2", "detection-results"]
            }
          }
        }
      ]
    },
    {
      "name": "Analytics",
      "item": [
        {
          "name": "Get Dashboard Analytics",
          "request": {
            "method": "GET",
            "header": [],
            "url": {
              "raw": "{{base_url}}/api/v2/analytics/dashboard",
              "host": ["{{base_url}}"],
              "path": ["api", "v2", "analytics", "dashboard"]
            }
          }
        }
      ]
    },
    {
      "name": "Economy",
      "item": [
        {
          "name": "Get Balance",
          "request": {
            "method": "GET",
            "header": [],
            "url": {
              "raw": "{{base_url}}/api/v2/economy/balance",
              "host": ["{{base_url}}"],
              "path": ["api", "v2", "economy", "balance"]
            }
          }
        },
        {
          "name": "Create Transaction",
          "request": {
            "method": "POST",
            "header": [
              {
                "key": "Content-Type",
                "value": "application/json"
              }
            ],
            "body": {
              "mode": "raw",
              "raw": "{\n  \"type\": \"reward\",\n  \"amount\": 10.50,\n  \"description\": \"Detection reward\"\n}"
            },
            "url": {
              "raw": "{{base_url}}/api/v2/economy/transactions",
              "host": ["{{base_url}}"],
              "path": ["api", "v2", "economy", "transactions"]
            }
          }
        }
      ]
    },
    {
      "name": "Monitoring",
      "item": [
        {
          "name": "Get Health",
          "request": {
            "method": "GET",
            "header": [],
            "url": {
              "raw": "{{base_url}}/api/v2/monitoring/health",
              "host": ["{{base_url}}"],
              "path": ["api", "v2", "monitoring", "health"]
            }
          }
        },
        {
          "name": "Get Metrics",
          "request": {
            "method": "GET",
            "header": [],
            "url": {
              "raw": "{{base_url}}/api/v2/monitoring/metrics",
              "host": ["{{base_url}}"],
              "path": ["api", "v2", "monitoring", "metrics"]
            }
          }
        }
      ]
    },
    {
      "name": "Jetson API",
      "item": [
        {
          "name": "Jetson Health",
          "request": {
            "method": "GET",
            "header": [],
            "url": {
              "raw": "{{jetson_url}}/api/health",
              "host": ["{{jetson_url}}"],
              "path": ["api", "health"]
            }
          }
        },
        {
          "name": "Jetson Status",
          "request": {
            "method": "GET",
            "header": [],
            "url": {
              "raw": "{{jetson_url}}/api/status",
              "host": ["{{jetson_url}}"],
              "path": ["api", "status"]
            }
          }
        },
        {
          "name": "Jetson Detection",
          "request": {
            "method": "POST",
            "header": [
              {
                "key": "Content-Type",
                "value": "application/json"
              },
              {
                "key": "Authorization",
                "value": "Bearer {{api_key}}"
              }
            ],
            "body": {
              "mode": "raw",
              "raw": "{\n  \"rvm_id\": 1,\n  \"image_path\": \"/path/to/test/image.jpg\",\n  \"detection_results\": [\n    {\n      \"class\": \"bottle\",\n      \"confidence\": 0.95,\n      \"bbox\": [100, 100, 200, 200]\n    }\n  ],\n  \"processing_time\": 1.5\n}"
            },
            "url": {
              "raw": "{{jetson_url}}/api/detection",
              "host": ["{{jetson_url}}"],
              "path": ["api", "detection"]
            }
          }
        }
      ]
    }
  ]
}
```

---

**Last Updated**: 2025-01-02  
**Version**: 2.0.0  
**Status**: ✅ COMPLETE EXAMPLES & CODE SAMPLES DOCUMENTATION
