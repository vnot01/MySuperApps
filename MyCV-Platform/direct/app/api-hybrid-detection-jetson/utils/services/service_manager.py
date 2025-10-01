#!/usr/bin/env python3
"""
RVM Service Manager
Manages RVM polling services and provides unified interface
"""

import os
import sys
import logging
import signal
import threading
from typing import Dict, List, Optional
from datetime import datetime

# Add parent directories to path for imports
sys.path.append(os.path.join(os.path.dirname(__file__), '../../../..'))

from .status_poller import RVMStatusPoller
from .api_client import RVMAPIClient

class RVMServiceManager:
    """Main service manager for RVM polling services"""
    
    def __init__(self, config_file: str = None):
        """
        Initialize RVM Service Manager
        
        Args:
            config_file: Path to configuration file
        """
        self.config_file = config_file or 'rvm_config.env'
        self.config = self._load_config()
        
        # Service registry
        self.pollers: Dict[int, RVMStatusPoller] = {}
        self.is_running = False
        
        # Setup logging
        self.logger = logging.getLogger(__name__)
        
        # Signal handling
        signal.signal(signal.SIGINT, self._signal_handler)
        signal.signal(signal.SIGTERM, self._signal_handler)
    
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
    
    def _signal_handler(self, signum, frame):
        """Handle shutdown signals"""
        self.logger.info(f"Received signal {signum}, shutting down...")
        self.stop_all_services()
        sys.exit(0)
    
    def add_rvm_poller(self, rvm_id: int) -> bool:
        """
        Add RVM poller for specific RVM ID
        
        Args:
            rvm_id: RVM ID to add poller for
            
        Returns:
            True if added successfully, False otherwise
        """
        try:
            if rvm_id in self.pollers:
                self.logger.warning(f"Poller for RVM {rvm_id} already exists")
                return False
            
            # Create poller
            poller = RVMStatusPoller(rvm_id, self.config_file)
            
            # Add status callback
            poller.add_status_callback(self._on_status_update)
            poller.add_health_callback(self._on_health_update)
            
            # Store poller
            self.pollers[rvm_id] = poller
            
            self.logger.info(f"RVM poller added for RVM {rvm_id}")
            return True
            
        except Exception as e:
            self.logger.error(f"Error adding RVM poller for {rvm_id}: {e}")
            return False
    
    def remove_rvm_poller(self, rvm_id: int) -> bool:
        """
        Remove RVM poller for specific RVM ID
        
        Args:
            rvm_id: RVM ID to remove poller for
            
        Returns:
            True if removed successfully, False otherwise
        """
        try:
            if rvm_id not in self.pollers:
                self.logger.warning(f"Poller for RVM {rvm_id} not found")
                return False
            
            # Stop and remove poller
            poller = self.pollers[rvm_id]
            poller.stop_polling()
            del self.pollers[rvm_id]
            
            self.logger.info(f"RVM poller removed for RVM {rvm_id}")
            return True
            
        except Exception as e:
            self.logger.error(f"Error removing RVM poller for {rvm_id}: {e}")
            return False
    
    def start_all_services(self) -> bool:
        """Start all RVM polling services"""
        try:
            if self.is_running:
                self.logger.warning("Services already running")
                return False
            
            # Get RVM IDs from config
            rvm_ids_str = self.config.get('RVM_IDS', '1')
            rvm_ids = [int(id.strip()) for id in rvm_ids_str.split(',') if id.strip().isdigit()]
            
            if not rvm_ids:
                self.logger.error("No valid RVM IDs found in configuration")
                return False
            
            # Add and start pollers for each RVM
            for rvm_id in rvm_ids:
                if self.add_rvm_poller(rvm_id):
                    self.pollers[rvm_id].start_polling()
            
            self.is_running = True
            self.logger.info(f"All RVM services started for RVM IDs: {rvm_ids}")
            return True
            
        except Exception as e:
            self.logger.error(f"Error starting services: {e}")
            return False
    
    def stop_all_services(self):
        """Stop all RVM polling services"""
        try:
            if not self.is_running:
                return
            
            # Stop all pollers
            for rvm_id, poller in self.pollers.items():
                try:
                    poller.stop_polling()
                    self.logger.info(f"Stopped poller for RVM {rvm_id}")
                except Exception as e:
                    self.logger.error(f"Error stopping poller for RVM {rvm_id}: {e}")
            
            # Clear pollers
            self.pollers.clear()
            self.is_running = False
            
            self.logger.info("All RVM services stopped")
            
        except Exception as e:
            self.logger.error(f"Error stopping services: {e}")
    
    def start_rvm_service(self, rvm_id: int) -> bool:
        """Start service for specific RVM"""
        try:
            if rvm_id not in self.pollers:
                self.logger.error(f"Poller for RVM {rvm_id} not found")
                return False
            
            self.pollers[rvm_id].start_polling()
            self.logger.info(f"Started service for RVM {rvm_id}")
            return True
            
        except Exception as e:
            self.logger.error(f"Error starting service for RVM {rvm_id}: {e}")
            return False
    
    def stop_rvm_service(self, rvm_id: int) -> bool:
        """Stop service for specific RVM"""
        try:
            if rvm_id not in self.pollers:
                self.logger.error(f"Poller for RVM {rvm_id} not found")
                return False
            
            self.pollers[rvm_id].stop_polling()
            self.logger.info(f"Stopped service for RVM {rvm_id}")
            return True
            
        except Exception as e:
            self.logger.error(f"Error stopping service for RVM {rvm_id}: {e}")
            return False
    
    def get_rvm_status(self, rvm_id: int) -> Optional[Dict]:
        """Get status for specific RVM"""
        try:
            if rvm_id not in self.pollers:
                return None
            
            return self.pollers[rvm_id].get_current_status()
            
        except Exception as e:
            self.logger.error(f"Error getting status for RVM {rvm_id}: {e}")
            return None
    
    def get_all_status(self) -> Dict[int, Dict]:
        """Get status for all RVMs"""
        status = {}
        
        for rvm_id, poller in self.pollers.items():
            try:
                status[rvm_id] = poller.get_current_status()
            except Exception as e:
                self.logger.error(f"Error getting status for RVM {rvm_id}: {e}")
                status[rvm_id] = {'error': str(e)}
        
        return status
    
    def get_health_summary(self, rvm_id: int) -> Optional[Dict]:
        """Get health summary for specific RVM"""
        try:
            if rvm_id not in self.pollers:
                return None
            
            return self.pollers[rvm_id].get_health_summary()
            
        except Exception as e:
            self.logger.error(f"Error getting health summary for RVM {rvm_id}: {e}")
            return None
    
    def get_all_health(self) -> Dict[int, Dict]:
        """Get health summary for all RVMs"""
        health = {}
        
        for rvm_id, poller in self.pollers.items():
            try:
                health[rvm_id] = poller.get_health_summary()
            except Exception as e:
                self.logger.error(f"Error getting health for RVM {rvm_id}: {e}")
                health[rvm_id] = {'error': str(e)}
        
        return health
    
    def test_connection(self) -> bool:
        """Test connection to MyRVM-Platform server"""
        try:
            api_client = RVMAPIClient(
                base_url=self.config.get('RVM_API_BASE_URL', 'http://100.123.143.87:8001'),
                api_key=self.config.get('RVM_API_KEY', ''),
                timeout=int(self.config.get('API_TIMEOUT', '30'))
            )
            
            success = api_client.test_connection()
            api_client.close()
            
            if success:
                self.logger.info("Connection test successful")
            else:
                self.logger.error("Connection test failed")
            
            return success
            
        except Exception as e:
            self.logger.error(f"Connection test error: {e}")
            return False
    
    def force_poll_all(self) -> Dict[int, bool]:
        """Force immediate poll for all RVMs"""
        results = {}
        
        for rvm_id, poller in self.pollers.items():
            try:
                results[rvm_id] = poller.force_poll()
            except Exception as e:
                self.logger.error(f"Error force polling RVM {rvm_id}: {e}")
                results[rvm_id] = False
        
        return results
    
    def get_service_summary(self) -> Dict:
        """Get comprehensive service summary"""
        return {
            'is_running': self.is_running,
            'total_rvms': len(self.pollers),
            'rvm_ids': list(self.pollers.keys()),
            'config': {
                'server_url': self.config.get('RVM_API_BASE_URL', 'http://100.123.143.87:8001'),
                'polling_interval': self.config.get('POLLING_INTERVAL', '60'),
                'monitoring_interval': self.config.get('MONITORING_INTERVAL', '30')
            },
            'status': self.get_all_status(),
            'health': self.get_all_health(),
            'last_update': datetime.now().isoformat()
        }
    
    def _on_status_update(self, status: Dict):
        """Handle status update callback"""
        rvm_id = status.get('rvm_id', 'unknown')
        rvm_status = status.get('rvm_status', 'unknown')
        connection_status = status.get('connection_status', 'unknown')
        
        self.logger.info(f"RVM {rvm_id} status update: {rvm_status} (connection: {connection_status})")
    
    def _on_health_update(self, health_data: Dict):
        """Handle health update callback"""
        rvm_id = health_data.get('rvm_id', 'unknown')
        health_score = health_data.get('health_score', 0)
        system_health = health_data.get('system_health', 'unknown')
        
        self.logger.info(f"RVM {rvm_id} health update: {system_health} (score: {health_score:.1f})")
    
    def run_forever(self):
        """Run services forever (blocking)"""
        try:
            if not self.start_all_services():
                self.logger.error("Failed to start services")
                return False
            
            self.logger.info("RVM Service Manager running... Press Ctrl+C to stop")
            
            # Keep running until interrupted
            while self.is_running:
                try:
                    # Check if any pollers have stopped unexpectedly
                    for rvm_id, poller in list(self.pollers.items()):
                        if not poller.is_running:
                            self.logger.warning(f"Poller for RVM {rvm_id} stopped unexpectedly")
                            # Restart poller
                            poller.start_polling()
                    
                    # Sleep for a bit
                    threading.Event().wait(10)
                    
                except KeyboardInterrupt:
                    break
                except Exception as e:
                    self.logger.error(f"Error in main loop: {e}")
                    threading.Event().wait(5)
            
            return True
            
        except Exception as e:
            self.logger.error(f"Error in run_forever: {e}")
            return False
        finally:
            self.stop_all_services()
