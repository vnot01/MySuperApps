# MyCV-Platform

## 🎯 **Computer Vision Processing Service**

**VM**: 102 (cv-host)  
**Purpose**: Real YOLO11 + SAM2 processing untuk production  
**Integration**: MyRVM-Platform (VM 100)  

---

## 🚀 **Quick Start**

### **Prerequisites:**
- Ubuntu 22.04 LTS
- Python 3.11+
- Docker & Docker Compose
- NVIDIA GPU (optional, untuk acceleration)

### **Installation:**
```bash
# Clone repository
git clone <repository-url>
cd MyCV-Platform

# Setup environment (includes virtual environment, GPU/CPU detection, mock data testing)
./scripts/setup.sh

# Install models (with virtual environment and capability detection)
./scripts/install_models.sh

# Detect environment capabilities (optional)
./scripts/detect_environment.sh

# Start services
docker-compose up -d
```

### **Access:**
- **Dashboard**: http://localhost:8000
- **API**: http://localhost:8000/api/v1
- **Health Check**: http://localhost:8000/api/v1/health

---

## 📊 **Features**

- ✅ **Real YOLO11 Models** (yolo11s.pt, yolo11m.pt, etc.)
- ✅ **Real SAM2 Models** (sam2.1_l.pt, sam2.1_b.pt)
- ✅ **Dynamic Model Management** dengan URL downloads
- ✅ **FastAPI Dashboard** untuk testing
- ✅ **REST API** untuk production integration
- ✅ **GPU Acceleration** (CUDA support)
- ✅ **Model Validation** dan integrity checking
- ✅ **Virtual Environment Support** (konsisten di semua eksekusi Python)
- ✅ **Automatic GPU/CPU Detection** dengan informasi yang jelas
- ✅ **Mock Data Testing** untuk validasi sistem
- ✅ **Environment Detection Utility** untuk troubleshooting

---

## 🔧 **Model Management**

### **Available YOLO11 Models:**
- `yolo11n.pt` - Nano (2.6M params, fastest)
- `yolo11s.pt` - Small (9.4M params, balanced)
- `yolo11m.pt` - Medium (20.1M params, higher accuracy)
- `yolo11l.pt` - Large (25.3M params, high accuracy)
- `yolo11x.pt` - Extra Large (68.2M params, highest accuracy)

### **Available SAM2 Models:**
- `sam2.1_b.pt` - Base (fastest segmentation)
- `sam2.1_l.pt` - Large (best segmentation quality)

---

## 📚 **Documentation**

- [Installation Guide](docs/INSTALLATION.md)
- [API Documentation](docs/API.md)
- [Model Management](docs/MODEL_MANAGEMENT.md)
- [Deployment Guide](docs/DEPLOYMENT.md)
- [Troubleshooting](docs/TROUBLESHOOTING.md)

---

## 🔗 **Integration**

### **MyRVM-Platform Integration:**
```php
// Laravel API call
$response = Http::post('http://cv-host:8000/api/v1/analyze', [
    'image' => $imageFile,
    'yolo_model' => 'yolo11s.pt',
    'sam_model' => 'sam2.1_l.pt',
    'confidence' => 0.7
]);
```

---

## 🔧 **Environment Detection**

### **Virtual Environment:**
- ✅ Konsisten menggunakan virtual environment di semua eksekusi Python
- ✅ Otomatis deteksi dan aktivasi virtual environment
- ✅ Validasi virtual environment sebelum menjalankan script

### **GPU/CPU Mode Detection:**
- 🚀 **GPU MODE**: Otomatis deteksi NVIDIA GPU dan CUDA support
- 💻 **CPU MODE**: Fallback ke CPU jika GPU tidak tersedia
- 📊 Informasi detail tentang GPU memory, CUDA version, dan CPU threads

### **Mock Data Testing:**
- 🧪 **MOCK DATA MODE**: Testing dengan data sintetis untuk validasi
- ✅ Validasi tensor operations dan image processing
- 🔍 Deteksi device (GPU/CPU) yang digunakan untuk processing

### **Usage:**
```bash
# Deteksi environment capabilities
./scripts/detect_environment.sh

# Deteksi environment di Docker container
./scripts/docker_detect_environment.sh

# Jalankan semua environment tests
./scripts/run_all_environment_tests.sh

# Atau gunakan Python utility langsung
python3 app/utils/environment_detector.py
```

### **Available Scripts:**
- `detect_environment.sh` - Deteksi environment di host system
- `docker_detect_environment.sh` - Deteksi environment di Docker container
- `run_all_environment_tests.sh` - Jalankan semua environment tests
- `startup_environment_check.sh` - Environment check pada startup

---

**Status**: 🚧 **IN DEVELOPMENT**  
**Version**: 1.0.0-alpha  
**Last Updated**: 10 September 2025
