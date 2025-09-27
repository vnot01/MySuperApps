# MyCV-Platform

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
cd direct
source venv/bin/activate
./scripts/run_fresh_integration_test.sh
```

### Option 2: Web Application (Real-time Camera)
```bash
./run_web.sh
```

### Option 3: Docker CPU Testing
```bash
cd docker
docker-compose -f docker-compose.cpu.yml up --build
```

## 📊 Perbandingan

| Feature | Direct | Web App | Docker GPU | Docker CPU |
|---------|--------|---------|------------|------------|
| **GPU Support** | ✅ | ✅ | ❌ | ❌ |
| **Real-time** | ❌ | ✅ | ❌ | ❌ |
| **Performance** | ⭐⭐⭐ | ⭐⭐⭐ | N/A | ⭐ |
| **Setup** | ⭐⭐⭐ | ⭐⭐⭐ | ❌ | ⭐⭐ |
| **Production** | ✅ | ✅ | ❌ | ❌ |
| **Development** | ✅ | ✅ | ❌ | ✅ |

## 🎯 Rekomendasi

**Untuk Production**: Gunakan folder `direct/` atau `run_web.sh`
**Untuk Real-time**: Gunakan `run_web.sh` untuk deteksi kamera
**Untuk Development**: Gunakan folder `direct/` atau `docker/` (CPU-only)

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

### Docker Issues
- Docker GPU bermasalah karena bug NVIDIA Container Toolkit
- Gunakan Docker CPU untuk testing: `docker-compose -f docker-compose.cpu.yml up`

## 📞 Support

Jika ada masalah, cek dokumentasi di masing-masing folder atau lihat [Testing Guide](docs/TESTING_GUIDE.md).