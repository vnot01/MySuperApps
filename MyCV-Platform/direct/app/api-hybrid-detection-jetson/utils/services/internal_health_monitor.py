#!/usr/bin/env python3
"""
RVM Health Monitor Service
Tracks and manages RVM health metrics and status monitoring
"""

import time
import psutil
import threading
from typing import Dict, List, Optional
from datetime import datetime, timedelta
from collections import deque
import logging

class RVMHealthMonitor:
    """Health monitoring service for RVM status tracking"""
    
    def __init__(self, rvm_id: int, monitoring_interval: int = 30):
        """
        Initialize RVM Health Monitor
        
        Args:
            rvm_id: RVM ID for this monitor
            monitoring_interval: Monitoring interval in seconds
        """
        self.rvm_id = rvm_id
        self.monitoring_interval = monitoring_interval
        self.is_running = False
        self.monitor_thread = None
        
        # Health metrics storage
        self.health_metrics = {
            'api_response_time': deque(maxlen=100),  # Last 100 response times
            'cpu_usage': deque(maxlen=100),
            'memory_usage': deque(maxlen=100),
            'disk_usage': deque(maxlen=100),
            'gpu_usage': deque(maxlen=100),
            'network_io': deque(maxlen=100),
            'detection_count': 0,
            'error_count': 0,
            'last_detection_time': None,
            'uptime_start': time.time()
        }
        
        # Current status
        self.current_status = {
            'rvm_status': 'unknown',
            'latest_detection': None,
            'detection_stats': {},
            'last_update': None,
            'connection_status': 'disconnected',
            'health_score': 0.0,
            'system_health': 'unknown'
        }
        
        # Setup logging
        self.logger = logging.getLogger(__name__)
    
    def start_monitoring(self):
        """Start health monitoring in background thread"""
        if self.is_running:
            self.logger.warning("Health monitoring already running")
            return
        
        self.is_running = True
        self.monitor_thread = threading.Thread(target=self._monitoring_loop, daemon=True)
        self.monitor_thread.start()
        self.logger.info(f"Health monitoring started for RVM {self.rvm_id}")
    
    def stop_monitoring(self):
        """Stop health monitoring"""
        self.is_running = False
        if self.monitor_thread:
            self.monitor_thread.join(timeout=5)
        self.logger.info(f"Health monitoring stopped for RVM {self.rvm_id}")
    
    def _monitoring_loop(self):
        """Main monitoring loop"""
        while self.is_running:
            try:
                # Collect system metrics
                self._collect_system_metrics()
                
                # Update health score
                self._update_health_score()
                
                # Sleep for monitoring interval
                time.sleep(self.monitoring_interval)
                
            except Exception as e:
                self.logger.error(f"Error in monitoring loop: {e}")
                self.health_metrics['error_count'] += 1
                time.sleep(5)  # Short sleep on error
    
    def _collect_system_metrics(self):
        """Collect system health metrics"""
        try:
            # CPU usage
            cpu_percent = psutil.cpu_percent(interval=1)
            self.health_metrics['cpu_usage'].append(cpu_percent)
            
            # Memory usage
            memory = psutil.virtual_memory()
            self.health_metrics['memory_usage'].append(memory.percent)
            
            # Disk usage
            disk = psutil.disk_usage('/')
            disk_percent = (disk.used / disk.total) * 100
            self.health_metrics['disk_usage'].append(disk_percent)
            
            # Network I/O
            net_io = psutil.net_io_counters()
            network_data = {
                'bytes_sent': net_io.bytes_sent,
                'bytes_recv': net_io.bytes_recv,
                'packets_sent': net_io.packets_sent,
                'packets_recv': net_io.packets_recv,
                'timestamp': time.time()
            }
            self.health_metrics['network_io'].append(network_data)
            
            # GPU usage (if available)
            try:
                import GPUtil
                gpus = GPUtil.getGPUs()
                if gpus:
                    gpu_usage = gpus[0].load * 100
                    self.health_metrics['gpu_usage'].append(gpu_usage)
            except ImportError:
                pass  # GPU monitoring not available
            except Exception:
                pass  # GPU monitoring failed
            
        except Exception as e:
            self.logger.error(f"Error collecting system metrics: {e}")
            self.health_metrics['error_count'] += 1
    
    def _update_health_score(self):
        """Update overall health score based on metrics"""
        try:
            scores = []
            
            # CPU health score (lower is better)
            if self.health_metrics['cpu_usage']:
                avg_cpu = sum(self.health_metrics['cpu_usage']) / len(self.health_metrics['cpu_usage'])
                cpu_score = max(0, 100 - avg_cpu)  # 100 - CPU usage
                scores.append(cpu_score)
            
            # Memory health score (lower is better)
            if self.health_metrics['memory_usage']:
                avg_memory = sum(self.health_metrics['memory_usage']) / len(self.health_metrics['memory_usage'])
                memory_score = max(0, 100 - avg_memory)  # 100 - Memory usage
                scores.append(memory_score)
            
            # Disk health score (lower is better)
            if self.health_metrics['disk_usage']:
                avg_disk = sum(self.health_metrics['disk_usage']) / len(self.health_metrics['disk_usage'])
                disk_score = max(0, 100 - avg_disk)  # 100 - Disk usage
                scores.append(disk_score)
            
            # API response time score (lower is better)
            if self.health_metrics['api_response_time']:
                avg_response_time = sum(self.health_metrics['api_response_time']) / len(self.health_metrics['api_response_time'])
                # Convert to score (0-100, lower response time = higher score)
                response_score = max(0, 100 - (avg_response_time * 10))  # 10s = 0 score
                scores.append(response_score)
            
            # Calculate overall health score
            if scores:
                self.current_status['health_score'] = sum(scores) / len(scores)
                
                # Determine system health status
                if self.current_status['health_score'] >= 80:
                    self.current_status['system_health'] = 'excellent'
                elif self.current_status['health_score'] >= 60:
                    self.current_status['system_health'] = 'good'
                elif self.current_status['health_score'] >= 40:
                    self.current_status['system_health'] = 'fair'
                elif self.current_status['health_score'] >= 20:
                    self.current_status['system_health'] = 'poor'
                else:
                    self.current_status['system_health'] = 'critical'
            else:
                self.current_status['health_score'] = 0.0
                self.current_status['system_health'] = 'unknown'
                
        except Exception as e:
            self.logger.error(f"Error updating health score: {e}")
            self.current_status['health_score'] = 0.0
            self.current_status['system_health'] = 'error'
    
    def record_api_response_time(self, response_time: float):
        """Record API response time"""
        self.health_metrics['api_response_time'].append(response_time)
    
    def record_detection(self, detection_data: Dict):
        """Record detection event"""
        self.health_metrics['detection_count'] += 1
        self.health_metrics['last_detection_time'] = time.time()
        self.current_status['latest_detection'] = detection_data
    
    def record_error(self):
        """Record error event"""
        self.health_metrics['error_count'] += 1
    
    def get_health_summary(self) -> Dict:
        """Get comprehensive health summary"""
        uptime = time.time() - self.health_metrics['uptime_start']
        
        # Calculate averages
        avg_cpu = sum(self.health_metrics['cpu_usage']) / len(self.health_metrics['cpu_usage']) if self.health_metrics['cpu_usage'] else 0
        avg_memory = sum(self.health_metrics['memory_usage']) / len(self.health_metrics['memory_usage']) if self.health_metrics['memory_usage'] else 0
        avg_disk = sum(self.health_metrics['disk_usage']) / len(self.health_metrics['disk_usage']) if self.health_metrics['disk_usage'] else 0
        avg_response_time = sum(self.health_metrics['api_response_time']) / len(self.health_metrics['api_response_time']) if self.health_metrics['api_response_time'] else 0
        
        return {
            'rvm_id': self.rvm_id,
            'uptime_seconds': uptime,
            'uptime_human': str(timedelta(seconds=int(uptime))),
            'health_score': self.current_status['health_score'],
            'system_health': self.current_status['system_health'],
            'metrics': {
                'cpu_usage_avg': round(avg_cpu, 2),
                'memory_usage_avg': round(avg_memory, 2),
                'disk_usage_avg': round(avg_disk, 2),
                'api_response_time_avg': round(avg_response_time, 3),
                'detection_count': self.health_metrics['detection_count'],
                'error_count': self.health_metrics['error_count']
            },
            'current_status': self.current_status,
            'last_update': datetime.now().isoformat()
        }
    
    def get_current_status(self) -> Dict:
        """Get current RVM status"""
        return self.current_status.copy()
    
    def update_rvm_status(self, status_data: Dict):
        """Update RVM status from server response"""
        self.current_status.update({
            'rvm_status': status_data.get('rvm_status', 'unknown'),
            'latest_detection': status_data.get('latest_detection'),
            'detection_stats': status_data.get('detection_stats', {}),
            'last_update': datetime.now().isoformat(),
            'connection_status': 'connected'
        })
    
    def set_connection_status(self, status: str):
        """Set connection status"""
        self.current_status['connection_status'] = status
        self.current_status['last_update'] = datetime.now().isoformat()
