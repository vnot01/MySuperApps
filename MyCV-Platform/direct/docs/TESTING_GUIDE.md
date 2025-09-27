# MyCV-Platform Testing Guide

## 🧪 **YOLO + SAM2 Integration Testing**

### **Overview**
MyCV-Platform menyediakan sistem testing yang komprehensif untuk integrasi YOLO + SAM2. Testing dilakukan di host system menggunakan virtual environment, bukan Docker.

---

## 🚀 **Quick Start Testing**

### **1. Fresh Integration Test (Recommended)**
```bash
# Jalankan pengujian lengkap dengan environment bersih
./scripts/run_fresh_integration_test.sh
```

**Yang dilakukan:**
- ✅ Membersihkan hasil pengujian sebelumnya
- ✅ Download model YOLO11m dan SAM2_b jika belum ada
- ✅ Download model best.pt dari GitHub release
- ✅ Jalankan deteksi YOLO11m pada semua test images
- ✅ Jalankan deteksi best.pt pada semua test images
- ✅ Gunakan bounding box sebagai prompt untuk SAM2
- ✅ Generate segmentation masks
- ✅ Buat visualisasi lengkap
- ✅ Simpan hasil dalam format JSON dan PNG

---

## 🔧 **Individual Model Testing**

### **2. YOLO11m Test Only**
```bash
# Jalankan hanya YOLO11m + SAM2
./scripts/run_yolo11m_test.sh
```

**Fitur:**
- Deteksi objek umum (bottle, dining table, dll)
- Bounding box coordinates
- Confidence scores
- SAM2 segmentation menggunakan YOLO11m bounding boxes

### **3. best.pt Test Only**
```bash
# Jalankan hanya best.pt + SAM2
./scripts/run_best_pt_test.sh
```

**Fitur:**
- Deteksi mineral khusus (mineral, not_empty, soda, dishwasher)
- Bounding box coordinates
- Confidence scores
- SAM2 segmentation menggunakan best.pt bounding boxes

---

## 🐍 **Manual Testing (Python Scripts)**

### **4. Direct Python Execution**
```bash
# Aktifkan virtual environment
source venv/bin/activate

# Jalankan integrasi lengkap
python run_yolo_sam_integration.py

# Generate visualisasi
python visualize_results.py
```

### **5. Environment Detection**
```bash
# Cek environment capabilities
./scripts/detect_environment.sh

# Atau gunakan Python utility
python app/utils/environment_detector.py
```

---

## 📁 **Test Images**

### **Available Test Images:**
- `21_mineral.jpg` - Image dengan mineral (640x480)
- `24_mineral.jpg` - Image dengan mineral (864x480)
- `27_not_mineral.jpg` - Image tanpa mineral (640x480)

### **Image Locations:**
```
data/input/test_images/
├── 21_mineral.jpg
├── 24_mineral.jpg
└── 27_not_mineral.jpg
```

---

## 📊 **Output Files**

### **Detection Results (JSON):**
```
data/output/integration_results/
├── 21_mineral.jpg_yolo11m_detections.json
├── 21_mineral.jpg_best_pt_detections.json
├── 24_mineral.jpg_best_pt_detections.json
├── 27_not_mineral.jpg_yolo11m_detections.json
└── 27_not_mineral.jpg_best_pt_detections.json
```

### **Segmentation Masks (PNG):**
```
data/output/integration_results/
├── 21_mineral.jpg_yolo11m_masks/
│   └── mask_1_bottle.png
├── 21_mineral.jpg_best_pt_masks/
│   └── mask_1_mineral.png
├── 24_mineral.jpg_best_pt_masks/
│   └── mask_1_mineral.png
├── 27_not_mineral.jpg_yolo11m_masks/
│   └── mask_1_bottle.png
└── 27_not_mineral.jpg_best_pt_masks/
    └── mask_1_soda.png
```

### **Visualizations (PNG):**
```
data/output/visualizations/
├── 21_mineral_yolo11m_visualization.png
├── 21_mineral_best_pt_visualization.png
├── 24_mineral_best_pt_visualization.png
├── 27_not_mineral_yolo11m_visualization.png
└── 27_not_mineral_best_pt_visualization.png
```

---

## 🎯 **Expected Results**

### **YOLO11m Detections:**
- **21_mineral.jpg**: 1 bottle (conf: 0.744)
- **24_mineral.jpg**: 0 objects
- **27_not_mineral.jpg**: 2 objects (bottle: 0.674, dining table: 0.354)

### **best.pt Detections:**
- **21_mineral.jpg**: 1 mineral (conf: 0.842)
- **24_mineral.jpg**: 2 objects (mineral: 0.887, not_empty: 0.635)
- **27_not_mineral.jpg**: 2 objects (soda: 0.280, dishwasher: 0.271)

### **SAM2 Segmentations:**
- Pixel-perfect masks untuk setiap detected object
- Mask files dalam format PNG
- Overlay masks pada original images

---

## 🔍 **Testing Environment**

### **System Requirements:**
- **OS**: Ubuntu 22.04 LTS
- **Python**: 3.11+ (dalam virtual environment)
- **GPU**: NVIDIA RTX 3060 (11.6GB VRAM)
- **CUDA**: 12.9
- **PyTorch**: 2.0+ dengan CUDA support

### **Model Sources:**
- **YOLO11m**: Downloaded from Ultralytics assets
- **SAM2_b**: Downloaded from Ultralytics assets
- **best.pt**: Downloaded from GitHub release: https://github.com/vnot01/MySuperApps/releases/download/trained-models/best.pt

### **Environment Details:**
- **Virtual Environment**: `/home/my/MySuperApps/MyCV-Platform/venv`
- **Python Path**: `venv/bin/python`
- **Device**: CUDA (GPU mode)
- **Docker**: Tidak digunakan (host system)

---

## 🛠️ **Troubleshooting**

### **Common Issues:**

#### **1. Model Not Found:**
```bash
# Download model manually
source venv/bin/activate
python -c "from ultralytics import YOLO; YOLO('yolo11m.pt')"
```

#### **2. Virtual Environment Not Active:**
```bash
# Activate virtual environment
source venv/bin/activate
```

#### **3. GPU Not Detected:**
```bash
# Check GPU status
nvidia-smi
python -c "import torch; print(torch.cuda.is_available())"
```

#### **4. Permission Denied:**
```bash
# Make scripts executable
chmod +x scripts/*.sh
```

### **Debug Commands:**
```bash
# Check environment
./scripts/detect_environment.sh

# Check models
ls -la data/models/yolo/active/
ls -la data/models/sam/active/
ls -la data/models/trained/

# Check test images
ls -la data/input/test_images/

# Check output
ls -la data/output/integration_results/
ls -la data/output/visualizations/
```

---

## 📈 **Performance Metrics**

### **Processing Time:**
- **YOLO11m Detection**: ~2-3 seconds per image
- **best.pt Detection**: ~2-3 seconds per image
- **SAM2 Segmentation**: ~1-2 seconds per bounding box
- **Total per Image**: ~5-8 seconds

### **Memory Usage:**
- **GPU Memory**: ~2-3GB (RTX 3060)
- **RAM Usage**: ~1-2GB
- **Disk Usage**: ~200MB untuk models

### **Accuracy:**
- **YOLO11m**: Good untuk objek umum
- **best.pt**: Excellent untuk mineral detection
- **SAM2**: High precision segmentation

---

## 🎉 **Success Indicators**

### **✅ Test Passed:**
- All models loaded successfully
- Detections found on test images
- Segmentation masks generated
- Visualization images created
- No error messages in output

### **❌ Test Failed:**
- Model loading errors
- No detections found
- Segmentation failures
- Missing output files
- Error messages in output

---

## 📚 **Additional Resources**

- [Main README](README.md)
- [Environment Detection](ENVIRONMENT_DETECTION.md)
- [API Documentation](API.md)
- [Model Management](MODEL_MANAGEMENT.md)

---

**Status**: ✅ **READY FOR TESTING**  
**Version**: 1.0.0-alpha  
**Last Updated**: 11 September 2025
