#!/usr/bin/env python3
"""
Camera Utility Functions
Helper functions for camera management and operations
"""

import cv2
import os
import subprocess
import json
import base64
from typing import Dict, List, Optional, Tuple
import logging

logger = logging.getLogger(__name__)

def check_camera_availability() -> Dict:
    """Check if cameras are available on the system"""
    try:
        # Check if v4l2-ctl is available
        result = subprocess.run(['which', 'v4l2-ctl'], capture_output=True, text=True)
        v4l2_available = result.returncode == 0
        
        # Check for video devices
        video_devices = []
        for i in range(10):  # Check first 10 video devices
            device_path = f'/dev/video{i}'
            if os.path.exists(device_path):
                video_devices.append(device_path)
        
        # Test camera access
        working_cameras = []
        for device in video_devices:
            try:
                cap = cv2.VideoCapture(device)
                if cap.isOpened():
                    ret, frame = cap.read()
                    if ret:
                        working_cameras.append(device)
                    cap.release()
            except:
                continue
        
        return {
            'v4l2_available': v4l2_available,
            'video_devices': video_devices,
            'working_cameras': working_cameras,
            'total_cameras': len(working_cameras)
        }
    except Exception as e:
        logger.error(f"Error checking camera availability: {e}")
        return {
            'v4l2_available': False,
            'video_devices': [],
            'working_cameras': [],
            'total_cameras': 0,
            'error': str(e)
        }

def get_camera_capabilities(device_path: str) -> Dict:
    """Get camera capabilities using v4l2-ctl"""
    try:
        capabilities = {}
        
        # Get device info
        result = subprocess.run(['v4l2-ctl', '--device', device_path, '--all'],
                              capture_output=True, text=True, timeout=10)
        
        if result.returncode == 0:
            lines = result.stdout.split('\n')
            for line in lines:
                line = line.strip()
                if 'Width/Height' in line:
                    capabilities['resolution'] = line
                elif 'Pixel Format' in line:
                    capabilities['pixel_format'] = line
                elif 'Frames per second' in line:
                    capabilities['fps'] = line
                elif 'Video input' in line:
                    capabilities['input'] = line
        
        # Get supported formats
        result = subprocess.run(['v4l2-ctl', '--device', device_path, '--list-formats-ext'],
                              capture_output=True, text=True, timeout=10)
        
        if result.returncode == 0:
            capabilities['supported_formats'] = result.stdout.split('\n')
        
        return capabilities
    except Exception as e:
        logger.error(f"Error getting camera capabilities for {device_path}: {e}")
        return {'error': str(e)}

def test_camera_capture(camera_id: int, timeout: int = 5) -> Tuple[bool, str]:
    """Test camera capture functionality"""
    try:
        cap = cv2.VideoCapture(camera_id)
        if not cap.isOpened():
            return False, f"Could not open camera {camera_id}"
        
        # Try to capture a frame
        ret, frame = cap.read()
        cap.release()
        
        if ret and frame is not None:
            return True, f"Camera {camera_id} working correctly"
        else:
            return False, f"Camera {camera_id} failed to capture frame"
            
    except Exception as e:
        return False, f"Error testing camera {camera_id}: {str(e)}"

def capture_to_base64(camera_id: int, quality: int = 90) -> Tuple[bool, str]:
    """Capture image and return as base64 string"""
    try:
        cap = cv2.VideoCapture(camera_id)
        if not cap.isOpened():
            return False, f"Could not open camera {camera_id}"
        
        ret, frame = cap.read()
        cap.release()
        
        if not ret or frame is None:
            return False, "Failed to capture frame"
        
        # Encode image to JPEG
        encode_param = [int(cv2.IMWRITE_JPEG_QUALITY), quality]
        _, buffer = cv2.imencode('.jpg', frame, encode_param)
        
        # Convert to base64
        img_base64 = base64.b64encode(buffer).decode('utf-8')
        return True, img_base64
        
    except Exception as e:
        return False, f"Error capturing image: {str(e)}"

def save_capture_to_file(camera_id: int, file_path: str, quality: int = 90) -> Tuple[bool, str]:
    """Capture image and save to file"""
    try:
        cap = cv2.VideoCapture(camera_id)
        if not cap.isOpened():
            return False, f"Could not open camera {camera_id}"
        
        ret, frame = cap.read()
        cap.release()
        
        if not ret or frame is None:
            return False, "Failed to capture frame"
        
        # Save image
        success = cv2.imwrite(file_path, frame)
        if success:
            return True, f"Image saved to {file_path}"
        else:
            return False, f"Failed to save image to {file_path}"
            
    except Exception as e:
        return False, f"Error saving image: {str(e)}"

def get_camera_resolution(camera_id: int) -> Tuple[bool, Tuple[int, int]]:
    """Get camera resolution"""
    try:
        cap = cv2.VideoCapture(camera_id)
        if not cap.isOpened():
            return False, (0, 0)
        
        width = int(cap.get(cv2.CAP_PROP_FRAME_WIDTH))
        height = int(cap.get(cv2.CAP_PROP_FRAME_HEIGHT))
        cap.release()
        
        return True, (width, height)
    except Exception as e:
        logger.error(f"Error getting camera resolution: {e}")
        return False, (0, 0)

def set_camera_resolution(camera_id: int, width: int, height: int) -> bool:
    """Set camera resolution"""
    try:
        cap = cv2.VideoCapture(camera_id)
        if not cap.isOpened():
            return False
        
        cap.set(cv2.CAP_PROP_FRAME_WIDTH, width)
        cap.set(cv2.CAP_PROP_FRAME_HEIGHT, height)
        cap.release()
        
        return True
    except Exception as e:
        logger.error(f"Error setting camera resolution: {e}")
        return False

def get_camera_fps(camera_id: int) -> float:
    """Get camera FPS"""
    try:
        cap = cv2.VideoCapture(camera_id)
        if not cap.isOpened():
            return 0.0
        
        fps = cap.get(cv2.CAP_PROP_FPS)
        cap.release()
        
        return fps
    except Exception as e:
        logger.error(f"Error getting camera FPS: {e}")
        return 0.0

def list_available_cameras() -> List[Dict]:
    """List all available cameras with their information"""
    cameras = []
    
    try:
        # Check for video devices
        for i in range(10):
            device_path = f'/dev/video{i}'
            if os.path.exists(device_path):
                # Test if camera works
                success, message = test_camera_capture(i)
                if success:
                    # Get camera info
                    resolution_success, resolution = get_camera_resolution(i)
                    fps = get_camera_fps(i)
                    capabilities = get_camera_capabilities(device_path)
                    
                    cameras.append({
                        'id': i,
                        'device_path': device_path,
                        'name': f"Camera {i}",
                        'status': 'available',
                        'resolution': resolution if resolution_success else (0, 0),
                        'fps': fps,
                        'capabilities': capabilities,
                        'test_message': message
                    })
        
        return cameras
    except Exception as e:
        logger.error(f"Error listing cameras: {e}")
        return []

def create_camera_config(camera_id: int, config: Dict) -> bool:
    """Create camera configuration file"""
    try:
        config_dir = '/tmp/camera_configs'
        os.makedirs(config_dir, exist_ok=True)
        
        config_file = os.path.join(config_dir, f'camera_{camera_id}.json')
        
        with open(config_file, 'w') as f:
            json.dump(config, f, indent=2)
        
        return True
    except Exception as e:
        logger.error(f"Error creating camera config: {e}")
        return False

def load_camera_config(camera_id: int) -> Optional[Dict]:
    """Load camera configuration file"""
    try:
        config_file = f'/tmp/camera_configs/camera_{camera_id}.json'
        
        if os.path.exists(config_file):
            with open(config_file, 'r') as f:
                return json.load(f)
        return None
    except Exception as e:
        logger.error(f"Error loading camera config: {e}")
        return None

def cleanup_camera_resources():
    """Cleanup camera resources"""
    try:
        # This would be called when shutting down the service
        # Release any open camera handles
        pass
    except Exception as e:
        logger.error(f"Error during camera cleanup: {e}")

def get_camera_health_status() -> Dict:
    """Get overall camera system health status"""
    try:
        availability = check_camera_availability()
        cameras = list_available_cameras()
        
        return {
            'system_healthy': availability['total_cameras'] > 0,
            'total_cameras': availability['total_cameras'],
            'working_cameras': len(cameras),
            'v4l2_available': availability['v4l2_available'],
            'cameras': cameras,
            'timestamp': __import__('datetime').datetime.now().isoformat()
        }
    except Exception as e:
        logger.error(f"Error getting camera health status: {e}")
        return {
            'system_healthy': False,
            'error': str(e),
            'timestamp': __import__('datetime').datetime.now().isoformat()
        }
