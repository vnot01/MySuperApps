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
- **Jetson Info**: Model, L4T version, Jetpack version, kernel
- **CUDA Info**: Availability, version, device count, memory usage
- **Memory Info**: System memory, swap memory (total/used/free)
- **Disk Info**: Storage devices, NVMe detection, filesystem usage
- **Camera Info**: USB cameras, CSI cameras, nvargus status
- **Network Info**: Interfaces, Tailscale IP, local IP, public IP

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
      "available": true,
      "version": "12.6",
      "device_count": 1,
      "device_name": "Orin",
      "memory_total_gb": 7.4,
      "memory_used_gb": 1.2,
      "memory_free_gb": 6.2
    },
    "memory_info": {
      "total_gb": 7.4,
      "available_gb": 6.0,
      "used_gb": 1.4,
      "free_gb": 3.1,
      "swap_total_gb": 16.0,
      "swap_used_gb": 0.0,
      "swap_free_gb": 16.0
    },
    "disk_info": {
      "devices": [...],
      "nvme_devices": [...],
      "root_filesystem": {...}
    },
    "camera_info": {
      "usb_cameras": [...],
      "jetson_cameras": [...],
      "total_cameras": 2,
      "nvargus_status": "active"
    },
    "network_info": {
      "interfaces": [...],
      "tailscale_ip": "100.117.234.2",
      "local_ip": "192.168.1.100",
      "public_ip": "203.0.113.1",
      "tailscale_status": "connected"
    }
  }
}
```

## Configuration

See `rvm_config.env` for all configuration options.

## Documentation

See `RVM_INTEGRATION.md` for detailed integration documentation.
