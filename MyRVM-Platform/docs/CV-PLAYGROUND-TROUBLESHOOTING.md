# Computer Vision Playground V2 - Troubleshooting Guide

## 🚨 **ERROR YANG SUDAH DIPERBAIKI**

### **1. "Network error: The string did not match the expected pattern"**

**Penyebab:**
- CSRF token mismatch (HTTP 419)
- Laravel validation gagal untuk file .pt
- Response format tidak sesuai dengan yang diharapkan frontend

**Solusi yang Diterapkan:**
```php
// Manual validation untuk file .pt (Laravel tidak mengenali MIME type .pt)
$modelExtension = strtolower($modelFile->getClientOriginalExtension());
if (!in_array($modelExtension, ['pt', 'pth', 'onnx'])) {
    return response()->json([
        'success' => false,
        'error' => 'Model file must be .pt, .pth, or .onnx format'
    ], 422);
}
```

```javascript
// Enhanced error handling di frontend
if (!response.ok) {
    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
}

const contentType = response.headers.get('content-type');
if (!contentType || !contentType.includes('application/json')) {
    const text = await response.text();
    console.error('Non-JSON response:', text);
    throw new Error('Server returned non-JSON response');
}
```

### **2. File Upload Size Limit**

**Penyebab:**
- Nginx default limit terlalu kecil untuk model YOLO
- PHP upload limit tidak mencukupi

**Solusi yang Diterapkan:**
```nginx
# nginx configuration
client_max_body_size 50M;
client_body_timeout 60s;
client_header_timeout 60s;
```

### **3. Command Execution Issues**

**Penyebab:**
- `escapeshellcmd()` terlalu agresif dalam escaping
- Path dengan spasi tidak ter-handle dengan baik

**Solusi yang Diterapkan:**
```php
// Menggunakan escapeshellarg() untuk setiap parameter
$command = "python3 " . escapeshellarg($scriptPath) . " " . 
           escapeshellarg($absoluteModelPath) . " " . 
           escapeshellarg($absoluteImagePath) . " " . 
           escapeshellarg($absoluteOutputDir) . " " . 
           escapeshellarg($request->confidence);
```

## 🔧 **CARA DEBUGGING**

### **1. Cek Container Status**
```bash
docker-compose ps
```

### **2. Cek Logs**
```bash
# Laravel logs
docker exec myrvm_app_dev tail -20 /var/www/html/storage/logs/laravel.log

# Container logs
docker-compose logs app --tail=20
docker-compose logs web --tail=20
```

### **3. Test Python Script Langsung**
```bash
docker exec myrvm_app_dev python3 /var/www/html/storage/app/cv_scripts/cv_tester.py \
    /var/www/html/public/weights/best.pt \
    /var/www/html/storage/app/public/images/test_image1.jpg \
    /tmp/test_output \
    0.5
```

### **4. Test API Endpoint**
```bash
# Test dashboard
curl -I http://localhost:8000/cv-playground

# Test dengan file (contoh)
curl -X POST \
  -F "model_file=@best.pt" \
  -F "image_file=@test_image.jpg" \
  -F "confidence=0.5" \
  -H "X-CSRF-TOKEN: $(curl -s http://localhost:8000/cv-playground | grep csrf-token | cut -d'"' -f4)" \
  http://localhost:8000/cv-playground/run-test
```

## 🐛 **COMMON ISSUES & SOLUTIONS**

### **Issue 1: 504 Gateway Timeout**
```bash
# Restart containers
docker-compose restart

# Check nginx configuration
docker exec myrvm_web_dev nginx -t
```

### **Issue 2: File Upload Fails**
```bash
# Check file permissions
docker exec myrvm_app_dev ls -la /var/www/html/storage/app/

# Check nginx upload limits
docker exec myrvm_web_dev cat /etc/nginx/conf.d/default.conf | grep client_max_body_size
```

### **Issue 3: Python Script Error**
```bash
# Check Python environment
docker exec myrvm_app_dev python3 --version
docker exec myrvm_app_dev python3 -c "import cv2; print(cv2.__version__)"

# Test script with verbose output
docker exec myrvm_app_dev python3 -u /var/www/html/storage/app/cv_scripts/cv_tester.py
```

### **Issue 4: CSRF Token Mismatch**
```bash
# Clear Laravel cache
docker exec myrvm_app_dev php artisan cache:clear
docker exec myrvm_app_dev php artisan config:clear
docker exec myrvm_app_dev php artisan route:clear
```

## 📊 **PERFORMANCE MONITORING**

### **Check Resource Usage**
```bash
# Container resource usage
docker stats

# Disk usage
docker exec myrvm_app_dev df -h
docker exec myrvm_app_dev du -sh /var/www/html/storage/app/
```

### **Check Network Connectivity**
```bash
# Test internal connectivity
docker exec myrvm_web_dev nc -zv myrvm_app_dev 9000

# Test external access
curl -v http://localhost:8000/cv-playground
```

## 🔍 **DEBUGGING STEPS**

### **Step 1: Verify Basic Setup**
1. ✅ Docker containers running
2. ✅ Nginx responding
3. ✅ PHP-FPM working
4. ✅ Python environment ready

### **Step 2: Test Individual Components**
1. ✅ Laravel routes working
2. ✅ File upload functionality
3. ✅ Python script execution
4. ✅ JSON response format

### **Step 3: Test End-to-End**
1. ✅ Upload model file
2. ✅ Upload test image
3. ✅ Run CV test
4. ✅ View results

## 📝 **LOGGING & MONITORING**

### **Enable Debug Logging**
```php
// In CvPlaygroundController.php
Log::info('CV Playground: Starting test', [
    'model_file' => $request->file('model_file')->getClientOriginalName(),
    'image_file' => $request->file('image_file')->getClientOriginalName(),
    'confidence' => $request->confidence
]);
```

### **Monitor Logs in Real-time**
```bash
# Laravel logs
docker exec myrvm_app_dev tail -f /var/www/html/storage/logs/laravel.log

# Container logs
docker-compose logs -f app
```

## 🚀 **OPTIMIZATION TIPS**

### **1. File Upload Optimization**
- Compress model files jika memungkinkan
- Gunakan format gambar yang optimal (JPEG untuk foto, PNG untuk grafik)
- Set confidence threshold yang sesuai

### **2. Performance Tuning**
- Monitor memory usage saat processing
- Consider batch processing untuk multiple images
- Implement caching untuk hasil yang sama

### **3. Error Handling**
- Implement retry mechanism untuk transient errors
- Add progress indicators untuk long-running operations
- Provide detailed error messages untuk debugging

---

**Status**: ✅ **SEMUA ERROR UTAMA SUDAH DIPERBAIKI**
**Last Updated**: September 10, 2025
**Version**: CV Playground V2.1
