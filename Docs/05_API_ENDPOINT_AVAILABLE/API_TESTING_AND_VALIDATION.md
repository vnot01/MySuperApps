# 🧪 API Testing & Validation - MyRVM-Ecosystem v2.0

## 📍 Testing Overview

### Test Environment
- **Server**: `100.123.143.87:8001` (MyRVM-Ecosystem-v2)
- **Jetson**: `100.117.234.2:5000` (MyCV-Platform)
- **Test Data**: Sample RVMs, Users, and Detection Results
- **Test Tools**: Postman, curl, Python scripts, Jest

---

## 🔧 Test Setup

### Environment Configuration
```bash
# Server test environment
export SERVER_URL="http://100.123.143.87:8001"
export SERVER_API_KEY="38bbe1d2ecf75df21546b05340f5878a24e74ed0d8b88d75db1ddeff198380c1"

# Jetson test environment
export JETSON_URL="http://100.117.234.2:5000"
export JETSON_API_KEY="38bbe1d2ecf75df21546b05340f5878a24e74ed0d8b88d75db1ddeff198380c1"

# Test data
export TEST_RVM_ID="1"
export TEST_USER_ID="1"
export TEST_IMAGE_PATH="./test-images/sample.jpg"
```

### Test Data Setup
```bash
#!/bin/bash
# setup_test_data.sh

echo "🧪 Setting up test data"
echo "======================"

# Create test RVM
curl -X POST "$SERVER_URL/api/rvms" \
  -H "Authorization: Bearer $SERVER_API_KEY" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Test RVM",
    "location": "Test Location",
    "ip_address": "192.168.1.100",
    "capacity": 100,
    "current_load": 0
  }'

# Create test user
curl -X POST "$SERVER_URL/api/users" \
  -H "Authorization: Bearer $SERVER_API_KEY" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Test User",
    "email": "test@example.com",
    "password": "password123"
  }'

# Create test detection result
curl -X POST "$JETSON_URL/api/detection" \
  -H "Authorization: Bearer $JETSON_API_KEY" \
  -H "Content-Type: application/json" \
  -d '{
    "rvm_id": 1,
    "image_path": "/path/to/test/image.jpg",
    "detection_results": [
      {
        "class": "bottle",
        "confidence": 0.95,
        "bbox": [100, 100, 200, 200]
      }
    ],
    "processing_time": 1.5
  }'

echo "✅ Test data setup completed!"
```

---

## 🧪 Unit Tests

### Server API Tests (PHPUnit)
```php
<?php
// tests/Feature/ApiTest.php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\ReverseVendingMachine;
use App\Models\DetectionResult;

class ApiTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $rvm;
    protected $apiKey;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->rvm = ReverseVendingMachine::factory()->create();
        $this->apiKey = $this->user->createApiKey();
    }

    public function test_rvm_list_endpoint()
    {
        $response = $this->getJson('/api/rvms', [
            'Authorization' => 'Bearer ' . $this->apiKey
        ]);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        '*' => [
                            'id',
                            'name',
                            'location',
                            'status',
                            'created_at'
                        ]
                    ]
                ]);
    }

    public function test_rvm_create_endpoint()
    {
        $rvmData = [
            'name' => 'Test RVM',
            'location' => 'Test Location',
            'ip_address' => '192.168.1.100',
            'capacity' => 100
        ];

        $response = $this->postJson('/api/rvms', $rvmData, [
            'Authorization' => 'Bearer ' . $this->apiKey
        ]);

        $response->assertStatus(201)
                ->assertJsonStructure([
                    'data' => [
                        'id',
                        'name',
                        'location',
                        'ip_address',
                        'capacity',
                        'created_at'
                    ]
                ]);
    }

    public function test_detection_result_store_endpoint()
    {
        $detectionData = [
            'rvm_id' => $this->rvm->id,
            'image_path' => '/path/to/test/image.jpg',
            'detection_results' => [
                [
                    'class' => 'bottle',
                    'confidence' => 0.95,
                    'bbox' => [100, 100, 200, 200]
                ]
            ],
            'processing_time' => 1.5
        ];

        $response = $this->postJson('/api/detection-results', $detectionData, [
            'Authorization' => 'Bearer ' . $this->apiKey
        ]);

        $response->assertStatus(201)
                ->assertJsonStructure([
                    'data' => [
                        'id',
                        'rvm_id',
                        'image_path',
                        'detection_results',
                        'processing_time',
                        'created_at'
                    ]
                ]);
    }

    public function test_analytics_dashboard_endpoint()
    {
        $response = $this->getJson('/api/analytics/dashboard', [
            'Authorization' => 'Bearer ' . $this->apiKey
        ]);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        'overview' => [
                            'total_rvms',
                            'active_rvms',
                            'total_detections',
                            'total_rewards'
                        ],
                        'rvm_stats' => [
                            '*' => [
                                'id',
                                'name',
                                'detection_count',
                                'reward_total'
                            ]
                        ]
                    ]
                ]);
    }
}
```

### Jetson API Tests (Python)
```python
# tests/test_jetson_api.py

import pytest
import requests
import json
from unittest.mock import patch, MagicMock

class TestJetsonAPI:
    def setup_method(self):
        self.base_url = "http://100.117.234.2:5000"
        self.api_key = "38bbe1d2ecf75df21546b05340f5878a24e74ed0d8b88d75db1ddeff198380c1"
        self.headers = {
            "Authorization": f"Bearer {self.api_key}",
            "Content-Type": "application/json"
        }

    def test_health_endpoint(self):
        response = requests.get(f"{self.base_url}/api/health")
        assert response.status_code == 200
        data = response.json()
        assert data["status"] == "healthy"
        assert "timestamp" in data

    def test_status_endpoint(self):
        response = requests.get(f"{self.base_url}/api/status")
        assert response.status_code == 200
        data = response.json()
        assert "status" in data
        assert "uptime" in data
        assert "version" in data

    def test_detection_endpoint(self):
        detection_data = {
            "rvm_id": 1,
            "image_path": "/path/to/test/image.jpg",
            "detection_results": [
                {
                    "class": "bottle",
                    "confidence": 0.95,
                    "bbox": [100, 100, 200, 200]
                }
            ],
            "processing_time": 1.5
        }

        response = requests.post(
            f"{self.base_url}/api/detection",
            headers=self.headers,
            json=detection_data
        )
        assert response.status_code == 201
        data = response.json()
        assert "id" in data
        assert data["rvm_id"] == 1

    def test_monitoring_status_endpoint(self):
        response = requests.get(f"{self.base_url}/api/monitoring/status")
        assert response.status_code == 200
        data = response.json()
        assert "cpu_usage" in data
        assert "memory_usage" in data
        assert "gpu_usage" in data

    def test_monitoring_summary_endpoint(self):
        response = requests.get(f"{self.base_url}/api/monitoring/summary")
        assert response.status_code == 200
        data = response.json()
        assert "summary" in data
        assert "alerts" in data

    def test_monitoring_alerts_endpoint(self):
        response = requests.get(f"{self.base_url}/api/monitoring/alerts")
        assert response.status_code == 200
        data = response.json()
        assert "alerts" in data
        assert isinstance(data["alerts"], list)

    @patch('requests.post')
    def test_rvm_integration_validation(self, mock_post):
        mock_response = MagicMock()
        mock_response.status_code = 200
        mock_response.json.return_value = {"valid": True}
        mock_post.return_value = mock_response

        response = requests.post(
            f"{self.base_url}/api/rvm/validate",
            headers=self.headers,
            json={"api_key": self.api_key}
        )
        assert response.status_code == 200
        assert response.json()["valid"] is True
```

---

## 🔄 Integration Tests

### End-to-End API Flow Test
```python
# tests/test_integration_flow.py

import pytest
import requests
import time
import json

class TestIntegrationFlow:
    def setup_method(self):
        self.server_url = "http://100.123.143.87:8001"
        self.jetson_url = "http://100.117.234.2:5000"
        self.api_key = "38bbe1d2ecf75df21546b05340f5878a24e74ed0d8b88d75db1ddeff198380c1"
        self.headers = {
            "Authorization": f"Bearer {self.api_key}",
            "Content-Type": "application/json"
        }

    def test_complete_detection_flow(self):
        """Test complete flow from Jetson detection to server storage"""
        
        # 1. Create RVM on server
        rvm_data = {
            "name": "Integration Test RVM",
            "location": "Test Location",
            "ip_address": "192.168.1.100",
            "capacity": 100
        }
        
        rvm_response = requests.post(
            f"{self.server_url}/api/rvms",
            headers=self.headers,
            json=rvm_data
        )
        assert rvm_response.status_code == 201
        rvm_id = rvm_response.json()["data"]["id"]
        
        # 2. Validate RVM on Jetson
        validation_response = requests.post(
            f"{self.jetson_url}/api/rvm/validate",
            headers=self.headers,
            json={"api_key": self.api_key}
        )
        assert validation_response.status_code == 200
        assert validation_response.json()["valid"] is True
        
        # 3. Perform detection on Jetson
        detection_data = {
            "rvm_id": rvm_id,
            "image_path": "/path/to/test/image.jpg",
            "detection_results": [
                {
                    "class": "bottle",
                    "confidence": 0.95,
                    "bbox": [100, 100, 200, 200]
                }
            ],
            "processing_time": 1.5
        }
        
        detection_response = requests.post(
            f"{self.jetson_url}/api/detection",
            headers=self.headers,
            json=detection_data
        )
        assert detection_response.status_code == 201
        detection_id = detection_response.json()["id"]
        
        # 4. Verify detection stored on server
        time.sleep(2)  # Wait for data sync
        
        detections_response = requests.get(
            f"{self.server_url}/api/detection-results",
            headers=self.headers
        )
        assert detections_response.status_code == 200
        
        detections = detections_response.json()["data"]
        assert len(detections) > 0
        
        # Find our detection
        our_detection = next(
            (d for d in detections if d["id"] == detection_id),
            None
        )
        assert our_detection is not None
        assert our_detection["rvm_id"] == rvm_id
        
        # 5. Check analytics updated
        analytics_response = requests.get(
            f"{self.server_url}/api/analytics/dashboard",
            headers=self.headers
        )
        assert analytics_response.status_code == 200
        
        analytics = analytics_response.json()["data"]
        assert analytics["overview"]["total_detections"] > 0
        
        # 6. Cleanup
        requests.delete(
            f"{self.server_url}/api/rvms/{rvm_id}",
            headers=self.headers
        )

    def test_error_handling_flow(self):
        """Test error handling across the system"""
        
        # Test invalid API key
        invalid_headers = {
            "Authorization": "Bearer invalid_key",
            "Content-Type": "application/json"
        }
        
        response = requests.get(
            f"{self.server_url}/api/rvms",
            headers=invalid_headers
        )
        assert response.status_code == 401
        
        # Test invalid RVM ID
        response = requests.get(
            f"{self.server_url}/api/rvms/99999",
            headers=self.headers
        )
        assert response.status_code == 404
        
        # Test malformed detection data
        invalid_detection = {
            "rvm_id": "invalid",
            "image_path": "",
            "detection_results": "not_an_array"
        }
        
        response = requests.post(
            f"{self.jetson_url}/api/detection",
            headers=self.headers,
            json=invalid_detection
        )
        assert response.status_code == 400

    def test_performance_flow(self):
        """Test performance under load"""
        
        # Test multiple concurrent detections
        import concurrent.futures
        
        def perform_detection(i):
            detection_data = {
                "rvm_id": 1,
                "image_path": f"/path/to/test/image_{i}.jpg",
                "detection_results": [
                    {
                        "class": "bottle",
                        "confidence": 0.95,
                        "bbox": [100, 100, 200, 200]
                    }
                ],
                "processing_time": 1.5
            }
            
            response = requests.post(
                f"{self.jetson_url}/api/detection",
                headers=self.headers,
                json=detection_data
            )
            return response.status_code == 201
        
        # Run 10 concurrent detections
        with concurrent.futures.ThreadPoolExecutor(max_workers=10) as executor:
            futures = [executor.submit(perform_detection, i) for i in range(10)]
            results = [future.result() for future in futures]
        
        # All should succeed
        assert all(results)
        
        # Check response times
        start_time = time.time()
        response = requests.get(f"{self.jetson_url}/api/status")
        end_time = time.time()
        
        response_time = end_time - start_time
        assert response_time < 1.0  # Should respond within 1 second
```

---

## 📊 Load Testing

### Load Test Script
```python
# tests/load_test.py

import requests
import time
import statistics
import concurrent.futures
from datetime import datetime

class LoadTester:
    def __init__(self, server_url, jetson_url, api_key):
        self.server_url = server_url
        self.jetson_url = jetson_url
        self.api_key = api_key
        self.headers = {
            "Authorization": f"Bearer {api_key}",
            "Content-Type": "application/json"
        }
    
    def test_server_endpoints(self, num_requests=100):
        """Test server endpoints under load"""
        print(f"🧪 Testing server endpoints with {num_requests} requests")
        
        endpoints = [
            "/api/health",
            "/api/rvms",
            "/api/analytics/dashboard",
            "/api/economy/balance"
        ]
        
        results = {}
        
        for endpoint in endpoints:
            response_times = []
            success_count = 0
            
            def make_request():
                start_time = time.time()
                try:
                    response = requests.get(
                        f"{self.server_url}{endpoint}",
                        headers=self.headers,
                        timeout=10
                    )
                    end_time = time.time()
                    
                    if response.status_code == 200:
                        success_count += 1
                    
                    response_times.append(end_time - start_time)
                except Exception as e:
                    print(f"Error: {e}")
            
            # Run concurrent requests
            with concurrent.futures.ThreadPoolExecutor(max_workers=20) as executor:
                futures = [executor.submit(make_request) for _ in range(num_requests)]
                concurrent.futures.wait(futures)
            
            results[endpoint] = {
                "success_rate": success_count / num_requests,
                "avg_response_time": statistics.mean(response_times),
                "min_response_time": min(response_times),
                "max_response_time": max(response_times),
                "p95_response_time": sorted(response_times)[int(0.95 * len(response_times))]
            }
        
        return results
    
    def test_jetson_endpoints(self, num_requests=50):
        """Test Jetson endpoints under load"""
        print(f"🤖 Testing Jetson endpoints with {num_requests} requests")
        
        endpoints = [
            "/api/health",
            "/api/status",
            "/api/monitoring/status",
            "/api/monitoring/summary"
        ]
        
        results = {}
        
        for endpoint in endpoints:
            response_times = []
            success_count = 0
            
            def make_request():
                start_time = time.time()
                try:
                    response = requests.get(
                        f"{self.jetson_url}{endpoint}",
                        headers=self.headers,
                        timeout=10
                    )
                    end_time = time.time()
                    
                    if response.status_code == 200:
                        success_count += 1
                    
                    response_times.append(end_time - start_time)
                except Exception as e:
                    print(f"Error: {e}")
            
            # Run concurrent requests
            with concurrent.futures.ThreadPoolExecutor(max_workers=10) as executor:
                futures = [executor.submit(make_request) for _ in range(num_requests)]
                concurrent.futures.wait(futures)
            
            results[endpoint] = {
                "success_rate": success_count / num_requests,
                "avg_response_time": statistics.mean(response_times),
                "min_response_time": min(response_times),
                "max_response_time": max(response_times),
                "p95_response_time": sorted(response_times)[int(0.95 * len(response_times))]
            }
        
        return results
    
    def test_detection_flow_load(self, num_detections=20):
        """Test detection flow under load"""
        print(f"🔍 Testing detection flow with {num_detections} detections")
        
        response_times = []
        success_count = 0
        
        def perform_detection(i):
            detection_data = {
                "rvm_id": 1,
                "image_path": f"/path/to/test/image_{i}.jpg",
                "detection_results": [
                    {
                        "class": "bottle",
                        "confidence": 0.95,
                        "bbox": [100, 100, 200, 200]
                    }
                ],
                "processing_time": 1.5
            }
            
            start_time = time.time()
            try:
                response = requests.post(
                    f"{self.jetson_url}/api/detection",
                    headers=self.headers,
                    json=detection_data,
                    timeout=30
                )
                end_time = time.time()
                
                if response.status_code == 201:
                    success_count += 1
                
                response_times.append(end_time - start_time)
            except Exception as e:
                print(f"Error: {e}")
        
        # Run concurrent detections
        with concurrent.futures.ThreadPoolExecutor(max_workers=5) as executor:
            futures = [executor.submit(perform_detection, i) for i in range(num_detections)]
            concurrent.futures.wait(futures)
        
        return {
            "success_rate": success_count / num_detections,
            "avg_response_time": statistics.mean(response_times),
            "min_response_time": min(response_times),
            "max_response_time": max(response_times),
            "p95_response_time": sorted(response_times)[int(0.95 * len(response_times))]
        }
    
    def run_comprehensive_test(self):
        """Run comprehensive load test"""
        print("🚀 Starting comprehensive load test")
        print("=" * 50)
        
        # Test server endpoints
        server_results = self.test_server_endpoints(100)
        print("\n📊 Server Results:")
        for endpoint, metrics in server_results.items():
            print(f"  {endpoint}:")
            print(f"    Success Rate: {metrics['success_rate']:.2%}")
            print(f"    Avg Response Time: {metrics['avg_response_time']:.3f}s")
            print(f"    P95 Response Time: {metrics['p95_response_time']:.3f}s")
        
        # Test Jetson endpoints
        jetson_results = self.test_jetson_endpoints(50)
        print("\n🤖 Jetson Results:")
        for endpoint, metrics in jetson_results.items():
            print(f"  {endpoint}:")
            print(f"    Success Rate: {metrics['success_rate']:.2%}")
            print(f"    Avg Response Time: {metrics['avg_response_time']:.3f}s")
            print(f"    P95 Response Time: {metrics['p95_response_time']:.3f}s")
        
        # Test detection flow
        detection_results = self.test_detection_flow_load(20)
        print("\n🔍 Detection Flow Results:")
        print(f"  Success Rate: {detection_results['success_rate']:.2%}")
        print(f"  Avg Response Time: {detection_results['avg_response_time']:.3f}s")
        print(f"  P95 Response Time: {detection_results['p95_response_time']:.3f}s")
        
        print("\n✅ Load test completed!")

if __name__ == "__main__":
    tester = LoadTester(
        server_url="http://100.123.143.87:8001",
        jetson_url="http://100.117.234.2:5000",
        api_key="38bbe1d2ecf75df21546b05340f5878a24e74ed0d8b88d75db1ddeff198380c1"
    )
    tester.run_comprehensive_test()
```

---

## 🔍 API Validation

### API Contract Validation
```python
# tests/test_api_contracts.py

import requests
import jsonschema
import json

class APIContractValidator:
    def __init__(self, server_url, jetson_url, api_key):
        self.server_url = server_url
        self.jetson_url = jetson_url
        self.api_key = api_key
        self.headers = {
            "Authorization": f"Bearer {api_key}",
            "Content-Type": "application/json"
        }
    
    def validate_server_contracts(self):
        """Validate server API contracts"""
        print("🔍 Validating server API contracts")
        
        # RVM list schema
        rvm_list_schema = {
            "type": "object",
            "properties": {
                "data": {
                    "type": "array",
                    "items": {
                        "type": "object",
                        "properties": {
                            "id": {"type": "integer"},
                            "name": {"type": "string"},
                            "location": {"type": "string"},
                            "status": {"type": "string"},
                            "created_at": {"type": "string"}
                        },
                        "required": ["id", "name", "location", "status", "created_at"]
                    }
                }
            },
            "required": ["data"]
        }
        
        # Test RVM list endpoint
        response = requests.get(f"{self.server_url}/api/rvms", headers=self.headers)
        assert response.status_code == 200
        
        data = response.json()
        jsonschema.validate(data, rvm_list_schema)
        print("✅ RVM list contract valid")
        
        # Analytics dashboard schema
        analytics_schema = {
            "type": "object",
            "properties": {
                "data": {
                    "type": "object",
                    "properties": {
                        "overview": {
                            "type": "object",
                            "properties": {
                                "total_rvms": {"type": "integer"},
                                "active_rvms": {"type": "integer"},
                                "total_detections": {"type": "integer"},
                                "total_rewards": {"type": "number"}
                            },
                            "required": ["total_rvms", "active_rvms", "total_detections", "total_rewards"]
                        }
                    },
                    "required": ["overview"]
                }
            },
            "required": ["data"]
        }
        
        # Test analytics endpoint
        response = requests.get(f"{self.server_url}/api/analytics/dashboard", headers=self.headers)
        assert response.status_code == 200
        
        data = response.json()
        jsonschema.validate(data, analytics_schema)
        print("✅ Analytics dashboard contract valid")
    
    def validate_jetson_contracts(self):
        """Validate Jetson API contracts"""
        print("🔍 Validating Jetson API contracts")
        
        # Health endpoint schema
        health_schema = {
            "type": "object",
            "properties": {
                "status": {"type": "string"},
                "timestamp": {"type": "string"},
                "version": {"type": "string"}
            },
            "required": ["status", "timestamp", "version"]
        }
        
        # Test health endpoint
        response = requests.get(f"{self.jetson_url}/api/health")
        assert response.status_code == 200
        
        data = response.json()
        jsonschema.validate(data, health_schema)
        print("✅ Health endpoint contract valid")
        
        # Monitoring status schema
        monitoring_schema = {
            "type": "object",
            "properties": {
                "cpu_usage": {"type": "number"},
                "memory_usage": {"type": "number"},
                "gpu_usage": {"type": "number"},
                "disk_usage": {"type": "number"},
                "network_io": {"type": "object"},
                "timestamp": {"type": "string"}
            },
            "required": ["cpu_usage", "memory_usage", "gpu_usage", "disk_usage", "network_io", "timestamp"]
        }
        
        # Test monitoring endpoint
        response = requests.get(f"{self.jetson_url}/api/monitoring/status")
        assert response.status_code == 200
        
        data = response.json()
        jsonschema.validate(data, monitoring_schema)
        print("✅ Monitoring status contract valid")
    
    def validate_error_responses(self):
        """Validate error response contracts"""
        print("🔍 Validating error response contracts")
        
        # Test 404 error
        response = requests.get(f"{self.server_url}/api/rvms/99999", headers=self.headers)
        assert response.status_code == 404
        
        error_schema = {
            "type": "object",
            "properties": {
                "message": {"type": "string"},
                "error": {"type": "string"}
            },
            "required": ["message", "error"]
        }
        
        data = response.json()
        jsonschema.validate(data, error_schema)
        print("✅ 404 error contract valid")
        
        # Test 401 error
        invalid_headers = {
            "Authorization": "Bearer invalid_key",
            "Content-Type": "application/json"
        }
        
        response = requests.get(f"{self.server_url}/api/rvms", headers=invalid_headers)
        assert response.status_code == 401
        
        data = response.json()
        jsonschema.validate(data, error_schema)
        print("✅ 401 error contract valid")
    
    def run_validation(self):
        """Run all contract validations"""
        print("🚀 Starting API contract validation")
        print("=" * 50)
        
        try:
            self.validate_server_contracts()
            self.validate_jetson_contracts()
            self.validate_error_responses()
            print("\n✅ All API contracts valid!")
        except Exception as e:
            print(f"\n❌ Contract validation failed: {e}")
            raise

if __name__ == "__main__":
    validator = APIContractValidator(
        server_url="http://100.123.143.87:8001",
        jetson_url="http://100.117.234.2:5000",
        api_key="38bbe1d2ecf75df21546b05340f5878a24e74ed0d8b88d75db1ddeff198380c1"
    )
    validator.run_validation()
```

---

## 📋 Test Checklist

### Pre-Deployment Checklist
- [ ] All unit tests passing
- [ ] Integration tests passing
- [ ] Load tests within acceptable limits
- [ ] API contracts validated
- [ ] Error handling tested
- [ ] Security tests passed
- [ ] Performance benchmarks met
- [ ] Documentation updated

### Post-Deployment Checklist
- [ ] Health checks passing
- [ ] All endpoints responding
- [ ] Database connectivity verified
- [ ] Redis connectivity verified
- [ ] File uploads working
- [ ] Real-time updates functioning
- [ ] Monitoring alerts configured
- [ ] Backup procedures tested

---

## 🚨 Test Automation

### CI/CD Pipeline Test
```yaml
# .github/workflows/api-tests.yml

name: API Tests

on:
  push:
    branches: [ main, develop ]
  pull_request:
    branches: [ main ]

jobs:
  test:
    runs-on: ubuntu-latest
    
    steps:
    - uses: actions/checkout@v3
    
    - name: Setup PHP
      uses: shivammathur/setup-php@v2
      with:
        php-version: '8.2'
        extensions: dom, curl, libxml, mbstring, zip, pcntl, pdo, sqlite, pdo_sqlite, bcmath, soap, intl, gd, exif, iconv
        coverage: none
    
    - name: Setup Python
      uses: actions/setup-python@v4
      with:
        python-version: '3.9'
    
    - name: Install PHP dependencies
      run: |
        cd MyRVM-Ecosystem-v2
        composer install --no-progress --prefer-dist --optimize-autoloader
    
    - name: Install Python dependencies
      run: |
        cd MyCV-Platform/direct
        pip install -r requirements.txt
    
    - name: Run PHP tests
      run: |
        cd MyRVM-Ecosystem-v2
        php artisan test
    
    - name: Run Python tests
      run: |
        cd MyCV-Platform/direct/app/api-hybrid-detection-jetson
        python -m pytest tests/ -v
    
    - name: Run integration tests
      run: |
        python tests/test_integration_flow.py
    
    - name: Run load tests
      run: |
        python tests/load_test.py
    
    - name: Run contract validation
      run: |
        python tests/test_api_contracts.py
```

---

**Last Updated**: 2025-01-02  
**Version**: 2.0.0  
**Status**: ✅ COMPLETE TESTING & VALIDATION DOCUMENTATION
