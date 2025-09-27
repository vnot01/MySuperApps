# MyCV-Platform

## 🎯 **Computer Vision Processing Service**

**VM**: 102 (cv-host)  
**Purpose**: Real YOLO11 + SAM2 processing untuk production  
**Integration**: MyRVM-Platform (VM 100)  

Platform Computer Vision dengan YOLO + SAM2 Integration.

## 📁 Struktur Project

Project ini dibagi menjadi 2 folder:

### 1. 📂 `direct/` - Direct Execution
**Untuk menjalankan langsung di VM tanpa Docker**

- ✅ **GPU Support**: Berfungsi dengan baik
- ✅ **Performance**: Optimal tanpa overhead Docker
- ✅ **Production Ready**: Siap untuk production
- ✅ **Simple Setup**: Langsung jalankan script

**Gunakan folder ini untuk:**
- Production deployment
- Development dengan GPU
- Testing dengan performa optimal

### 2. 📂 `docker/` - Docker Testing
**Untuk testing dengan Docker**

- ❌ **GPU Support**: Bermasalah (NVIDIA Container Toolkit bug)
- ✅ **CPU Support**: Berfungsi untuk testing CPU-only
- ⚠️ **Development Only**: Hanya untuk development/testing

**Gunakan folder ini untuk:**
- Development testing
- CPU-only testing
- Docker environment testing

## 🚀 Quick Start

### Option 1: Direct Execution (Recommended)
```bash
cd MySuperApps/MyCV-Platform
cd direct
python3 -m venv venv
source venv/bin/activate
pip install -r requirements.txt
./scripts/run_fresh_integration_test.sh
```

### Option 2: Copy Version with Enhanced Features
```bash
cd MySuperApps/MyCV-Platform
cd direct
python3 -m venv venv
source venv/bin/activate
pip install -r requirements.txt
./scripts/run_fresh_integration_test-copy.sh
```

### Option 3: Web Application (Real-time Camera)
```bash
cd direct
python3 -m venv venv
source venv/bin/activate
cd MySuperApps/MyCV-Platform
./scripts/run_web.sh
```

### Option 4: Docker CPU Testing
```bash
cd docker
docker-compose -f docker-compose.cpu.yml up --build
```

## 📊 Perbandingan

| Feature | Direct | Copy Version | Web App | Docker GPU | Docker CPU |
|---------|--------|--------------|---------|------------|------------|
| **GPU Support** | ✅ | ✅ | ✅ | ❌ | ❌ |
| **Real-time** | ❌ | ❌ | ✅ | ❌ | ❌ |
| **Performance** | ⭐⭐⭐ | ⭐⭐⭐ | ⭐⭐⭐ | N/A | ⭐ |
| **Setup** | ⭐⭐⭐ | ⭐⭐⭐ | ⭐⭐⭐ | ❌ | ⭐⭐ |
| **Production** | ✅ | ✅ | ✅ | ❌ | ❌ |
| **Development** | ✅ | ✅ | ✅ | ❌ | ✅ |
| **Enhanced Features** | ❌ | ✅ | ❌ | ❌ | ❌ |
| **Structured Output** | ❌ | ✅ | ❌ | ❌ | ❌ |
| **Compare Visualization** | ❌ | ✅ | ❌ | ❌ | ❌ |

## 🎯 Rekomendasi

**Untuk Production**: Gunakan `run_fresh_integration_test-copy.sh` (Copy Version) dengan fitur enhanced
**Untuk Real-time**: Gunakan `run_web.sh` untuk deteksi kamera
**Untuk Development**: Gunakan folder `direct/` atau `docker/` (CPU-only)
**Untuk Testing**: Gunakan Copy Version untuk fitur lengkap dan output terstruktur

## 📦 Backup & Kompresi

### Quick Backup
```bash
# Kompres folder direct
./compress_direct.sh

# Kompres folder docker
./compress_docker.sh

# Kompres seluruh project
./compress_all.sh

# Menu interaktif
./compress.sh
```

### Backup Location
- **Folder**: `../backups/`
- **Format**: `.tar.gz` dengan timestamp
- **Exclude**: `venv/`, `__pycache__/`, `logs/`, `data/output/`

## 📚 Dokumentasi

- [Direct Execution Guide](direct/README.md)
- [Docker Testing Guide](docker/README.md)
- [Web Application Guide](direct/app/web/README.md)
- [Testing Guide](docs/TESTING_GUIDE.md)
- [Model Management](docs/MODEL_MANAGEMENT.md)

## 🔧 Troubleshooting

### GPU Issues
- Pastikan NVIDIA driver terinstall: `nvidia-smi`
- Pastikan GPU terdeteksi: `lspci | grep -i nvidia`
- Gunakan folder `direct/` untuk akses GPU

### Copy Version Issues
- **Brightness Issues**: Alpha blending sudah dioptimasi ke 0.3 untuk SAM2
- **JSON Missing**: Hanya best.pt yang menghasilkan JSON (YOLO11m JSON dihapus)
- **Directory Structure**: Output tersimpan di `data/output/remote/(timestamp)/(user_id)/`
- **Compare Visualization**: Pastikan semua file detection, segmentation, dan hybrid tersedia

### Docker Issues
- Docker GPU bermasalah karena bug NVIDIA Container Toolkit
- Gunakan Docker CPU untuk testing: `docker-compose -f docker-compose.cpu.yml up`

## 📞 Support

Jika ada masalah, cek dokumentasi di masing-masing folder atau lihat [Testing Guide](docs/TESTING_GUIDE.md).

---

## 📊 **Features**

### **Core Features:**
- ✅ **Real YOLO11 Models** (yolo11s.pt, yolo11m.pt, etc.)
- ✅ **Real SAM2 Models** (sam2.1_l.pt, sam2.1_b.pt)
- ✅ **Custom Trained Models** (best.pt untuk production)
- ✅ **GPU Acceleration** (CUDA support)
- ✅ **Virtual Environment Support** (konsisten di semua eksekusi Python)
- ✅ **Automatic GPU/CPU Detection** dengan informasi yang jelas

### **Enhanced Features (Copy Version):**
- ✅ **Structured Output Directories** (yolo/, best/, segmentasi/, hybrid/)
- ✅ **Optimized JSON Output** (hanya best.pt, tidak ada YOLO11m JSON)
- ✅ **Enhanced Brightness Preservation** (alpha blending 0.3 untuk SAM2)
- ✅ **Compare Visualization** (gabungan semua hasil dalam 1 gambar)
- ✅ **Dynamic Directory Structure** (timestamp/user_id organization)
- ✅ **Improved File Naming Convention** (model-specific naming)

### **Integration Features:**
- ✅ **Dynamic Model Management** dengan URL downloads
- ✅ **FastAPI Dashboard** untuk testing
- ✅ **REST API** untuk production integration
- ✅ **Model Validation** dan integrity checking
- ✅ **Mock Data Testing** untuk validasi sistem
- ✅ **Environment Detection Utility** untuk troubleshooting
- ✅ **Model Management System** untuk file model besar (best.pt, dll)
- ✅ **Cloud Storage Integration** untuk upload/download model
- ✅ **Local Backup System** untuk backup dan restore model

---

## 🆕 **Copy Version - Enhanced Features**

### **Overview:**
Copy Version adalah implementasi terbaru dengan fitur-fitur enhanced yang dirancang untuk production dan testing yang lebih optimal.

### **Key Improvements:**

#### **1. Structured Output Directories:**
```
data/output/remote/(timestamp)/(user_id)/
├── yolo/                    # YOLO11m detection results
├── best/                    # best.pt detection results  
├── segmentasi/              # SAM2 segmentation results
├── hybrid/                  # Combined (detection + segmentation)
├── *.json                   # JSON files (best.pt only)
└── *-compare.png           # Compare visualization
```

#### **2. Enhanced File Naming Convention:**
- **Best only**: `(image_name)-(model_name)-best.png`
- **Detection/YOLO11**: `(image_name)-(model_name)-detection.png`
- **Segmentation**: `(image_name)-(model_name)-segmentation.png`
- **Hybrid**: `(image_name)-(model_name)-hybrid.png`
- **Compare**: `(image_name)-(model_name)-compare.png`
- **JSON**: `(image_name)-(model_name)-detection.json` (best.pt only)

#### **3. Optimized JSON Output:**
- ✅ **Hanya best.pt** yang menghasilkan JSON file
- ❌ **YOLO11m JSON dihapus** untuk efisiensi storage
- 📄 **Format JSON**: bbox, confidence, class_id, class_name

#### **4. Enhanced Brightness Preservation:**
- 🎨 **Alpha blending 0.3** (dari 0.5) untuk SAM2 segmentation
- 💡 **70% original image** + 30% mask overlay
- ✨ **Masking lebih terlihat** tanpa mengurangi kecerahan

#### **5. Compare Visualization:**
- 🖼️ **2x3 subplot** dengan semua hasil
- 📊 **Original + YOLO + Best + SAM + Hybrid** dalam 1 gambar
- 📁 **Disimpan di main output directory**

### **Usage:**
```bash
# Run Copy Version dengan enhanced features
cd direct
source venv/bin/activate
./scripts/run_fresh_integration_test-copy.sh

# Output akan tersimpan di:
# data/output/remote/(timestamp)/(user_id)/
```

### **Files:**
- `run_yolo_sam_integration-copy.py` - Enhanced integration script
- `visualize_results-copy.py` - Enhanced visualization script  
- `run_fresh_integration_test-copy.sh` - Enhanced test script

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

**Status**: ✅ **PRODUCTION READY**  
**Version**: 1.1.0-enhanced  
**Last Updated**: 27 September 2025

### **Recent Updates:**
- ✅ **Copy Version** dengan enhanced features
- ✅ **Optimized JSON output** (best.pt only)
- ✅ **Enhanced brightness preservation** untuk SAM2
- ✅ **Structured output directories** dengan subfolder organization
- ✅ **Compare visualization** dengan 2x3 subplot layout
- ✅ **Improved file naming convention** untuk better organization
