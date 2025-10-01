#!/usr/bin/env python3
"""
Full Integration Test for MyCV-Platform with MyRVM-Platform
Tests the complete workflow from RVM authentication to detection processing
"""

import requests
import json
import os
import time
import base64
from datetime import datetime
from pathlib import Path

# Configuration
CV_API_BASE_URL = "http://100.117.234.2:5000"
RVM_API_BASE_URL = "http://localhost:8000/api"  # Update with your RVM Platform URL
RVM_API_KEY = "test_rvm_api_key_123"  # Update with actual RVM API key
RVM_ID = 1  # Update with actual RVM ID

# Test image (1x1 pixel PNG)
TEST_IMAGE_DATA = b'\x89PNG\r\n\x1a\n\x00\x00\x00\rIHDR\x00\x00\x00\x01\x00\x00\x00\x01\x08\x02\x00\x00\x00\x90wS\xde\x00\x00\x00\tpHYs\x00\x00\x0b\x13\x00\x00\x0b\x13\x01\x00\x9a\x9c\x18\x00\x00\x00\nIDATx\x9cc```\x00\x00\x00\x04\x00\x01\xdd\x8d\xb4\x1c\x00\x00\x00\x00IEND\xaeB`\x82'

class RVMIntegrationTester:
    def __init__(self):
        self.session = requests.Session()
        self.test_results = []
        
    def log_test(self, test_name, success, message="", data=None):
        """Log test result"""
        status = "✅ PASS" if success else "❌ FAIL"
        print(f"{status} {test_name}")
        if message:
            print(f"   {message}")
        if data:
            print(f"   Data: {json.dumps(data, indent=2)}")
        
        self.test_results.append({
            'test_name': test_name,
            'success': success,
            'message': message,
            'data': data
        })
        return success

    def test_cv_api_health(self):
        """Test MyCV-Platform API health"""
        print("\n🏥 Testing MyCV-Platform API Health...")
        
        try:
            response = self.session.get(f"{CV_API_BASE_URL}/api/health")
            success = response.status_code == 200
            
            if success:
                data = response.json()
                return self.log_test("CV API Health", True, f"API is healthy", data)
            else:
                return self.log_test("CV API Health", False, f"HTTP {response.status_code}: {response.text}")
                
        except Exception as e:
            return self.log_test("CV API Health", False, f"Connection error: {e}")

    def test_rvm_validation(self):
        """Test RVM API key validation"""
        print("\n🔐 Testing RVM Validation...")
        
        try:
            response = self.session.post(
                f"{CV_API_BASE_URL}/api/rvm/validate",
                json={"api_key": RVM_API_KEY}
            )
            
            if response.status_code == 200:
                data = response.json()
                if data.get('valid'):
                    return self.log_test("RVM Validation", True, f"API key valid for RVM {data.get('rvm', {}).get('id')}", data)
                else:
                    return self.log_test("RVM Validation", False, f"API key invalid: {data.get('error')}")
            else:
                return self.log_test("RVM Validation", False, f"HTTP {response.status_code}: {response.text}")
                
        except Exception as e:
            return self.log_test("RVM Validation", False, f"Error: {e}")

    def test_upload_with_rvm(self):
        """Test file upload with RVM authentication"""
        print("\n📤 Testing File Upload with RVM...")
        
        try:
            headers = {"X-RVM-API-Key": RVM_API_KEY}
            files = {
                'files': ('test_image.png', TEST_IMAGE_DATA, 'image/png')
            }
            data = {
                'rvm_id': RVM_ID,
                'user_id': 'integration_test_user'
            }
            
            response = self.session.post(
                f"{CV_API_BASE_URL}/api/upload",
                headers=headers,
                files=files,
                data=data
            )
            
            if response.status_code == 200:
                result = response.json()
                session_id = result.get('session_id')
                return self.log_test("File Upload with RVM", True, f"Upload successful, session: {session_id}", result)
            else:
                return self.log_test("File Upload with RVM", False, f"HTTP {response.status_code}: {response.text}")
                
        except Exception as e:
            return self.log_test("File Upload with RVM", False, f"Error: {e}")

    def test_processing_status(self, session_id):
        """Test processing status endpoint"""
        print(f"\n⏳ Testing Processing Status for session {session_id}...")
        
        try:
            response = self.session.get(f"{CV_API_BASE_URL}/api/process/{session_id}")
            
            if response.status_code == 200:
                data = response.json()
                status = data.get('status')
                return self.log_test("Processing Status", True, f"Status: {status}", data)
            else:
                return self.log_test("Processing Status", False, f"HTTP {response.status_code}: {response.text}")
                
        except Exception as e:
            return self.log_test("Processing Status", False, f"Error: {e}")

    def test_detections_with_rvm(self):
        """Test detections endpoint with RVM filtering"""
        print("\n🔍 Testing Detections with RVM Filtering...")
        
        try:
            headers = {"X-RVM-API-Key": RVM_API_KEY}
            params = {
                'rvm_id': RVM_ID,
                'page': 1,
                'limit': 10
            }
            
            response = self.session.get(
                f"{CV_API_BASE_URL}/api/detections",
                headers=headers,
                params=params
            )
            
            if response.status_code == 200:
                data = response.json()
                detections = data.get('recent_detections', [])
                return self.log_test("Detections with RVM", True, f"Found {len(detections)} detections", data)
            else:
                return self.log_test("Detections with RVM", False, f"HTTP {response.status_code}: {response.text}")
                
        except Exception as e:
            return self.log_test("Detections with RVM", False, f"Error: {e}")

    def test_search_with_rvm(self):
        """Test search endpoint with RVM filtering"""
        print("\n🔎 Testing Search with RVM Filtering...")
        
        try:
            headers = {
                "X-RVM-API-Key": RVM_API_KEY,
                "Content-Type": "application/json"
            }
            search_data = {
                'rvm_id': RVM_ID,
                'page': 1,
                'limit': 10,
                'when': datetime.now().strftime("%Y%m%d")
            }
            
            response = self.session.post(
                f"{CV_API_BASE_URL}/api/detections/search",
                headers=headers,
                json=search_data
            )
            
            if response.status_code == 200:
                data = response.json()
                detections = data.get('recent_detections', [])
                return self.log_test("Search with RVM", True, f"Found {len(detections)} detections", data)
            else:
                return self.log_test("Search with RVM", False, f"HTTP {response.status_code}: {response.text}")
                
        except Exception as e:
            return self.log_test("Search with RVM", False, f"Error: {e}")

    def test_rvm_stats(self):
        """Test RVM statistics endpoint"""
        print("\n📊 Testing RVM Statistics...")
        
        try:
            headers = {"X-RVM-API-Key": RVM_API_KEY}
            response = self.session.get(
                f"{CV_API_BASE_URL}/api/rvm/{RVM_ID}/stats",
                headers=headers
            )
            
            if response.status_code == 200:
                data = response.json()
                return self.log_test("RVM Statistics", True, f"Stats retrieved for RVM {data.get('rvm_id')}", data)
            else:
                return self.log_test("RVM Statistics", False, f"HTTP {response.status_code}: {response.text}")
                
        except Exception as e:
            return self.log_test("RVM Statistics", False, f"Error: {e}")

    def test_legacy_compatibility(self):
        """Test legacy endpoint compatibility"""
        print("\n🔄 Testing Legacy Compatibility...")
        
        try:
            response = self.session.get(
                f"{CV_API_BASE_URL}/api/detections",
                params={'page': 1, 'limit': 5}
            )
            
            if response.status_code == 200:
                data = response.json()
                detections = data.get('recent_detections', [])
                return self.log_test("Legacy Compatibility", True, f"Legacy endpoint works, found {len(detections)} detections", data)
            else:
                return self.log_test("Legacy Compatibility", False, f"HTTP {response.status_code}: {response.text}")
                
        except Exception as e:
            return self.log_test("Legacy Compatibility", False, f"Error: {e}")

    def test_directory_structure(self):
        """Test RVM directory structure"""
        print("\n📁 Testing Directory Structure...")
        
        try:
            base_dir = Path("../../data-jetson")
            rvm_dirs = ["input/rvm_1", "input/rvm_2", "input/rvm_3", 
                       "output/rvm_1", "output/rvm_2", "output/rvm_3"]
            
            missing_dirs = []
            for rvm_dir in rvm_dirs:
                dir_path = base_dir / rvm_dir
                if not dir_path.exists():
                    missing_dirs.append(str(dir_path))
            
            if not missing_dirs:
                return self.log_test("Directory Structure", True, "All RVM directories exist")
            else:
                return self.log_test("Directory Structure", False, f"Missing directories: {missing_dirs}")
                
        except Exception as e:
            return self.log_test("Directory Structure", False, f"Error: {e}")

    def test_error_handling(self):
        """Test error handling scenarios"""
        print("\n⚠️  Testing Error Handling...")
        
        # Test invalid API key
        try:
            response = self.session.post(
                f"{CV_API_BASE_URL}/api/rvm/validate",
                json={"api_key": "invalid_key"}
            )
            
            if response.status_code == 401:
                return self.log_test("Error Handling", True, "Invalid API key properly rejected")
            else:
                return self.log_test("Error Handling", False, f"Expected 401, got {response.status_code}")
                
        except Exception as e:
            return self.log_test("Error Handling", False, f"Error: {e}")

    def run_all_tests(self):
        """Run all integration tests"""
        print("🚀 Starting Full RVM Integration Tests")
        print("=" * 60)
        
        # Basic connectivity tests
        self.test_cv_api_health()
        self.test_directory_structure()
        
        # RVM authentication tests
        self.test_rvm_validation()
        
        # Upload and processing tests
        session_id = None
        upload_success = self.test_upload_with_rvm()
        if upload_success:
            # Wait a bit for processing
            time.sleep(2)
            if session_id:
                self.test_processing_status(session_id)
        
        # Data retrieval tests
        self.test_detections_with_rvm()
        self.test_search_with_rvm()
        self.test_rvm_stats()
        
        # Compatibility tests
        self.test_legacy_compatibility()
        
        # Error handling tests
        self.test_error_handling()
        
        # Print summary
        self.print_summary()

    def print_summary(self):
        """Print test summary"""
        print("\n" + "="*60)
        print("📋 INTEGRATION TEST SUMMARY")
        print("="*60)
        
        passed = sum(1 for result in self.test_results if result['success'])
        total = len(self.test_results)
        
        for result in self.test_results:
            status = "✅ PASS" if result['success'] else "❌ FAIL"
            print(f"{status} {result['test_name']}")
            if result['message']:
                print(f"   {result['message']}")
        
        print(f"\nResults: {passed}/{total} tests passed")
        
        if passed == total:
            print("🎉 All integration tests passed! RVM integration is working correctly.")
        else:
            print("⚠️  Some tests failed. Check the output above for details.")
            print("\nTroubleshooting tips:")
            print("1. Ensure MyCV-Platform API is running on port 5000")
            print("2. Check RVM Platform connectivity")
            print("3. Verify API keys and RVM IDs")
            print("4. Check directory permissions")
        
        return passed == total

def main():
    """Main function"""
    tester = RVMIntegrationTester()
    success = tester.run_all_tests()
    return 0 if success else 1

if __name__ == "__main__":
    exit(main())
