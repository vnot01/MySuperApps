# MyCV-Platform RVM Integration

Folder ini berisi semua file yang diperlukan untuk integrasi MyCV-Platform dengan MyRVM-Platform.

## 📁 Struktur Folder

```
rvm-integration/
├── setup/                    # Script setup otomatis
│   ├── setup_rvm_platform.sh
│   └── setup_rvm_platform.py
├── documentation/            # Dokumentasi lengkap
│   ├── RVM_INTEGRATION.md
│   ├── README_RVM_INTEGRATION.md
│   ├── INTEGRATION_SUMMARY.md
│   └── RVM_PLATFORM_SETUP_GUIDE.md
├── templates/               # Template file untuk MyRVM-Platform
│   ├── detection_results_migration.php
│   ├── DetectionResult_model.php
│   ├── rvm_platform_endpoints_example.php
│   └── rvm_routes_example.php
├── examples/                # Contoh penggunaan dan testing
│   ├── test_rvm_integration.py
│   └── test_full_integration.py
├── scripts/                 # Script utilitas
│   └── setup_rvm_directories.py
└── README.md               # File ini
```

## 🚀 Quick Start

### 1. Setup MyRVM-Platform
```bash
# Copy script setup ke MyRVM-Platform
cp setup/setup_rvm_platform.sh /path/to/MyRVM-Platform/
cd /path/to/MyRVM-Platform
chmod +x setup_rvm_platform.sh
./setup_rvm_platform.sh
```

### 2. Setup MyCV-Platform
```bash
# Setup direktori RVM
python3 scripts/setup_rvm_directories.py 1,2,3

# Konfigurasi
cp rvm_config.example rvm_config.env
nano rvm_config.env

# Jalankan API
python3 run_rvm_api.py
```

### 3. Test Integrasi
```bash
# Test cepat
python3 examples/test_rvm_integration.py

# Test lengkap
python3 examples/test_full_integration.py
```

## 📚 Dokumentasi

- **RVM_INTEGRATION.md** - Panduan integrasi teknis
- **README_RVM_INTEGRATION.md** - Dokumentasi lengkap
- **INTEGRATION_SUMMARY.md** - Ringkasan implementasi
- **RVM_PLATFORM_SETUP_GUIDE.md** - Panduan setup MyRVM-Platform

## 🔧 Template Files

Semua file template untuk MyRVM-Platform tersedia di folder `templates/`:

1. **detection_results_migration.php** - Migration database
2. **DetectionResult_model.php** - Model Laravel
3. **rvm_platform_endpoints_example.php** - Controller API
4. **rvm_routes_example.php** - Routes API

## 🧪 Testing

Script testing tersedia di folder `examples/`:

- **test_rvm_integration.py** - Test integrasi dasar
- **test_full_integration.py** - Test integrasi lengkap

## 📞 Support

Untuk bantuan atau pertanyaan, lihat dokumentasi di folder `documentation/` atau hubungi tim support.
