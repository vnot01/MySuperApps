# 🤖 **02_EDGE - MyCV-Platform**

## 📋 **Overview**

Edge computing platform untuk computer vision yang terdiri dari:

1. **Jetson Orin Integration** - Edge device di setiap RVM
2. **CV Server with GPU** - Centralized GPU server untuk heavy processing

## 🏗️ **Architecture**

### **Jetson Orin (RVM Edge)**
- **IP**: `100.117.234.2`
- **Technology**: Python + Flask + AI Models
- **Models**: YOLO11 + SAM2
- **Function**: Real-time object detection dan segmentation

### **CV Server with GPU (CV Host)**
- **IP**: `100.98.142.94`
- **Technology**: Python + Flask + CUDA
- **GPU**: NVIDIA GPU untuk heavy processing
- **Function**: Batch processing dan model training

## 🌐 **Network Configuration**

- **Jetson IP**: `100.117.234.2:5000`
- **CV Server IP**: `100.98.142.94:5000`
- **VPN**: Tailscale network untuk secure communication
- **API Endpoints**: Health check, status, hardware info

## 📁 **Documentation Structure**

### **Requirements**
- **MyCV_Platform_Requirements.md** - Complete requirements untuk MyCV-Platform (PRODUCTION READY)
- Hardware requirements
- Software dependencies
- Model specifications
- API requirements

### **To-Do**
- Tasks yang perlu dikerjakan
- Model updates
- Performance optimizations

### **Implementation**
- Setup guides
- Model deployment
- API integration
- Troubleshooting

### **Done**
- Completed features
- Deployed models
- Tested functionality

## 🚀 **Quick Start**

### **Jetson Orin Setup**
```bash
# Navigate to Jetson implementation
cd MyRVM-Ecosystem/02_EDGE/jetson

# Install dependencies
pip install -r requirements.txt

# Run API server
python app.py
```

### **CV Server Setup**
```bash
# Navigate to CV server
cd MyRVM-Ecosystem/02_EDGE/cv-server

# Install dependencies
pip install -r requirements.txt

# Run GPU server
python server.py
```

## 📊 **Current Status**

- **Jetson Integration**: ✅ Active Development
- **CV Server**: ✅ Active Development
- **AI Models**: ✅ YOLO11 + SAM2
- **API**: ✅ RESTful APIs
- **Health Monitoring**: ✅ Implemented

---

**Last Updated**: 2025-01-23  
**Version**: 2.0  
**Status**: Active Development