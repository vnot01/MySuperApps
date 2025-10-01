# MyCV-Platform - Direct Execution

Folder ini berisi semua file yang bisa dijalankan **langsung di VM** tanpa Docker.

## 🚀 Quick Start

### 1. Setup Environment
```bash
cd direct
./setup.sh
```

### 2. Run Services

#### Web Interface
```bash
source venv/bin/activate
cd app/web && python app.py
```

#### GPU Server (Powerful Computer)
```bash
source venv/bin/activate
cd app/api-hybrid-detection && python app.py
```

#### Jetson API (Edge Device)
```bash
source venv/bin/activate
cd app/api-hybrid-detection-jetson && python app.py
```

## 📁 Struktur Folder

```
direct/
├── app/                           # Aplikasi utama
│   ├── web/                       # Web Interface (Flask)
│   ├── api-hybrid-detection/      # GPU Server API (FastAPI)
│   └── api-hybrid-detection-jetson/ # Jetson API (Flask + RVM Integration)
├── venv/                          # Virtual environment (shared)
├── requirements.txt               # Dependencies (unified)
├── setup.sh                      # Setup script
└── README.md                     # Dokumentasi ini
```

## 🎯 Services Overview

### 1. Web Interface (`app/web/`)
- **Framework**: Flask
- **Purpose**: Real-time camera detection
- **Features**: Upload images, view results, download files
- **Port**: 5000 (default)

### 2. GPU Server API (`app/api-hybrid-detection/`)
- **Framework**: FastAPI
- **Purpose**: High-performance detection API
- **Features**: RESTful API, background processing, file management
- **Port**: 5000 (default)

### 3. Jetson API (`app/api-hybrid-detection-jetson/`)
- **Framework**: Flask
- **Purpose**: Edge device detection with RVM integration
- **Features**: Multi-Jetson support, RVM authentication, database integration, hardware monitoring
- **Port**: 5000 (default)
- **Hardware Info**: `/api/hardware` endpoint for comprehensive Jetson hardware information

## ✅ Features

- ✅ **Unified Dependencies**: Satu requirements.txt untuk semua services
- ✅ **Cross-Platform Setup**: Setup script yang bekerja di semua environment
- ✅ **RVM Integration**: Multi-Jetson support dengan authentication
- ✅ **GPU Support**: Optimized untuk NVIDIA GPU
- ✅ **Hardware Monitoring**: Comprehensive Jetson hardware information
- ✅ **Virtual Environment**: Shared virtual environment
- ✅ **Production Ready**: Siap untuk deployment

## 🎯 Keunggulan

- **🧹 Clean Architecture**: Struktur folder yang terorganisir
- **📦 Unified Dependencies**: Satu requirements.txt untuk semua
- **🔧 Simple Setup**: Setup sekali, run di mana saja
- **🚀 Multi-Environment**: Web, GPU Server, Jetson API
- **🔗 RVM Integration**: Support multiple Jetson machines
- **⚡ Performance**: Optimized untuk setiap environment

## 🔗 RVM Integration

### **Multi-Jetson Support:**
- **Authentication**: API key-based authentication untuk setiap RVM
- **Directory Structure**: `data-jetson/input/rvm_{rvm_id}/{timestamp}/{user_id}/`
- **Database Integration**: Detection results tersimpan di MyRVM-Platform database
- **Caching**: In-memory cache untuk RVM data dan validation

### **RVM API Endpoints:**
- `POST /api/upload` - Upload images dengan RVM authentication
- `GET /api/detections` - List detections dengan RVM filter
- `POST /api/detections/search` - Search detections dengan RVM filter
- `POST /api/rvm/validate` - Validate RVM API key
- `GET /api/rvm/{rvm_id}/stats` - Get RVM statistics

### **Configuration:**
- **RVM API Base URL**: `http://100.123.143.87:8000/api`
- **API Host**: `100.117.234.2` (private IP untuk security)
- **Cache TTL**: 300 seconds

## 📚 Documentation

### **Service Documentation:**
- **Web Interface**: `app/web/README.md`
- **GPU Server API**: `app/api-hybrid-detection/README.md`
- **Jetson API**: `app/api-hybrid-detection-jetson/README.md`

### **RVM Integration:**
- **Quick Start**: `app/api-hybrid-detection-jetson/QUICK_START.md`
- **RVM Integration**: `app/api-hybrid-detection-jetson/rvm-integration/README.md`
- **Setup Guide**: `app/api-hybrid-detection-jetson/rvm-integration/documentation/RVM_PLATFORM_SETUP_GUIDE.md`

## 🔧 Configuration

### **Environment Variables:**
```bash
# RVM Integration (Jetson API)
RVM_API_BASE_URL=http://100.123.143.87:8000/api
RVM_API_KEY=your_master_api_key_here
API_HOST=100.117.234.2
API_PORT=5000

# GPU Configuration
CUDA_VISIBLE_DEVICES=0
```

### **Setup Script:**
```bash
# Cross-platform setup
./setup.sh
```

## 🎯 Next Steps

1. **Setup Environment**: Run `./setup.sh`
2. **Choose Service**: Select Web, GPU Server, or Jetson API
3. **Configure RVM**: Setup MyRVM-Platform integration (if using Jetson API)
4. **Deploy**: Deploy to production environment