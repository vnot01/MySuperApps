# MyCV-Platform Environment Detection

## 🎯 **Overview**

MyCV-Platform sekarang dilengkapi dengan sistem deteksi environment yang komprehensif yang memastikan:

- ✅ **Virtual Environment**: Konsisten menggunakan virtual environment di semua eksekusi Python
- ✅ **GPU/CPU Mode**: Deteksi otomatis dan informasi yang jelas tentang mode processing
- ✅ **Mock Data Testing**: Validasi sistem dengan data sintetis
- ✅ **Environment Validation**: Pengecekan capabilities sebelum menjalankan aplikasi

---

## 🔧 **Features**

### **1. Virtual Environment Detection**
- Otomatis deteksi apakah running di virtual environment
- Validasi virtual environment sebelum menjalankan script
- Informasi path virtual environment yang aktif

### **2. GPU/CPU Mode Detection**
- **🚀 GPU MODE**: Deteksi NVIDIA GPU dan CUDA support
- **💻 CPU MODE**: Fallback ke CPU jika GPU tidak tersedia
- Informasi detail tentang:
  - GPU name dan memory
  - CUDA version
  - CPU threads count

### **3. Mock Data Testing**
- **🧪 MOCK DATA MODE**: Testing dengan data sintetis
- Validasi tensor operations
- Validasi image processing
- Deteksi device (GPU/CPU) yang digunakan

---

## 📚 **Usage**

### **1. Command Line Detection**

#### **Host System:**
```bash
# Deteksi environment capabilities
./scripts/detect_environment.sh

# Atau gunakan Python utility langsung
python3 app/utils/environment_detector.py
```

#### **Docker Container:**
```bash
# Deteksi environment di dalam container
./scripts/docker_detect_environment.sh

# Atau langsung di container
docker exec myc-v-platform /app/venv/bin/python /app/app/utils/environment_detector.py
```

### **2. Python API Usage**

```python
from app.utils.environment_detector import EnvironmentDetector

# Create detector instance
detector = EnvironmentDetector()

# Run all detection checks
results = detector.detect_all()

# Print summary
detector.print_summary(results)

# Access specific results
print(f"GPU Mode: {results['gpu_capabilities']['mode']}")
print(f"Virtual Environment: {results['virtual_environment']['is_venv']}")
print(f"Mock Data Test: {results['mock_data_test']['tensor_test']}")
```

---

## 🔍 **Detection Results**

### **Virtual Environment Status:**
```
✅ Running in virtual environment: /app/venv
✅ Virtual environment found and ready
```

### **GPU Mode Detection:**
```
🚀 GPU MODE: Using CUDA device - NVIDIA GeForce RTX 4090
   GPU Memory: 24.0 GB
   CUDA Version: 11.8
   GPU Count: 1
```

### **CPU Mode Detection:**
```
💻 CPU MODE: Using CPU for inference
   CPU Threads: 8
   PyTorch Version: 2.0.1
```

### **Mock Data Testing:**
```
🧪 Testing with mock data...
✅ Mock tensor created on GPU
✅ Basic tensor operations successful
✅ Mock image processing successful
   Image shape: torch.Size([1, 3, 640, 640])
   Device: cuda:0
🧪 MOCK DATA MODE: All tests passed
```

---

## ⚙️ **Configuration**

### **Environment Variables:**

```bash
# GPU Configuration
CUDA_VISIBLE_DEVICES=0
USE_GPU=true
AUTO_DETECT_GPU=true
FORCE_CPU_MODE=false

# Processing Mode
PROCESSING_MODE=auto  # auto, gpu, cpu
MOCK_DATA_MODE=false
ENABLE_MOCK_DATA_TESTING=true

# Environment Detection
ENABLE_ENVIRONMENT_DETECTION=true
ENVIRONMENT_DETECTION_VERBOSE=true
AUTO_RUN_ENVIRONMENT_TESTS=true
```

---

## 🚀 **Integration**

### **1. Setup Script Integration:**
```bash
# setup.sh automatically runs environment detection
./scripts/setup.sh
```

### **2. Model Installation Integration:**
```bash
# install_models.sh includes environment validation
./scripts/install_models.sh
```

### **3. Docker Startup Integration:**
```bash
# Container automatically runs environment detection on startup
docker-compose up -d
```

---

## 🔧 **Troubleshooting**

### **Common Issues:**

#### **1. Virtual Environment Not Found:**
```
❌ Virtual environment not found! Please run ./scripts/setup.sh first
```
**Solution:** Run `./scripts/setup.sh` to create virtual environment

#### **2. GPU Not Detected:**
```
⚠️  NVIDIA GPU not detected - will use CPU mode
```
**Solution:** 
- Install NVIDIA drivers
- Install nvidia-docker2
- Check CUDA installation

#### **3. Mock Data Test Failed:**
```
❌ Mock data test failed: CUDA out of memory
```
**Solution:**
- Reduce batch size
- Use CPU mode
- Check GPU memory usage

### **Debug Commands:**

```bash
# Check virtual environment
echo $VIRTUAL_ENV

# Check GPU status
nvidia-smi

# Check PyTorch CUDA
python3 -c "import torch; print(torch.cuda.is_available())"

# Run detailed detection
python3 app/utils/environment_detector.py
```

---

## 📊 **Performance Impact**

- **Startup Time**: +2-3 seconds untuk environment detection
- **Memory Usage**: Minimal (hanya untuk testing)
- **CPU Usage**: Minimal (hanya untuk validation)
- **GPU Usage**: Minimal (hanya untuk capability testing)

---

## 🎯 **Best Practices**

1. **Always run environment detection** sebelum menjalankan aplikasi
2. **Check logs** untuk informasi mode yang digunakan
3. **Monitor GPU memory** jika menggunakan GPU mode
4. **Use mock data testing** untuk validasi sistem
5. **Keep virtual environment** aktif saat development

---

**Status**: ✅ **IMPLEMENTED**  
**Version**: 1.0.0-alpha  
**Last Updated**: 10 September 2025
