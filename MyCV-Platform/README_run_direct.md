# 🚀 MyCV-Platform Direct Execution

## 📋 Overview

Script `run_direct.sh` adalah launcher utama untuk menjalankan MyCV-Platform secara langsung di **Server/Computer** dengan **GPU Computing** tanpa menggunakan Docker. Script ini mengatur virtual environment, menginstall dependencies, dan menjalankan fresh integration test dengan YOLO + SAM2.

**Target Environment**: Server/Computer dengan GPU Computing capabilities  
**Purpose**: High-performance computer vision processing untuk production server  
**Integration**: MyRVM-Platform (VM 100) via API

## 🔗 File Location

**Script:** [`./run_direct.sh`](./run_direct.sh)

## 🎯 Fungsi Utama

1. **Environment Setup** - Membuat dan mengaktifkan virtual environment untuk server
2. **Dependency Management** - Menginstall semua dependencies yang diperlukan untuk GPU computing
3. **GPU Detection** - Otomatis deteksi dan konfigurasi GPU untuk optimal performance
4. **Integration Testing** - Menjalankan fresh integration test dengan YOLO + SAM2
5. **Output Management** - Menyimpan hasil di folder `data/output/` dengan struktur terorganisir
6. **Performance Optimization** - Optimasi untuk server environment dengan resource yang memadai

## 📁 Dependencies

### Folder yang Diperlukan:
- ✅ `direct/` - Folder utama aplikasi
- ✅ `direct/venv/` - Virtual environment (dibuat otomatis)
- ✅ `direct/scripts/` - Folder scripts

### File yang Diperlukan:
- ✅ `direct/requirements.txt` - Python dependencies
- ✅ `direct/scripts/run_fresh_integration_test.sh` - Integration test script

## 🚀 Cara Penggunaan

### 1. Persiapan
```bash
# Pastikan berada di root directory MyCV-Platform
cd /path/to/MyCV-Platform

# Pastikan script executable
chmod +x run_direct.sh
```

### 2. Menjalankan
```bash
# Jalankan script
./run_direct.sh
```

### 3. Output
Script akan:
- Membuat virtual environment jika belum ada
- Menginstall dependencies
- Menjalankan fresh integration test
- Menyimpan hasil di `direct/data/output/`

## 📊 Workflow

```mermaid
graph TD
    A[Start run_direct.sh] --> B{Cek folder direct/}
    B -->|Ada| C[Masuk ke folder direct/]
    B -->|Tidak ada| D[❌ Error: Folder tidak ditemukan]
    
    C --> E{Cek virtual environment}
    E -->|Ada| F[Aktifkan venv]
    E -->|Tidak ada| G[Buat venv baru]
    
    G --> H[Install dependencies]
    F --> I{Install missing deps}
    I --> J[Install termcolor]
    H --> J
    
    J --> K[Jalankan run_fresh_integration_test.sh]
    K --> L[✅ Selesai - Cek data/output/]
```

## 🔧 Konfigurasi

### Virtual Environment
- **Path:** `direct/venv/`
- **Python Version:** Python 3.x
- **Auto-create:** Ya, jika belum ada

### Dependencies
- **Main:** `direct/requirements.txt`
- **Additional:** `termcolor` (auto-install)

### Test Script
- **Script:** `direct/scripts/run_fresh_integration_test.sh`
- **Type:** Standard Version (YOLO + SAM2)

## 📂 Output Structure

Setelah menjalankan script, hasil akan tersimpan di:

```
direct/data/output/
├── integration_results/     # Hasil deteksi dan segmentasi
├── visualizations/          # File visualisasi PNG
└── remote/                  # Output Copy Version (jika ada)
```

## ⚠️ Troubleshooting

### Error: Folder 'direct' tidak ditemukan
```bash
# Pastikan berada di root MyCV-Platform
pwd
# Output harus: /path/to/MyCV-Platform
```

### Error: Virtual environment tidak aktif
```bash
# Script akan membuat venv otomatis
# Jika masih error, hapus dan buat ulang:
rm -rf direct/venv
./run_direct.sh
```

### Error: Dependencies tidak terinstall
```bash
# Script akan install otomatis
# Jika masih error, install manual:
cd direct
source venv/bin/activate
pip install -r requirements.txt
pip install termcolor
```

## 🔄 Related Scripts

- **Standard Version:** [`run_fresh_integration_test.sh`](./direct/scripts/run_fresh_integration_test.sh)
- **Enhanced Version:** [`run_fresh_integration_test-copy.sh`](./direct/scripts/run_fresh_integration_test-copy.sh)
- **Web Application:** [`run_web.sh`](./run_web.sh)
- **Docker Testing:** [`run_docker.sh`](./run_docker.sh)

## 📋 Requirements

### System Requirements:
- ✅ Linux/Unix system
- ✅ Python 3.x
- ✅ pip package manager
- ✅ Git (untuk model download)

### Hardware Requirements:
- ✅ **CPU**: Multi-core recommended (8+ cores untuk optimal performance)
- ✅ **RAM**: 16GB+ recommended (32GB+ untuk production)
- ✅ **Storage**: 50GB+ free space (untuk models dan data)
- ✅ **GPU**: **REQUIRED** - NVIDIA GPU dengan CUDA support (RTX 3060+ atau Tesla V100+)
- ✅ **Network**: Stable internet connection untuk model download

## 🎯 Use Cases

1. **Production Server Deployment** - High-performance server dengan GPU computing
2. **Development Environment** - Development setup untuk server development
3. **CI/CD Pipeline** - Automated testing di server environment
4. **GPU Computing** - Optimal performance untuk computer vision tasks
5. **API Server** - Backend processing untuk web applications
6. **Batch Processing** - Large-scale image processing dengan GPU acceleration

## 🔄 Perbedaan dengan Edge Devices

### **Server/GPU Computing (README_run_direct.md):**
- ✅ **High Performance**: Resource unlimited, optimal GPU utilization
- ✅ **Large Models**: Support untuk model besar (YOLO11x, SAM2.1_l)
- ✅ **Batch Processing**: Concurrent processing multiple images
- ✅ **Production Ready**: Stable, reliable untuk production server
- ✅ **API Integration**: Full REST API dengan background processing
- ✅ **Resource Intensive**: Memory dan storage tidak terbatas

### **Edge Devices (README-Edge.md):**
- ⚠️ **Resource Constrained**: Memory dan storage terbatas
- ⚠️ **Optimized Models**: Model size terbatas untuk edge constraints
- ⚠️ **Single Processing**: Sequential processing untuk memory efficiency
- ⚠️ **Edge Optimized**: PyTorch 2.5.0 khusus Jetson Platform 6.1
- ⚠️ **Independent Operation**: Tidak bergantung pada main server

## 📚 Documentation

- **Main README:** [`README.md`](./README.md)
- **Direct Folder:** [`direct/README.md`](./direct/README.md)
- **Scripts Guide:** [`direct/scripts/`](./direct/scripts/)
- **Edge Devices:** [`README-Edge.md`](./README-Edge.md) - Khusus untuk Edge Devices

## 🔗 Quick Links

- **Script File:** [`run_direct.sh`](./run_direct.sh)
- **Integration Test:** [`direct/scripts/run_fresh_integration_test.sh`](./direct/scripts/run_fresh_integration_test.sh)
- **Requirements:** [`direct/requirements.txt`](./direct/requirements.txt)
- **Output Folder:** [`direct/data/output/`](./direct/data/output/)

---

**Last Updated:** September 27, 2025  
**Version:** 1.1.0  
**Status:** ✅ Production Ready
