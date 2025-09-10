# Computer Vision Playground V2 - User Guide

## 🎯 Overview

Computer Vision Playground V2 adalah dashboard untuk testing model YOLO (You Only Look Once) dengan integrasi Python dan Laravel. Dashboard ini memungkinkan Anda untuk mengupload model YOLO (.pt) dan gambar test, kemudian menjalankan inference untuk deteksi objek.

## 🚀 Features

- **Model Upload**: Upload model YOLO (.pt, .pth, .onnx)
- **Image Upload**: Upload gambar untuk testing
- **Confidence Threshold**: Atur threshold confidence (0.1 - 0.9)
- **Real-time Results**: Lihat hasil deteksi secara real-time
- **Visual Output**: Tampilkan bounding box dan comparison image
- **JSON Response**: Data deteksi dalam format JSON

## 📋 Prerequisites

- Docker dan Docker Compose sudah terinstall
- Model YOLO (.pt) yang sudah trained
- Gambar test dalam format JPG, PNG, JPEG

## 🛠️ Installation & Setup

### 1. Clone Repository
```bash
git clone <repository-url>
cd MyRVM-Platform
```

### 2. Build Docker Container
```bash
docker-compose build app
```

### 3. Start Services
```bash
docker-compose up -d
```

### 4. Verify Installation
```bash
curl -I http://localhost:8000/cv-playground
# Should return HTTP 200 OK
```

## 🎮 How to Use

### 1. Access Dashboard
Buka browser dan akses: `http://localhost:8000/cv-playground`

### 2. Upload Files
- **Model File**: Upload file model YOLO (.pt, .pth, .onnx)
- **Test Image**: Upload gambar untuk testing
- **Confidence**: Atur threshold confidence (default: 0.5)

### 3. Run Test
Klik tombol "Run CV Test" untuk memulai inference

### 4. View Results
Dashboard akan menampilkan:
- **Detection Summary**: Total deteksi, confidence threshold, timestamp
- **Image Info**: Dimensi gambar, jumlah channel
- **Detections**: Detail setiap deteksi (class, confidence, bounding box)
- **Output Images**: Gambar dengan bounding box dan comparison

## 🔧 Technical Details

### Architecture
```
Laravel (PHP) ←→ Python Script ←→ YOLO Model
     ↓
  Web Interface
```

### File Structure
```
storage/app/
├── cv_models/          # Uploaded YOLO models
├── cv_test_images/     # Uploaded test images
├── cv_test_results/    # Output results
└── cv_scripts/         # Python scripts
    └── cv_tester.py    # Main CV testing script
```

### API Endpoints
- `GET /cv-playground` - Dashboard interface
- `POST /cv-playground/run-test` - Run CV test
- `GET /cv-playground/result/{filepath}` - Serve result images

## 🐍 Python Script Usage

### Direct Command Line
```bash
docker exec myrvm_app_dev python3 /var/www/html/storage/app/cv_scripts/cv_tester.py \
    /path/to/model.pt \
    /path/to/image.jpg \
    /path/to/output/dir \
    0.5
```

### Parameters
- `model_path`: Path ke model YOLO
- `image_path`: Path ke gambar test
- `output_dir`: Directory untuk output
- `confidence`: Threshold confidence (0.1-0.9)

### Output Format
```json
{
  "status": "success",
  "timestamp": "20250910_045544",
  "model_path": "/path/to/model.pt",
  "image_path": "/path/to/image.jpg",
  "confidence_threshold": 0.5,
  "total_detections": 1,
  "detections": [
    {
      "id": 0,
      "class_name": "test_object",
      "confidence": 0.95,
      "bbox": {
        "x1": 172.8,
        "y1": 96.0,
        "x2": 691.2,
        "y2": 384.0,
        "width": 518.4,
        "height": 288.0
      }
    }
  ],
  "output_images": {
    "yolo": "/path/to/yolo_output.png",
    "sam": null,
    "comparison": "/path/to/comparison.png"
  },
  "image_info": {
    "width": 864,
    "height": 480,
    "channels": 3
  }
}
```

## 🔍 Troubleshooting

### Common Issues

#### 1. 504 Gateway Timeout
```bash
# Check container status
docker-compose ps

# Check logs
docker-compose logs app
docker-compose logs web
```

#### 2. Python Script Error
```bash
# Test Python script directly
docker exec myrvm_app_dev python3 /var/www/html/storage/app/cv_scripts/cv_tester.py
```

#### 3. File Upload Issues
```bash
# Check file permissions
docker exec myrvm_app_dev ls -la /var/www/html/storage/app/
```

#### 4. Model Loading Error
- Pastikan model file (.pt) valid
- Check model compatibility dengan YOLO version
- Verify file path dan permissions

### Debug Commands
```bash
# Check container logs
docker-compose logs -f app

# Access container shell
docker exec -it myrvm_app_dev sh

# Check Python version
docker exec myrvm_app_dev python3 --version

# Check OpenCV
docker exec myrvm_app_dev python3 -c "import cv2; print(cv2.__version__)"
```

## 📊 Performance Notes

- **Processing Time**: 2-10 detik tergantung ukuran gambar dan model
- **Memory Usage**: ~2-4GB untuk model YOLO standard
- **File Size Limit**: Max 10MB untuk upload
- **Supported Formats**: JPG, PNG, JPEG untuk gambar; .pt, .pth, .onnx untuk model

## 🔒 Security Considerations

- File uploads divalidasi untuk tipe dan ukuran
- Path traversal protection
- CSRF token validation
- Input sanitization

## 🚀 Future Enhancements

- [ ] SAM2 integration untuk segmentation
- [ ] Batch processing multiple images
- [ ] Model comparison dashboard
- [ ] Real-time video processing
- [ ] Custom model training interface

## 📞 Support

Untuk bantuan teknis atau bug reports, silakan buat issue di repository atau hubungi tim development.

---

**Computer Vision Playground V2** - Powered by Laravel, Python, dan YOLO
