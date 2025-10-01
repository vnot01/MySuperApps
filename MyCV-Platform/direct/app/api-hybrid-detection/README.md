# MyCV-Platform Hybrid Detection API | 🖥️ GPU Server

RESTful API untuk deteksi objek dan segmentasi menggunakan YOLO + SAM2.

## 🚀 Quick Start

### 1. Jalankan API Server
```bash
cd /home/my/MySuperApps/MyCV-Platform/direct/app/api-hybrid-detection
./run_api.sh
```

### 2. Akses API
- **URL**: http://100.98.142.94:5000
- **Health Check**: http://100.98.142.94:5000/api/health

## 📋 API Endpoints

### Health & Status
- `GET /api/health` - Health check
- `GET /api/hardware` - Comprehensive hardware information
#### Response: `GET /api/hardware`
```json
{
  "status": "success",
  "service": "MyCV-GPU-Server",
  "hardware_info": {
    "system_info": {
      "architecture": "x86_64",
      "hostname": "cv-host",
      "kernel_version": "5.15.0-156-generic"
    },
    "gpu_info": {
      "status": "success",
      "cuda_available": true,
      "cudnn_enabled": true,
      "pytorch_cuda_version": "2.8.0+cu128",
      "available_gpus": 1,
      "gpus": [
        {
          "id": 0,
          "name": "NVIDIA GeForce RTX 3060",
          "memory_gb": 11.63
        }
      ],
      "total_memory_all_gpus_gb": 11.63
    },
    "memory_info": {
      "total_gb": 12.75
    },
    "disk_info": {
      "available": "74G",
      "size": "153G",
      "used": "72G",
      "use_percent": "50%"
    },
    "network_info": {
      "local_ip": "10.3.52.184",
      "public_ip": "202.152.145.34"
    },
    "updated_at": "2025-10-01T08:56:07.131973"
  }
}
```

#### Response: `GET /api/health`
```json
{
    "service": "MyCV-Platform Hybrid Detection API",
    "status": "healthy",
    "timestamp": "2025-09-28T13:52:36.623043",
    "uptime": 1759067556.6230543,
    "version": "1.0.0"
}
```

- `GET /api/status` - API status dan informasi lengkap dengan GPU details
#### Response: `GET /api/status`
```json
{
    "api_status": "online",
    "endpoints": [
        "/api/health",
        "/api/status",
        "/api/hardware",
        "/api/upload",
        "/api/process/<session_id>",
        "/api/results/<session_id>",
        "/api/download/<session_id>/<filename>",
        "/api/detections",
        "/api/backup/<session_id>"
    ],
    "gpu_info": {
        "available_gpus": 1,
        "cuda_available": true,
        "cudnn_enabled": true,
        "gpus": [
            {
                "id": 0,
                "name": "NVIDIA GeForce RTX 3060",
                "memory_gb": 11.63
            }
        ],
        "pytorch_cuda_version": "2.8.0+cu128",
        "status": "success",
        "total_memory_all_gpus_gb": 11.63
    },
    "service": "MyCV-Platform Hybrid Detection API",
    "timestamp": "2025-09-28T15:09:48.393352",
    "total_sessions_processed": 7,
    "version": "1.0.0"
}
```

### Upload & Processing
- `POST /api/upload` - Upload gambar untuk deteksi
#### Response: `POST /api/upload`
```json
{
    "message": "Files uploaded successfully. Processing started.",
    "results_url": "/api/results/session_9eebbca5",
    "session_id": "session_9eebbca5",
    "status_url": "/api/process/session_9eebbca5",
    "success": true,
    "timestamp": "20250928_135630",
    "uploaded_files": [
        {
            "original_name": "27_not_mineral.jpg",
            "path": "../../data/input/remote/20250928_135630/test_userrrr/27_not_mineral.jpg",
            "saved_name": "27_not_mineral.jpg"
        }
    ],
    "user_id": "test_userrrr"
}
```
- `GET /api/process/<session_id>` - Status pemrosesan
#### Response: `GET /api/process/session_9eebbca5`
```json
{
    "end_time": "2025-09-28T13:57:03.728872",
    "message": "Detection completed successfully",
    "start_time": "2025-09-28T13:56:30.727720",
    "status": "completed",
    "timestamp": "20250928_135630",
    "user_id": "test_userrrr"
}
```
- `GET /api/results/<session_id>` - Hasil deteksi dari summary.json
#### Response: `GET /api/results/session_a88aa433`
```json
{
    "results": {
        "detection_summary": [
            {
                "id": 0,
                "name": "1-botol_mineral-best_pt-detection.json",
                "datas": [
                    {
                        "bbox": [
                            57.36895751953125,
                            62.235107421875,
                            344.09259033203125,
                            577.8096923828125
                        ],
                        "confidence": 0.8787883520126343,
                        "class_id": 2,
                        "class_name": "mineral"
                    }
                ],
                "detection_count": 1,
                "summary_images_url": "https://100.98.142.94:5000/api/download/20250929_023747/test_user_multi/1-botol_mineral-best_pt-compare.png",
                "images": {
                    "best": "https://100.98.142.94:5000/api/download/20250929_023747/test_user_multi/best/1-botol_mineral-best_pt-best.png",
                    "yolo": "https://100.98.142.94:5000/api/download/20250929_023747/test_user_multi/yolo/1-botol_mineral-yolo11m-detection.png",
                    "sam": "https://100.98.142.94:5000/api/download/20250929_023747/test_user_multi/segmentasi/1-botol_mineral-best_pt-segmentation.png",
                    "hybrid": "https://100.98.142.94:5000/api/download/20250929_023747/test_user_multi/hybrid/1-botol_mineral-best_pt-hybrid.png"
                }
            },
            {
                "id": 1,
                "name": "24_mineral-best_pt-detection.json",
                "datas": [
                    {
                        "bbox": [
                            103.0361328125,
                            117.67948150634766,
                            713.5864868164062,
                            283.5408630371094
                        ],
                        "confidence": 0.8873822689056396,
                        "class_id": 2,
                        "class_name": "mineral"
                    },
                    {
                        "bbox": [
                            549.1739501953125,
                            188.59642028808594,
                            646.7528686523438,
                            271.01678466796875
                        ],
                        "confidence": 0.6350509524345398,
                        "class_id": 4,
                        "class_name": "not_empty"
                    }
                ],
                "detection_count": 2,
                "summary_images_url": "https://100.98.142.94:5000/api/download/20250929_023747/test_user_multi/24_mineral-best_pt-compare.png",
                "images": {
                    "best": "https://100.98.142.94:5000/api/download/20250929_023747/test_user_multi/best/24_mineral-best_pt-best.png",
                    "yolo": "https://100.98.142.94:5000/api/download/20250929_023747/test_user_multi/yolo/24_mineral-yolo11m-detection.png",
                    "sam": "https://100.98.142.94:5000/api/download/20250929_023747/test_user_multi/segmentasi/24_mineral-best_pt-segmentation.png",
                    "hybrid": "https://100.98.142.94:5000/api/download/20250929_023747/test_user_multi/hybrid/24_mineral-best_pt-hybrid.png"
                }
            }
        ],
        "class_summary": [
            {
                "class_name": "mineral",
                "count": 3
            },
            {
                "class_name": "not_empty",
                "count": 1
            }
        ],
        "object_count": 3
    },
    "session_id": "session_a88aa433",
    "status": "completed",
    "timestamp": "20250929_023747",
    "user_id": "test_user_multi"
}
```

### Download, Backup & History
- `GET /api/download/<session_id>/<filename>` - Download file hasil
- `GET /api/backup/<session_id>` - Buat dan unduh TAR.GZ backup satu sesi
- `GET /api/detections` - Semua deteksi terbaru
#### Response: `GET /api/detections`
```json
{
    "recent_detections": [
        {
            "detection_count": 2,
            "detections": [
                {
                    "bbox": [
                        289.1062316894531,
                        224.7684326171875,
                        368.2486267089844,
                        452.5482177734375
                    ],
                    "class_id": 5,
                    "class_name": "soda",
                    "confidence": 0.28023016452789307
                },
                {
                    "bbox": [
                        288.54638671875,
                        223.8448944091797,
                        369.2777099609375,
                        452.61724853515625
                    ],
                    "class_id": 0,
                    "class_name": "dishwasher",
                    "confidence": 0.27135953307151794
                }
            ],
            "image_name": "27_not_mineral",
            "timestamp": "20250928_135630",
            "user_id": "test_userrrr"
        },
        {
            "detection_count": 1,
            "detections": [
                {
                    "bbox": [
                        294.4569091796875,
                        308.8126220703125,
                        363.95751953125,
                        448.69964599609375
                    ],
                    "class_id": 2,
                    "class_name": "mineral",
                    "confidence": 0.8424936532974243
                }
            ],
            "image_name": "21_mineral",
            "timestamp": "20250928_092747",
            "user_id": "test_api_user"
        },
        {
            "detection_count": 1,
            "detections": [
                {
                    "bbox": [
                        57.36895751953125,
                        62.235107421875,
                        344.09259033203125,
                        577.8096923828125
                    ],
                    "class_id": 2,
                    "class_name": "mineral",
                    "confidence": 0.8787883520126343
                }
            ],
            "image_name": "1-botol_mineral",
            "timestamp": "20250928_091258",
            "user_id": "test_user_001"
        },
        {
            "detection_count": 1,
            "detections": [
                {
                    "bbox": [
                        294.4569091796875,
                        308.8126220703125,
                        363.95751953125,
                        448.69964599609375
                    ],
                    "class_id": 2,
                    "class_name": "mineral",
                    "confidence": 0.8424936532974243
                }
            ],
            "image_name": "21_mineral",
            "timestamp": "20250928_091258",
            "user_id": "test_user_001"
        },
        {
            "detection_count": 1,
            "detections": [
                {
                    "bbox": [
                        252.4176788330078,
                        145.65257263183594,
                        394.87469482421875,
                        367.3836669921875
                    ],
                    "class_id": 2,
                    "class_name": "mineral",
                    "confidence": 0.9168100953102112
                }
            ],
            "image_name": "244.mineral_crush",
            "timestamp": "20250928_091258",
            "user_id": "test_user_001"
        }
    ],
    "total_sessions": 5
}
```

## 📤 Upload Images

### Menggunakan curl:
```bash
curl -X POST \
  -F 'files=@image1.jpg' \
  -F 'files=@image2.jpg' \
  -F 'user_id=my_user' \
  http://100.98.142.94:5000/api/upload
```

### Response:
```json
{
  "success": true,
  "session_id": "session_abc123",
  "timestamp": "20250928_143022",
  "user_id": "my_user",
  "uploaded_files": [...],
  "status_url": "/api/process/session_abc123",
  "results_url": "/api/results/session_abc123"
}
```

## 📊 Check Processing Status

```bash
curl http://100.98.142.94:5000/api/process/session_abc123
```

### Response:
```json
{
  "status": "completed",
  "message": "Detection completed successfully",
  "timestamp": "20250928_143022",
  "user_id": "my_user",
  "start_time": "2025-09-28T14:30:22",
  "end_time": "2025-09-28T14:30:45"
}
```

## 📈 Get Detection Results

```bash
curl http://100.98.142.94:5000/api/results/session_abc123
```

### Response:
```json
{
  "session_id": "session_abc123",
  "status": "completed",
  "results": {
    "detection_summary": [
      {
        "id": 0,
        "name": "image1-best_pt-detection.json",
        "datas": [
          {
            "bbox": [100, 200, 300, 400],
            "confidence": 0.85,
            "class_id": 2,
            "class_name": "mineral"
          }
        ],
        "detection_count": 1,
        "summary_images_url": "https://100.98.142.94:5000/api/download/20250929_023747/test_user_multi/image1-best_pt-compare.png",
        "images": {
          "best": "https://100.98.142.94:5000/api/download/20250929_023747/test_user_multi/best/image1-best_pt-best.png",
          "yolo": "https://100.98.142.94:5000/api/download/20250929_023747/test_user_multi/yolo/image1-yolo11m-detection.png",
          "sam": "https://100.98.142.94:5000/api/download/20250929_023747/test_user_multi/segmentasi/image1-best_pt-segmentation.png",
          "hybrid": "https://100.98.142.94:5000/api/download/20250929_023747/test_user_multi/hybrid/image1-best_pt-hybrid.png"
        }
      }
    ],
    "class_summary": [
      {
        "class_name": "mineral",
        "count": 1
      }
    ],
    "object_count": 1
  }
}
```

## 📁 File Structure

```
./direct/app/api-hybrid-detection/
├── app.py                 # Flask API server
├── requirements.txt       # Python dependencies
├── run_api.sh            # API launcher script
└── README.md             # Documentation

./direct/data/
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
- `UPLOAD_FOLDER`: Directory untuk upload (default: `../../data/input/remote`)
- `OUTPUT_FOLDER`: Directory untuk output (default: `../../data/output/remote`)
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
- ✅ **GPU Detection**: Real-time GPU information dengan memory details
- ✅ **System Monitoring**: Total sessions processed tracking
- ✅ **Summary JSON**: Structured results dengan detection_summary, class_summary, dan object_count
- ✅ **Image URLs**: Direct download links untuk semua jenis visualisasi (best, yolo, sam, hybrid)
- ✅ **Compare Visualization**: Summary image yang menggabungkan semua hasil

## 🔍 Detection Models

1. **YOLO11m**: Object detection
2. **best.pt**: Custom trained model
3. **SAM2_b**: Segmentation model

## 🖥️ GPU Information

API secara otomatis mendeteksi dan melaporkan informasi GPU yang tersedia:

### GPU Detection Features:
- **Real-time Detection**: Mendeteksi GPU yang tersedia saat runtime
- **Memory Information**: Menampilkan total memory setiap GPU
- **CUDA Support**: Mengecek ketersediaan CUDA dan cuDNN
- **Multi-GPU Support**: Mendukung sistem dengan multiple GPU
- **PyTorch Integration**: Menggunakan PyTorch untuk deteksi GPU

### GPU Info Structure:
```json
{
    "gpu_info": {
        "available_gpus": 1,
        "cuda_available": true,
        "cudnn_enabled": true,
        "gpus": [
            {
                "id": 0,
                "name": "NVIDIA GeForce RTX 3060",
                "memory_gb": 11.63
            }
        ],
        "pytorch_cuda_version": "2.8.0+cu128",
        "status": "success",
        "total_memory_all_gpus_gb": 11.63
    }
}
```

### Supported GPU Types:
- **NVIDIA GPUs**: Semua GPU NVIDIA dengan CUDA support
- **Memory Detection**: Otomatis mendeteksi total memory
- **Multi-GPU Systems**: Mendukung sistem dengan multiple GPU
- **Fallback Support**: Graceful handling jika GPU tidak tersedia

## 🔧 Hardware Information

### Comprehensive Hardware Monitoring
API menyediakan endpoint `/api/hardware` untuk monitoring hardware GPU Server secara lengkap:

#### Hardware Information Includes:
- **System Info**: Architecture, hostname, kernel version
- **GPU Info**: Status, availability, cuDNN enabled, PyTorch CUDA version, GPU details
- **Memory Info**: Total memory (RAM + Swap combined)
- **Disk Info**: Available space, total size, used space, usage percentage
- **Network Info**: Local IP, public IP

#### Usage:
```bash
# Get comprehensive hardware information
curl http://100.98.142.94:5000/api/hardware

# With pretty print
curl http://100.98.142.94:5000/api/hardware | jq
```

#### Hardware Monitoring Features:
- **Real-time Status**: Live hardware information
- **Resource Monitoring**: Memory, disk, GPU usage
- **Network Detection**: Automatic IP detection (local, public)
- **System Information**: Architecture, hostname, kernel versions
- **Performance Metrics**: GPU memory usage, system memory

## 📈 System Monitoring

API menyediakan monitoring sistem secara real-time:

### Monitoring Features:
- **Session Tracking**: Menghitung total sessions yang telah diproses
- **Performance Metrics**: Melacak performa sistem
- **Resource Usage**: Monitoring penggunaan GPU dan memory
- **Health Status**: Real-time health check

### System Status Response:
```json
{
    "api_status": "online",
    "service": "MyCV-Platform Hybrid Detection API",
    "version": "1.0.0",
    "total_sessions_processed": 7,
    "gpu_info": { ... },
    "timestamp": "2025-09-28T15:09:48.393352"
}
```

### Monitoring Endpoints:
- `GET /api/status` - Comprehensive system status
- `GET /api/health` - Basic health check
- `GET /api/hardware` - Comprehensive hardware information
- `GET /api/detections` - Processing history

## 🌐 Web Application Integration

API terintegrasi dengan MyCV-Platform Web Application untuk menampilkan informasi real-time:

### Web App Features:
- **Real-time System Status**: Menampilkan status GPU dan sistem
- **Upload Interface**: Multi-file upload dengan drag & drop
- **Processing Status**: Real-time monitoring processing
- **Results Display**: Frame-based results visualization
- **Download Management**: Download hasil processing

### System Status Display:
Web application menampilkan informasi real dari API:
- **Service**: MyCV-Platform Hybrid Detection API
- **Status**: Online/Offline
- **Version**: 1.0.0
- **Server**: 100.98.142.94
- **GPU Available**: NVIDIA GeForce RTX 3060 (11.63GB)
- **GPU Count**: 1 GPU(s) - 11.63GB Total

### Integration Endpoints:
- Web App: `http://100.98.142.94:5002`
- API Service: `http://100.98.142.94:5000`
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
1. Check models tersedia
2. Check virtual environment
3. Check logs di terminal

## 📞 Support

Untuk bantuan atau laporan bug, silakan buka issue di repository GitHub.
