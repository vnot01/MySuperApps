#!/usr/bin/env python3
"""
RVM API Client Service
Handles communication with MyRVM-Platform server for RVM status polling
"""

import requests
import json
import time
from typing import Dict, Tuple, Optional
from datetime import datetime
import logging

class RVMAPIClient:
    """API Client for MyRVM-Platform communication"""
    
    def __init__(self, base_url: str, api_key: str, timeout: int = 30):
        """
        Initialize RVM API Client
        
        Args:
            base_url: MyRVM-Platform base URL
            api_key: API key for authentication
            timeout: Request timeout in seconds
        """
        self.base_url = base_url.rstrip('/')
        self.api_key = api_key
        self.timeout = timeout
        self.session = requests.Session()
        
        # Set default headers
        self.session.headers.update({
            'Authorization': f'Bearer {api_key}',
            'Content-Type': 'application/json',
            'User-Agent': 'MyCV-Edge-API/1.5.0'
        })
        
        # Setup logging
        self.logger = logging.getLogger(__name__)
    
    def _make_request(self, method: str, endpoint: str, data: Optional[Dict] = None) -> Tuple[bool, Dict]:
        """
        Make HTTP request to MyRVM-Platform
        
        Args:
            method: HTTP method (GET, POST, PUT, DELETE)
            endpoint: API endpoint
            data: Request data (for POST/PUT)
            
        Returns:
            Tuple of (success: bool, response: dict)
        """
        url = f"{self.base_url}{endpoint}"
        
        try:
            self.logger.debug(f"Making {method} request to: {url}")
            
            if method.upper() == 'GET':
                response = self.session.get(url, timeout=self.timeout)
            elif method.upper() == 'POST':
                response = self.session.post(url, json=data, timeout=self.timeout)
            elif method.upper() == 'PUT':
                response = self.session.put(url, json=data, timeout=self.timeout)
            elif method.upper() == 'DELETE':
                response = self.session.delete(url, timeout=self.timeout)
            else:
                return False, {'error': f'Unsupported HTTP method: {method}'}
            
            # Check response status
            if response.status_code == 200:
                try:
                    response_data = response.json()
                    self.logger.debug(f"API response: {response_data}")
                    return True, response_data
                except json.JSONDecodeError:
                    return False, {'error': 'Invalid JSON response', 'raw_response': response.text}
            else:
                error_msg = f"HTTP {response.status_code}: {response.text}"
                self.logger.error(f"API request failed: {error_msg}")
                return False, {'error': error_msg, 'status_code': response.status_code}
                
        except requests.exceptions.Timeout:
            error_msg = f"Request timeout after {self.timeout}s"
            self.logger.error(error_msg)
            return False, {'error': error_msg}
        except requests.exceptions.ConnectionError:
            error_msg = "Connection error - server unreachable"
            self.logger.error(error_msg)
            return False, {'error': error_msg}
        except Exception as e:
            error_msg = f"Unexpected error: {str(e)}"
            self.logger.error(error_msg)
            return False, {'error': error_msg}
    
    def get_rvm_status(self, rvm_id: int) -> Tuple[bool, Dict]:
        """
        Get RVM status from MyRVM-Platform
        
        Args:
            rvm_id: RVM ID to check status for
            
        Returns:
            Tuple of (success: bool, response: dict)
        """
        endpoint = f'/api/v2/rvm-status/{rvm_id}'
        return self._make_request('GET', endpoint)
    
    def update_rvm_health(self, rvm_id: int, health_data: Dict) -> Tuple[bool, Dict]:
        """
        Update RVM health data to MyRVM-Platform
        
        Args:
            rvm_id: RVM ID
            health_data: Health metrics data
            
        Returns:
            Tuple of (success: bool, response: dict)
        """
        endpoint = f'/api/v2/rvm-health/{rvm_id}'
        return self._make_request('POST', endpoint, health_data)
    
    def report_detection(self, rvm_id: int, detection_data: Dict) -> Tuple[bool, Dict]:
        """
        Report detection results to MyRVM-Platform
        
        Args:
            rvm_id: RVM ID
            detection_data: Detection results data
            
        Returns:
            Tuple of (success: bool, response: dict)
        """
        endpoint = f'/api/v2/rvm-detection/{rvm_id}'
        return self._make_request('POST', endpoint, detection_data)
    
    def get_rvm_config(self, rvm_id: int) -> Tuple[bool, Dict]:
        """
        Get RVM configuration from MyRVM-Platform
        
        Args:
            rvm_id: RVM ID
            
        Returns:
            Tuple of (success: bool, response: dict)
        """
        endpoint = f'/api/v2/rvm-config/{rvm_id}'
        return self._make_request('GET', endpoint)
    
    def test_connection(self) -> Tuple[bool, Dict]:
        """
        Test connection to MyRVM-Platform
        
        Returns:
            Tuple of (success: bool, response: dict)
        """
        endpoint = '/api/v2/health'
        return self._make_request('GET', endpoint)
    
    def close(self):
        """Close the session"""
        if self.session:
            self.session.close()
    
    def __enter__(self):
        return self
    
    def __exit__(self, exc_type, exc_val, exc_tb):
        self.close()
