# MyCV-Platform Architecture

## 🏗️ **System Overview**

MyCV-Platform adalah sistem computer vision yang terdiri dari tiga service utama yang dapat berjalan secara independen atau terintegrasi:

### **1. Web Interface (`app/web/`)**
- **Framework**: Flask
- **Purpose**: Real-time camera detection dan image upload
- **Target**: End users, testing, demonstration
- **Features**: Upload images, view results, download files

### **2. GPU Server API (`app/api-hybrid-detection/`)**
- **Framework**: FastAPI
- **Purpose**: High-performance detection API untuk powerful computers
- **Target**: Production servers, high-throughput processing
- **Features**: RESTful API, background processing, file management

### **3. Jetson API (`app/api-hybrid-detection-jetson/`)**
- **Framework**: Flask
- **Purpose**: Edge device detection dengan RVM integration
- **Target**: NVIDIA Jetson devices, reverse vending machines
- **Features**: Multi-Jetson support, RVM authentication, database integration

## 🔧 **Shared Components**

### **Virtual Environment**
- **Location**: `venv/`
- **Purpose**: Shared Python environment untuk semua services
- **Dependencies**: `requirements.txt` (unified)

### **Setup Script**
- **File**: `setup.sh`
- **Purpose**: Cross-platform environment setup
- **Features**: Python detection, virtual environment creation, dependency installation

## 🔗 **RVM Integration Architecture**

### **Multi-Jetson Support**
```
MyRVM-Platform (Dashboard)
├── PostgreSQL Database
├── MinIO Object Storage
├── Redis Cache
└── Reverb WebSocket

MyCV-Platform (Jetson API)
├── RVM Authentication
├── Detection Processing
├── Data Storage
└── API Communication
```

### **Data Flow**
1. **RVM Authentication**: Jetson API validates dengan MyRVM-Platform
2. **Image Upload**: Images uploaded ke Jetson API
3. **Processing**: Detection dan segmentation di Jetson
4. **Storage**: Results stored di RVM-specific directories
5. **Database**: Detection results sent ke MyRVM-Platform database
6. **Caching**: RVM data cached untuk performance

## 📁 **Directory Structure**

```
direct/
├── app/                           # Services
│   ├── web/                       # Web Interface
│   ├── api-hybrid-detection/      # GPU Server API
│   └── api-hybrid-detection-jetson/ # Jetson API + RVM Integration
├── venv/                          # Shared virtual environment
├── requirements.txt               # Unified dependencies
├── setup.sh                      # Setup script
└── docs/                         # Documentation
```

## 🚀 **Deployment Options**

### **Option 1: Single Service**
- Deploy hanya satu service sesuai kebutuhan
- Web Interface untuk testing
- GPU Server untuk production
- Jetson API untuk edge devices

### **Option 2: Multi-Service**
- Deploy multiple services pada server yang sama
- Gunakan port yang berbeda
- Shared virtual environment

### **Option 3: Distributed**
- Web Interface di frontend server
- GPU Server di processing server
- Jetson API di edge devices
- MyRVM-Platform di central server

## 🔧 **Configuration Management**

### **Environment Variables**
```bash
# RVM Integration
RVM_API_BASE_URL=http://100.123.143.87:8000/api
RVM_API_KEY=your_master_api_key_here
API_HOST=100.117.234.2
API_PORT=5000

# GPU Configuration
CUDA_VISIBLE_DEVICES=0
```

### **Service-Specific Config**
- **Web Interface**: `app/web/config.py`
- **GPU Server**: `app/api-hybrid-detection/config.py`
- **Jetson API**: `app/api-hybrid-detection-jetson/rvm_config.env`

## 📊 **Performance Characteristics**

### **Web Interface**
- **CPU Usage**: Low
- **Memory**: ~500MB
- **Concurrent Users**: 10-50
- **Response Time**: <1 second

### **GPU Server API**
- **GPU Usage**: High
- **Memory**: ~2-4GB
- **Concurrent Requests**: 5-20
- **Processing Time**: 2-5 seconds per image

### **Jetson API**
- **GPU Usage**: Medium
- **Memory**: ~1-2GB
- **Concurrent Requests**: 2-10
- **Processing Time**: 3-8 seconds per image

## 🔒 **Security Considerations**

### **RVM Authentication**
- API key-based authentication
- In-memory caching untuk performance
- Private IP addressing untuk security

### **Data Privacy**
- RVM-specific directory structure
- Encrypted communication dengan MyRVM-Platform
- Secure file storage

## 🎯 **Best Practices**

1. **Use Virtual Environment**: Selalu aktifkan virtual environment
2. **Configure Properly**: Setup environment variables sesuai kebutuhan
3. **Monitor Performance**: Monitor GPU memory dan CPU usage
4. **Backup Data**: Regular backup detection results
5. **Update Dependencies**: Keep requirements.txt updated
6. **Test Integration**: Test RVM integration sebelum production

---

**Status**: ✅ **IMPLEMENTED**  
**Version**: 1.0.0  
**Last Updated**: 15 January 2025

