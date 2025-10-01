#!/usr/bin/env python3
"""
RVM Polling Integration with Jetson API
Integrates RVM polling services with the main Jetson API
"""

import os
import sys
import logging
import threading
from typing import Dict, Optional

# Add parent directories to path for imports
sys.path.append(os.path.join(os.path.dirname(__file__), '../../../../..'))

from internal_service_manager import RVMServiceManager

class RVMAPIIntegration:
    """Integration between RVM polling services and Jetson API"""
    
    def __init__(self, config_file: str = None):
        """
        Initialize RVM API Integration
        
        Args:
            config_file: Path to configuration file
        """
        self.config_file = config_file or 'rvm_config.env'
        self.service_manager = RVMServiceManager(self.config_file)
        
        # Integration state
        self.is_integrated = False
        self.integration_thread = None
        
        # Setup logging
        self.logger = logging.getLogger(__name__)
        
        # RVM status cache
        self.rvm_status_cache = {}
        self.last_status_update = None
    
    def start_integration(self) -> bool:
        """Start RVM polling integration"""
        try:
            if self.is_integrated:
                self.logger.warning("RVM integration already running")
                return False
            
            # Test connection first
            if not self.service_manager.test_connection():
                self.logger.error("Failed to connect to MyRVM-Platform server")
                return False
            
            # Start services
            if not self.service_manager.start_all_services():
                self.logger.error("Failed to start RVM services")
                return False
            
            # Start integration thread
            self.is_integrated = True
            self.integration_thread = threading.Thread(target=self._integration_loop, daemon=True)
            self.integration_thread.start()
            
            self.logger.info("RVM polling integration started successfully")
            return True
            
        except Exception as e:
            self.logger.error(f"Error starting RVM integration: {e}")
            return False
    
    def stop_integration(self):
        """Stop RVM polling integration"""
        try:
            if not self.is_integrated:
                return
            
            self.is_integrated = False
            
            # Stop services
            self.service_manager.stop_all_services()
            
            # Wait for integration thread
            if self.integration_thread:
                self.integration_thread.join(timeout=5)
            
            self.logger.info("RVM polling integration stopped")
            
        except Exception as e:
            self.logger.error(f"Error stopping RVM integration: {e}")
    
    def _integration_loop(self):
        """Main integration loop"""
        while self.is_integrated:
            try:
                # Update RVM status cache
                self._update_status_cache()
                
                # Sleep for a bit
                threading.Event().wait(10)
                
            except Exception as e:
                self.logger.error(f"Error in integration loop: {e}")
                threading.Event().wait(5)
    
    def _update_status_cache(self):
        """Update RVM status cache"""
        try:
            all_status = self.service_manager.get_all_status()
            all_health = self.service_manager.get_all_health()
            
            # Update cache
            for rvm_id, status in all_status.items():
                health = all_health.get(rvm_id, {})
                
                self.rvm_status_cache[rvm_id] = {
                    'status': status,
                    'health': health,
                    'last_update': status.get('last_update')
                }
            
            self.last_status_update = all_status
            
        except Exception as e:
            self.logger.error(f"Error updating status cache: {e}")
    
    def get_rvm_status(self, rvm_id: int) -> Optional[Dict]:
        """Get RVM status for API endpoint"""
        try:
            if rvm_id in self.rvm_status_cache:
                return self.rvm_status_cache[rvm_id]
            
            # Fallback to direct service manager call
            status = self.service_manager.get_rvm_status(rvm_id)
            health = self.service_manager.get_health_summary(rvm_id)
            
            if status:
                return {
                    'status': status,
                    'health': health,
                    'last_update': status.get('last_update')
                }
            
            return None
            
        except Exception as e:
            self.logger.error(f"Error getting RVM status for {rvm_id}: {e}")
            return None
    
    def get_all_rvm_status(self) -> Dict[int, Dict]:
        """Get all RVM status for API endpoint"""
        return self.rvm_status_cache.copy()
    
    def is_rvm_operational(self, rvm_id: int) -> bool:
        """Check if RVM is operational"""
        try:
            rvm_data = self.get_rvm_status(rvm_id)
            if not rvm_data:
                return False
            
            status = rvm_data['status']
            health = rvm_data['health']
            
            # Check RVM status
            rvm_status = status.get('rvm_status', 'unknown')
            if rvm_status not in ['active']:
                return False
            
            # Check connection status
            connection_status = status.get('connection_status', 'disconnected')
            if connection_status != 'connected':
                return False
            
            # Check health (optional)
            if health and 'error' not in health:
                system_health = health.get('system_health', 'unknown')
                if system_health in ['critical']:
                    return False
            
            return True
            
        except Exception as e:
            self.logger.error(f"Error checking RVM operational status for {rvm_id}: {e}")
            return False
    
    def get_rvm_health_summary(self, rvm_id: int) -> Optional[Dict]:
        """Get RVM health summary for API endpoint"""
        try:
            rvm_data = self.get_rvm_status(rvm_id)
            if not rvm_data:
                return None
            
            return rvm_data['health']
            
        except Exception as e:
            self.logger.error(f"Error getting RVM health summary for {rvm_id}: {e}")
            return None
    
    def get_integration_status(self) -> Dict:
        """Get integration status for API endpoint"""
        try:
            service_summary = self.service_manager.get_service_summary()
            
            return {
                'is_integrated': self.is_integrated,
                'is_running': service_summary['is_running'],
                'total_rvms': service_summary['total_rvms'],
                'rvm_ids': service_summary['rvm_ids'],
                'last_update': self.last_status_update,
                'cache_size': len(self.rvm_status_cache),
                'server_url': service_summary['config']['server_url'],
                'polling_interval': service_summary['config']['polling_interval']
            }
            
        except Exception as e:
            self.logger.error(f"Error getting integration status: {e}")
            return {
                'is_integrated': False,
                'error': str(e)
            }
    
    def force_status_update(self) -> bool:
        """Force immediate status update"""
        try:
            if not self.is_integrated:
                return False
            
            # Force poll all RVMs
            results = self.service_manager.force_poll_all()
            
            # Update cache
            self._update_status_cache()
            
            return all(results.values())
            
        except Exception as e:
            self.logger.error(f"Error forcing status update: {e}")
            return False
    
    def test_connection(self) -> bool:
        """Test connection to MyRVM-Platform"""
        return self.service_manager.test_connection()
    
    def get_operational_rvms(self) -> list:
        """Get list of operational RVM IDs"""
        operational = []
        
        for rvm_id in self.rvm_status_cache.keys():
            if self.is_rvm_operational(rvm_id):
                operational.append(rvm_id)
        
        return operational
    
    def get_rvm_status_summary(self) -> Dict:
        """Get comprehensive RVM status summary"""
        try:
            summary = {
                'total_rvms': len(self.rvm_status_cache),
                'operational_rvms': self.get_operational_rvms(),
                'rvm_details': {}
            }
            
            for rvm_id, rvm_data in self.rvm_status_cache.items():
                status = rvm_data['status']
                health = rvm_data['health']
                
                summary['rvm_details'][rvm_id] = {
                    'rvm_status': status.get('rvm_status', 'unknown'),
                    'connection_status': status.get('connection_status', 'unknown'),
                    'is_operational': self.is_rvm_operational(rvm_id),
                    'system_health': health.get('system_health', 'unknown') if health else 'unknown',
                    'health_score': health.get('health_score', 0) if health else 0,
                    'last_update': rvm_data.get('last_update')
                }
            
            return summary
            
        except Exception as e:
            self.logger.error(f"Error getting RVM status summary: {e}")
            return {'error': str(e)}

# Global integration instance
_integration_instance = None

def get_integration() -> Optional[RVMAPIIntegration]:
    """Get global integration instance"""
    return _integration_instance

def initialize_integration(config_file: str = None) -> RVMAPIIntegration:
    """Initialize global integration instance"""
    global _integration_instance
    
    if _integration_instance is None:
        _integration_instance = RVMAPIIntegration(config_file)
    
    return _integration_instance

def start_integration(config_file: str = None) -> bool:
    """Start global integration"""
    integration = initialize_integration(config_file)
    return integration.start_integration()

def stop_integration():
    """Stop global integration"""
    global _integration_instance
    
    if _integration_instance:
        _integration_instance.stop_integration()
        _integration_instance = None
