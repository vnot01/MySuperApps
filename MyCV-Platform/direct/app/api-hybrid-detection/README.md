# MyCV-Platform Hybrid Detection API

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
- `GET /api/status` - API status dan informasi

### Upload & Processing
- `POST /api/upload` - Upload gambar untuk deteksi
- `GET /api/process/<session_id>` - Status pemrosesan
- `GET /api/results/<session_id>` - Hasil deteksi

### Download & History
- `GET /api/download/<session_id>/<filename>` - Download file hasil
- `GET /api/detections` - Semua deteksi terbaru

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
    "images_processed": [
      {
        "image_name": "image1",
        "detections": [
          {
            "bbox": [100, 200, 300, 400],
            "confidence": 0.85,
            "class_id": 2,
            "class_name": "mineral"
          }
        ],
        "detection_count": 1,
        "visualizations": [
          {
            "type": "compare",
            "file": "image1-best_pt-compare.png",
            "path": "/image1-best_pt-compare.png"
          }
        ]
      }
    ],
    "total_files": 1,
    "detection_summary": {
      "mineral": 1
    }
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

## 🔍 Detection Models

1. **YOLO11m**: Object detection
2. **best.pt**: Custom trained model
3. **SAM2_b**: Segmentation model

## 📊 Output Files

Untuk setiap gambar yang diproses:

- `{image}-best_pt-detection.json` - Detection results
- `{image}-best_pt-compare.png` - Compare visualization
- `{image}-best_pt-best.png` - Best detection
- `{image}-best_pt-segmentation.png` - SAM2 segmentation
- `{image}-best_pt-hybrid.png` - Combined result

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
