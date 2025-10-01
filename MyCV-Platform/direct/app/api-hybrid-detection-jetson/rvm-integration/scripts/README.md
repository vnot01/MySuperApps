# Script Utilitas

Folder ini berisi script utilitas untuk setup dan maintenance.

## 📁 File

- **setup_rvm_directories.py** - Script untuk membuat struktur direktori RVM

## 🚀 Penggunaan

### Setup Direktori RVM
```bash
# Setup untuk RVM ID 1, 2, 3
python3 setup_rvm_directories.py 1,2,3

# Setup untuk RVM ID custom
python3 setup_rvm_directories.py 10,20,30
```

## 📋 Yang Dilakukan Script

1. ✅ Buat struktur direktori RVM
2. ✅ Buat direktori legacy untuk backward compatibility
3. ✅ Buat direktori model
4. ✅ Generate file konfigurasi
5. ✅ Tampilkan struktur direktori

## 📁 Struktur yang Dibuat

```
data-jetson/
├── input/
│   ├── rvm_1/, rvm_2/, rvm_3/    # RVM-specific input
│   └── legacy/                    # Backward compatibility
├── output/
│   ├── rvm_1/, rvm_2/, rvm_3/    # RVM-specific output
│   │   ├── yolo/, best/, segmentasi/, hybrid/
│   └── legacy/                    # Backward compatibility
└── models/                        # Shared AI models
    ├── yolo/active/
    ├── trained/active/
    └── sam/active/
```

## 🔧 Konfigurasi

Script akan membuat:
- `rvm_config.env` - File konfigurasi
- `README_RVM.md` - Dokumentasi setup

## ⚠️ Prerequisites

- Python 3.x
- Permission untuk membuat direktori
- MyCV-Platform sudah terinstall
