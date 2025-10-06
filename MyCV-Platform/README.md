# MyCV-Platform

## 🎯 **Computer Vision Processing Service**

**VM**: 102 (cv-host)  
**Purpose**: Real YOLO11 + SAM2 processing untuk production  
**Integration**: MyRVM-Platform (VM 100)  
**Features**: Computer Vision integration, Live streaming, Real-time processing

Platform Computer Vision dengan YOLO + SAM2 Integration dan **Computer Vision integration** untuk real-time processing.

## 📁 Struktur Project

Project ini fokus pada **Direct Execution** untuk production deployment:

### 📂 `direct/` - Main Application
**Untuk menjalankan langsung di Server/Computer dengan GPU Computing**

- ✅ **GPU Support**: Full CUDA support
- ✅ **Performance**: Optimal untuk production
- ✅ **Production Ready**: Siap untuk production deployment
- ✅ **API Integration**: RESTful API untuk external access
- ✅ **Edge Support**: NVIDIA Jetson integration
- ✅ **Computer Vision**: Real-time CV processing dengan live streaming
- ✅ **Live Streaming**: Multiple streaming modes (MJPEG, Base64, WebSocket, WebRTC)
- ✅ **CV Models**: YOLO v8, SAM2, custom models support

## 🚀 Quick Start

### Option 1: Direct Execution (Recommended)
```bash
cd MySuperApps/MyCV-Platform
./run_direct.sh
```

**📖 Detailed Guide:** [README_run_direct.md](./README_run_direct.md) - Khusus untuk Server/Computer dengan GPU Computing

### Option 2: Web Application (Real-time Camera)
```bash
cd MySuperApps/MyCV-Platform
./run_web.sh
```

### Option 3: API Hybrid Detection (Public Access)
```bash
cd MySuperApps/MyCV-Platform
./run_api.sh
```

**📖 API Documentation:** [API README](./direct/app/api-hybrid-detection/README.md)

### Option 4: Edge Devices (NVIDIA Jetson)
```bash
cd MySuperApps/MyCV-Platform/direct
./scripts/run_test_hybrid_integration-jetson.sh
```

**📖 Jetson Documentation:** [README-Edge.md](./README-Edge.md) - Khusus untuk NVIDIA Jetson dan Edge Devices

### Option 5: API Service Manager (Background + Auto-Start)
```bash
cd MySuperApps/MyCV-Platform
./api_service.sh start    # Start API in background
./api_service.sh stop     # Stop API
./api_service.sh status   # Check status
./api_service.sh logs     # View logs
```

**🔧 Auto-Start Setup:**
```bash
# Setup auto-start on boot
echo "@reboot /home/my/MySuperApps/MyCV-Platform/auto_start_api.sh" | crontab -
```

## 📊 Perbandingan

| Feature | Direct | Web App | API | Service Manager | Jetson |
|---------|--------|---------|-----|----------------|--------|
| **GPU Support** | ✅ | ✅ | ✅ | ✅ | ✅ |
| **Real-time** | ❌ | ✅ | ❌ | ❌ | ✅ |
| **Public Access** | ❌ | ❌ | ✅ | ✅ | ❌ |
| **Background Running** | ❌ | ❌ | ❌ | ✅ | ❌ |
| **Auto-Start on Boot** | ❌ | ❌ | ❌ | ✅ | ❌ |
| **Edge Device** | ❌ | ❌ | ❌ | ❌ | ✅ |
| **Computer Vision** | ❌ | ✅ | ❌ | ❌ | ✅ |
| **Live Streaming** | ❌ | ✅ | ❌ | ❌ | ✅ |
| **Performance** | ⭐⭐⭐ | ⭐⭐⭐ | ⭐⭐⭐ | ⭐⭐⭐ | ⭐⭐ |
| **Setup** | ⭐⭐⭐ | ⭐⭐⭐ | ⭐⭐⭐ | ⭐⭐⭐ | ⭐⭐ |
| **Production** | ✅ | ✅ | ✅ | ✅ | ✅ |
| **RESTful API** | ❌ | ❌ | ✅ | ✅ | ✅ |
| **Background Processing** | ❌ | ❌ | ✅ | ✅ | ✅ |

## 🎯 Rekomendasi

**Untuk Production Server**: Gunakan `./run_direct.sh` untuk server dengan GPU computing
**Untuk Public API**: Gunakan `./run_api.sh` untuk akses publik via RESTful API
**Untuk Background Service**: Gunakan `./api_service.sh` untuk API yang berjalan di background
**Untuk Real-time + CV**: Gunakan `./run_web.sh` untuk deteksi kamera dengan Computer Vision
**Untuk Edge Devices**: Gunakan `./scripts/run_test_hybrid_integration-jetson.sh` untuk NVIDIA Jetson
**Untuk External Integration**: Gunakan API Hybrid Detection di port 5000

## 📚 Dokumentasi

- [Direct Execution Guide](direct/README.md)
- [API Hybrid Detection Guide](direct/app/api-hybrid-detection/README.md)
- [Web Application Guide](direct/app/web/README.md)
- [Edge Devices (NVIDIA Jetson) Guide](README-Edge.md) - Khusus untuk Edge Devices
- [Server/GPU Computing Guide](README_run_direct.md) - Khusus untuk Server dengan GPU
- [Computer Vision Implementation](Docs/03_PLAYGROUND/COMPUTER_VISION_IMPLEMENTATION.md)
- [Streaming Optimization](Docs/03_PLAYGROUND/STREAMING_OPTIMIZATION.md)

## 🔧 Troubleshooting

### GPU Issues
- Pastikan NVIDIA driver terinstall: `nvidia-smi`
- Pastikan GPU terdeteksi: `lspci | grep -i nvidia`
- Gunakan folder `direct/` untuk akses GPU

### API Issues
- **API tidak bisa diakses**: Check firewall `sudo ufw allow 5000`
- **Upload gagal**: Check file size (max 16MB) dan format (PNG, JPG, JPEG, GIF, BMP)
- **Processing gagal**: Check models tersedia dan virtual environment
- **Port conflict**: Check port usage `netstat -tlnp | grep 5000`

### Service Manager Issues
- **Service tidak start**: Check virtual environment dan dependencies
- **PID file error**: Delete `/tmp/mycv_api.pid` dan restart service
- **Auto-start tidak jalan**: Check crontab `crontab -l` dan log `/tmp/mycv_auto_start.log`
- **Background process hilang**: Use `./api_service.sh status` untuk check status

### Computer Vision Issues
- **CV processing gagal**: Check camera connectivity dan model availability
- **Streaming tidak smooth**: Check network bandwidth dan GPU memory
- **FPS rendah**: Optimize streaming mode (MJPEG vs Base64)
- **Memory issues**: Check swap configuration dan model loading

## 📞 Support

Jika ada masalah, cek dokumentasi di masing-masing folder atau lihat [Testing Guide](docs/TESTING_GUIDE.md).

---

## 📊 **Core Features**

### **Computer Vision Models:**
- ✅ **YOLO11 Models** (yolo11s.pt, yolo11m.pt, yolo11l.pt, yolo11x.pt)
- ✅ **YOLO v8 Models** (v8n, v8s, v8m) untuk real-time processing
- ✅ **SAM2 Models** (sam2_b.pt, sam2.1_b.pt, sam2.1_l.pt)
- ✅ **Custom Trained Models** (best.pt untuk production)
- ✅ **GPU Acceleration** (CUDA support)

### **Computer Vision Integration:**
- ✅ **Real-time Processing** dengan live streaming
- ✅ **Image Capture** selama streaming tanpa mengganggu video
- ✅ **Multiple Models** support (YOLO, SAM2, custom)
- ✅ **Continuous Processing** mode untuk automatic CV
- ✅ **Manual Capture** untuk on-demand processing
- ✅ **Result Visualization** dengan overlay detection results
- ✅ **Performance Monitoring** dengan FPS dan latency tracking
- ✅ **Download Results** sebagai JSON format

### **Streaming Features:**
- ✅ **MJPEG Streaming** untuk real-time video
- ✅ **Base64 Polling** sebagai fallback mode
- ✅ **WebSocket Support** untuk low-latency streaming
- ✅ **WebRTC Integration** untuk browser-based streaming
- ✅ **Adaptive Bitrate** untuk optimal performance
- ✅ **FPS Monitoring** dengan real-time tracking
- ✅ **Latency Optimization** untuk smooth streaming

### **API Features:**
- ✅ **RESTful API** dengan 7 endpoints lengkap
- ✅ **Multi-file Upload** dengan background processing
- ✅ **Session Management** dengan unique session IDs
- ✅ **Real-time Status** monitoring
- ✅ **File Download** untuk hasil visualisasi
- ✅ **Public Access** di http://100.98.142.94:5000
- ✅ **Computer Vision Endpoints** untuk CV processing
- ✅ **Camera Control API** untuk live streaming

### **Service Management:**
- ✅ **Background Running** independent dari terminal
- ✅ **Auto-Start on Boot** via crontab
- ✅ **Status Monitoring** real-time
- ✅ **Production Mode** dengan disabled debug

---

**Status**: ✅ **PRODUCTION READY WITH CV INTEGRATION**  
**Version**: 1.5.0-cv-integration  
**Last Updated**: 6 Oktober 2025

### **Recent Updates:**
- ✅ **Computer Vision Integration** - Real-time CV processing dengan live streaming
- ✅ **Live Streaming Support** - Multiple streaming modes (MJPEG, Base64, WebSocket, WebRTC)
- ✅ **YOLO v8 Models** - Support untuk real-time object detection
- ✅ **SAM2 Integration** - Advanced image segmentation
- ✅ **Performance Monitoring** - Real-time FPS dan latency tracking
- ✅ **UI Controls** - Modern interface untuk CV operations
- ✅ **Result Visualization** - Overlay detection results pada live stream
- ✅ **Download Support** - Export results sebagai JSON
- ✅ **Edge Devices Support** - NVIDIA Jetson Orin integration
- ✅ **API Integration** - RESTful API untuk public access
- ✅ **Service Management** - Background running dengan auto-start