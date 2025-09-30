# MyCV-Platform Hybrid Detection API (Jetson)

RESTful API untuk deteksi objek dan segmentasi menggunakan YOLO + SAM2 pada NVIDIA Jetson Orin.

## 🚀 Quick Start

### 1. Jalankan API Server di Jetson
```bash
cd /home/my/MySuperApps/MyCV-Platform/direct/app/api-hybrid-detection-jetson
./run_api.sh
```

### 2. Akses API
- **URL**: http://100.117.234.2:5000
- **Health Check**: http://100.117.234.2:5000/api/health

## 📋 API Endpoints

### Health & Status
- `GET /api/health` - Health check
- `GET /api/status` - API status dengan informasi GPU Jetson

### Upload & Processing
- `POST /api/upload` - Upload gambar untuk deteksi
- `GET /api/process/<session_id>` - Status pemrosesan
- `GET /api/results/<session_id>` - Hasil deteksi dari summary.json

### Download, Backup & History
- `GET /api/download/<session_id>/<filename>` - Download file hasil
- `GET /api/backup/<session_id>` - Buat dan unduh TAR.GZ backup satu sesi
- `GET /api/detections` - Semua deteksi terbaru

## 📤 Upload Images

### Menggunakan curl:
```bash
curl -X POST \
  -F 'files=@image1.jpg' \
  -F 'files=@image2.jpg' \
  -F 'user_id=my_user' \
  http://100.117.234.2:5000/api/upload
```

## 📁 File Structure (Jetson)

```
./direct/app/api-hybrid-detection-jetson/
├── app.py                 # Flask API server untuk Jetson
├── requirements.txt       # Python dependencies
├── run_api.sh            # API launcher script untuk Jetson
└── README.md             # Documentation

./direct/data-jetson/
├── input/remote/         # Uploaded images
│   └── <timestamp>/<user_id>/
└── output/remote/        # Detection results
    └── <timestamp>/<user_id>/
        ├── yolo/         # YOLO11m results
        ├── best/         # best.pt results
        ├── segmentasi/   # SAM2 segmentation
        ├── hybrid/       # Combined results
        └── *.json        # Detection data
```

## 🔧 Configuration

### Environment Variables
- `UPLOAD_FOLDER`: Directory untuk upload (default: `../../data-jetson/input/remote`)
- `OUTPUT_FOLDER`: Directory untuk output (default: `../../data-jetson/output/remote`)
- `MAX_CONTENT_LENGTH`: Max file size (default: 16MB)

### Supported File Types
- PNG, JPG, JPEG, GIF, BMP

## 🎯 Features

- ✅ **Multi-file Upload**: Upload multiple images sekaligus
- ✅ **Background Processing**: Processing berjalan di background
- ✅ **Session Management**: Setiap upload mendapat session ID unik
- ✅ **Real-time Status**: Check status processing real-time
- ✅ **File Download**: Download hasil visualisasi
- ✅ **Detection History**: Lihat semua deteksi terbaru
- ✅ **CORS Support**: Support untuk web applications
- ✅ **Error Handling**: Comprehensive error handling
- ✅ **GPU Detection**: Real-time GPU information untuk Jetson
- ✅ **System Monitoring**: Total sessions processed tracking
- ✅ **Summary JSON**: Structured results dengan detection_summary, class_summary, dan object_count
- ✅ **Image URLs**: Direct download links untuk semua jenis visualisasi (best, yolo, sam, hybrid)
- ✅ **Compare Visualization**: Summary image yang menggabungkan semua hasil

## 🔍 Detection Models (Jetson)

1. **YOLO11m**: Object detection
2. **best.pt**: Custom trained model
3. **SAM2_b**: Segmentation model

## 🖥️ GPU Information (Jetson)

API secara otomatis mendeteksi dan melaporkan informasi GPU Jetson:

### GPU Detection Features:
- **Real-time Detection**: Mendeteksi GPU Jetson yang tersedia
- **Memory Information**: Menampilkan total memory GPU Jetson
- **CUDA Support**: Mengecek ketersediaan CUDA dan cuDNN
- **Jetson Optimization**: Optimized untuk Jetson Orin

### GPU Info Structure:
```json
{
    "gpu_info": {
        "status": "success",
        "cuda_available": true,
        "cudnn_enabled": true,
        "available_gpus": 1,
        "gpus": [
            {
                "id": 0,
                "name": "Orin",
                "memory_gb": 7.4
            }
        ],
        "pytorch_cuda_version": "2.5.0a0+872d972e41.nv24.08",
        "total_memory_all_gpus_gb": 7.4
    }
}
```

## 📈 System Monitoring

API menyediakan monitoring sistem secara real-time untuk Jetson:

### Monitoring Features:
- **Session Tracking**: Menghitung total sessions yang telah diproses
- **Performance Metrics**: Melacak performa sistem Jetson
- **Resource Usage**: Monitoring penggunaan GPU dan memory
- **Health Status**: Real-time health check

## 🌐 Web Application Integration

API terintegrasi dengan MyCV-Platform Web Application untuk menampilkan informasi real-time:

### Web App Features:
- **Real-time System Status**: Menampilkan status GPU Jetson dan sistem
- **Upload Interface**: Multi-file upload dengan drag & drop
- **Processing Status**: Real-time monitoring processing
- **Results Display**: Frame-based results visualization
- **Download Management**: Download hasil processing

### System Status Display:
Web application menampilkan informasi real dari API Jetson:
- **Service**: MyCV-Platform Hybrid Detection API (Jetson)
- **Status**: Online/Offline
- **Version**: 1.0.0
- **Server**: 100.117.234.2
- **GPU Available**: Orin (7.4GB)
- **GPU Count**: 1 GPU(s) - 7.4GB Total

### Integration Endpoints:
- Web App: `http://100.117.234.2:5002`
- API Service: `http://100.117.234.2:5000`
- Real-time data exchange antara Web App dan API

## 📊 Output Files

Untuk setiap gambar yang diproses:

- `{image}-best_pt-detection.json` - Detection results (JSON)
- `{image}-best_pt-compare.png` - Compare visualization (4-panel)
- `{image}-best_pt-best.png` - Best detection (best/ folder)
- `{image}-yolo11m-detection.png` - YOLO11m detection (yolo/ folder)
- `{image}-best_pt-segmentation.png` - SAM2 segmentation (segmentasi/ folder)
- `{image}-best_pt-hybrid.png` - Combined result (hybrid/ folder)
- `summary.json` - Session summary dengan detection_summary, class_summary, object_count

## 🚨 Troubleshooting

### API tidak bisa diakses
1. Check firewall: `sudo ufw allow 5000`
2. Check port usage: `netstat -tlnp | grep 5000`
3. Restart API: `./run_api.sh`

### Upload gagal
1. Check file size (max 16MB)
2. Check file format (PNG, JPG, JPEG, GIF, BMP)
3. Check disk space

### Processing gagal
1. Check models tersedia di data-jetson/models/
2. Check virtual environment
3. Check logs di terminal

## 📞 Support

Untuk bantuan atau laporan bug, silakan buka issue di repository GitHub.

## 🔄 Independence

API ini dirancang untuk bekerja **independen** dari main platform:
- ✅ **Local Processing**: Semua processing dilakukan di Jetson
- ✅ **Local Storage**: Data disimpan di data-jetson/
- ✅ **Local Models**: Model tersimpan di data-jetson/models/
- ✅ **No Dependencies**: Tidak bergantung pada main platform
- ✅ **Standalone Operation**: Bisa berjalan tanpa main platform