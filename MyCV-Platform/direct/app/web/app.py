#!/usr/bin/env python3
"""
MyCV-Web-Interface
Real-time web interface for camera detection with YOLO + SAM2
Interactive dashboard for monitoring and controlling computer vision processing
"""

import os
import sys
import cv2
import numpy as np
import torch
import base64
import json
from pathlib import Path
from flask import Flask, render_template, Response, jsonify, request
from datetime import datetime
from ultralytics import YOLO, SAM
import threading
import time
from termcolor import colored

# Add parent directory to path
sys.path.append(os.path.dirname(os.path.dirname(os.path.abspath(__file__))))

app = Flask(__name__)

# Global variables
camera = None
is_detecting = False
detection_results = {}
models = {}
device = 'cpu'
camera_available = False
camera_initialized = False

def log_message(message, level='info'):
    """Print colored log message"""
    colors = {
        'info': 'blue',
        'success': 'green',
        'warning': 'yellow',
        'error': 'red'
    }
    color = colors.get(level, 'white')
    print(colored(f"{level.upper()}: {message}", color))

def initialize_models():
    """Initialize YOLO and SAM models"""
    global models, device
    
    log_message("🔧 Initializing models...", 'info')
    
    # Check GPU availability
    if torch.cuda.is_available():
        device = 'cuda'
        log_message(f"🚀 GPU MODE: Using CUDA device - {torch.cuda.get_device_name(0)}", 'success')
    else:
        device = 'cpu'
        log_message("💻 CPU MODE: Using CPU for inference", 'warning')
    
    try:
        # Load YOLO11m
        yolo11m_path = "../../data/models/yolo/active/yolo11m.pt"
        if os.path.exists(yolo11m_path):
            models['yolo11m'] = YOLO(yolo11m_path)
            log_message("✅ YOLO11m loaded successfully", 'success')
        else:
            log_message("❌ YOLO11m model not found", 'error')
            return False
        
        # Load best.pt
        best_pt_path = "../../data/models/trained/best.pt"
        if os.path.exists(best_pt_path):
            models['best_pt'] = YOLO(best_pt_path)
            log_message("✅ best.pt loaded successfully", 'success')
        else:
            log_message("❌ best.pt model not found", 'error')
            return False
        
        # Load SAM2_b
        sam2_path = "../../data/models/sam/active/sam2_b.pt"
        if os.path.exists(sam2_path):
            models['sam2_b'] = SAM(sam2_path)
            log_message("✅ SAM2_b loaded successfully", 'success')
        else:
            log_message("❌ SAM2_b model not found", 'error')
            return False
        
        return True
        
    except Exception as e:
        log_message(f"❌ Failed to load models: {e}", 'error')
        return False

def detect_objects(frame, model_name):
    """Detect objects in frame using specified model"""
    global models
    
    if model_name not in models:
        return []
    
    try:
        results = models[model_name](frame, verbose=False)
        detections = []
        
        for result in results:
            if result.boxes is not None:
                boxes = result.boxes.xyxy.cpu().numpy()
                confidences = result.boxes.conf.cpu().numpy()
                classes = result.boxes.cls.cpu().numpy()
                
                for i, (box, conf, cls) in enumerate(zip(boxes, confidences, classes)):
                    detection = {
                        'bbox': box.tolist(),
                        'confidence': float(conf),
                        'class_id': int(cls),
                        'class_name': models[model_name].names[int(cls)] if hasattr(models[model_name], 'names') else f'class_{int(cls)}'
                    }
                    detections.append(detection)
        
        return detections
        
    except Exception as e:
        log_message(f"❌ Detection failed: {e}", 'error')
        return []

def run_sam_segmentation(frame, detections):
    """Run SAM2 segmentation using detections as prompts"""
    global models
    
    if 'sam2_b' not in models:
        return []
    
    try:
        # Convert frame to RGB
        frame_rgb = cv2.cvtColor(frame, cv2.COLOR_BGR2RGB)
        
        # Convert bounding boxes to SAM format
        boxes = []
        for detection in detections:
            x1, y1, x2, y2 = detection['bbox']
            boxes.append([x1, y1, x2, y2])
        
        if not boxes:
            return []
        
        # Run SAM2 segmentation
        results = models['sam2_b'](frame_rgb, bboxes=boxes, verbose=False)
        
        # Extract masks
        masks = []
        for i, result in enumerate(results):
            if hasattr(result, 'masks') and result.masks is not None:
                mask = result.masks.data.cpu().numpy()
                masks.append({
                    'mask': mask,
                    'bbox': detections[i]['bbox'],
                    'confidence': detections[i]['confidence'],
                    'class_name': detections[i]['class_name']
                })
        
        return masks
        
    except Exception as e:
        log_message(f"❌ SAM2 segmentation failed: {e}", 'error')
        return []

def draw_detections(frame, detections, color=(0, 255, 0)):
    """Draw bounding boxes on frame"""
    result_frame = frame.copy()
    
    for detection in detections:
        x1, y1, x2, y2 = map(int, detection['bbox'])
        confidence = detection['confidence']
        class_name = detection['class_name']
        
        # Draw bounding box
        cv2.rectangle(result_frame, (x1, y1), (x2, y2), color, 2)
        
        # Draw label
        label = f"{class_name}: {confidence:.3f}"
        label_size = cv2.getTextSize(label, cv2.FONT_HERSHEY_SIMPLEX, 0.5, 2)[0]
        cv2.rectangle(result_frame, (x1, y1 - label_size[1] - 10), 
                     (x1 + label_size[0], y1), color, -1)
        cv2.putText(result_frame, label, (x1, y1 - 5), 
                   cv2.FONT_HERSHEY_SIMPLEX, 0.5, (255, 255, 255), 2)
    
    return result_frame

def overlay_masks(frame, masks, alpha=0.5):
    """Overlay segmentation masks on frame"""
    result_frame = frame.copy()
    
    colors = [
        (255, 0, 0),    # Red
        (0, 255, 0),    # Green
        (0, 0, 255),    # Blue
        (255, 255, 0),  # Yellow
        (255, 0, 255),  # Magenta
        (0, 255, 255),  # Cyan
    ]
    
    for i, mask_data in enumerate(masks):
        mask = mask_data['mask']
        color = colors[i % len(colors)]
        
        # Create colored mask
        colored_mask = np.zeros_like(frame)
        colored_mask[mask[0] > 0] = color
        
        # Overlay mask
        result_frame = cv2.addWeighted(result_frame, 1 - alpha, colored_mask, alpha, 0)
    
    return result_frame

def generate_frames():
    """Generate video frames with detection"""
    global camera, is_detecting, detection_results, camera_available, camera_initialized
    
    while True:
        if not camera_available or camera is None:
            # Generate placeholder frame when camera is not available
            frame = np.zeros((480, 640, 3), dtype=np.uint8)
            cv2.putText(frame, "Camera Not Available", (150, 200), 
                       cv2.FONT_HERSHEY_SIMPLEX, 1, (0, 0, 255), 2)
            cv2.putText(frame, "Click 'Initialize Camera' to start", (120, 250), 
                       cv2.FONT_HERSHEY_SIMPLEX, 0.7, (255, 255, 255), 2)
        else:
            success, frame = camera.read()
            if not success:
                # Camera disconnected, reset availability
                camera_available = False
                camera_initialized = False
                frame = np.zeros((480, 640, 3), dtype=np.uint8)
                cv2.putText(frame, "Camera Disconnected", (150, 200), 
                           cv2.FONT_HERSHEY_SIMPLEX, 1, (0, 0, 255), 2)
                cv2.putText(frame, "Click 'Initialize Camera' to reconnect", (100, 250), 
                           cv2.FONT_HERSHEY_SIMPLEX, 0.7, (255, 255, 255), 2)
            elif is_detecting:
                # Run YOLO11m detection
                yolo11m_detections = detect_objects(frame, 'yolo11m')
                
                # Run best.pt detection
                best_pt_detections = detect_objects(frame, 'best_pt')
                
                # Run SAM2 segmentation for YOLO11m
                yolo11m_masks = run_sam_segmentation(frame, yolo11m_detections)
                
                # Run SAM2 segmentation for best.pt
                best_pt_masks = run_sam_segmentation(frame, best_pt_detections)
                
                # Draw detections
                frame = draw_detections(frame, yolo11m_detections, (0, 255, 0))  # Green for YOLO11m
                frame = draw_detections(frame, best_pt_detections, (255, 0, 0))  # Red for best.pt
                
                # Overlay masks
                frame = overlay_masks(frame, yolo11m_masks, 0.3)
                frame = overlay_masks(frame, best_pt_masks, 0.3)
                
                # Update detection results
                detection_results = {
                    'yolo11m': yolo11m_detections,
                    'best_pt': best_pt_detections,
                    'yolo11m_masks': len(yolo11m_masks),
                    'best_pt_masks': len(best_pt_masks)
                }
        
        # Encode frame as JPEG
        ret, buffer = cv2.imencode('.jpg', frame)
        frame_bytes = buffer.tobytes()
        
        yield (b'--frame\r\n'
               b'Content-Type: image/jpeg\r\n\r\n' + frame_bytes + b'\r\n')

@app.route('/')
def index():
    """Main page"""
    return render_template('index.html')

@app.route('/video_feed')
def video_feed():
    """Video streaming route"""
    return Response(generate_frames(),
                   mimetype='multipart/x-mixed-replace; boundary=frame')

@app.route('/start_detection', methods=['POST'])
def start_detection():
    """Start detection"""
    global is_detecting
    is_detecting = True
    return jsonify({'status': 'started'})

@app.route('/stop_detection', methods=['POST'])
def stop_detection():
    """Stop detection"""
    global is_detecting
    is_detecting = False
    return jsonify({'status': 'stopped'})

@app.route('/detection_status')
def detection_status():
    """Get detection status and results"""
    return jsonify({
        'is_detecting': is_detecting,
        'results': detection_results
    })

@app.route('/initialize_camera', methods=['POST'])
def initialize_camera():
    """Initialize camera manually"""
    global camera, camera_available, camera_initialized
    
    try:
        # Try to initialize camera
        camera = cv2.VideoCapture(0)
        
        if camera.isOpened():
            camera_available = True
            camera_initialized = True
            log_message("✅ Camera initialized successfully", 'success')
            return jsonify({
                'status': 'success',
                'message': 'Camera initialized successfully',
                'camera_available': True
            })
        else:
            camera_available = False
            camera_initialized = False
            log_message("❌ Failed to initialize camera", 'error')
            return jsonify({
                'status': 'error',
                'message': 'Failed to initialize camera',
                'camera_available': False
            })
    except Exception as e:
        camera_available = False
        camera_initialized = False
        log_message(f"❌ Camera initialization error: {e}", 'error')
        return jsonify({
            'status': 'error',
            'message': f'Camera initialization error: {str(e)}',
            'camera_available': False
        })

@app.route('/camera_status')
def camera_status():
    """Get camera status"""
    return jsonify({
        'camera_available': camera_available,
        'camera_initialized': camera_initialized,
        'is_detecting': is_detecting
    })

@app.route('/health')
def health():
    """Health check"""
    return jsonify({
        'status': 'healthy',
        'service': 'MyCV-Web-Interface',
        'version': '1.0.0',
        'timestamp': datetime.now().isoformat(),
        'uptime': time.time()
    })


def main():
    """Main function"""
    global camera, camera_available, camera_initialized
    
    log_message("🚀 MyCV-Platform Web Application", 'info')
    log_message("=" * 50, 'info')
    
    # Initialize models
    if not initialize_models():
        log_message("❌ Failed to initialize models", 'error')
        return
    
    # Try to initialize camera (optional)
    log_message("📷 Checking camera availability...", 'info')
    try:
        camera = cv2.VideoCapture(0)
        if camera.isOpened():
            camera_available = True
            camera_initialized = True
            log_message("✅ Camera initialized successfully", 'success')
        else:
            camera_available = False
            camera_initialized = False
            log_message("⚠️ Camera not available - will start without camera", 'warning')
            log_message("💡 Use 'Initialize Camera' button in web interface to connect camera", 'info')
    except Exception as e:
        camera_available = False
        camera_initialized = False
        log_message(f"⚠️ Camera initialization failed: {e}", 'warning')
        log_message("💡 Use 'Initialize Camera' button in web interface to connect camera", 'info')
    
    # Start Flask app
    log_message("🌐 Starting web server...", 'info')
    log_message("🌐 Web application will be available at: http://100.98.142.94:5002", 'success')
    log_message("📱 Open your browser and navigate to the URL above", 'info')
    app.run(host='0.0.0.0', port=5002, debug=False, threaded=True)

if __name__ == '__main__':
    main()
