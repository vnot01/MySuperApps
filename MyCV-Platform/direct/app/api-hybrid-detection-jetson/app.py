#!/usr/bin/env python3
"""
MyCV-Edge-API
Edge computing API for YOLO + SAM2 object detection and segmentation
Optimized for NVIDIA Jetson devices with RVM integration and multi-Jetson support
"""

import os
import sys
import json
import time
import uuid
import requests
import hashlib
import hmac
from datetime import datetime
from flask import Flask, request, jsonify, send_file
import tarfile
import tempfile
from flask_cors import CORS
from werkzeug.utils import secure_filename
import subprocess
import threading
from pathlib import Path
import torch

# Add parent directory to path to import detection modules
sys.path.append(os.path.join(os.path.dirname(__file__), '../../'))

app = Flask(__name__)
CORS(app, origins=["*"])

# Configuration for Jetson with RVM support
BASE_DATA_DIR = '../../data-jetson'
UPLOAD_FOLDER = f'{BASE_DATA_DIR}/input'
OUTPUT_FOLDER = f'{BASE_DATA_DIR}/output'
ALLOWED_EXTENSIONS = {'png', 'jpg', 'jpeg', 'gif', 'bmp'}
MAX_CONTENT_LENGTH = 16 * 1024 * 1024  # 16MB max file size

# RVM Platform Integration
RVM_API_BASE_URL = os.getenv('RVM_API_BASE_URL', 'http://localhost:8000/api')
RVM_API_KEY = os.getenv('RVM_API_KEY', '')  # Master API key for RVM platform
RVM_CACHE_TTL = 300  # 5 minutes cache for RVM data

app.config['UPLOAD_FOLDER'] = UPLOAD_FOLDER
app.config['MAX_CONTENT_LENGTH'] = MAX_CONTENT_LENGTH

# Global variables for tracking processing
processing_status = {}
processing_results = {}

# RVM Cache for authentication and data
rvm_cache = {}
rvm_cache_timestamps = {}

def allowed_file(filename):
    """Check if file extension is allowed"""
    return '.' in filename and \
           filename.rsplit('.', 1)[1].lower() in ALLOWED_EXTENSIONS

def generate_session_id():
    """Generate unique session ID"""
    return f"session_{uuid.uuid4().hex[:8]}"

def validate_rvm_api_key(api_key):
    """Validate RVM API key against RVM Platform"""
    if not api_key:
        return None
    
    # Check cache first
    cache_key = f"rvm_auth_{api_key}"
    if cache_key in rvm_cache:
        cache_time = rvm_cache_timestamps.get(cache_key, 0)
        if time.time() - cache_time < RVM_CACHE_TTL:
            return rvm_cache[cache_key]
    
    try:
        # Validate with RVM Platform API
        headers = {
            'Authorization': f'Bearer {RVM_API_KEY}',
            'Content-Type': 'application/json'
        }
        
        response = requests.get(
            f"{RVM_API_BASE_URL}/rvm/validate-api-key",
            headers=headers,
            params={'api_key': api_key},
            timeout=10
        )
        
        if response.status_code == 200:
            rvm_data = response.json()
            # Cache the result
            rvm_cache[cache_key] = rvm_data
            rvm_cache_timestamps[cache_key] = time.time()
            return rvm_data
        else:
            return None
            
    except Exception as e:
        print(f"Error validating RVM API key: {e}")
        return None

def get_rvm_data(rvm_id):
    """Get RVM data from cache or API"""
    cache_key = f"rvm_data_{rvm_id}"
    if cache_key in rvm_cache:
        cache_time = rvm_cache_timestamps.get(cache_key, 0)
        if time.time() - cache_time < RVM_CACHE_TTL:
            return rvm_cache[cache_key]
    
    try:
        headers = {
            'Authorization': f'Bearer {RVM_API_KEY}',
            'Content-Type': 'application/json'
        }
        
        response = requests.get(
            f"{RVM_API_BASE_URL}/rvm/{rvm_id}",
            headers=headers,
            timeout=10
        )
        
        if response.status_code == 200:
            rvm_data = response.json()
            # Cache the result
            rvm_cache[cache_key] = rvm_data
            rvm_cache_timestamps[cache_key] = time.time()
            return rvm_data
        else:
            return None
            
    except Exception as e:
        print(f"Error getting RVM data: {e}")
        return None

def create_rvm_directory_structure(rvm_id, timestamp, user_id):
    """Create directory structure for specific RVM"""
    input_dir = os.path.join(UPLOAD_FOLDER, f"rvm_{rvm_id}", timestamp, user_id)
    output_dir = os.path.join(OUTPUT_FOLDER, f"rvm_{rvm_id}", timestamp, user_id)
    
    # Create directories
    os.makedirs(input_dir, exist_ok=True)
    os.makedirs(output_dir, exist_ok=True)
    os.makedirs(os.path.join(output_dir, 'yolo'), exist_ok=True)
    os.makedirs(os.path.join(output_dir, 'best'), exist_ok=True)
    os.makedirs(os.path.join(output_dir, 'segmentasi'), exist_ok=True)
    os.makedirs(os.path.join(output_dir, 'hybrid'), exist_ok=True)
    
    return input_dir, output_dir

def save_detection_to_rvm_database(rvm_id, session_id, detection_data, image_path=None):
    """Save detection results to RVM Platform database"""
    try:
        headers = {
            'Authorization': f'Bearer {RVM_API_KEY}',
            'Content-Type': 'application/json'
        }
        
        # Prepare detection data for RVM platform
        payload = {
            'rvm_id': rvm_id,
            'session_id': session_id,
            'detection_data': detection_data,
            'image_path': image_path,
            'detected_at': datetime.now().isoformat()
        }
        
        response = requests.post(
            f"{RVM_API_BASE_URL}/detections/store",
            headers=headers,
            json=payload,
            timeout=30
        )
        
        if response.status_code == 201:
            return response.json()
        else:
            print(f"Error saving detection to RVM database: {response.status_code} - {response.text}")
            return None
            
    except Exception as e:
        print(f"Error saving detection to RVM database: {e}")
        return None

def get_gpu_info():
    """Get detailed GPU information"""
    gpus_data = []
    total_memory_all_gpus_gb = 0.0
    
    try:
        # Periksa apakah CUDA tersedia
        if torch.cuda.is_available():
            num_gpus = torch.cuda.device_count()

            # Iterasi untuk setiap GPU yang tersedia
            for i in range(num_gpus):
                device_properties = torch.cuda.get_device_properties(i)
                gpu_name = device_properties.name
                total_memory_bytes = device_properties.total_memory
                total_memory_gb = total_memory_bytes / 1024**3
                
                # Buat dictionary untuk satu GPU
                gpu_info = {
                    "id": i,
                    "name": gpu_name,
                    "memory_gb": round(total_memory_gb, 2)
                }
                gpus_data.append(gpu_info)
                total_memory_all_gpus_gb += total_memory_gb

            return {
                "status": "success",
                "cuda_available": True,
                "cudnn_enabled": torch.backends.cudnn.enabled,
                "pytorch_cuda_version": torch.__version__,
                "available_gpus": num_gpus,
                "gpus": gpus_data,
                "total_memory_all_gpus_gb": round(total_memory_all_gpus_gb, 2)
            }
        else:
            # Kondisi jika CUDA tidak tersedia
            return {
                "status": "error",
                "message": "Tidak ada GPU berkemampuan CUDA yang ditemukan.",
                "cuda_available": False,
                "available_gpus": 0,
                "gpus": [],
                "total_memory_all_gpus_gb": 0.0
            }

    except Exception as e:
        # Tangani error jika terjadi pengecualian
        return {
            "status": "error",
            "message": f"Terjadi kesalahan saat mengambil data GPU: {str(e)}",
            "cuda_available": False,
            "available_gpus": 0,
            "gpus": [],
            "total_memory_all_gpus_gb": 0.0
        }

def create_directory_structure(timestamp, user_id, rvm_id=None):
    """Create directory structure for processing (backward compatibility)"""
    if rvm_id:
        return create_rvm_directory_structure(rvm_id, timestamp, user_id)
    else:
        # Legacy structure for backward compatibility
        input_dir = os.path.join(UPLOAD_FOLDER, 'legacy', timestamp, user_id)
        output_dir = os.path.join(OUTPUT_FOLDER, 'legacy', timestamp, user_id)
        
        # Create directories
        os.makedirs(input_dir, exist_ok=True)
        os.makedirs(output_dir, exist_ok=True)
        os.makedirs(os.path.join(output_dir, 'yolo'), exist_ok=True)
        os.makedirs(os.path.join(output_dir, 'best'), exist_ok=True)
        os.makedirs(os.path.join(output_dir, 'segmentasi'), exist_ok=True)
        os.makedirs(os.path.join(output_dir, 'hybrid'), exist_ok=True)
        
        return input_dir, output_dir

def run_detection_process(timestamp, user_id, session_id, rvm_id=None):
    """Run the detection process in background"""
    try:
        # Update status
        processing_status[session_id] = {
            'status': 'processing',
            'message': 'Starting detection process...',
            'timestamp': timestamp,
            'user_id': user_id,
            'rvm_id': rvm_id,
            'start_time': datetime.now().isoformat()
        }
        
        # Change to direct directory
        direct_dir = os.path.join(os.path.dirname(__file__), '../../')
        
        # Run the detection script with specific session parameters for Jetson
        cmd = [
            'python3', 'run_api_hybrid_detection-jetson.py', 
            '--timestamp', timestamp,
            '--user_id', user_id,
            '--session_id', session_id
        ]
        
        result = subprocess.run(
            cmd,
            cwd=direct_dir,
            capture_output=True,
            text=True,
            timeout=900  # 15 minutes timeout to handle many images
        )
        
        if result.returncode == 0:
            # Process completed successfully
            processing_status[session_id]['status'] = 'completed'
            processing_status[session_id]['message'] = 'Detection completed successfully'
            processing_status[session_id]['end_time'] = datetime.now().isoformat()
            
            # Results will be read from summary.json when requested
            processing_results[session_id] = {'status': 'completed'}
            
        else:
            # Process failed
            processing_status[session_id]['status'] = 'failed'
            processing_status[session_id]['message'] = f'Detection failed: {result.stderr}'
            processing_status[session_id]['end_time'] = datetime.now().isoformat()
            
    except subprocess.TimeoutExpired:
        processing_status[session_id]['status'] = 'failed'
        processing_status[session_id]['message'] = 'Detection process timed out'
        processing_status[session_id]['end_time'] = datetime.now().isoformat()
        
    except Exception as e:
        processing_status[session_id]['status'] = 'failed'
        processing_status[session_id]['message'] = f'Detection process error: {str(e)}'
        processing_status[session_id]['end_time'] = datetime.now().isoformat()

def collect_detection_results(output_dir):
    """Collect detection results from output directory"""
    results = {
        'images_processed': [],
        'total_files': 0,
        'detection_summary': {}
    }
    
    try:
        # Find all JSON files (detection results)
        json_files = []
        for root, dirs, files in os.walk(output_dir):
            for file in files:
                if file.endswith('.json'):
                    json_files.append(os.path.join(root, file))
        
        results['total_files'] = len(json_files)
        
        # Process each JSON file
        for json_file in json_files:
            try:
                with open(json_file, 'r') as f:
                    detection_data = json.load(f)
                
                # Extract image name from file path
                image_name = os.path.basename(json_file).replace('-best_pt-detection.json', '')
                
                image_result = {
                    'image_name': image_name,
                    'detections': detection_data,
                    'detection_count': len(detection_data),
                    'json_file': json_file.replace(output_dir, ''),
                    'visualizations': []
                }
                
                # Find related visualization files
                base_name = image_name
                vis_files = [
                    f"{base_name}-best_pt-compare.png",
                    f"{base_name}-best_pt-best.png",
                    f"{base_name}-best_pt-segmentation.png",
                    f"{base_name}-best_pt-hybrid.png"
                ]
                
                for vis_file in vis_files:
                    vis_path = os.path.join(output_dir, vis_file)
                    if os.path.exists(vis_path):
                        image_result['visualizations'].append({
                            'type': vis_file.split('-')[-1].replace('.png', ''),
                            'file': vis_file,
                            'path': vis_path.replace(output_dir, '')
                        })
                
                results['images_processed'].append(image_result)
                
                # Update detection summary
                for detection in detection_data:
                    class_name = detection.get('class_name', 'unknown')
                    if class_name not in results['detection_summary']:
                        results['detection_summary'][class_name] = 0
                    results['detection_summary'][class_name] += 1
                    
            except Exception as e:
                print(f"Error processing {json_file}: {e}")
                continue
                
    except Exception as e:
        print(f"Error collecting results: {e}")
    
    return results

@app.route('/api/health', methods=['GET'])
def health_check():
    """Health check endpoint"""
    return jsonify({
        'status': 'healthy',
        'service': 'MyCV-Edge-API',
        'version': '1.0.0',
        'timestamp': datetime.now().isoformat(),
        'uptime': time.time()
    })

@app.route('/api/status', methods=['GET'])
def api_status():
    """API status endpoint with GPU information"""
    # Get GPU information
    gpu_info = get_gpu_info()
    
    # Get detection history for total sessions
    total_sessions = 0
    try:
        if os.path.exists(OUTPUT_FOLDER):
            for timestamp_dir in os.listdir(OUTPUT_FOLDER):
                timestamp_path = os.path.join(OUTPUT_FOLDER, timestamp_dir)
                if os.path.isdir(timestamp_path):
                    for user_dir in os.listdir(timestamp_path):
                        user_path = os.path.join(timestamp_path, user_dir)
                        if os.path.isdir(user_path):
                            # Count JSON files (detection results)
                            json_count = 0
                            for root, dirs, files in os.walk(user_path):
                                for file in files:
                                    if file.endswith('.json'):
                                        json_count += 1
                            total_sessions += json_count
    except Exception:
        total_sessions = 0
    
    return jsonify({
        'api_status': 'online',
        'service': 'MyCV-Edge-API',
        'version': '1.0.0',
        'endpoints': [
            '/api/health',
            '/api/status', 
            '/api/hardware',
            '/api/upload',
            '/api/process/<session_id>',
            '/api/results/<session_id>',
            '/api/download/<session_id>/<filename>',
            '/api/detections'
        ],
        'timestamp': datetime.now().isoformat(),
        'gpu_info': gpu_info,
        'total_sessions_processed': total_sessions
    })

@app.route('/api/hardware', methods=['GET'])
def hardware_info():
    """Get comprehensive Jetson hardware information"""
    try:
        hardware_data = {
            'jetson_info': get_jetson_info(),
            'cuda_info': get_cuda_info(),
            'memory_info': get_memory_info(),
            'disk_info': get_disk_info(),
            'camera_info': get_camera_info(),
            'network_info': get_network_info(),
            'updated_at': datetime.now().isoformat()
        }
        
        return jsonify({
            'status': 'success',
            'service': 'MyCV-Edge-API',
            'hardware_info': hardware_data
        })
    except Exception as e:
        return jsonify({
            'status': 'error',
            'message': f'Failed to get hardware info: {str(e)}',
            'updated_at': datetime.now().isoformat()
        }), 500

def get_jetson_info():
    """Get Jetson device information"""
    jetson_info = {
        'architecture': 'Unknown',
        'kernel_version': 'Unknown',
        'model': 'Unknown'
    }
    
    try:
        # Get kernel version
        result = subprocess.run(['uname', '-r'], 
                              capture_output=True, text=True, timeout=5)
        if result.returncode == 0:
            jetson_info['kernel_version'] = result.stdout.strip()
        
        # Get architecture
        result = subprocess.run(['uname', '-m'], 
                              capture_output=True, text=True, timeout=5)
        if result.returncode == 0:
            jetson_info['architecture'] = result.stdout.strip()
        
        # Try to detect Jetson model
        try:
            with open('/proc/device-tree/model', 'r') as f:
                jetson_info['model'] = f.read().strip('\x00')
        except:
            pass
        
        # Get Jetpack and L4T versions using the utility script
        try:
            utils_path = os.path.join(os.path.dirname(__file__), 'utils/python/get_jetpack_versions.py')
            result = subprocess.run(['python3', utils_path], 
                                  capture_output=True, text=True, timeout=10)
            if result.returncode == 0:
                # Parse the output to get JetPack and L4T versions
                lines = result.stdout.split('\n')
                for line in lines:
                    if 'Your L4T version' in line:
                        l4t_version = line.split(':')[1].strip()
                        jetson_info['l4t_version'] = l4t_version
                    elif 'JetPack SDK' in line:
                        jetpack_version = line.split('JetPack SDK')[1].strip().rstrip('.')
                        jetson_info['jetpack_version'] = jetpack_version
        except:
            jetson_info['l4t_version'] = 'Unknown'
            jetson_info['jetpack_version'] = 'Unknown'
            
    except Exception as e:
        jetson_info['error'] = str(e)
    
    return jetson_info

def get_cuda_info():
    """Get CUDA information similar to get_gpu_info()"""
    cuda_info = {
        'status': 'error',
        'cuda_available': False,
        'cudnn_enabled': False,
        'pytorch_cuda_version': 'Unknown',
        'available_gpus': 0,
        'gpus': [],
        'total_memory_all_gpus_gb': 0.0
    }
    
    try:
        if torch.cuda.is_available():
            cuda_info['status'] = 'success'
            cuda_info['cuda_available'] = True
            cuda_info['cudnn_enabled'] = torch.backends.cudnn.enabled
            cuda_info['pytorch_cuda_version'] = torch.__version__
            cuda_info['available_gpus'] = torch.cuda.device_count()
            
            total_memory_all_gpus_gb = 0.0
            
            for i in range(torch.cuda.device_count()):
                device_properties = torch.cuda.get_device_properties(i)
                gpu_name = device_properties.name
                total_memory_bytes = device_properties.total_memory
                total_memory_gb = total_memory_bytes / 1024**3
                
                gpu_info = {
                    "id": i,
                    "name": gpu_name,
                    "memory_gb": round(total_memory_gb, 2)
                }
                cuda_info['gpus'].append(gpu_info)
                total_memory_all_gpus_gb += total_memory_gb
            
            cuda_info['total_memory_all_gpus_gb'] = round(total_memory_all_gpus_gb, 2)
        else:
            cuda_info['status'] = 'error'
            cuda_info['message'] = 'Tidak ada GPU berkemampuan CUDA yang ditemukan.'
            
    except Exception as e:
        cuda_info['status'] = 'error'
        cuda_info['message'] = f'Terjadi kesalahan saat mengambil data GPU: {str(e)}'
    
    return cuda_info

def get_memory_info():
    """Get system memory information - simplified version"""
    memory_info = {
        'total_gb': 0.0
    }
    
    try:
        total_memory_gb = 0.0
        swap_total_gb = 0.0
        
        with open('/proc/meminfo', 'r') as f:
            for line in f:
                if line.startswith('MemTotal:'):
                    total_memory_gb = int(line.split()[1]) / (1024**2)  # Convert KB to GB
                elif line.startswith('SwapTotal:'):
                    swap_total_gb = int(line.split()[1]) / (1024**2)  # Convert KB to GB
        
        # Total memory = RAM + Swap
        memory_info['total_gb'] = round(total_memory_gb + swap_total_gb, 2)
        
    except Exception as e:
        memory_info['error'] = str(e)
    
    return memory_info

def get_disk_info():
    """Get disk storage information - simplified version"""
    disk_info = {
        'available': 'Unknown',
        'size': 'Unknown',
        'used': 'Unknown',
        'use_percent': 'Unknown'
    }
    
    try:
        result = subprocess.run(['df', '-h'], capture_output=True, text=True, timeout=10)
        if result.returncode == 0:
            lines = result.stdout.strip().split('\n')[1:]  # Skip header
            for line in lines:
                parts = line.split()
                if len(parts) >= 6 and parts[5] == '/':  # Root filesystem
                    disk_info['available'] = parts[3]
                    disk_info['size'] = parts[1]
                    disk_info['used'] = parts[2]
                    disk_info['use_percent'] = parts[4]
                    break
            
    except Exception as e:
        disk_info['error'] = str(e)
    
    return disk_info

def get_camera_info():
    """Get camera information using v4l2-ctl"""
    camera_info = {
        'usb_cameras': []
    }
    
    try:
        # Use v4l2-ctl to list devices
        result = subprocess.run(['v4l2-ctl', '--list-devices'], capture_output=True, text=True, timeout=10)
        if result.returncode == 0:
            lines = result.stdout.strip().split('\n')
            current_device = None
            
            for line in lines:
                line = line.strip()
                if not line:
                    continue
                    
                # Check if this is a device path
                if line.startswith('/dev/video'):
                    current_device = line
                elif current_device and ':' in line:
                    # This is a device name/description
                    device_name = line.rstrip(':')
                    camera_info['usb_cameras'].append({
                        'device': current_device,
                        'name': device_name
                    })
                    current_device = None
        
        # If v4l2-ctl fails, fallback to ls /dev/video*
        if not camera_info['usb_cameras']:
            result = subprocess.run(['ls', '/dev/video*'], capture_output=True, text=True, timeout=5)
            if result.returncode == 0:
                video_devices = result.stdout.strip().split('\n')
                for device in video_devices:
                    if device.strip():
                        camera_info['usb_cameras'].append({
                            'device': device.strip(),
                            'name': 'Unknown Device'
                        })
        
    except Exception as e:
        camera_info['error'] = str(e)
    
    return camera_info

def get_network_info():
    """Get network interface information - simplified version"""
    network_info = {
        'wlP1p1s0_ip': None,
        'tailscale0_ip': None,
        'public_ip': None
    }
    
    try:
        # Get wlP1p1s0 IP (WiFi interface)
        result = subprocess.run(['ip', 'addr', 'show', 'wlP1p1s0'], capture_output=True, text=True, timeout=5)
        if result.returncode == 0:
            for line in result.stdout.split('\n'):
                if 'inet ' in line:
                    ip = line.split()[1].split('/')[0]
                    network_info['wlP1p1s0_ip'] = ip
                    break
        
        # Get tailscale0 IP
        result = subprocess.run(['ip', 'addr', 'show', 'tailscale0'], capture_output=True, text=True, timeout=5)
        if result.returncode == 0:
            for line in result.stdout.split('\n'):
                if 'inet ' in line:
                    ip = line.split()[1].split('/')[0]
                    network_info['tailscale0_ip'] = ip
                    break
        
        # Validate Tailscale connection using tailscale status
        try:
            result = subprocess.run(['tailscale', 'status', '--json'], 
                                  capture_output=True, text=True, timeout=5)
            if result.returncode == 0:
                import json
                tailscale_data = json.loads(result.stdout)
                tailscale_self = tailscale_data.get('Self', {})
                
                # Validate using AllowedIPs and tailscale_status
                if 'AllowedIPs' in tailscale_self and tailscale_self.get('Online'):
                    network_info['tailscale_connected'] = True
                    network_info['tailscale_allowed_ips'] = tailscale_self.get('AllowedIPs', [])
                else:
                    network_info['tailscale_connected'] = False
            else:
                network_info['tailscale_connected'] = False
        except:
            network_info['tailscale_connected'] = False
        
        # Try to get public IP
        try:
            result = subprocess.run(['curl', '-s', '--max-time', '5', 'ifconfig.me'], 
                                  capture_output=True, text=True, timeout=10)
            if result.returncode == 0 and result.stdout.strip():
                network_info['public_ip'] = result.stdout.strip()
        except:
            pass
            
    except Exception as e:
        network_info['error'] = str(e)
    
    return network_info

@app.route('/api/upload', methods=['POST'])
def upload_files():
    """Upload images for detection processing with RVM authentication"""
    try:
        # Get RVM authentication parameters
        api_key = request.headers.get('X-RVM-API-Key') or request.form.get('api_key')
        rvm_id = request.form.get('rvm_id')
        
        # Validate RVM authentication if provided
        rvm_data = None
        if api_key and rvm_id:
            rvm_data = validate_rvm_api_key(api_key)
            if not rvm_data or rvm_data.get('id') != int(rvm_id):
                return jsonify({'error': 'Invalid RVM API key or RVM ID mismatch'}), 401
        
        # Check if files are present
        if 'files' not in request.files:
            return jsonify({'error': 'No files provided'}), 400
        
        files = request.files.getlist('files')
        
        if not files or files[0].filename == '':
            return jsonify({'error': 'No files selected'}), 400
        
        # Generate session info
        timestamp = datetime.now().strftime("%Y%m%d_%H%M%S")
        user_id = request.form.get('user_id', 'public_user')
        session_id = generate_session_id()
        
        # Create directory structure (with RVM support)
        input_dir, output_dir = create_directory_structure(timestamp, user_id, rvm_id)
        
        # Save uploaded files
        uploaded_files = []
        for file in files:
            if file and allowed_file(file.filename):
                filename = secure_filename(file.filename)
                file_path = os.path.join(input_dir, filename)
                file.save(file_path)
                uploaded_files.append({
                    'original_name': file.filename,
                    'saved_name': filename,
                    'path': file_path
                })
        
        if not uploaded_files:
            return jsonify({'error': 'No valid files uploaded'}), 400
        
        # Start detection process in background
        thread = threading.Thread(
            target=run_detection_process,
            args=(timestamp, user_id, session_id, rvm_id)
        )
        thread.daemon = True
        thread.start()
        
        response_data = {
            'success': True,
            'session_id': session_id,
            'timestamp': timestamp,
            'user_id': user_id,
            'uploaded_files': uploaded_files,
            'message': 'Files uploaded successfully. Processing started.',
            'status_url': f'/api/process/{session_id}',
            'results_url': f'/api/results/{session_id}'
        }
        
        # Add RVM data if authenticated
        if rvm_data:
            response_data['rvm'] = {
                'id': rvm_data.get('id'),
                'name': rvm_data.get('name'),
                'location': rvm_data.get('location_description')
            }
        
        return jsonify(response_data)
        
    except Exception as e:
        return jsonify({'error': f'Upload failed: {str(e)}'}), 500

@app.route('/api/process/<session_id>', methods=['GET'])
def get_process_status(session_id):
    """Get processing status for a session"""
    if session_id not in processing_status:
        return jsonify({'error': 'Session not found'}), 404
    
    return jsonify(processing_status[session_id])

@app.route('/api/results/<session_id>', methods=['GET'])
def get_results(session_id):
    """Get detection results for a session from summary.json"""
    if session_id not in processing_status:
        return jsonify({'error': 'Session not found'}), 404
    
    if processing_status[session_id]['status'] != 'completed':
        return jsonify({
            'error': 'Processing not completed yet',
            'status': processing_status[session_id]['status']
        }), 202
    
    try:
        # Get session info
        timestamp = processing_status[session_id]['timestamp']
        user_id = processing_status[session_id]['user_id']
        
        # Look for summary.json in the output directory
        output_dir = os.path.join(OUTPUT_FOLDER, timestamp, user_id)
        summary_file = os.path.join(output_dir, 'summary.json')
        
        if not os.path.exists(summary_file):
            return jsonify({'error': 'Summary file not found'}), 404
        
        # Read summary.json
        with open(summary_file, 'r') as f:
            summary_data = json.load(f)
        
        # Return the data in the requested format
        return jsonify({
            'results': summary_data,
            'session_id': session_id,
            'status': 'completed',
            'timestamp': timestamp,
            'user_id': user_id
        })
        
    except Exception as e:
        return jsonify({'error': f'Failed to read results: {str(e)}'}), 500

@app.route('/api/download/<session_id>/<filename>', methods=['GET'])
def download_file(session_id, filename):
    """Download result files"""
    if session_id not in processing_status:
        return jsonify({'error': 'Session not found'}), 404
    
    if processing_status[session_id]['status'] != 'completed':
        return jsonify({'error': 'Processing not completed yet'}), 202
    
    # Construct file path
    timestamp = processing_status[session_id]['timestamp']
    user_id = processing_status[session_id]['user_id']
    output_dir = os.path.join(OUTPUT_FOLDER, timestamp, user_id)
    file_path = os.path.join(output_dir, filename)
    
    if not os.path.exists(file_path):
        return jsonify({'error': 'File not found'}), 404
    
    return send_file(file_path, as_attachment=True)

@app.route('/api/backup/<session_id>', methods=['GET'])
def backup_session(session_id):
    """Create and download TAR.GZ backup for a session output directory."""
    if session_id not in processing_status:
        return jsonify({'error': 'Session not found'}), 404
    if processing_status[session_id]['status'] != 'completed':
        return jsonify({'error': 'Processing not completed yet'}), 202

    timestamp = processing_status[session_id]['timestamp']
    user_id = processing_status[session_id]['user_id']
    output_dir = os.path.join(OUTPUT_FOLDER, timestamp, user_id)
    if not os.path.exists(output_dir):
        return jsonify({'error': 'Output directory not found'}), 404

    backup_name = f"session_backup_{timestamp}_{user_id}.tar.gz"
    final_path = os.path.join(output_dir, backup_name)

    try:
        # Create tar.gz in a temp file then move to session dir to avoid read-while-writing issues
        with tempfile.NamedTemporaryFile(delete=False, suffix='.tar.gz') as tmpf:
            tmp_path = tmpf.name
        with tarfile.open(tmp_path, 'w:gz') as tar:
            tar.add(output_dir, arcname='.', filter=lambda x: None if x.name.endswith('.tar.gz') else x)
        # Move over existing
        if os.path.exists(final_path):
            os.remove(final_path)
        os.replace(tmp_path, final_path)
    except Exception as e:
        try:
            if os.path.exists(tmp_path):
                os.remove(tmp_path)
        except Exception:
            pass
        return jsonify({'error': f'Failed to create backup: {str(e)}'}), 500

    return send_file(final_path, as_attachment=True)

@app.route('/api/detections', methods=['GET'])
def get_all_detections():
    """Get all recent detection results with pagination and RVM filtering"""
    try:
        # Get pagination parameters from query string
        page = int(request.args.get('page', 1))
        limit = int(request.args.get('limit', 20))
        user_id_filter = request.args.get('user_id', None)
        rvm_id_filter = request.args.get('rvm_id', None)
        
        # Validate RVM access if rvm_id is provided
        if rvm_id_filter:
            api_key = request.headers.get('X-RVM-API-Key')
            if not api_key:
                return jsonify({'error': 'RVM API key required for RVM-specific queries'}), 401
            
            rvm_data = validate_rvm_api_key(api_key)
            if not rvm_data or rvm_data.get('id') != int(rvm_id_filter):
                return jsonify({'error': 'Invalid RVM API key or access denied'}), 403
        
        # Validate parameters
        if page < 1:
            page = 1
        if limit < 1 or limit > 100:  # Max 100 items per page
            limit = 20
        
        recent_results = []
        
        # Get all output directories
        if os.path.exists(OUTPUT_FOLDER):
            for rvm_dir in os.listdir(OUTPUT_FOLDER):
                rvm_path = os.path.join(OUTPUT_FOLDER, rvm_dir)
                if os.path.isdir(rvm_path):
                    # Extract RVM ID from directory name (rvm_123)
                    if rvm_dir.startswith('rvm_'):
                        current_rvm_id = rvm_dir.replace('rvm_', '')
                        
                        # Apply RVM filter if provided
                        if rvm_id_filter and current_rvm_id != str(rvm_id_filter):
                            continue
                    else:
                        # Legacy directory structure
                        if rvm_id_filter:
                            continue
                        current_rvm_id = None
                    
                    for timestamp_dir in os.listdir(rvm_path):
                        timestamp_path = os.path.join(rvm_path, timestamp_dir)
                        if os.path.isdir(timestamp_path):
                            for user_dir in os.listdir(timestamp_path):
                                # Apply user_id filter if provided
                                if user_id_filter and user_dir != user_id_filter:
                                    continue
                                    
                                user_path = os.path.join(timestamp_path, user_dir)
                                if os.path.isdir(user_path):
                                    # Find JSON files
                                    for root, dirs, files in os.walk(user_path):
                                        for file in files:
                                            if file.endswith('.json'):
                                                json_path = os.path.join(root, file)
                                                try:
                                                    with open(json_path, 'r') as f:
                                                        detection_data = json.load(f)
                                                    
                                                    result_item = {
                                                        'timestamp': timestamp_dir,
                                                        'user_id': user_dir,
                                                        'image_name': file.replace('-best_pt-detection.json', ''),
                                                        'detections': detection_data,
                                                        'detection_count': len(detection_data)
                                                    }
                                                    
                                                    # Add RVM ID if available
                                                    if current_rvm_id:
                                                        result_item['rvm_id'] = int(current_rvm_id)
                                                    
                                                    recent_results.append(result_item)
                                                except:
                                                    continue
        
        # Sort by timestamp (newest first)
        recent_results.sort(key=lambda x: x['timestamp'], reverse=True)
        
        # Calculate pagination
        total_items = len(recent_results)
        total_pages = (total_items + limit - 1) // limit  # Ceiling division
        start_index = (page - 1) * limit
        end_index = start_index + limit
        
        # Get paginated results
        paginated_results = recent_results[start_index:end_index]
        
        return jsonify({
            'pagination': {
                'current_page': page,
                'total_pages': total_pages,
                'total_items': total_items,
                'items_per_page': limit,
                'has_next': page < total_pages,
                'has_prev': page > 1,
                'next_page': page + 1 if page < total_pages else None,
                'prev_page': page - 1 if page > 1 else None
            },
            'filters': {
                'user_id': user_id_filter,
                'rvm_id': rvm_id_filter
            },
            'recent_detections': paginated_results
        })
        
    except Exception as e:
        return jsonify({'error': f'Failed to get detections: {str(e)}'}), 500

@app.route('/api/detections/search', methods=['POST'])
def search_detections():
    """Search detections with JSON body parameters and RVM support"""
    try:
        # Get parameters from JSON body
        data = request.get_json()
        
        if not data:
            return jsonify({'error': 'JSON body required'}), 400
        
        page = int(data.get('page', 1))
        limit = int(data.get('limit', 20))
        user_id_filter = data.get('user_id', None)
        when_filter = data.get('when', None)
        class_name_filter = data.get('class_name', None)
        rvm_id_filter = data.get('rvm_id', None)
        
        # Validate RVM access if rvm_id is provided
        if rvm_id_filter:
            api_key = request.headers.get('X-RVM-API-Key')
            if not api_key:
                return jsonify({'error': 'RVM API key required for RVM-specific queries'}), 401
            
            rvm_data = validate_rvm_api_key(api_key)
            if not rvm_data or rvm_data.get('id') != int(rvm_id_filter):
                return jsonify({'error': 'Invalid RVM API key or access denied'}), 403
        
        # Validate parameters
        if page < 1:
            page = 1
        if limit < 1 or limit > 100:  # Max 100 items per page
            limit = 20
        
        recent_results = []
        
        # Get all output directories
        if os.path.exists(OUTPUT_FOLDER):
            for rvm_dir in os.listdir(OUTPUT_FOLDER):
                rvm_path = os.path.join(OUTPUT_FOLDER, rvm_dir)
                if os.path.isdir(rvm_path):
                    # Extract RVM ID from directory name (rvm_123)
                    if rvm_dir.startswith('rvm_'):
                        current_rvm_id = rvm_dir.replace('rvm_', '')
                        
                        # Apply RVM filter if provided
                        if rvm_id_filter and current_rvm_id != str(rvm_id_filter):
                            continue
                    else:
                        # Legacy directory structure
                        if rvm_id_filter:
                            continue
                        current_rvm_id = None
                    
                    for timestamp_dir in os.listdir(rvm_path):
                        # Apply when filter if provided
                        if when_filter and when_filter not in timestamp_dir:
                            continue
                            
                        timestamp_path = os.path.join(rvm_path, timestamp_dir)
                        if os.path.isdir(timestamp_path):
                            for user_dir in os.listdir(timestamp_path):
                                # Apply user_id filter if provided
                                if user_id_filter and user_dir != user_id_filter:
                                    continue
                                    
                                user_path = os.path.join(timestamp_path, user_dir)
                                if os.path.isdir(user_path):
                                    # Find JSON files
                                    for root, dirs, files in os.walk(user_path):
                                        for file in files:
                                            if file.endswith('.json'):
                                                json_path = os.path.join(root, file)
                                                try:
                                                    with open(json_path, 'r') as f:
                                                        detection_data = json.load(f)
                                                    
                                                    # Apply class_name filter if provided
                                                    if class_name_filter:
                                                        filtered_detections = [
                                                            det for det in detection_data 
                                                            if det.get('class_name', '').lower() == class_name_filter.lower()
                                                        ]
                                                        if not filtered_detections:
                                                            continue
                                                        detection_data = filtered_detections
                                                    
                                                    result_item = {
                                                        'timestamp': timestamp_dir,
                                                        'user_id': user_dir,
                                                        'image_name': file.replace('-best_pt-detection.json', ''),
                                                        'detections': detection_data,
                                                        'detection_count': len(detection_data)
                                                    }
                                                    
                                                    # Add RVM ID if available
                                                    if current_rvm_id:
                                                        result_item['rvm_id'] = int(current_rvm_id)
                                                    
                                                    recent_results.append(result_item)
                                                except:
                                                    continue
        
        # Sort by timestamp (newest first)
        recent_results.sort(key=lambda x: x['timestamp'], reverse=True)
        
        # Calculate pagination
        total_items = len(recent_results)
        total_pages = (total_items + limit - 1) // limit  # Ceiling division
        start_index = (page - 1) * limit
        end_index = start_index + limit
        
        # Get paginated results
        paginated_results = recent_results[start_index:end_index]
        
        return jsonify({
            'pagination': {
                'current_page': page,
                'total_pages': total_pages,
                'total_items': total_items,
                'items_per_page': limit,
                'has_next': page < total_pages,
                'has_prev': page > 1,
                'next_page': page + 1 if page < total_pages else None,
                'prev_page': page - 1 if page > 1 else None
            },
            'filters': {
                'user_id': user_id_filter,
                'when': when_filter,
                'class_name': class_name_filter,
                'rvm_id': rvm_id_filter
            },
            'recent_detections': paginated_results
        })
        
    except Exception as e:
        return jsonify({'error': f'Failed to search detections: {str(e)}'}), 500

@app.route('/api/rvm/validate', methods=['POST'])
def validate_rvm():
    """Validate RVM API key and return RVM information"""
    try:
        data = request.get_json()
        if not data or 'api_key' not in data:
            return jsonify({'error': 'API key required'}), 400
        
        api_key = data['api_key']
        rvm_data = validate_rvm_api_key(api_key)
        
        if rvm_data:
            return jsonify({
                'valid': True,
                'rvm': {
                    'id': rvm_data.get('id'),
                    'name': rvm_data.get('name'),
                    'location': rvm_data.get('location_description'),
                    'status': rvm_data.get('status')
                }
            })
        else:
            return jsonify({'valid': False, 'error': 'Invalid API key'}), 401
            
    except Exception as e:
        return jsonify({'error': f'Validation failed: {str(e)}'}), 500

@app.route('/api/rvm/<int:rvm_id>/stats', methods=['GET'])
def get_rvm_stats(rvm_id):
    """Get statistics for a specific RVM"""
    try:
        # Validate RVM access
        api_key = request.headers.get('X-RVM-API-Key')
        if not api_key:
            return jsonify({'error': 'RVM API key required'}), 401
        
        rvm_data = validate_rvm_api_key(api_key)
        if not rvm_data or rvm_data.get('id') != rvm_id:
            return jsonify({'error': 'Invalid RVM API key or access denied'}), 403
        
        # Get RVM-specific statistics
        rvm_output_dir = os.path.join(OUTPUT_FOLDER, f'rvm_{rvm_id}')
        
        stats = {
            'rvm_id': rvm_id,
            'total_sessions': 0,
            'total_detections': 0,
            'recent_activity': []
        }
        
        if os.path.exists(rvm_output_dir):
            # Count sessions and detections
            for timestamp_dir in os.listdir(rvm_output_dir):
                timestamp_path = os.path.join(rvm_output_dir, timestamp_dir)
                if os.path.isdir(timestamp_path):
                    for user_dir in os.listdir(timestamp_path):
                        user_path = os.path.join(timestamp_path, user_dir)
                        if os.path.isdir(user_path):
                            stats['total_sessions'] += 1
                            
                            # Count JSON files (detections)
                            for root, dirs, files in os.walk(user_path):
                                for file in files:
                                    if file.endswith('.json'):
                                        stats['total_detections'] += 1
        
        return jsonify(stats)
        
    except Exception as e:
        return jsonify({'error': f'Failed to get RVM stats: {str(e)}'}), 500

if __name__ == '__main__':
    # Ensure directories exist
    os.makedirs(UPLOAD_FOLDER, exist_ok=True)
    os.makedirs(OUTPUT_FOLDER, exist_ok=True)
    
    print("🚀 Starting MyCV-Platform Hybrid Detection API (Jetson)")
    print("📡 API will be available at: http://100.117.234.2:5000")
    print("📋 Available endpoints:")
    print("   GET  /api/health - Health check")
    print("   GET  /api/status - API status")
    print("   POST /api/upload - Upload images for detection (with RVM support)")
    print("   GET  /api/process/<session_id> - Get processing status")
    print("   GET  /api/results/<session_id> - Get detection results")
    print("   GET  /api/download/<session_id>/<filename> - Download result files")
    print("   GET  /api/detections - Get all recent detections (with RVM filtering)")
    print("   POST /api/detections/search - Search detections (with RVM filtering)")
    print("   POST /api/rvm/validate - Validate RVM API key")
    print("   GET  /api/rvm/<rvm_id>/stats - Get RVM statistics")
    print("=" * 60)
    print("🔐 RVM Integration:")
    print("   - Use X-RVM-API-Key header for authentication")
    print("   - Include rvm_id parameter for RVM-specific operations")
    print("   - Data stored in rvm_{id}/ structure")
    print("=" * 60)
    
    app.run(host='0.0.0.0', port=5000, debug=False)
