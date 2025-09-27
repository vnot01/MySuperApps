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

### Web Application
- `run_web.sh` - Real-time camera detection

## ✅ Status

- ✅ **GPU Support**: Berfungsi dengan baik
- ✅ **NVIDIA Driver**: Terdeteksi dan berjalan
- ✅ **Virtual Environment**: Siap digunakan
- ✅ **All Scripts**: Siap dijalankan
- ✅ **Copy Version**: Enhanced features ready

## 🎯 Keunggulan

- **Performance Optimal**: Tanpa overhead Docker
- **GPU Access**: Langsung akses ke GPU
- **Simple Setup**: Langsung jalankan script
- **Production Ready**: Siap untuk production

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