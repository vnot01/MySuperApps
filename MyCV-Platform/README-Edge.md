# MyCV-Platform - Edge Devices (NVIDIA Jetson)

## 🎯 **Computer Vision Processing for Edge Devices**

**Target Devices**: NVIDIA Jetson Orin Nano, Orin NX, Orin AGX  
**Purpose**: Real YOLO11 + SAM2 processing untuk edge deployment  
**Integration**: MyRVM-Platform (VM 100) via API  

Platform Computer Vision dengan YOLO + SAM2 Integration untuk NVIDIA Jetson devices.

## 📋 System Requirements

### **Hardware Requirements:**
- **NVIDIA Jetson Orin Nano** (atau seri apapun: Orin NX, Orin AGX)
- **Minimum 8GB RAM** (recommended 16GB+)
- **32GB+ Storage** (untuk models dan dependencies)
- **Network connectivity** untuk API communication

### **Software Requirements:**
- **Ubuntu 22.04 LTS**
- **Jetpack 6.1** (rev 1)
- **L4T 36.4.2**
- **Python 3.10+**

## 🚀 Quick Start for Jetson

### 1. System Preparation

#### **Create 16GB Swap File:**
```bash
# Create 16GB swap file
sudo fallocate -l 16G /swapfile

# Set proper permissions
sudo chmod 600 /swapfile

# Format as swap
sudo mkswap /swapfile

# Activate swap file
sudo swapon /swapfile
```

#### **Make Swap Permanent:**
```bash
# Add to fstab for automatic mounting
echo '/swapfile none swap sw 0 0' | sudo tee -a /etc/fstab
```

#### **Optimize for Jetson Orin:**
```bash
# Optimize swappiness (lower = less aggressive swap usage)
echo 'vm.swappiness=10' | sudo tee -a /etc/sysctl.conf
echo 'vm.vfs_cache_pressure=50' | sudo tee -a /etc/sysctl.conf

# Apply settings immediately
sudo sysctl vm.swappiness=10
sudo sysctl vm.vfs_cache_pressure=50
```

#### **Verify Memory Configuration:**
```bash
# Check total memory
free -h

# Check active swap devices
swapon --show
```

**Expected Output:**
```
               total        used        free      shared  buff/cache   available
Mem:           7.4Gi       1.2Gi       3.1Gi        21Mi       3.1Gi       6.0Gi
Swap:           19Gi          0B        19Gi
```

### 2. PyTorch Installation for Jetson

#### **Install PyTorch 2.5.0 for Jetson Platform 6.1:**
```bash
# Install PyTorch 2.5.0 for Jetson Platform 6.1
pip install https://github.com/ultralytics/assets/releases/download/v0.0.0/torch-2.5.0a0+872d972e41.nv24.08-cp310-cp310-linux_aarch64.whl

# Install TorchVision 0.20.0 for Jetson Platform 6.1
pip install https://github.com/ultralytics/assets/releases/download/v0.0.0/torchvision-0.20.0a0+afc54f7-cp310-cp310-linux_aarch64.whl
```

#### **Verify Installation:**
```bash
python3 -c "
import torch
import torchvision
print(f'PyTorch version: {torch.__version__}')
print(f'CUDA available: {torch.cuda.is_available()}')
print(f'CUDA version: {torch.version.cuda}')
print(f'TorchVision version: {torchvision.__version__}')
print(f'CUDA device - {torch.cuda.get_device_name(0)}')
print(f'GPU Memory: {torch.cuda.get_device_properties(0).total_memory / 1024**3:.1f} GB')
"
```

**Expected Output:**
```
PyTorch version: 2.5.0a0+872d972e41.nv24.08
CUDA available: True
CUDA version: 12.6
TorchVision version: 0.20.0a0+afc54f7
CUDA device - Orin
GPU Memory: 7.4 GB
```

### 3. MyCV-Platform Setup

#### **Clone and Setup:**
```bash
# Clone repository
git clone https://github.com/vnot01/MySuperApps.git
cd MySuperApps/MyCV-Platform/direct

# Create virtual environment
python3 -m venv venv
source venv/bin/activate

# Install dependencies
pip install -r requirements.txt
```

#### **Run Jetson Integration Test:**
```bash
# Run Jetson-specific integration test
./scripts/run_test_hybrid_integration-jetson.sh
```

## 🔧 Configuration

### **API Configuration:**
- **Default API URL**: `http://100.117.234.2:5000`
- **Data Directory**: `data-jetson/` (separate from main data directory)
- **Models Directory**: `data-jetson/models/`

### **Directory Structure:**
```
data-jetson/
├── input/
│   ├── remote/         # Uploaded images
│   │   └── <timestamp>/<user_id>/
│   └── test_images/    # Test images
├── output/remote/      # Detection results
│   └── <timestamp>/<user_id>/
│       ├── yolo/       # YOLO11m results
│       ├── best/       # best.pt results
│       ├── segmentasi/ # SAM2 segmentation
│       ├── hybrid/     # Combined results
│       └── *.json      # Detection data
└── models/
    ├── yolo/active/    # YOLO models
    ├── sam/active/     # SAM models
    └── trained/active/ # Trained models
```

## 🎯 Features

### **Jetson-Optimized Features:**
- ✅ **PyTorch 2.5.0** optimized for Jetson Platform 6.1
- ✅ **CUDA Support** dengan automatic verification
- ✅ **Memory Optimization** dengan 16GB swap configuration
- ✅ **Edge-Specific Paths** menggunakan `data-jetson/` directory
- ✅ **API Integration** dengan Jetson IP (100.117.234.2:5000)
- ✅ **Auto-Installation** PyTorch jika tidak terdeteksi
- ✅ **Model Management** terpisah dari main installation

### **Performance Optimizations:**
- **Swap Configuration**: 16GB swap dengan swappiness=10
- **Memory Management**: Optimized untuk Jetson Orin memory constraints
- **CUDA Acceleration**: Full GPU support untuk inference
- **Model Caching**: Local model storage untuk offline capability

## 🚨 Troubleshooting

### **PyTorch Installation Issues:**
```bash
# Check system requirements
cat /etc/nv_tegra_release
nvidia-smi

# Verify Jetpack version
dpkg -l | grep nvidia-jetpack

# Reinstall PyTorch if needed
pip uninstall torch torchvision
pip install https://github.com/ultralytics/assets/releases/download/v0.0.0/torch-2.5.0a0+872d972e41.nv24.08-cp310-cp310-linux_aarch64.whl
pip install https://github.com/ultralytics/assets/releases/download/v0.0.0/torchvision-0.20.0a0+afc54f7-cp310-cp310-linux_aarch64.whl
```

### **Memory Issues:**
```bash
# Check memory usage
free -h
swapon --show

# Increase swap if needed
sudo fallocate -l 32G /swapfile2
sudo chmod 600 /swapfile2
sudo mkswap /swapfile2
sudo swapon /swapfile2
```

### **CUDA Issues:**
```bash
# Check CUDA availability
python3 -c "import torch; print(f'CUDA available: {torch.cuda.is_available()}')"

# Check GPU memory
nvidia-smi

# Restart services if needed
sudo systemctl restart nvidia-persistenced
```

### **API Connection Issues:**
```bash
# Test API connectivity
curl -s http://100.117.234.2:5000/api/health

# Check network connectivity
ping 100.117.234.2

# Verify firewall settings
sudo ufw status
```

## 📊 Performance Monitoring

### **System Monitoring:**
```bash
# Monitor GPU usage
watch -n 1 nvidia-smi

# Monitor memory usage
watch -n 1 free -h

# Monitor CPU usage
htop
```

### **Performance Metrics:**
- **Inference Time**: ~2-5 seconds per image (depending on model)
- **Memory Usage**: ~4-6GB RAM + 2-4GB GPU memory
- **Power Consumption**: ~15-25W (Jetson Orin Nano)
- **Temperature**: Monitor dengan `tegrastats`

## 🔗 Integration

### **API Integration:**
```bash
# Upload images via API
curl -X POST \
  -F 'files=@image1.jpg' \
  -F 'files=@image2.jpg' \
  -F 'user_id=jetson_user' \
  http://100.117.234.2:5000/api/upload

# Check processing status
curl http://100.117.234.2:5000/api/process/session_abc123

# Get results
curl http://100.117.234.2:5000/api/results/session_abc123
```

### **MyRVM-Platform Integration:**
```php
// Laravel API call to Jetson
$response = Http::post('http://100.117.234.2:5000/api/upload', [
    'files' => $imageFile,
    'user_id' => 'jetson_user'
]);
```

## 📚 Additional Resources

### **NVIDIA Documentation:**
- [Jetson Orin Developer Kit](https://developer.nvidia.com/embedded/jetson-orin)
- [Jetpack 6.1 Documentation](https://docs.nvidia.com/jetpack/)
- [PyTorch for Jetson](https://pytorch.org/get-started/locally/)

### **Performance Tuning:**
- [Jetson Performance Tuning](https://developer.nvidia.com/embedded/jetson-performance-tuning)
- [CUDA Optimization Guide](https://docs.nvidia.com/cuda/cuda-c-best-practices-guide/)

## 🆘 Support

Untuk bantuan atau laporan bug pada Jetson deployment:
1. Cek system requirements dan Jetpack version
2. Verify PyTorch installation dengan CUDA support
3. Check memory configuration dan swap settings
4. Test API connectivity ke main server
5. Monitor system resources selama processing

---

**Status**: ✅ **JETSON READY**  
**Version**: 1.4.1-edge  
**Last Updated**: 29 September 2025  
**Target Devices**: NVIDIA Jetson Orin Series
