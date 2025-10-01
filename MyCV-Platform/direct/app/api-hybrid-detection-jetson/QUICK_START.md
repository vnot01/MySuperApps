# 🚀 Quick Start - MyCV-Platform RVM Integration

## 📁 Struktur Folder yang Sudah Dirapihkan

```
api-hybrid-detection-jetson/
├── 🚀 CORE FILES
│   ├── app.py                      # API utama dengan RVM integration
│   ├── run_rvm_api.py             # Runner dengan konfigurasi RVM
│   ├── run_api.sh                 # Script launcher Jetson
│   └── requirements.txt           # Dependencies
│
├── ⚙️ CONFIGURATION
│   ├── rvm_config.example         # Template konfigurasi
│   └── rvm_config.env            # Konfigurasi aktif
│
├── 📖 DOCUMENTATION
│   ├── README.md                  # Dokumentasi API utama
│   ├── README_RVM.md             # Dokumentasi RVM singkat
│   ├── README_ORGANIZED.md       # Struktur terorganisir
│   └── QUICK_START.md            # File ini
│
└── 📁 rvm-integration/           # FOLDER INTEGRASI RVM
    ├── setup/                    # 🛠️ Script setup otomatis
    ├── documentation/            # 📚 Dokumentasi lengkap
    ├── templates/               # 📋 Template MyRVM-Platform
    ├── examples/                # 🧪 Testing & contoh
    └── scripts/                 # 🔧 Script utilitas
```

## 🎯 File yang Harus Tersedia di MyRVM-Platform

Berdasarkan integrasi, **4 file utama** harus diimplementasikan di MyRVM-Platform:

### 1. 🗄️ Database Migration
**File**: `database/migrations/xxxx_xx_xx_xxxxxx_create_detection_results_table.php`
**Template**: `rvm-integration/templates/detection_results_migration.php`

### 2. 📊 Model
**File**: `app/Models/DetectionResult.php`
**Template**: `rvm-integration/templates/DetectionResult_model.php`

### 3. 🎮 Controller
**File**: `app/Http/Controllers/Api/RvmIntegrationController.php`
**Template**: `rvm-integration/templates/rvm_platform_endpoints_example.php`

### 4. 🛣️ Routes
**File**: `routes/api.php` (tambahkan routes)
**Template**: `rvm-integration/templates/rvm_routes_example.php`

## 🚀 Setup Cepat (3 Langkah)

### Step 1: Setup MyRVM-Platform
```bash
# Copy script setup
cp rvm-integration/setup/setup_rvm_platform.sh /path/to/MyRVM-Platform/
cd /path/to/MyRVM-Platform
chmod +x setup_rvm_platform.sh
./setup_rvm_platform.sh
```

### Step 2: Setup MyCV-Platform
```bash
# Setup direktori RVM
python3 rvm-integration/scripts/setup_rvm_directories.py 1,2,3

# Konfigurasi
cp rvm_config.example rvm_config.env
nano rvm_config.env  # Update RVM Platform URL dan API key

# Jalankan API
python3 run_rvm_api.py
```

### Step 3: Test Integrasi
```bash
# Test cepat
python3 rvm-integration/examples/test_rvm_integration.py

# Test lengkap
python3 rvm-integration/examples/test_full_integration.py
```

## 📋 Checklist Implementasi

### MyRVM-Platform (Dashboard)
- [ ] ✅ Database migration dijalankan
- [ ] ✅ Model DetectionResult dibuat
- [ ] ✅ Controller RvmIntegrationController dibuat
- [ ] ✅ Routes API ditambahkan
- [ ] ✅ RVM API keys dikonfigurasi

### MyCV-Platform (Jetson)
- [ ] ✅ Direktori RVM dibuat
- [ ] ✅ Konfigurasi RVM Platform diupdate
- [ ] ✅ API dengan RVM integration dijalankan
- [ ] ✅ Test integrasi berhasil

## 🔧 Konfigurasi Penting

### MyCV-Platform (`rvm_config.env`)
```bash
RVM_API_BASE_URL=http://100.123.143.87:8000/api
RVM_API_KEY=your_master_api_key_here
API_HOST=100.117.234.2
API_PORT=5000
```

### MyRVM-Platform
- Pastikan tabel `reverse_vending_machines` ada
- Generate API keys untuk setiap RVM
- Test endpoints setelah implementasi

## 📞 Troubleshooting

### Error 401 Unauthorized
- Cek API key RVM di database
- Pastikan RVM status aktif

### Error 403 Forbidden
- Verifikasi RVM ID sesuai dengan API key
- Cek permission akses

### Error Connection
- Cek URL RVM Platform
- Pastikan MyRVM-Platform running
- Cek network connectivity

## 📚 Dokumentasi Lengkap

- **Struktur Detail**: `README_ORGANIZED.md`
- **Panduan Teknis**: `rvm-integration/documentation/RVM_INTEGRATION.md`
- **Setup Guide**: `rvm-integration/documentation/RVM_PLATFORM_SETUP_GUIDE.md`
- **API Reference**: `README.md`

## 🎉 Selesai!

Setelah mengikuti 3 langkah di atas, integrasi MyCV-Platform dengan MyRVM-Platform sudah siap digunakan dengan dukungan multi-RVM yang aman dan terorganisir!
