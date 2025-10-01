# MyCV-Platform Hybrid Detection API (Jetson) | 🚀 Jetson API

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
- `GET /api/hardware` - Informasi hardware Jetson lengkap

### Upload & Processing
- `POST /api/upload` - Upload gambar untuk deteksi
- `GET /api/process/<session_id>` - Status pemrosesan
- `GET /api/results/<session_id>` - Hasil deteksi dari summary.json

### Download, Backup & History
- `GET /api/download/<session_id>/<filename>` - Download file hasil
- `GET /api/backup/<session_id>` - Buat dan unduh TAR.GZ backup satu sesi
- `GET /api/detections` - Semua deteksi terbaru dengan pagination (Query Parameters)
- `POST /api/detections/search` - Search detections dengan JSON body

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

## 🔧 Hardware Information (Jetson)

### Comprehensive Hardware Monitoring
API menyediakan endpoint `/api/hardware` untuk monitoring hardware Jetson secara lengkap:

#### Hardware Information Includes:
- **Jetson Info**: Model, L4T version, Jetpack version, kernel version
- **CUDA Info**: Availability, version, device count, memory usage (total/used/free)
- **Memory Info**: System memory, swap memory dengan konversi GB
- **Disk Info**: Storage devices, NVMe detection, filesystem usage
- **Camera Info**: USB cameras, CSI cameras, nvargus status
- **Network Info**: Interfaces, Tailscale IP, local IP, public IP

#### Usage:
```bash
# Get comprehensive hardware information
curl http://100.117.234.2:5000/api/hardware

# With pretty print
curl http://100.117.234.2:5000/api/hardware | jq
```

#### Example Response:
```json
{
  "status": "success",
  "service": "MyCV-Edge-API",
  "hardware_info": {
    "jetson_info": {
      "model": "Jetson Orin Nano",
      "l4t_version": "R36.4.2",
      "jetpack_version": "6.1",
      "kernel_version": "5.10.120-tegra",
      "architecture": "aarch64"
    },
    "cuda_info": {
      "available": true,
      "version": "12.6",
      "device_count": 1,
      "device_name": "Orin",
      "memory_total_gb": 7.4,
      "memory_used_gb": 1.2,
      "memory_free_gb": 6.2
    },
    "memory_info": {
      "total_gb": 7.4,
      "available_gb": 6.0,
      "used_gb": 1.4,
      "free_gb": 3.1,
      "swap_total_gb": 16.0,
      "swap_used_gb": 0.0,
      "swap_free_gb": 16.0
    },
    "disk_info": {
      "devices": [
        {
          "filesystem": "/dev/nvme0n1p1",
          "size": "32G",
          "used": "8.2G",
          "available": "22G",
          "use_percent": "28%",
          "mounted_on": "/"
        }
      ],
      "nvme_devices": [
        {
          "name": "nvme0n1",
          "size": "32G",
          "type": "disk",
          "mountpoint": "/"
        }
      ]
    },
    "camera_info": {
      "usb_cameras": [
        {
          "device": "/dev/video0",
          "type": "USB"
        }
      ],
      "jetson_cameras": [
        {
          "device": "/dev/video1",
          "type": "CSI",
          "info": "CSI Camera Info"
        }
      ],
      "total_cameras": 2,
      "nvargus_status": "active"
    },
    "network_info": {
      "interfaces": [
        {
          "name": "eth0",
          "addresses": ["192.168.1.100"]
        },
        {
          "name": "tailscale0",
          "addresses": ["100.117.234.2"]
        }
      ],
      "tailscale_ip": "100.117.234.2",
      "local_ip": "192.168.1.100",
      "public_ip": "203.0.113.1",
      "tailscale_status": "connected"
    }
  }
}
```

#### Hardware Monitoring Features:
- **Real-time Status**: Live hardware information
- **Resource Monitoring**: Memory, disk, GPU usage
- **Network Detection**: Automatic IP detection (Tailscale, local, public)
- **Camera Detection**: USB and CSI camera enumeration
- **System Information**: Jetson model, L4T, Jetpack versions
- **Performance Metrics**: CUDA memory usage, system memory

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

## 📊 API Detections dengan Pagination

### Endpoint: `GET /api/detections`

Mengambil semua deteksi terbaru dengan pagination dan filtering.

#### Query Parameters:
- `page` (optional): Halaman yang diminta (default: 1)
- `limit` (optional): Jumlah item per halaman (default: 20, max: 100)
- `user_id` (optional): Filter berdasarkan user ID

#### Contoh Request:
```bash
# Halaman pertama, 20 items
curl "http://100.117.234.2:5000/api/detections"

# Halaman kedua, 10 items per halaman
curl "http://100.117.234.2:5000/api/detections?page=2&limit=10"

# Filter berdasarkan user_id
curl "http://100.117.234.2:5000/api/detections?user_id=my_user&page=1&limit=5"
```

#### Response Format:
```json
{
    "pagination": {
        "current_page": 1,
        "total_pages": 5,
        "total_items": 95,
        "items_per_page": 20,
        "has_next": true,
        "has_prev": false,
        "next_page": 2,
        "prev_page": null
    },
    "filters": {
        "user_id": "my_user"
    },
    "recent_detections": [
        {
            "timestamp": "20250930_201225",
            "user_id": "my_user",
            "image_name": "image1",
            "detections": [...],
            "detection_count": 3
        }
    ]
}
```

#### Pagination Features:
- ✅ **Page Navigation**: `current_page`, `total_pages`
- ✅ **Item Count**: `total_items`, `items_per_page`
- ✅ **Navigation Helpers**: `has_next`, `has_prev`, `next_page`, `prev_page`
- ✅ **User Filtering**: Filter berdasarkan `user_id`
- ✅ **Performance**: Max 100 items per page untuk performa optimal

## 📊 API Detections dengan JSON Body

### Endpoint: `POST /api/detections/search`

Search detections menggunakan JSON body dengan filtering yang lebih advanced.

#### Request Body (JSON):
```json
{
    "page": 1,
    "limit": 20,
    "user_id": "my_user",
    "timestamp": "20250930",
    "class_name": "person"
}
```

#### Field Descriptions:
- `page` (optional): Halaman yang diminta (default: 1)
- `limit` (optional): Jumlah item per halaman (default: 20, max: 100)
- `user_id` (optional): Filter berdasarkan user ID
- `timestamp` (optional): Filter berdasarkan timestamp (partial match)
- `class_name` (optional): Filter berdasarkan class name detection

#### Contoh Request di Postman:

**Method**: `POST`  
**URL**: `http://100.117.234.2:5000/api/detections/search`  
**Headers**:
```
Content-Type: application/json
Accept: application/json
```

**Body (raw JSON)**:
```json
{
    "page": 1,
    "limit": 10,
    "user_id": "my_user",
    "class_name": "person"
}
```

#### Response Format:
```json
{
    "pagination": {
        "current_page": 1,
        "total_pages": 3,
        "total_items": 25,
        "items_per_page": 10,
        "has_next": true,
        "has_prev": false,
        "next_page": 2,
        "prev_page": null
    },
    "filters": {
        "user_id": "my_user",
        "timestamp": null,
        "class_name": "person"
    },
    "recent_detections": [
        {
            "timestamp": "20250930_201225",
            "user_id": "my_user",
            "image_name": "image1",
            "detections": [
                {
                    "class_name": "person",
                    "confidence": 0.95,
                    "bbox": [100, 200, 300, 400]
                }
            ],
            "detection_count": 1
        }
    ]
}
```

#### Advanced Filtering Examples:

**1. Filter by User dan Class:**
```json
{
    "page": 1,
    "limit": 20,
    "user_id": "test_user",
    "class_name": "car"
}
```

**2. Filter by Timestamp:**
```json
{
    "page": 1,
    "limit": 50,
    "timestamp": "20250930"
}
```

**3. Complex Filter:**
```json
{
    "page": 2,
    "limit": 15,
    "user_id": "admin",
    "timestamp": "20250930",
    "class_name": "person"
}
```

#### Error Responses:

**400 Bad Request (No JSON Body):**
```json
{
    "error": "JSON body required"
}
```

**500 Internal Server Error:**
```json
{
    "error": "Failed to search detections: [error message]"
}
```

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