# MyCV-Edge-API Utils

Utility scripts and tools for MyCV-Edge-API hardware monitoring and system management.

## 📁 Directory Structure

```
utils/
├── python/                    # Python utility scripts
│   ├── get_jetpack_versions.py    # JetPack and L4T version detection
│   └── camera_utils.py            # Camera utility functions
├── services/                  # Service modules
│   ├── camera_service.py          # Camera control service
│   ├── api_client.py              # API client untuk RVM communication
│   └── internal_*.py              # Internal RVM services
├── shell/                     # Shell utility scripts
│   ├── install_v4l_utils.sh      # v4l-utils installation script
│   └── camera_control.sh         # Camera control shell script
├── config/                    # Configuration files
└── README.md                  # This file
```

## 🐍 Python Utils

### get_jetpack_versions.py
Scrapes NVIDIA JetPack archive page to get JetPack and L4T version mapping.

**Usage:**
```bash
cd utils/python
python3 get_jetpack_versions.py
```

**Dependencies:**
```bash
# Dependencies are included in main requirements.txt
# No additional installation needed
```

**Features:**
- Dynamic scraping of NVIDIA JetPack versions
- Local L4T version detection
- Version compatibility matching
- Used by hardware monitoring API

### camera_utils.py
Camera utility functions untuk camera operations dan management.

**Usage:**
```bash
cd utils/python
python3 -c "from camera_utils import *; print('Camera utils loaded')"
```

**Features:**
- Camera device detection
- Image processing utilities
- Camera status monitoring
- Device capability checking
- Used by camera control API

## 🐚 Shell Utils

### install_v4l_utils.sh
Installs v4l-utils package for camera detection.

**Usage:**
```bash
chmod +x utils/shell/install_v4l_utils.sh
./utils/shell/install_v4l_utils.sh
```

**Features:**
- Automatic v4l-utils installation
- Camera device detection
- Video device enumeration
- Used by camera monitoring API

### camera_control.sh
Shell script interface untuk camera control operations.

**Usage:**
```bash
chmod +x utils/shell/camera_control.sh

# List cameras
./utils/shell/camera_control.sh list

# Start camera
./utils/shell/camera_control.sh start 0

# Capture image
./utils/shell/camera_control.sh capture 0 /tmp/image.jpg

# Test camera
./utils/shell/camera_control.sh test 0

# Get status
./utils/shell/camera_control.sh status 0
```

**Features:**
- Camera listing dan discovery
- Camera start/stop operations
- Image capture dengan file save
- Camera testing dan validation
- Status monitoring
- Command-line interface untuk camera control

## 🔧 Configuration

### Python Dependencies
Python utility dependencies are included in the main requirements.txt:
```bash
# Dependencies are already installed with main project
# No additional installation needed
```

### System Dependencies
Install system dependencies:
```bash
# Install v4l-utils for camera detection
sudo apt install v4l-utils

# Or use the provided script
./utils/shell/install_v4l_utils.sh
```

## 🎯 Integration

These utilities are automatically used by the `/api/hardware` endpoint:

- **JetPack Detection**: `get_jetpack_versions.py` provides accurate JetPack and L4T version information
- **Camera Detection**: `v4l-utils` enables proper camera device enumeration
- **Hardware Monitoring**: All utilities contribute to comprehensive hardware information

## 📊 API Integration

The hardware monitoring API (`/api/hardware`) uses these utilities to provide:

- **Jetson Info**: Model, architecture, kernel version, JetPack/L4T versions
- **CUDA Info**: GPU availability, memory, device information
- **Memory Info**: System memory totals (RAM + Swap)
- **Disk Info**: Storage usage and availability
- **Camera Info**: USB and CSI camera detection
- **Network Info**: WiFi, Tailscale, and public IP addresses

## 🚀 Quick Start

1. **Install Dependencies:**
   ```bash
   # Python dependencies are included in main requirements.txt
   # System dependencies
   ./utils/shell/install_v4l_utils.sh
   ```

2. **Test Utilities:**
   ```bash
   # Test JetPack detection
   python3 utils/python/get_jetpack_versions.py
   
   # Test camera detection
   v4l2-ctl --list-devices
   ```

3. **Use in API:**
   ```bash
   # Test hardware endpoint
   curl http://100.117.234.2:5000/api/hardware
   ```

## 🔍 Troubleshooting

### JetPack Version Detection Issues
- Check internet connectivity for web scraping
- Verify `/etc/nv_tegra_release` file exists
- Check Python dependencies: `pip install requests beautifulsoup4`

### Camera Detection Issues
- Install v4l-utils: `sudo apt install v4l-utils`
- Check video devices: `ls /dev/video*`
- Test v4l2-ctl: `v4l2-ctl --list-devices`

### Network Detection Issues
- Check interface names: `ip addr show`
- Verify Tailscale installation: `tailscale status`
- Test public IP: `curl ifconfig.me`
