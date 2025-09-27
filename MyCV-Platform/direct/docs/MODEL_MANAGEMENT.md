# MyCV-Platform Model Management

## 📦 **Model Overview**

MyCV-Platform menggunakan tiga jenis model utama:

### **1. YOLO11 Models (Object Detection)**
- **Source**: Ultralytics GitHub releases
- **URL**: `https://github.com/ultralytics/assets/releases/download/v8.3.0/`
- **Models**:
  - `yolo11n.pt` - Nano (2.6M params, fastest)
  - `yolo11s.pt` - Small (9.4M params, balanced)
  - `yolo11m.pt` - Medium (20.1M params, higher accuracy)
  - `yolo11l.pt` - Large (25.3M params, high accuracy)
  - `yolo11x.pt` - Extra Large (68.2M params, highest accuracy)

### **2. SAM2 Models (Segmentation)**
- **Source**: Ultralytics GitHub releases
- **URL**: `https://github.com/ultralytics/assets/releases/download/v8.3.0/`
- **Models**:
  - `sam2_b.pt` - Base (fastest segmentation)
  - `sam2_l.pt` - Large (best segmentation quality)

### **3. Custom Trained Models**
- **Source**: GitHub releases
- **URL**: `https://github.com/vnot01/MySuperApps/releases/download/trained-models/`
- **Models**:
  - `best.pt` - Custom trained model for mineral detection

---

## 🗂️ **Directory Structure**

```
data/models/
├── yolo/
│   ├── active/          # Active YOLO models (ready to use)
│   └── downloads/       # Downloaded YOLO models (temporary)
├── sam/
│   ├── active/          # Active SAM models (ready to use)
│   └── downloads/       # Downloaded SAM models (temporary)
└── trained/
    └── *.pt            # Custom trained models
```

---

## 🚀 **Model Installation Methods**

### **Method 1: Automatic Installation (Recommended)**
```bash
# Install all default models
./scripts/install_models.sh

# Or install specific models
./scripts/install_models.sh --yolo yolo11m --sam sam2_b
```

### **Method 2: Test Scripts (Auto-download)**
```bash
# Fresh integration test (downloads models as needed)
./scripts/run_fresh_integration_test.sh

# Individual model tests
./scripts/run_yolo11m_test.sh
./scripts/run_best_pt_test.sh
```

### **Method 3: Manual Download**
```bash
# Download YOLO11m
wget -O data/models/yolo/active/yolo11m.pt \
  https://github.com/ultralytics/assets/releases/download/v8.3.0/yolo11m.pt

# Download SAM2_b
wget -O data/models/sam/active/sam2_b.pt \
  https://github.com/ultralytics/assets/releases/download/v8.3.0/sam2_b.pt

# Download best.pt
wget -O data/models/trained/best.pt \
  https://github.com/vnot01/MySuperApps/releases/download/trained-models/best.pt
```

---

## 🧹 **Model Cleanup**

### **Clean All Models**
```bash
# Remove all downloaded models
./scripts/clean_models.sh
```

### **Manual Cleanup**
```bash
# Clean specific model types
rm -f data/models/yolo/active/*.pt
rm -f data/models/sam/active/*.pt
rm -f data/models/trained/*.pt

# Clean all models
find . -name "*.pt" -type f -delete
```

---

## 📊 **Model Information**

### **File Sizes**
- **yolo11n.pt**: ~2.6MB
- **yolo11s.pt**: ~9.4MB
- **yolo11m.pt**: ~40MB
- **yolo11l.pt**: ~50MB
- **yolo11x.pt**: ~140MB
- **sam2_b.pt**: ~160MB
- **sam2_l.pt**: ~350MB
- **best.pt**: ~19MB

### **Total Space Requirements**
- **Minimum (yolo11m + sam2_b + best.pt)**: ~220MB
- **Full installation (all models)**: ~800MB

---

## 🔧 **Script Details**

### **install_models.sh**
- **Purpose**: Main model installation script
- **Method**: wget with progress bar
- **Features**:
  - Downloads from Ultralytics releases
  - Validates downloaded files
  - Moves to active directories
  - Supports selective installation

### **run_fresh_integration_test.sh**
- **Purpose**: Fresh integration test with auto-download
- **Method**: Python requests + tqdm
- **Features**:
  - Downloads YOLO11m and SAM2_b if missing
  - Downloads best.pt from GitHub release
  - Cleans previous results
  - Runs complete integration test

### **run_yolo11m_test.sh**
- **Purpose**: YOLO11m + SAM2 test only
- **Method**: Python requests + tqdm
- **Features**:
  - Downloads YOLO11m and SAM2_b if missing
  - Runs YOLO11m detection + SAM2 segmentation
  - Saves results in JSON and PNG formats

### **run_best_pt_test.sh**
- **Purpose**: best.pt + SAM2 test only
- **Method**: Python requests + tqdm
- **Features**:
  - Downloads best.pt from GitHub release
  - Downloads SAM2_b if missing
  - Runs best.pt detection + SAM2 segmentation
  - Saves results in JSON and PNG formats

### **clean_models.sh**
- **Purpose**: Model cleanup script
- **Features**:
  - Removes all .pt files
  - Cleans all model directories
  - Shows disk space freed
  - Verifies cleanup

---

## 🚨 **Troubleshooting**

### **Common Issues**

#### **1. Download Failures**
```bash
# Check internet connection
ping github.com

# Check disk space
df -h

# Try manual download
wget --progress=bar:force -O test.pt <URL>
```

#### **2. Permission Issues**
```bash
# Make scripts executable
chmod +x scripts/*.sh

# Check directory permissions
ls -la data/models/
```

#### **3. Model Loading Errors**
```bash
# Check file integrity
file data/models/yolo/active/yolo11m.pt

# Check file size
ls -lh data/models/yolo/active/

# Re-download if corrupted
rm data/models/yolo/active/yolo11m.pt
./scripts/install_models.sh
```

#### **4. Virtual Environment Issues**
```bash
# Activate virtual environment
source venv/bin/activate

# Check Python path
which python
```

---

## 📈 **Performance Tips**

### **1. Selective Installation**
- Install only needed models to save space
- Use `yolo11m.pt` for balanced speed/accuracy
- Use `sam2_b.pt` for faster segmentation

### **2. Model Caching**
- Models are cached in `active/` directories
- Re-download only when needed
- Use `clean_models.sh` to free space

### **3. Network Optimization**
- Use stable internet connection
- Consider downloading during off-peak hours
- Use `wget` for better resume capability

---

## 🔄 **Model Updates**

### **Checking for Updates**
```bash
# Check Ultralytics releases
curl -s https://api.github.com/repos/ultralytics/assets/releases/latest | grep tag_name

# Check custom model releases
curl -s https://api.github.com/repos/vnot01/MySuperApps/releases/latest | grep tag_name
```

### **Updating Models**
```bash
# Clean old models
./scripts/clean_models.sh

# Reinstall with latest versions
./scripts/install_models.sh
```

---

## 📚 **Additional Resources**

- [Ultralytics YOLO11 Documentation](https://docs.ultralytics.com/)
- [SAM2 Documentation](https://github.com/facebookresearch/segment-anything-2)
- [PyTorch Model Loading](https://pytorch.org/tutorials/beginner/saving_loading_models.html)

---

**Status**: ✅ **READY FOR USE**  
**Version**: 1.0.0-alpha  
**Last Updated**: 11 September 2025
