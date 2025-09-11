#!/usr/bin/env python3
"""
MyCV-Platform Environment Detection Utility
Detects virtual environment, GPU/CPU mode, and mock data capabilities
"""

import os
import sys
import torch
import numpy as np
from typing import Dict, Any, Optional
from termcolor import colored
import platform
import subprocess


class EnvironmentDetector:
    """Environment detection utility for MyCV-Platform"""
    
    def __init__(self):
        self.results = {}
        self.colors = {
            'info': 'blue',
            'success': 'green',
            'warning': 'yellow',
            'error': 'red'
        }
    
    def log_message(self, message: str, level: str = 'info') -> None:
        """Print colored log message"""
        color = self.colors.get(level, 'white')
        print(colored(f"{level.upper()}: {message}", color))
    
    def check_virtual_environment(self) -> Dict[str, Any]:
        """Check if running in virtual environment"""
        venv_info = {
            'is_venv': False,
            'venv_path': None,
            'python_path': sys.executable,
            'python_version': sys.version
        }
        
        if 'VIRTUAL_ENV' in os.environ:
            venv_info['is_venv'] = True
            venv_info['venv_path'] = os.environ['VIRTUAL_ENV']
            self.log_message(f"✅ Running in virtual environment: {os.environ['VIRTUAL_ENV']}", 'success')
        else:
            self.log_message("⚠️  Not running in virtual environment", 'warning')
        
        return venv_info
    
    def check_gpu_capabilities(self) -> Dict[str, Any]:
        """Check GPU and CUDA capabilities"""
        gpu_info = {
            'cuda_available': False,
            'gpu_count': 0,
            'gpu_name': None,
            'cuda_version': None,
            'gpu_memory': None,
            'mode': 'CPU'
        }
        
        try:
            if torch.cuda.is_available():
                gpu_info['cuda_available'] = True
                gpu_info['gpu_count'] = torch.cuda.device_count()
                gpu_info['gpu_name'] = torch.cuda.get_device_name(0)
                gpu_info['cuda_version'] = torch.version.cuda
                gpu_info['gpu_memory'] = torch.cuda.get_device_properties(0).total_memory / 1024**3
                gpu_info['mode'] = 'GPU'
                
                self.log_message(f"✅ PyTorch CUDA available - {gpu_info['gpu_name']}", 'success')
                self.log_message(f"   CUDA Version: {gpu_info['cuda_version']}", 'info')
                self.log_message(f"   GPU Count: {gpu_info['gpu_count']}", 'info')
                self.log_message(f"   GPU Memory: {gpu_info['gpu_memory']:.1f} GB", 'info')
                self.log_message("🚀 GPU MODE: Ready for GPU acceleration", 'success')
            else:
                gpu_info['mode'] = 'CPU'
                self.log_message("⚠️  PyTorch CUDA not available - will use CPU mode", 'warning')
                self.log_message(f"   PyTorch Version: {torch.__version__}", 'info')
                self.log_message(f"   CPU Threads: {torch.get_num_threads()}", 'info')
                self.log_message("💻 CPU MODE: Using CPU for inference", 'warning')
                
        except Exception as e:
            self.log_message(f"❌ Error checking PyTorch: {e}", 'error')
            gpu_info['error'] = str(e)
        
        return gpu_info
    
    def check_nvidia_smi(self) -> Optional[Dict[str, Any]]:
        """Check NVIDIA-SMI availability and GPU info"""
        try:
            result = subprocess.run(['nvidia-smi', '--query-gpu=name,memory.total,memory.used', 
                                   '--format=csv,noheader,nounits'], 
                                  capture_output=True, text=True, timeout=10)
            
            if result.returncode == 0:
                gpu_lines = result.stdout.strip().split('\n')
                gpu_list = []
                for line in gpu_lines:
                    if line.strip():
                        parts = line.split(', ')
                        if len(parts) >= 3:
                            gpu_list.append({
                                'name': parts[0],
                                'memory_total': int(parts[1]),
                                'memory_used': int(parts[2])
                            })
                
                self.log_message("✅ NVIDIA GPU detected via nvidia-smi", 'success')
                for i, gpu in enumerate(gpu_list):
                    self.log_message(f"   GPU {i}: {gpu['name']} ({gpu['memory_total']}MB total, {gpu['memory_used']}MB used)", 'info')
                
                return {'available': True, 'gpus': gpu_list}
            else:
                self.log_message("⚠️  nvidia-smi not available or no GPU detected", 'warning')
                return {'available': False, 'error': result.stderr}
                
        except (subprocess.TimeoutExpired, FileNotFoundError, subprocess.SubprocessError) as e:
            self.log_message("⚠️  nvidia-smi not available", 'warning')
            return {'available': False, 'error': str(e)}
    
    def test_mock_data(self) -> Dict[str, Any]:
        """Test system with mock data"""
        mock_results = {
            'tensor_test': False,
            'image_test': False,
            'device': 'unknown',
            'errors': []
        }
        
        try:
            self.log_message("🧪 Testing PyTorch with mock data...", 'info')
            
            # Test basic tensor operations
            mock_tensor = torch.randn(1, 3, 224, 224)
            if torch.cuda.is_available():
                mock_tensor = mock_tensor.cuda()
                mock_results['device'] = 'cuda'
                self.log_message("✅ Mock tensor created on GPU", 'success')
            else:
                mock_results['device'] = 'cpu'
                self.log_message("✅ Mock tensor created on CPU", 'success')
            
            # Test basic operations
            result = torch.nn.functional.relu(mock_tensor)
            mock_results['tensor_test'] = True
            self.log_message("✅ Basic tensor operations successful", 'success')
            
            # Test with random image data
            self.log_message("🧪 Testing with mock image data...", 'info')
            mock_image = np.random.randint(0, 255, (640, 640, 3), dtype=np.uint8)
            mock_tensor = torch.from_numpy(mock_image).permute(2, 0, 1).float().unsqueeze(0)
            
            if torch.cuda.is_available():
                mock_tensor = mock_tensor.cuda()
            
            mock_results['image_test'] = True
            self.log_message("✅ Mock image processing successful", 'success')
            self.log_message(f"   Image shape: {mock_tensor.shape}", 'info')
            self.log_message(f"   Device: {mock_tensor.device}", 'info')
            self.log_message("🧪 MOCK DATA MODE: All tests passed", 'success')
            
        except Exception as e:
            error_msg = f"Mock data test failed: {e}"
            mock_results['errors'].append(error_msg)
            self.log_message(f"❌ {error_msg}", 'error')
        
        return mock_results
    
    def get_system_info(self) -> Dict[str, Any]:
        """Get general system information"""
        return {
            'platform': platform.platform(),
            'python_version': sys.version,
            'python_executable': sys.executable,
            'pytorch_version': torch.__version__,
            'numpy_version': np.__version__,
            'cwd': os.getcwd()
        }
    
    def detect_all(self) -> Dict[str, Any]:
        """Run all detection checks"""
        self.log_message("🔍 MyCV-Platform Environment Detection", 'info')
        self.log_message("=" * 50, 'info')
        
        results = {
            'virtual_environment': self.check_virtual_environment(),
            'gpu_capabilities': self.check_gpu_capabilities(),
            'nvidia_smi': self.check_nvidia_smi(),
            'mock_data_test': self.test_mock_data(),
            'system_info': self.get_system_info()
        }
        
        self.log_message("=" * 50, 'info')
        self.log_message("🎉 Environment detection completed!", 'success')
        
        return results
    
    def print_summary(self, results: Dict[str, Any]) -> None:
        """Print a summary of detection results"""
        self.log_message("\n📊 Detection Summary:", 'info')
        self.log_message(f"   Virtual Environment: {'✅ Yes' if results['virtual_environment']['is_venv'] else '❌ No'}", 'info')
        self.log_message(f"   GPU Mode: {'🚀 GPU' if results['gpu_capabilities']['mode'] == 'GPU' else '💻 CPU'}", 'info')
        self.log_message(f"   Mock Data Test: {'✅ Passed' if results['mock_data_test']['tensor_test'] and results['mock_data_test']['image_test'] else '❌ Failed'}", 'info')
        
        if results['gpu_capabilities']['mode'] == 'GPU':
            self.log_message(f"   GPU: {results['gpu_capabilities']['gpu_name']}", 'info')
            self.log_message(f"   GPU Memory: {results['gpu_capabilities']['gpu_memory']:.1f} GB", 'info')
        else:
            self.log_message(f"   CPU Threads: {torch.get_num_threads()}", 'info')


def main():
    """Main function for command line usage"""
    detector = EnvironmentDetector()
    results = detector.detect_all()
    detector.print_summary(results)
    
    # Return appropriate exit code
    if results['mock_data_test']['tensor_test'] and results['mock_data_test']['image_test']:
        sys.exit(0)
    else:
        sys.exit(1)


if __name__ == "__main__":
    main()
