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
```bash
./scripts/run_fresh_integration_test.sh
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

- `run_fresh_integration_test.sh` - Test lengkap YOLO + SAM2
- `run_yolo11m_test.sh` - Test YOLO11m saja
- `run_best_pt_test.sh` - Test best.pt saja
- `create_backup.sh` - Buat backup project
- `clean_models.sh` - Hapus model yang didownload

## ✅ Status

- ✅ **GPU Support**: Berfungsi dengan baik
- ✅ **NVIDIA Driver**: Terdeteksi dan berjalan
- ✅ **Virtual Environment**: Siap digunakan
- ✅ **All Scripts**: Siap dijalankan

## 🎯 Keunggulan

- **Performance Optimal**: Tanpa overhead Docker
- **GPU Access**: Langsung akses ke GPU
- **Simple Setup**: Langsung jalankan script
- **Production Ready**: Siap untuk production