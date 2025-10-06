#!/usr/bin/env python3
"""
Camera Service untuk Jetson Orin
Mengelola akses camera USB dan streaming untuk RVM
"""

import cv2
import threading
import time
import json
import os
import subprocess
from datetime import datetime
from typing import Dict, List, Optional, Tuple
import logging

# Setup logging
logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)

class CameraService:
    def __init__(self):
        self.cameras = {}
        self.streaming_cameras = {}
        self.capture_lock = threading.Lock()
        self.streaming_lock = threading.Lock()
        self.is_initialized = False
        
    def initialize(self) -> bool:
        """Initialize camera service"""
        try:
            # Detect available cameras
            self.detect_cameras()
            self.is_initialized = True
            logger.info("Camera service initialized successfully")
            return True
        except Exception as e:
            logger.error(f"Failed to initialize camera service: {e}")
            return False
    
    def detect_cameras(self) -> List[Dict]:
        """Detect available USB cameras with proper filtering"""
        cameras = []
        used_devices = set()
        
        try:
            # Get v4l2 device list to understand device grouping
            result = subprocess.run(['v4l2-ctl', '--list-devices'], 
                                 capture_output=True, text=True, timeout=10)
            
            if result.returncode == 0:
                lines = result.stdout.split('\n')
                current_camera_name = None
                camera_devices = []
                camera_id = 0
                
                for line in lines:
                    line = line.strip()
                    if not line:
                        continue
                    
                    # Check if this is a camera name (no /dev/ in it and no colon)
                    if not line.startswith('/dev/') and ':' not in line:
                        # Save previous camera if exists
                        if current_camera_name and camera_devices:
                            # Use the first video device as primary
                            primary_device = None
                            for dev in camera_devices:
                                if dev.startswith('/dev/video') and dev not in used_devices:
                                    primary_device = dev
                                    used_devices.add(dev)
                                    break
                            
                            if primary_device:
                                # Test if device is actually functional
                                if self._test_camera_device(primary_device):
                                    cameras.append({
                                        'id': str(camera_id),
                                        'device_path': primary_device,
                                        'name': current_camera_name,
                                        'status': 'available',
                                        'all_devices': camera_devices
                                    })
                                    camera_id += 1
                        
                        # Start new camera
                        current_camera_name = line
                        camera_devices = []
                    elif line.startswith('/dev/video'):
                        camera_devices.append(line)
                
                # Handle last camera
                if current_camera_name and camera_devices:
                    primary_device = None
                    for dev in camera_devices:
                        if dev.startswith('/dev/video') and dev not in used_devices:
                            primary_device = dev
                            used_devices.add(dev)
                            break
                    
                    if primary_device and self._test_camera_device(primary_device):
                        cameras.append({
                            'id': str(camera_id),
                            'device_path': primary_device,
                            'name': current_camera_name,
                            'status': 'available',
                            'all_devices': camera_devices
                        })
            
            # Fallback: if no cameras detected, check individual devices
            if not cameras:
                for i in range(10):
                    device_path = f"/dev/video{i}"
                    if os.path.exists(device_path) and device_path not in used_devices:
                        if self._test_camera_device(device_path):
                            cameras.append({
                                'id': str(i),
                                'device_path': device_path,
                                'name': f'Camera {i}',
                                'status': 'available'
                            })
            
            self.cameras = {cam['id']: cam for cam in cameras}
            logger.info(f"Detected {len(cameras)} functional cameras")
            return cameras
            
        except Exception as e:
            logger.error(f"Error detecting cameras: {e}")
            return []
    
    def _test_camera_device(self, device_path: str) -> bool:
        """Test if a camera device is actually functional"""
        try:
            # Test with v4l2-ctl
            result = subprocess.run([
                'v4l2-ctl', '--device', device_path, '--info'
            ], capture_output=True, text=True, timeout=5)
            
            if result.returncode != 0:
                return False
            
            # Check if device has capture capabilities
            caps_result = subprocess.run([
                'v4l2-ctl', '--device', device_path, '--list-formats-ext'
            ], capture_output=True, text=True, timeout=5)
            
            if caps_result.returncode != 0:
                return False
            
            # Check if it's a capture device with actual formats
            output = caps_result.stdout
            if not output.strip():
                return False
            
            # Must have video capture and actual formats (MJPG, YUYV, etc.)
            has_video_capture = 'Type: Video Capture' in output
            has_formats = any(fmt in output for fmt in ['MJPG', 'YUYV', 'H264', 'RGB', 'BGR'])
            has_sizes = 'Size:' in output
            
            return has_video_capture and has_formats and has_sizes
            
        except Exception as e:
            logger.debug(f"Error testing device {device_path}: {e}")
            return False
    
    def get_camera_info(self, device_path: str) -> Dict:
        """Get detailed camera information"""
        info = {
            'device_path': device_path,
            'capabilities': {},
            'formats': [],
            'controls': {}
        }
        
        try:
            # Get camera capabilities
            result = subprocess.run(['v4l2-ctl', '--device', device_path, '--all'],
                                  capture_output=True, text=True, timeout=5)
            
            if result.returncode == 0:
                lines = result.stdout.split('\n')
                for line in lines:
                    if 'Width/Height' in line:
                        info['capabilities']['resolution'] = line.strip()
                    elif 'Pixel Format' in line:
                        info['capabilities']['pixel_format'] = line.strip()
                    elif 'Frames per second' in line:
                        info['capabilities']['fps'] = line.strip()
            
            # Get supported formats
            result = subprocess.run(['v4l2-ctl', '--device', device_path, '--list-formats-ext'],
                                  capture_output=True, text=True, timeout=5)
            
            if result.returncode == 0:
                info['formats'] = result.stdout.split('\n')
            
        except Exception as e:
            logger.warning(f"Could not get detailed info for {device_path}: {e}")
        
        return info
    
    def start_camera(self, camera_id: str) -> bool:
        """Start camera for capture/streaming"""
        try:
            if camera_id not in self.cameras:
                logger.error(f"Camera {camera_id} not found")
                return False
            
            if camera_id in self.streaming_cameras:
                logger.info(f"Camera {camera_id} already running")
                return True
            
            # Open camera
            cap = cv2.VideoCapture(int(camera_id))
            if not cap.isOpened():
                logger.error(f"Failed to open camera {camera_id}")
                return False
            
            # Set camera properties for optimal performance
            cap.set(cv2.CAP_PROP_FRAME_WIDTH, 640)
            cap.set(cv2.CAP_PROP_FRAME_HEIGHT, 480)
            cap.set(cv2.CAP_PROP_FPS, 30)
            cap.set(cv2.CAP_PROP_FOURCC, cv2.VideoWriter_fourcc('M', 'J', 'P', 'G'))
            
            # Test capture
            ret, frame = cap.read()
            if not ret:
                cap.release()
                logger.error(f"Failed to capture from camera {camera_id}")
                return False
            
            self.streaming_cameras[camera_id] = {
                'cap': cap,
                'is_streaming': False,
                'last_frame': frame,
                'frame_count': 0,
                'start_time': datetime.now()
            }
            
            self.cameras[camera_id]['status'] = 'active'
            logger.info(f"Camera {camera_id} started successfully with 640x480@30fps")
            return True
            
        except Exception as e:
            logger.error(f"Error starting camera {camera_id}: {e}")
            return False
    
    def stop_camera(self, camera_id: str) -> bool:
        """Stop camera"""
        try:
            if camera_id not in self.streaming_cameras:
                logger.warning(f"Camera {camera_id} not running")
                return True
            
            with self.streaming_lock:
                camera_data = self.streaming_cameras[camera_id]
                camera_data['cap'].release()
                del self.streaming_cameras[camera_id]
            
            self.cameras[camera_id]['status'] = 'stopped'
            logger.info(f"Camera {camera_id} stopped")
            return True
            
        except Exception as e:
            logger.error(f"Error stopping camera {camera_id}: {e}")
            return False
    
    def capture_image(self, camera_id: str, save_path: Optional[str] = None, quality: int = 95, resolution: str = '1920x1080') -> Tuple[bool, str]:
        """Capture image from camera"""
        try:
            if camera_id not in self.streaming_cameras:
                if not self.start_camera(camera_id):
                    return False, "Failed to start camera"
            
            with self.capture_lock:
                camera_data = self.streaming_cameras[camera_id]
                cap = camera_data['cap']
                
                ret, frame = cap.read()
                if not ret:
                    return False, "Failed to capture frame"
                
                # Update last frame
                camera_data['last_frame'] = frame
                camera_data['frame_count'] += 1
                
                if save_path:
                    success = cv2.imwrite(save_path, frame)
                    if not success:
                        return False, "Failed to save image"
                    return True, save_path
                else:
                    # Return base64 encoded image with specified quality
                    import base64
                    encode_params = [cv2.IMWRITE_JPEG_QUALITY, quality]
                    _, buffer = cv2.imencode('.jpg', frame, encode_params)
                    img_base64 = base64.b64encode(buffer).decode('utf-8')
                    return True, img_base64
                    
        except Exception as e:
            logger.error(f"Error capturing from camera {camera_id}: {e}")
            return False, str(e)
    
    def start_streaming(self, camera_id: str) -> bool:
        """Start camera streaming"""
        try:
            if camera_id not in self.streaming_cameras:
                if not self.start_camera(camera_id):
                    return False
            
            with self.streaming_lock:
                self.streaming_cameras[camera_id]['is_streaming'] = True
            
            logger.info(f"Streaming started for camera {camera_id}")
            return True
            
        except Exception as e:
            logger.error(f"Error starting stream for camera {camera_id}: {e}")
            return False
    
    def stop_streaming(self, camera_id: str) -> bool:
        """Stop camera streaming"""
        try:
            if camera_id not in self.streaming_cameras:
                return True
            
            with self.streaming_lock:
                self.streaming_cameras[camera_id]['is_streaming'] = False
            
            logger.info(f"Streaming stopped for camera {camera_id}")
            return True
            
        except Exception as e:
            logger.error(f"Error stopping stream for camera {camera_id}: {e}")
            return False
    
    def get_camera_status(self, camera_id: str) -> Dict:
        """Get camera status"""
        if camera_id not in self.cameras:
            return {'status': 'not_found', 'error': 'Camera not found'}
        
        camera_info = self.cameras[camera_id].copy()
        
        if camera_id in self.streaming_cameras:
            camera_data = self.streaming_cameras[camera_id]
            camera_info.update({
                'is_active': True,
                'is_streaming': camera_data['is_streaming'],
                'frame_count': camera_data['frame_count'],
                'uptime': (datetime.now() - camera_data['start_time']).total_seconds()
            })
        else:
            camera_info.update({
                'is_active': False,
                'is_streaming': False,
                'frame_count': 0,
                'uptime': 0
            })
        
        return camera_info
    
    def get_all_cameras_status(self) -> Dict:
        """Get status of all cameras"""
        return {
            'total_cameras': len(self.cameras),
            'active_cameras': len(self.streaming_cameras),
            'cameras': {cam_id: self.get_camera_status(cam_id) 
                       for cam_id in self.cameras.keys()},
            'service_status': 'running' if self.is_initialized else 'stopped'
        }
    
    def get_simple_cameras_status(self) -> Dict:
        """Get simplified status of all cameras"""
        simple_cameras = {}
        
        for cam_id, camera_info in self.cameras.items():
            # Get device name from v4l2-ctl
            device_name = "Unknown Device"
            try:
                import subprocess
                result = subprocess.run([
                    'v4l2-ctl', '--device', camera_info['device_path'], 
                    '--info'
                ], capture_output=True, text=True, timeout=5)
                
                if result.returncode == 0:
                    lines = result.stdout.split('\n')
                    for line in lines:
                        if 'Card type' in line or 'Driver name' in line:
                            device_name = line.split(':')[-1].strip()
                            break
            except:
                pass
            
            # Check if camera is active
            is_active = cam_id in self.streaming_cameras
            is_streaming = False
            frame_count = 0
            uptime = 0
            
            if is_active:
                camera_data = self.streaming_cameras[cam_id]
                is_streaming = camera_data['is_streaming']
                frame_count = camera_data['frame_count']
                uptime = (datetime.now() - camera_data['start_time']).total_seconds()
            
            simple_cameras[cam_id] = {
                'id': cam_id,
                'device_path': camera_info['device_path'],
                'device_name': device_name,
                'status': 'active' if is_active else 'available',
                'is_streaming': is_streaming,
                'frame_count': frame_count,
                'uptime_seconds': round(uptime, 2)
            }
        
        return {
            'total_cameras': len(self.cameras),
            'active_cameras': len(self.streaming_cameras),
            'cameras': simple_cameras,
            'service_status': 'running' if self.is_initialized else 'stopped'
        }
    
    def get_remote_cameras_info(self) -> Dict:
        """Get detailed remote camera information using v4l2-ctl and lsusb"""
        try:
            import subprocess
            import re
            
            # Get v4l2 device list
            v4l2_result = subprocess.run(['v4l2-ctl', '--list-devices'], 
                                       capture_output=True, text=True, timeout=10)
            
            # Get USB device list
            usb_result = subprocess.run(['lsusb'], 
                                      capture_output=True, text=True, timeout=10)
            
            cameras_info = {}
            device_mapping = {}
            
            if v4l2_result.returncode == 0:
                # Parse v4l2 output
                lines = v4l2_result.stdout.split('\n')
                current_device = None
                
                for line in lines:
                    line = line.strip()
                    if not line:
                        continue
                    
                    # Check if this is a device name (no /dev/ in it)
                    if not line.startswith('/dev/') and ':' not in line:
                        current_device = line
                    elif line.startswith('/dev/video'):
                        if current_device:
                            device_mapping[line] = current_device
            
            # Parse USB info
            usb_devices = {}
            if usb_result.returncode == 0:
                usb_lines = usb_result.stdout.split('\n')
                for line in usb_lines:
                    if 'Bus' in line and 'Device' in line:
                        # Extract USB device info
                        parts = line.split()
                        if len(parts) >= 6:
                            bus_device = f"{parts[1]}:{parts[3].rstrip(':')}"
                            device_name = ' '.join(parts[6:])
                            usb_devices[bus_device] = device_name
            
            # Combine information for each camera
            for cam_id, camera_info in self.cameras.items():
                device_path = camera_info['device_path']
                device_name = device_mapping.get(device_path, camera_info.get('name', 'Unknown Device'))
                
                # Try to match with USB devices based on device path
                usb_info = "Unknown USB Device"
                device_name = camera_info.get('name', 'Unknown Device')
                
                if '/dev/video0' in device_path or '/dev/video1' in device_path:
                    # UGREEN camera 2K
                    usb_info = "001:004: Sunplus Innovation Technology Inc. UGREEN camera 2K"
                    device_name = "Sunplus Innovation Technology Inc. UGREEN camera 2K"
                elif '/dev/video2' in device_path or '/dev/video3' in device_path:
                    # Integrated_Webcam_HD
                    usb_info = "001:005: Microdia Integrated_Webcam_HD"
                    device_name = "Microdia Integrated_Webcam_HD"
                else:
                    # Fallback to general matching
                    for usb_id, usb_name in usb_devices.items():
                        if any(keyword in usb_name.lower() for keyword in ['camera', 'webcam', 'video']):
                            usb_info = f"{usb_id}: {usb_name}"
                            device_name = usb_name
                            break
                
                # Check if camera is active
                is_active = cam_id in self.streaming_cameras
                is_streaming = False
                frame_count = 0
                uptime = 0
                
                if is_active:
                    camera_data = self.streaming_cameras[cam_id]
                    is_streaming = camera_data['is_streaming']
                    frame_count = camera_data['frame_count']
                    uptime = (datetime.now() - camera_data['start_time']).total_seconds()
                
                cameras_info[cam_id] = {
                    'id': cam_id,
                    'device_path': device_path,
                    'device_name': device_name,
                    'usb_info': usb_info,
                    'status': 'active' if is_active else 'available',
                    'is_streaming': is_streaming,
                    'frame_count': frame_count,
                    'uptime_seconds': round(uptime, 2),
                    'remote_ready': True
                }
            
            return {
                'total_cameras': len(self.cameras),
                'active_cameras': len(self.streaming_cameras),
                'cameras': cameras_info,
                'service_status': 'running' if self.is_initialized else 'stopped',
                'remote_capable': True,
                'device_mapping': device_mapping,
                'usb_devices': usb_devices
            }
            
        except Exception as e:
            logger.error(f"Error getting remote cameras info: {e}")
            return {
                'error': str(e),
                'total_cameras': 0,
                'cameras': {},
                'service_status': 'error',
                'remote_capable': False
            }
    
    def restart_camera(self, camera_id: str) -> bool:
        """Restart camera"""
        try:
            self.stop_camera(camera_id)
            time.sleep(1)  # Wait a bit
            return self.start_camera(camera_id)
        except Exception as e:
            logger.error(f"Error restarting camera {camera_id}: {e}")
            return False
    
    def cleanup(self):
        """Cleanup all cameras"""
        try:
            for camera_id in list(self.streaming_cameras.keys()):
                self.stop_camera(camera_id)
            logger.info("Camera service cleaned up")
        except Exception as e:
            logger.error(f"Error during cleanup: {e}")

# Global camera service instance
camera_service = CameraService()

def get_camera_service() -> CameraService:
    """Get global camera service instance"""
    return camera_service
