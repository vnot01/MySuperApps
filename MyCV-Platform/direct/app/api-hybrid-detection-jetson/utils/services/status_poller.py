#!/usr/bin/env python3
"""
RVM Status Poller Service
Main service for polling RVM status from MyRVM-Platform server
"""

import time
import threading
import logging
from typing import Dict, Optional, Callable
from datetime import datetime
import os
import sys

# Add parent directories to path for imports
sys.path.append(os.path.join(os.path.dirname(__file__), '../../../..'))

from .api_client import RVMAPIClient
from .health_monitor import RVMHealthMonitor

class RVMStatusPoller:
    """Main RVM status polling service"""
    
    def __init__(self, rvm_id: int, config_file: str = None):
        """
        Initialize RVM Status Poller
        
        Args:
            rvm_id: RVM ID for this poller
            config_file: Path to configuration file
        """
        self.rvm_id = rvm_id
        self.config_file = config_file or 'rvm_config.env'
        
        # Load configuration
        self.config = self._load_config()
        
        # Initialize services
        self.api_client = RVMAPIClient(
            base_url=self.config.get('RVM_API_BASE_URL', 'http://100.123.143.87:8001'),
            api_key=self.config.get('RVM_API_KEY', ''),
            timeout=int(self.config.get('API_TIMEOUT', '30'))
        )
        
        self.health_monitor = RVMHealthMonitor(
            rvm_id=rvm_id,
            monitoring_interval=int(self.config.get('MONITORING_INTERVAL', '30'))
        )
        
        # Polling control
        self.is_running = False
        self.polling_thread = None
        self.polling_interval = int(self.config.get('POLLING_INTERVAL', '60'))  # Default 60 seconds
        
        # Status callbacks
        self.status_callbacks: list[Callable] = []
        self.health_callbacks: list[Callable] = []
        
        # Setup logging
        self.logger = logging.getLogger(__name__)
        
        # Statistics
        self.stats = {
            'poll_count': 0,
            'successful_polls': 0,
            'failed_polls': 0,
            'last_poll_time': None,
            'last_successful_poll': None,
            'consecutive_failures': 0
        }
    
    def _load_config(self) -> Dict[str, str]:
        """Load configuration from file"""
        config = {}
        
        try:
            config_path = os.path.join(
                os.path.dirname(__file__), 
                '../../../..', 
                self.config_file
            )
            
            if os.path.exists(config_path):
                with open(config_path, 'r') as f:
                    for line in f:
                        line = line.strip()
                        if line and not line.startswith('#') and '=' in line:
                            key, value = line.split('=', 1)
                            config[key.strip()] = value.strip().strip('"\'')
                self.logger.info(f"Configuration loaded from {config_path}")
            else:
                self.logger.warning(f"Configuration file not found: {config_path}")
                
        except Exception as e:
            self.logger.error(f"Error loading configuration: {e}")
        
        return config
    
    def add_status_callback(self, callback: Callable[[Dict], None]):
        """Add callback for status updates"""
        self.status_callbacks.append(callback)
    
    def add_health_callback(self, callback: Callable[[Dict], None]):
        """Add callback for health updates"""
        self.health_callbacks.append(callback)
    
    def start_polling(self):
        """Start RVM status polling"""
        if self.is_running:
            self.logger.warning("RVM status polling already running")
            return
        
        # Start health monitoring
        self.health_monitor.start_monitoring()
        
        # Start polling
        self.is_running = True
        self.polling_thread = threading.Thread(target=self._polling_loop, daemon=True)
        self.polling_thread.start()
        
        self.logger.info(f"RVM status polling started for RVM {self.rvm_id}")
        self.logger.info(f"Polling interval: {self.polling_interval} seconds")
        self.logger.info(f"Server URL: {self.config.get('RVM_API_BASE_URL', 'http://100.123.143.87:8001')}")
    
    def stop_polling(self):
        """Stop RVM status polling"""
        self.is_running = False
        
        if self.polling_thread:
            self.polling_thread.join(timeout=10)
        
        self.health_monitor.stop_monitoring()
        self.api_client.close()
        
        self.logger.info(f"RVM status polling stopped for RVM {self.rvm_id}")
    
    def _polling_loop(self):
        """Main polling loop"""
        while self.is_running:
            try:
                # Poll RVM status
                self._poll_rvm_status()
                
                # Sleep for polling interval
                time.sleep(self.polling_interval)
                
            except Exception as e:
                self.logger.error(f"Error in polling loop: {e}")
                self.stats['failed_polls'] += 1
                self.stats['consecutive_failures'] += 1
                time.sleep(5)  # Short sleep on error
    
    def _poll_rvm_status(self):
        """Poll RVM status from server"""
        try:
            start_time = time.time()
            self.stats['poll_count'] += 1
            self.stats['last_poll_time'] = datetime.now().isoformat()
            
            self.logger.debug(f"Polling RVM status for RVM {self.rvm_id}")
            
            # Make API request
            success, response = self.api_client.get_rvm_status(self.rvm_id)
            
            response_time = time.time() - start_time
            self.health_monitor.record_api_response_time(response_time)
            
            if success:
                self._handle_successful_poll(response)
            else:
                self._handle_failed_poll(response)
                
        except Exception as e:
            self.logger.error(f"Error polling RVM status: {e}")
            self._handle_failed_poll({'error': str(e)})
    
    def _handle_successful_poll(self, response: Dict):
        """Handle successful poll response"""
        try:
            self.stats['successful_polls'] += 1
            self.stats['consecutive_failures'] = 0
            self.stats['last_successful_poll'] = datetime.now().isoformat()
            
            # Extract RVM status data
            rvm_data = response.get('data', {})
            rvm_info = rvm_data.get('rvm', {})
            
            # Update health monitor with status
            status_data = {
                'rvm_status': rvm_info.get('status', 'unknown'),
                'latest_detection': rvm_data.get('latest_detection'),
                'detection_stats': rvm_data.get('detection_stats', {})
            }
            
            self.health_monitor.update_rvm_status(status_data)
            self.health_monitor.set_connection_status('connected')
            
            # Get current status
            current_status = self.health_monitor.get_current_status()
            
            # Log status update
            self.logger.info(f"RVM {self.rvm_id} status: {current_status['rvm_status']}")
            self.logger.debug(f"RVM status data: {current_status}")
            
            # Notify callbacks
            self._notify_status_callbacks(current_status)
            
            # Send health data to server
            self._send_health_data()
            
        except Exception as e:
            self.logger.error(f"Error handling successful poll: {e}")
            self._handle_failed_poll({'error': f'Response processing error: {e}'})
    
    def _handle_failed_poll(self, error_response: Dict):
        """Handle failed poll response"""
        self.stats['failed_polls'] += 1
        self.stats['consecutive_failures'] += 1
        
        error_msg = error_response.get('error', 'Unknown error')
        self.logger.error(f"RVM status poll failed: {error_msg}")
        
        # Update health monitor
        self.health_monitor.set_connection_status('disconnected')
        self.health_monitor.record_error()
        
        # Get current status
        current_status = self.health_monitor.get_current_status()
        
        # Notify callbacks
        self._notify_status_callbacks(current_status)
    
    def _send_health_data(self):
        """Send health data to server"""
        try:
            health_summary = self.health_monitor.get_health_summary()
            
            # Add RVM-specific data
            health_data = {
                'rvm_id': self.rvm_id,
                'timestamp': datetime.now().isoformat(),
                'health_summary': health_summary,
                'polling_stats': self.stats.copy()
            }
            
            success, response = self.api_client.update_rvm_health(self.rvm_id, health_data)
            
            if success:
                self.logger.debug("Health data sent successfully")
            else:
                self.logger.warning(f"Failed to send health data: {response}")
                
        except Exception as e:
            self.logger.error(f"Error sending health data: {e}")
    
    def _notify_status_callbacks(self, status: Dict):
        """Notify status callbacks"""
        for callback in self.status_callbacks:
            try:
                callback(status)
            except Exception as e:
                self.logger.error(f"Error in status callback: {e}")
    
    def _notify_health_callbacks(self, health_data: Dict):
        """Notify health callbacks"""
        for callback in self.health_callbacks:
            try:
                callback(health_data)
            except Exception as e:
                self.logger.error(f"Error in health callback: {e}")
    
    def get_current_status(self) -> Dict:
        """Get current RVM status"""
        return self.health_monitor.get_current_status()
    
    def get_health_summary(self) -> Dict:
        """Get health summary"""
        return self.health_monitor.get_health_summary()
    
    def get_polling_stats(self) -> Dict:
        """Get polling statistics"""
        return self.stats.copy()
    
    def force_poll(self) -> bool:
        """Force immediate poll (for testing)"""
        try:
            self._poll_rvm_status()
            return True
        except Exception as e:
            self.logger.error(f"Error in force poll: {e}")
            return False
    
    def test_connection(self) -> bool:
        """Test connection to server"""
        try:
            success, response = self.api_client.test_connection()
            if success:
                self.logger.info("Connection test successful")
                return True
            else:
                self.logger.error(f"Connection test failed: {response}")
                return False
        except Exception as e:
            self.logger.error(f"Connection test error: {e}")
            return False
    
    def is_healthy(self) -> bool:
        """Check if RVM is healthy"""
        health_summary = self.get_health_summary()
        return (
            health_summary['system_health'] in ['excellent', 'good', 'fair'] and
            self.stats['consecutive_failures'] < 5
        )
    
    def get_status_summary(self) -> Dict:
        """Get comprehensive status summary"""
        return {
            'rvm_id': self.rvm_id,
            'is_running': self.is_running,
            'is_healthy': self.is_healthy(),
            'current_status': self.get_current_status(),
            'health_summary': self.get_health_summary(),
            'polling_stats': self.get_polling_stats(),
            'config': {
                'polling_interval': self.polling_interval,
                'monitoring_interval': self.health_monitor.monitoring_interval,
                'server_url': self.config.get('RVM_API_BASE_URL', 'http://100.123.143.87:8001')
            }
        }
