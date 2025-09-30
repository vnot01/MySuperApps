# MyCV-Platform - Direct Execution

Folder ini berisi semua file yang bisa dijalankan **langsung di VM** tanpa Docker.

## 🚀 Quick Start

### 1. Setup Environment
```bash
cd direct
python3 -m venv venv
source venv/bin/activate
pip install -r requirements.txt
```

### 2. Install Models
```bash
./scripts/install_models.sh
```

### 3. Run Integration Test

#### Option A: Standard Version
```bash
./scripts/run_fresh_integration_test.sh
```

#### Option B: Copy Version (Enhanced Features) - **RECOMMENDED**
```bash
./scripts/run_fresh_integration_test-copy.sh
```

### 4. Web Application (Real-time Camera)
```bash
cd direct
source venv/bin/activate
cd MySuperApps/MyCV-Platform
./run_web.sh
```

## 📁 Struktur Folder

```
direct/
├── app/                    # Aplikasi utama
├── config/                 # Konfigurasi
├── data/                   # Data dan model
├── docs/                   # Dokumentasi
├── scripts/                # Script automation
├── venv/                   # Virtual environment
├── *.py                    # Python scripts
├── requirements.txt        # Dependencies
└── README.md              # Dokumentasi ini
```

## 🧪 Testing Scripts

### Standard Scripts
- `run_fresh_integration_test.sh` - Test lengkap YOLO + SAM2 (standard)
- `run_yolo11m_test.sh` - Test YOLO11m saja
- `run_best_pt_test.sh` - Test best.pt saja
- `create_backup.sh` - Buat backup project
- `clean_models.sh` - Hapus model yang didownload

### Enhanced Scripts (Copy Version)
- `run_fresh_integration_test-copy.sh` - **Test lengkap dengan enhanced features**
- `run_yolo_sam_integration-copy.py` - Enhanced integration script
- `visualize_results-copy.py` - Enhanced visualization script

### API Scripts
- `run_api_hybrid_detection.sh` - **API detection script dengan integrated visualization**
- `run_api_hybrid_detection.py` - **Python script untuk API processing**
- `run_test_hybrid_integration.sh` - Test script untuk hybrid integration

### Web Application
- `run_web.sh` - Real-time camera detection

## ✅ Status

- ✅ **GPU Support**: Berfungsi dengan baik
- ✅ **NVIDIA Driver**: Terdeteksi dan berjalan
- ✅ **Virtual Environment**: Siap digunakan
- ✅ **All Scripts**: Siap dijalankan
- ✅ **Copy Version**: Enhanced features ready
- ✅ **API Hybrid Detection**: RESTful API siap digunakan
- ✅ **Public Access**: API accessible di http://100.98.142.94:5000

## 🎯 Keunggulan

- **Performance Optimal**: Tanpa overhead Docker
- **GPU Access**: Langsung akses ke GPU
- **Simple Setup**: Langsung jalankan script
- **Production Ready**: Siap untuk production
- **RESTful API**: Public API untuk akses eksternal
- **Background Processing**: Processing berjalan di background
- **Session Management**: Setiap request mendapat session ID unik

## 🆕 Enhanced Features (Copy Version)

### **Structured Output Directories:**
```
data/output/remote/(timestamp)/(user_id)/
├── yolo/                    # YOLO11m detection results
├── best/                    # best.pt detection results  
├── segmentasi/              # SAM2 segmentation results
├── hybrid/                  # Combined (detection + segmentation)
├── *.json                   # JSON files (best.pt only)
└── *-compare.png           # Compare visualization
```

### **Key Improvements:**
- ✅ **Optimized JSON Output**: Hanya best.pt yang menghasilkan JSON
- ✅ **Enhanced Brightness**: Alpha blending 0.3 untuk SAM2 segmentation
- ✅ **Compare Visualization**: Gabungan semua hasil dalam 1 gambar
- ✅ **Dynamic Directory Structure**: Timestamp/user_id organization
- ✅ **Improved File Naming**: Model-specific naming convention

### **File Naming Convention:**
- **Best only**: `(image_name)-(model_name)-best.png`
- **Detection/YOLO11**: `(image_name)-(model_name)-detection.png`
- **Segmentation**: `(image_name)-(model_name)-segmentation.png`
- **Hybrid**: `(image_name)-(model_name)-hybrid.png`
- **Compare**: `(image_name)-(model_name)-compare.png`
- **JSON**: `(image_name)-(model_name)-detection.json` (best.pt only)

## 🚀 API Hybrid Detection

### **RESTful API untuk Public Access**
- **URL**: http://100.98.142.94:5000
- **Location**: `./app/api-hybrid-detection/`
- **Features**: Upload, processing, download, history

### **API Endpoints:**
- `GET /api/health` - Health check
- `GET /api/status` - API status dengan GPU information
- `POST /api/upload` - Upload images untuk deteksi
- `GET /api/process/<session_id>` - Status pemrosesan
- `GET /api/results/<session_id>` - Hasil deteksi dari summary.json
- `GET /api/download/<session_id>/<filename>` - Download file hasil
- `GET /api/backup/<session_id>` - Download TAR.GZ backup hasil satu sesi
- `GET /api/detections` - Semua deteksi terbaru

### **Cara Menjalankan API:**
```bash
# Option 1: Dari root directory
cd /home/my/MySuperApps/MyCV-Platform
./run_api.sh

# Option 2: Dari API directory
cd /home/my/MySuperApps/MyCV-Platform/direct/app/api-hybrid-detection
./run_api.sh
```

### **Contoh Upload:**
```bash
curl -X POST \
  -F 'files=@image.jpg' \
  -F 'user_id=my_user' \
  http://100.98.142.94:5000/api/upload
```

### **API Features:**
- ✅ **Multi-file Upload**: Upload multiple images sekaligus
- ✅ **Background Processing**: Processing berjalan di background
- ✅ **Session Management**: Setiap upload mendapat session ID unik
- ✅ **Real-time Status**: Check status processing real-time
- ✅ **File Download**: Download hasil visualisasi
- ✅ **Detection History**: Lihat semua deteksi terbaru
- ✅ **CORS Support**: Support untuk web applications
- ✅ **Error Handling**: Comprehensive error handling
- ✅ **Summary JSON**: Structured results dengan detection_summary, class_summary, object_count
- ✅ **Image URLs**: Direct download links untuk semua jenis visualisasi (best, yolo, sam, hybrid)
- ✅ **Compare Visualization**: Summary image yang menggabungkan semua hasil
- ✅ **GPU Detection**: Real-time GPU information dengan memory details
- ✅ **Backup Per-Session**: Endpoint `GET /api/backup/<session_id>` untuk mengunduh seluruh hasil dalam satu arsip TAR.GZ

### **API Documentation:**
- **README**: `./app/api-hybrid-detection/README.md`
- **Requirements**: `./app/api-hybrid-detection/requirements.txt`
- **Launcher**: `./app/api-hybrid-detection/run_api.sh`

## 🚀 Edge Devices (NVIDIA Jetson)

### **Jetson Integration:**
- **Target Devices**: NVIDIA Jetson Orin Nano, Orin NX, Orin AGX
- **Script**: `./scripts/run_test_hybrid_integration-jetson.sh`
- **Data Directory**: `data-jetson/` (separate from main data)
- **API URL**: `http://100.117.234.2:5000`

### **Quick Start for Jetson:**
```bash
# Run Jetson-specific integration test
./scripts/run_test_hybrid_integration-jetson.sh
```

### **Features:**
- ✅ **PyTorch 2.5.0** optimized for Jetson Platform 6.1
- ✅ **CUDA Support** dengan automatic verification
- ✅ **Memory Optimization** dengan 16GB swap configuration
- ✅ **Edge-Specific Paths** menggunakan `data-jetson/` directory
- ✅ **Auto-Installation** PyTorch jika tidak terdeteksi

### **Requirements:**
- **Hardware**: NVIDIA Jetson Orin (Nano/NX/AGX)
- **Software**: Ubuntu 22.04, Jetpack 6.1, L4T 36.4.2
- **Memory**: 16GB swap file configuration
- **Network**: Connectivity ke API server

**📖 Detailed Guide:** [README-Edge.md](../README-Edge.md)