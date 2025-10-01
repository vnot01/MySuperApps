#!/usr/bin/env python3
"""
Test script for MyCV-Platform RVM Integration
Tests all RVM-specific endpoints and functionality
"""

import requests
import json
import os
import time
from datetime import datetime

# Configuration
API_BASE_URL = "http://100.117.234.2:5000"
RVM_API_KEY = "test_rvm_api_key_123"  # Replace with actual RVM API key
RVM_ID = 1  # Replace with actual RVM ID

def test_rvm_validation():
    """Test RVM API key validation"""
    print("🔐 Testing RVM validation...")
    
    url = f"{API_BASE_URL}/api/rvm/validate"
    data = {"api_key": RVM_API_KEY}
    
    try:
        response = requests.post(url, json=data)
        print(f"Status: {response.status_code}")
        
        if response.status_code == 200:
            result = response.json()
            print(f"✅ Validation successful: {result}")
            return True
        else:
            print(f"❌ Validation failed: {response.text}")
            return False
    except Exception as e:
        print(f"❌ Error: {e}")
        return False

def test_upload_with_rvm():
    """Test file upload with RVM authentication"""
    print("\n📤 Testing file upload with RVM...")
    
    url = f"{API_BASE_URL}/api/upload"
    headers = {"X-RVM-API-Key": RVM_API_KEY}
    
    # Create a test image file (1x1 pixel PNG)
    test_image_data = b'\x89PNG\r\n\x1a\n\x00\x00\x00\rIHDR\x00\x00\x00\x01\x00\x00\x00\x01\x08\x02\x00\x00\x00\x90wS\xde\x00\x00\x00\tpHYs\x00\x00\x0b\x13\x00\x00\x0b\x13\x01\x00\x9a\x9c\x18\x00\x00\x00\nIDATx\x9cc```\x00\x00\x00\x04\x00\x01\xdd\x8d\xb4\x1c\x00\x00\x00\x00IEND\xaeB`\x82'
    
    files = {
        'files': ('test_image.png', test_image_data, 'image/png')
    }
    data = {
        'rvm_id': RVM_ID,
        'user_id': 'test_user'
    }
    
    try:
        response = requests.post(url, headers=headers, files=files, data=data)
        print(f"Status: {response.status_code}")
        
        if response.status_code == 200:
            result = response.json()
            print(f"✅ Upload successful: {result}")
            return result.get('session_id')
        else:
            print(f"❌ Upload failed: {response.text}")
            return None
    except Exception as e:
        print(f"❌ Error: {e}")
        return None

def test_detections_with_rvm():
    """Test detections endpoint with RVM filtering"""
    print("\n🔍 Testing detections with RVM filtering...")
    
    url = f"{API_BASE_URL}/api/detections"
    headers = {"X-RVM-API-Key": RVM_API_KEY}
    params = {
        'rvm_id': RVM_ID,
        'page': 1,
        'limit': 10
    }
    
    try:
        response = requests.get(url, headers=headers, params=params)
        print(f"Status: {response.status_code}")
        
        if response.status_code == 200:
            result = response.json()
            print(f"✅ Detections retrieved: {len(result.get('recent_detections', []))} items")
            print(f"Filters: {result.get('filters', {})}")
            return True
        else:
            print(f"❌ Detections failed: {response.text}")
            return False
    except Exception as e:
        print(f"❌ Error: {e}")
        return False

def test_search_with_rvm():
    """Test search endpoint with RVM filtering"""
    print("\n🔎 Testing search with RVM filtering...")
    
    url = f"{API_BASE_URL}/api/detections/search"
    headers = {
        "X-RVM-API-Key": RVM_API_KEY,
        "Content-Type": "application/json"
    }
    data = {
        'rvm_id': RVM_ID,
        'page': 1,
        'limit': 10,
        'when': datetime.now().strftime("%Y%m%d")
    }
    
    try:
        response = requests.post(url, headers=headers, json=data)
        print(f"Status: {response.status_code}")
        
        if response.status_code == 200:
            result = response.json()
            print(f"✅ Search successful: {len(result.get('recent_detections', []))} items")
            print(f"Filters: {result.get('filters', {})}")
            return True
        else:
            print(f"❌ Search failed: {response.text}")
            return False
    except Exception as e:
        print(f"❌ Error: {e}")
        return False

def test_rvm_stats():
    """Test RVM statistics endpoint"""
    print("\n📊 Testing RVM statistics...")
    
    url = f"{API_BASE_URL}/api/rvm/{RVM_ID}/stats"
    headers = {"X-RVM-API-Key": RVM_API_KEY}
    
    try:
        response = requests.get(url, headers=headers)
        print(f"Status: {response.status_code}")
        
        if response.status_code == 200:
            result = response.json()
            print(f"✅ Stats retrieved: {result}")
            return True
        else:
            print(f"❌ Stats failed: {response.text}")
            return False
    except Exception as e:
        print(f"❌ Error: {e}")
        return False

def test_legacy_compatibility():
    """Test legacy endpoint compatibility"""
    print("\n🔄 Testing legacy compatibility...")
    
    url = f"{API_BASE_URL}/api/detections"
    params = {'page': 1, 'limit': 5}
    
    try:
        response = requests.get(url, params=params)
        print(f"Status: {response.status_code}")
        
        if response.status_code == 200:
            result = response.json()
            print(f"✅ Legacy endpoint works: {len(result.get('recent_detections', []))} items")
            return True
        else:
            print(f"❌ Legacy endpoint failed: {response.text}")
            return False
    except Exception as e:
        print(f"❌ Error: {e}")
        return False

def test_health_check():
    """Test API health check"""
    print("\n🏥 Testing health check...")
    
    url = f"{API_BASE_URL}/api/health"
    
    try:
        response = requests.get(url)
        print(f"Status: {response.status_code}")
        
        if response.status_code == 200:
            result = response.json()
            print(f"✅ Health check passed: {result}")
            return True
        else:
            print(f"❌ Health check failed: {response.text}")
            return False
    except Exception as e:
        print(f"❌ Error: {e}")
        return False

def main():
    """Run all tests"""
    print("🚀 Starting MyCV-Platform RVM Integration Tests")
    print("=" * 60)
    
    tests = [
        ("Health Check", test_health_check),
        ("RVM Validation", test_rvm_validation),
        ("Legacy Compatibility", test_legacy_compatibility),
        ("Upload with RVM", test_upload_with_rvm),
        ("Detections with RVM", test_detections_with_rvm),
        ("Search with RVM", test_search_with_rvm),
        ("RVM Statistics", test_rvm_stats),
    ]
    
    results = []
    
    for test_name, test_func in tests:
        print(f"\n{'='*20} {test_name} {'='*20}")
        try:
            result = test_func()
            results.append((test_name, result))
        except Exception as e:
            print(f"❌ Test {test_name} crashed: {e}")
            results.append((test_name, False))
    
    # Summary
    print("\n" + "="*60)
    print("📋 TEST SUMMARY")
    print("="*60)
    
    passed = 0
    total = len(results)
    
    for test_name, result in results:
        status = "✅ PASS" if result else "❌ FAIL"
        print(f"{status} {test_name}")
        if result:
            passed += 1
    
    print(f"\nResults: {passed}/{total} tests passed")
    
    if passed == total:
        print("🎉 All tests passed! RVM integration is working correctly.")
    else:
        print("⚠️  Some tests failed. Check the output above for details.")
    
    return passed == total

if __name__ == "__main__":
    success = main()
    exit(0 if success else 1)
