# MyCV-Platform

## 🎯 **Computer Vision Processing Service**

**VM**: 102 (cv-host)  
**Purpose**: Real YOLO11 + SAM2 processing untuk production  
**Integration**: MyRVM-Platform (VM 100)  

---

## 🚀 **Quick Start**

### **Prerequisites:**
- Ubuntu 22.04 LTS (recommended) atau macOS/Linux lainnya
- Python 3.11+
- Docker & Docker Compose
- NVIDIA GPU (optional, untuk acceleration)
- Bash 3.2+ (untuk macOS) atau Bash 4.0+ (untuk Linux)

### **System Compatibility:**
- **Bash 4.0+**: Gunakan `install_models.sh` (Linux)
- **Bash 3.2**: Gunakan `install_models_compatible.sh` (macOS, older Linux)
- **Docker**: Required untuk production deployment
- **Virtual Environment**: Otomatis dibuat dan dikelola

### **Installation:**
```bash
# Navigate to MyCV-Platform directory
cd MyCV-Platform

# Setup environment (includes virtual environment, GPU/CPU detection, mock data testing)
./scripts/setup.sh

# Install models (with virtual environment and capability detection)
# For Bash 4.0+ systems:
./scripts/install_models.sh

# For Bash 3.2 systems (macOS, older Linux):
./scripts/install_models_compatible.sh

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
- ✅ **Model Management System** untuk file model besar (best.pt, dll)
- ✅ **Cloud Storage Integration** untuk upload/download model
- ✅ **Local Backup System** untuk backup dan restore model

---

## 🔧 **Model Management**

### **Available YOLO11 Models:**
- `yolo11n.pt` - Nano (2.6M params, fastest)
- `yolo11s.pt` - Small (9.4M params, balanced)
- `yolo11m.pt` - Medium (20.1M params, higher accuracy)
- `yolo11l.pt` - Large (25.3M params, high accuracy)
- `yolo11x.pt` - Extra Large (68.2M params, highest accuracy)

### **Available SAM2 Models:**
- `sam2_b.pt` - Base (91.0M params, fastest segmentation)
- `sam2.1_b.pt` - Base (358MB, fastest segmentation) - Available but not active by default
- `sam2.1_l.pt` - Large (2.4GB, best segmentation quality) - Available but not active by default

### **Available Trained Models:**
- `best.pt` - Custom trained YOLO model from MySuperApps
  - **Source**: GitHub Releases
  - **URL**: `https://github.com/vnot01/MySuperApps/releases/download/trained-models/best.pt`
  - **Location**: `data/models/trained/`
  - **Description**: Pre-trained model untuk production use
  - **Auto-download**: Included in install_models.sh

### **Model Storage Structure:**
```
data/models/
├── yolo/
│   ├── active/          # YOLO models yang aktif
│   └── downloads/       # YOLO models yang didownload
├── sam/
│   ├── active/          # SAM models yang aktif
│   └── downloads/       # SAM models yang didownload
├── trained/             # Trained models (best.pt, dll)
├── cloud/               # Cloud storage cache
├── backups/             # Local backups
└── downloads/           # General downloads
```

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
- `setup.sh` - Initial setup dan environment configuration
- `install_models.sh` - Install models (Bash 4.0+)
- `install_models_compatible.sh` - Install models (Bash 3.2 compatible)
- `detect_environment.sh` - Deteksi environment di host system
- `docker_detect_environment.sh` - Deteksi environment di Docker container
- `run_all_environment_tests.sh` - Jalankan semua environment tests
- `startup_environment_check.sh` - Environment check pada startup
- `model_manager.sh` - Manajemen model (upload, download, backup, restore)
- `download_models.sh` - Download model dari cloud storage
- `apply_all_changes.sh` - Apply semua environment changes

### **Model Management:**
```bash
# Setup model management
./scripts/model_manager.sh setup

# Upload model ke cloud storage
./scripts/model_manager.sh upload best.pt my_model.pt

# Download model dari cloud storage
./scripts/model_manager.sh download my_model.pt

# Backup model lokal
./scripts/model_manager.sh backup best.pt

# Restore model dari backup
./scripts/model_manager.sh restore best_backup_20231201_120000.pt

# List semua model
./scripts/model_manager.sh list
```

### **Troubleshooting:**
```bash
# Jika install_models.sh gagal (Bash version issue)
./scripts/install_models_compatible.sh

# Apply semua environment changes
./scripts/apply_all_changes.sh

# Run comprehensive environment tests
./scripts/run_all_environment_tests.sh

# Check environment capabilities
./scripts/detect_environment.sh
```

---

**Status**: 🚧 **IN DEVELOPMENT**  
**Version**: 1.0.0-alpha  
**Last Updated**: 10 September 2025
