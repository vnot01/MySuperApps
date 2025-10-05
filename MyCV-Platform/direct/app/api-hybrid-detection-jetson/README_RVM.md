# MyCV-Platform RVM Setup

This directory contains the RVM-integrated version of MyCV-Platform.

## Quick Start

1. **Configure RVM Platform**
   ```bash
   # Update rvm_config.env with your RVM Platform details
   cp rvm_config.env .env
   nano .env
   ```

2. **Start the API**
   ```bash
   python3 app.py
   ```

3. **Test Integration**
   ```bash
   python3 test_rvm_integration.py
   ```

## Directory Structure

- `rvm_{id}/` - RVM-specific data directories
- `legacy/` - Backward compatibility directory
- `models/` - AI model files

## API Endpoints

### Health & Status
- `GET /api/health` - Health check
- `GET /api/status` - API status with GPU information
- `GET /api/hardware` - Comprehensive Jetson hardware information

### Upload & Processing
- `POST /api/upload` - Upload with RVM authentication
- `GET /api/process/<session_id>` - Processing status
- `GET /api/results/<session_id>` - Detection results

### Download & History
- `GET /api/download/<session_id>/<filename>` - Download result files
- `GET /api/backup/<session_id>` - Create TAR.GZ backup
- `GET /api/detections` - Get detections with RVM filtering
- `POST /api/detections/search` - Search with RVM filtering

### Camera Control
- `GET /api/cameras` - List semua camera yang tersedia
- `GET /api/cameras/<camera_id>/info` - Informasi detail camera
- `POST /api/cameras/<camera_id>/start` - Start camera untuk capture/streaming
- `POST /api/cameras/<camera_id>/stop` - Stop camera
- `POST /api/cameras/<camera_id>/restart` - Restart camera
- `GET /api/cameras/<camera_id>/stream` - Status streaming camera
- `POST /api/cameras/<camera_id>/capture` - Capture image dari camera
- `POST /api/cameras/<camera_id>/capture/base64` - Capture image dan return sebagai base64
- `GET /api/cameras/<camera_id>/settings` - Get camera settings
- `POST /api/cameras/<camera_id>/settings` - Update camera settings
- `GET /api/cameras/status` - Status semua camera (detailed)
- `GET /api/cameras/status/simple` - Status camera sederhana dengan device names
- `GET /api/cameras/remote` - Info camera dengan v4l2 dan USB mapping
- `GET /api/cameras/dashboard` - Info camera optimized untuk admin dashboard
- `GET /api/cameras/discovery` - Comprehensive camera discovery
- `GET /api/cameras/<camera_id>/status` - Status camera spesifik
- `POST /api/cameras/<camera_id>/stream/start` - Start camera streaming
- `POST /api/cameras/<camera_id>/stream/stop` - Stop camera streaming

### RVM Integration
- `POST /api/rvm/validate` - Validate RVM API key
- `GET /api/rvm/{id}/stats` - Get RVM statistics

## Authentication

Use `X-RVM-API-Key` header for RVM-specific operations:
```bash
curl -H "X-RVM-API-Key: your_key" http://localhost:5000/api/detections?rvm_id=1
```

## Hardware Information

### Hardware Monitoring Endpoint
The `/api/hardware` endpoint provides comprehensive Jetson hardware information:

```bash
# Get hardware information
curl http://100.117.234.2:5000/api/hardware
```

#### Hardware Information Includes:
- **Jetson Info**: Model, L4T version, Jetpack version, kernel version, architecture
- **CUDA Info**: Status, availability, cuDNN enabled, PyTorch CUDA version, GPU details
- **Memory Info**: Total memory (RAM + Swap combined)
- **Disk Info**: Available space, total size, used space, usage percentage
- **Camera Info**: USB cameras with device names, USB devices with detailed info, nvargus status
- **Network Info**: Local IP, network IP (Tailscale), public IP, connection status

#### Example Response:
```json
{
  "status": "success",
  "service": "MyCV-Edge-API",
  "hardware_info": {
    "jetson_info": {
      "model": "Jetson Orin Nano",
      "l4t_version": "R36.4.2",
      "jetpack_version": "6.1",
      "kernel_version": "5.10.120-tegra",
      "architecture": "aarch64"
    },
    "cuda_info": {
      "status": "success",
      "cuda_available": true,
      "cudnn_enabled": true,
      "pytorch_cuda_version": "2.5.0a0+872d972e41.nv24.08",
      "available_gpus": 1,
      "gpus": [
        {
          "id": 0,
          "name": "Orin",
          "memory_gb": 7.44
        }
      ],
      "total_memory_all_gpus_gb": 7.44
    },
    "memory_info": {
      "total_gb": 23.44
    },
    "disk_info": {
      "available": "134G",
      "size": "233G",
      "used": "88G",
      "use_percent": "40%"
    },
    "camera_info": {
      "usb_cameras": [
        {
          "device": "/dev/video3",
          "name": "Integrated_Webcam_HD: Integrate (usb-3610000.usb-2.4)"
        }
      ],
      "usb_devices": [
        {
          "device_id": "0c45:64ab",
          "name": "Microdia Integrated_Webcam_HD",
          "raw_line": "Bus 001 Device 004: ID 0c45:64ab Microdia Integrated_Webcam_HD"
        }
      ],
      "nvargus_status": "active"
    },
    "network_info": {
      "local_ip": "192.168.1.11",
      "network_ip": "100.117.234.2",
      "public_ip": "182.8.226.98",
      "network_connected": true,
      "tailscale_ip": [
        "100.117.234.2/32",
        "fd7a:115c:a1e0::1f35:ea02/128"
      ]
    }
  }
}
```

## Configuration

See `rvm_config.env` for all configuration options.

## Documentation

See `RVM_INTEGRATION.md` for detailed integration documentation.
