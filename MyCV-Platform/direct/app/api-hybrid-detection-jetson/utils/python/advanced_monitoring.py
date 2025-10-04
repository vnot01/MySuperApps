#!/usr/bin/env python3
"""
Advanced Monitoring System for MyCV-Platform
Real-time performance monitoring, alerts, and analytics
"""

import time
import psutil
import json
import logging
from datetime import datetime, timedelta
from typing import Dict, List, Optional
from dataclasses import dataclass
from pathlib import Path
import threading
import queue

@dataclass
class PerformanceMetrics:
    """Performance metrics data structure"""
    timestamp: datetime
    cpu_percent: float
    memory_percent: float
    gpu_memory_percent: float
    disk_usage_percent: float
    processing_time_ms: float
    detections_count: int
    error_count: int
    api_requests_count: int

class AdvancedMonitoring:
    """Advanced monitoring system for MyCV-Platform"""
    
    def __init__(self, config_file: str = "monitoring_config.json"):
        self.config_file = config_file
        self.config = self.load_config()
        self.metrics_queue = queue.Queue()
        self.alerts_queue = queue.Queue()
        self.is_monitoring = False
        self.monitoring_thread = None
        
        # Setup logging
        self.setup_logging()
        
        # Performance thresholds
        self.thresholds = {
            'cpu_percent': 80.0,
            'memory_percent': 85.0,
            'gpu_memory_percent': 90.0,
            'disk_usage_percent': 90.0,
            'processing_time_ms': 5000.0,
            'error_rate_percent': 5.0
        }
        
        # Historical data
        self.historical_metrics = []
        self.max_history_size = 1000
        
    def load_config(self) -> Dict:
        """Load monitoring configuration"""
        default_config = {
            "monitoring_interval": 30,  # seconds
            "alert_cooldown": 300,  # seconds
            "max_history_size": 1000,
            "enable_alerts": True,
            "enable_analytics": True,
            "log_level": "INFO"
        }
        
        try:
            if Path(self.config_file).exists():
                with open(self.config_file, 'r') as f:
                    config = json.load(f)
                    return {**default_config, **config}
            else:
                # Create default config file
                with open(self.config_file, 'w') as f:
                    json.dump(default_config, f, indent=2)
                return default_config
        except Exception as e:
            print(f"Error loading config: {e}")
            return default_config
    
    def setup_logging(self):
        """Setup logging configuration"""
        log_level = getattr(logging, self.config.get('log_level', 'INFO'))
        logging.basicConfig(
            level=log_level,
            format='%(asctime)s - %(name)s - %(levelname)s - %(message)s',
            handlers=[
                logging.FileHandler('monitoring.log'),
                logging.StreamHandler()
            ]
        )
        self.logger = logging.getLogger(__name__)
    
    def start_monitoring(self):
        """Start the monitoring system"""
        if self.is_monitoring:
            return
        
        self.is_monitoring = True
        self.monitoring_thread = threading.Thread(target=self._monitoring_loop, daemon=True)
        self.monitoring_thread.start()
        self.logger.info("Advanced monitoring started")
    
    def stop_monitoring(self):
        """Stop the monitoring system"""
        self.is_monitoring = False
        if self.monitoring_thread:
            self.monitoring_thread.join(timeout=5)
        self.logger.info("Advanced monitoring stopped")
    
    def _monitoring_loop(self):
        """Main monitoring loop"""
        while self.is_monitoring:
            try:
                metrics = self.collect_metrics()
                self.metrics_queue.put(metrics)
                self.historical_metrics.append(metrics)
                
                # Keep only recent metrics
                if len(self.historical_metrics) > self.max_history_size:
                    self.historical_metrics = self.historical_metrics[-self.max_history_size:]
                
                # Check for alerts
                if self.config.get('enable_alerts', True):
                    self.check_alerts(metrics)
                
                time.sleep(self.config.get('monitoring_interval', 30))
                
            except Exception as e:
                self.logger.error(f"Error in monitoring loop: {e}")
                time.sleep(5)
    
    def collect_metrics(self) -> PerformanceMetrics:
        """Collect current system metrics"""
        try:
            # CPU and Memory
            cpu_percent = psutil.cpu_percent(interval=1)
            memory = psutil.virtual_memory()
            memory_percent = memory.percent
            
            # Disk usage
            disk = psutil.disk_usage('/')
            disk_usage_percent = (disk.used / disk.total) * 100
            
            # GPU memory (if available)
            gpu_memory_percent = self.get_gpu_memory_usage()
            
            # Processing metrics (from recent activity)
            processing_time_ms = self.get_average_processing_time()
            detections_count = self.get_recent_detections_count()
            error_count = self.get_recent_error_count()
            api_requests_count = self.get_recent_api_requests_count()
            
            return PerformanceMetrics(
                timestamp=datetime.now(),
                cpu_percent=cpu_percent,
                memory_percent=memory_percent,
                gpu_memory_percent=gpu_memory_percent,
                disk_usage_percent=disk_usage_percent,
                processing_time_ms=processing_time_ms,
                detections_count=detections_count,
                error_count=error_count,
                api_requests_count=api_requests_count
            )
            
        except Exception as e:
            self.logger.error(f"Error collecting metrics: {e}")
            return PerformanceMetrics(
                timestamp=datetime.now(),
                cpu_percent=0.0,
                memory_percent=0.0,
                gpu_memory_percent=0.0,
                disk_usage_percent=0.0,
                processing_time_ms=0.0,
                detections_count=0,
                error_count=1,
                api_requests_count=0
            )
    
    def get_gpu_memory_usage(self) -> float:
        """Get GPU memory usage percentage"""
        try:
            import torch
            if torch.cuda.is_available():
                gpu_memory = torch.cuda.memory_allocated()
                gpu_memory_total = torch.cuda.get_device_properties(0).total_memory
                return (gpu_memory / gpu_memory_total) * 100
        except ImportError:
            pass
        except Exception as e:
            self.logger.warning(f"Could not get GPU memory usage: {e}")
        
        return 0.0
    
    def get_average_processing_time(self) -> float:
        """Get average processing time from recent activity"""
        # This would typically read from a log file or database
        # For now, return a placeholder
        return 0.0
    
    def get_recent_detections_count(self) -> int:
        """Get count of recent detections"""
        # This would typically read from a log file or database
        # For now, return a placeholder
        return 0
    
    def get_recent_error_count(self) -> int:
        """Get count of recent errors"""
        # This would typically read from a log file or database
        # For now, return a placeholder
        return 0
    
    def get_recent_api_requests_count(self) -> int:
        """Get count of recent API requests"""
        # This would typically read from a log file or database
        # For now, return a placeholder
        return 0
    
    def check_alerts(self, metrics: PerformanceMetrics):
        """Check for alert conditions"""
        alerts = []
        
        if metrics.cpu_percent > self.thresholds['cpu_percent']:
            alerts.append(f"High CPU usage: {metrics.cpu_percent:.1f}%")
        
        if metrics.memory_percent > self.thresholds['memory_percent']:
            alerts.append(f"High memory usage: {metrics.memory_percent:.1f}%")
        
        if metrics.gpu_memory_percent > self.thresholds['gpu_memory_percent']:
            alerts.append(f"High GPU memory usage: {metrics.gpu_memory_percent:.1f}%")
        
        if metrics.disk_usage_percent > self.thresholds['disk_usage_percent']:
            alerts.append(f"High disk usage: {metrics.disk_usage_percent:.1f}%")
        
        if metrics.processing_time_ms > self.thresholds['processing_time_ms']:
            alerts.append(f"Slow processing: {metrics.processing_time_ms:.1f}ms")
        
        # Send alerts
        for alert in alerts:
            self.send_alert(alert, metrics)
    
    def send_alert(self, message: str, metrics: PerformanceMetrics):
        """Send alert notification"""
        alert_data = {
            'timestamp': metrics.timestamp.isoformat(),
            'message': message,
            'metrics': {
                'cpu_percent': metrics.cpu_percent,
                'memory_percent': metrics.memory_percent,
                'gpu_memory_percent': metrics.gpu_memory_percent,
                'disk_usage_percent': metrics.disk_usage_percent,
                'processing_time_ms': metrics.processing_time_ms
            }
        }
        
        self.alerts_queue.put(alert_data)
        self.logger.warning(f"ALERT: {message}")
        
        # Here you could send to external systems like:
        # - Email notifications
        # - Slack/Discord webhooks
        # - SMS alerts
        # - Push notifications
    
    def get_current_metrics(self) -> Optional[PerformanceMetrics]:
        """Get current metrics from queue"""
        try:
            return self.metrics_queue.get_nowait()
        except queue.Empty:
            return None
    
    def get_historical_metrics(self, hours: int = 24) -> List[PerformanceMetrics]:
        """Get historical metrics for the specified hours"""
        cutoff_time = datetime.now() - timedelta(hours=hours)
        return [m for m in self.historical_metrics if m.timestamp >= cutoff_time]
    
    def get_performance_summary(self, hours: int = 24) -> Dict:
        """Get performance summary for the specified period"""
        metrics = self.get_historical_metrics(hours)
        
        if not metrics:
            return {"error": "No metrics available"}
        
        return {
            "period_hours": hours,
            "total_samples": len(metrics),
            "cpu": {
                "average": sum(m.cpu_percent for m in metrics) / len(metrics),
                "max": max(m.cpu_percent for m in metrics),
                "min": min(m.cpu_percent for m in metrics)
            },
            "memory": {
                "average": sum(m.memory_percent for m in metrics) / len(metrics),
                "max": max(m.memory_percent for m in metrics),
                "min": min(m.memory_percent for m in metrics)
            },
            "gpu_memory": {
                "average": sum(m.gpu_memory_percent for m in metrics) / len(metrics),
                "max": max(m.gpu_memory_percent for m in metrics),
                "min": min(m.gpu_memory_percent for m in metrics)
            },
            "disk_usage": {
                "average": sum(m.disk_usage_percent for m in metrics) / len(metrics),
                "max": max(m.disk_usage_percent for m in metrics),
                "min": min(m.disk_usage_percent for m in metrics)
            },
            "processing_time": {
                "average": sum(m.processing_time_ms for m in metrics) / len(metrics),
                "max": max(m.processing_time_ms for m in metrics),
                "min": min(m.processing_time_ms for m in metrics)
            },
            "total_detections": sum(m.detections_count for m in metrics),
            "total_errors": sum(m.error_count for m in metrics),
            "total_api_requests": sum(m.api_requests_count for m in metrics)
        }
    
    def get_alerts(self) -> List[Dict]:
        """Get recent alerts"""
        alerts = []
        try:
            while True:
                alert = self.alerts_queue.get_nowait()
                alerts.append(alert)
        except queue.Empty:
            pass
        return alerts
    
    def update_thresholds(self, new_thresholds: Dict):
        """Update alert thresholds"""
        self.thresholds.update(new_thresholds)
        self.logger.info(f"Updated thresholds: {new_thresholds}")
    
    def export_metrics(self, filename: str, hours: int = 24):
        """Export metrics to JSON file"""
        metrics = self.get_historical_metrics(hours)
        data = []
        
        for metric in metrics:
            data.append({
                'timestamp': metric.timestamp.isoformat(),
                'cpu_percent': metric.cpu_percent,
                'memory_percent': metric.memory_percent,
                'gpu_memory_percent': metric.gpu_memory_percent,
                'disk_usage_percent': metric.disk_usage_percent,
                'processing_time_ms': metric.processing_time_ms,
                'detections_count': metric.detections_count,
                'error_count': metric.error_count,
                'api_requests_count': metric.api_requests_count
            })
        
        with open(filename, 'w') as f:
            json.dump(data, f, indent=2)
        
        self.logger.info(f"Exported {len(data)} metrics to {filename}")

# Global monitoring instance
monitoring = AdvancedMonitoring()

def start_monitoring():
    """Start the monitoring system"""
    monitoring.start_monitoring()

def stop_monitoring():
    """Stop the monitoring system"""
    monitoring.stop_monitoring()

def get_current_metrics():
    """Get current metrics"""
    return monitoring.get_current_metrics()

def get_performance_summary(hours=24):
    """Get performance summary"""
    return monitoring.get_performance_summary(hours)

def get_alerts():
    """Get recent alerts"""
    return monitoring.get_alerts()

if __name__ == "__main__":
    # Test the monitoring system
    print("Starting advanced monitoring system...")
    start_monitoring()
    
    try:
        # Run for 60 seconds
        time.sleep(60)
        
        # Get performance summary
        summary = get_performance_summary()
        print("Performance Summary:")
        print(json.dumps(summary, indent=2))
        
        # Get alerts
        alerts = get_alerts()
        if alerts:
            print(f"\nAlerts ({len(alerts)}):")
            for alert in alerts:
                print(f"- {alert['message']}")
        
    finally:
        stop_monitoring()
        print("Monitoring stopped")
