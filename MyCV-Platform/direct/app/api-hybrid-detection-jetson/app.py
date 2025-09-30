#!/usr/bin/env python3
"""
MyCV-Platform Hybrid Detection API
RESTful API for YOLO + SAM2 object detection and segmentation
"""

import os
import sys
import json
import time
import uuid
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

# Configuration for Jetson
UPLOAD_FOLDER = '../../data-jetson/input/remote'
OUTPUT_FOLDER = '../../data-jetson/output/remote'
ALLOWED_EXTENSIONS = {'png', 'jpg', 'jpeg', 'gif', 'bmp'}
MAX_CONTENT_LENGTH = 16 * 1024 * 1024  # 16MB max file size

app.config['UPLOAD_FOLDER'] = UPLOAD_FOLDER
app.config['MAX_CONTENT_LENGTH'] = MAX_CONTENT_LENGTH

# Global variables for tracking processing
processing_status = {}
processing_results = {}

def allowed_file(filename):
    """Check if file extension is allowed"""
    return '.' in filename and \
           filename.rsplit('.', 1)[1].lower() in ALLOWED_EXTENSIONS

def generate_session_id():
    """Generate unique session ID"""
    return f"session_{uuid.uuid4().hex[:8]}"

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

def create_directory_structure(timestamp, user_id):
    """Create directory structure for processing"""
    input_dir = os.path.join(UPLOAD_FOLDER, timestamp, user_id)
    output_dir = os.path.join(OUTPUT_FOLDER, timestamp, user_id)
    
    # Create directories
    os.makedirs(input_dir, exist_ok=True)
    os.makedirs(output_dir, exist_ok=True)
    os.makedirs(os.path.join(output_dir, 'yolo'), exist_ok=True)
    os.makedirs(os.path.join(output_dir, 'best'), exist_ok=True)
    os.makedirs(os.path.join(output_dir, 'segmentasi'), exist_ok=True)
    os.makedirs(os.path.join(output_dir, 'hybrid'), exist_ok=True)
    
    return input_dir, output_dir

def run_detection_process(timestamp, user_id, session_id):
    """Run the detection process in background"""
    try:
        # Update status
        processing_status[session_id] = {
            'status': 'processing',
            'message': 'Starting detection process...',
            'timestamp': timestamp,
            'user_id': user_id,
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
        'service': 'MyCV-Platform Hybrid Detection API',
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
        'service': 'MyCV-Platform Hybrid Detection API',
        'version': '1.0.0',
        'endpoints': [
            '/api/health',
            '/api/status', 
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

@app.route('/api/upload', methods=['POST'])
def upload_files():
    """Upload images for detection processing"""
    try:
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
        
        # Create directory structure
        input_dir, output_dir = create_directory_structure(timestamp, user_id)
        
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
            args=(timestamp, user_id, session_id)
        )
        thread.daemon = True
        thread.start()
        
        return jsonify({
            'success': True,
            'session_id': session_id,
            'timestamp': timestamp,
            'user_id': user_id,
            'uploaded_files': uploaded_files,
            'message': 'Files uploaded successfully. Processing started.',
            'status_url': f'/api/process/{session_id}',
            'results_url': f'/api/results/{session_id}'
        })
        
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
    """Get all recent detection results with pagination"""
    try:
        # Get pagination parameters from query string
        page = int(request.args.get('page', 1))
        limit = int(request.args.get('limit', 20))
        user_id_filter = request.args.get('user_id', None)
        
        # Validate parameters
        if page < 1:
            page = 1
        if limit < 1 or limit > 100:  # Max 100 items per page
            limit = 20
        
        recent_results = []
        
        # Get all output directories
        if os.path.exists(OUTPUT_FOLDER):
            for timestamp_dir in os.listdir(OUTPUT_FOLDER):
                timestamp_path = os.path.join(OUTPUT_FOLDER, timestamp_dir)
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
                                            
                                            recent_results.append({
                                                'timestamp': timestamp_dir,
                                                'user_id': user_dir,
                                                'image_name': file.replace('-best_pt-detection.json', ''),
                                                'detections': detection_data,
                                                'detection_count': len(detection_data)
                                            })
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
                'user_id': user_id_filter
            },
            'recent_detections': paginated_results
        })
        
    except Exception as e:
        return jsonify({'error': f'Failed to get detections: {str(e)}'}), 500

@app.route('/api/detections/search', methods=['POST'])
def search_detections():
    """Search detections with JSON body parameters"""
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
        
        # Validate parameters
        if page < 1:
            page = 1
        if limit < 1 or limit > 100:  # Max 100 items per page
            limit = 20
        
        recent_results = []
        
        # Get all output directories
        if os.path.exists(OUTPUT_FOLDER):
            for timestamp_dir in os.listdir(OUTPUT_FOLDER):
                # Apply when filter if provided
                if when_filter and when_filter not in timestamp_dir:
                    continue
                    
                timestamp_path = os.path.join(OUTPUT_FOLDER, timestamp_dir)
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
                                            
                                            recent_results.append({
                                                'timestamp': timestamp_dir,
                                                'user_id': user_dir,
                                                'image_name': file.replace('-best_pt-detection.json', ''),
                                                'detections': detection_data,
                                                'detection_count': len(detection_data)
                                            })
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
                'class_name': class_name_filter
            },
            'recent_detections': paginated_results
        })
        
    except Exception as e:
        return jsonify({'error': f'Failed to search detections: {str(e)}'}), 500

if __name__ == '__main__':
    # Ensure directories exist
    os.makedirs(UPLOAD_FOLDER, exist_ok=True)
    os.makedirs(OUTPUT_FOLDER, exist_ok=True)
    
    print("🚀 Starting MyCV-Platform Hybrid Detection API (Jetson)")
    print("📡 API will be available at: http://100.117.234.2:5000")
    print("📋 Available endpoints:")
    print("   GET  /api/health - Health check")
    print("   GET  /api/status - API status")
    print("   POST /api/upload - Upload images for detection")
    print("   GET  /api/process/<session_id> - Get processing status")
    print("   GET  /api/results/<session_id> - Get detection results")
    print("   GET  /api/download/<session_id>/<filename> - Download result files")
    print("   GET  /api/detections - Get all recent detections")
    print("=" * 60)
    
    app.run(host='0.0.0.0', port=5000, debug=False)
