# Setup Scripts

Folder ini berisi script untuk setup otomatis integrasi RVM.

## 📁 File

- **setup_rvm_platform.sh** - Script bash untuk setup MyRVM-Platform
- **setup_rvm_platform.py** - Script Python untuk generate setup files

## 🚀 Penggunaan

### Setup MyRVM-Platform (Otomatis)
```bash
# Copy script ke MyRVM-Platform root directory
cp setup_rvm_platform.sh /path/to/MyRVM-Platform/
cd /path/to/MyRVM-Platform
chmod +x setup_rvm_platform.sh
./setup_rvm_platform.sh
```

### Generate Setup Files
```bash
# Generate script setup baru
python3 setup_rvm_platform.py
```

## 📋 Yang Dilakukan Script

1. ✅ Cek apakah di Laravel project
2. ✅ Buat direktori yang diperlukan
3. ✅ Copy file template dari MyCV-Platform
4. ✅ Tambahkan routes ke api.php
5. ✅ Jalankan migration database
6. ✅ Tampilkan instruksi selanjutnya

## ⚠️ Prerequisites

- MyRVM-Platform sudah terinstall
- Laravel artisan tersedia
- Database sudah dikonfigurasi
- File template tersedia di MyCV-Platform
