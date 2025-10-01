# Contoh Penggunaan dan Testing

Folder ini berisi script testing dan contoh penggunaan integrasi RVM.

## 📁 File

### Testing Scripts
- **test_rvm_integration.py** - Test integrasi dasar
- **test_full_integration.py** - Test integrasi lengkap

## 🧪 Testing

### Test Integrasi Dasar
```bash
python3 test_rvm_integration.py
```

**Test yang dilakukan:**
- ✅ Health check API
- ✅ RVM validation
- ✅ File upload dengan RVM
- ✅ Detections dengan RVM filtering
- ✅ Search dengan RVM filtering
- ✅ RVM statistics
- ✅ Legacy compatibility

### Test Integrasi Lengkap
```bash
python3 test_full_integration.py
```

**Test yang dilakukan:**
- ✅ Semua test dasar
- ✅ Directory structure check
- ✅ Error handling scenarios
- ✅ Performance testing
- ✅ Security testing

## 🔧 Konfigurasi Testing

Edit konfigurasi di awal file:

```python
# Configuration
API_BASE_URL = "http://100.117.234.2:5000"
RVM_API_KEY = "your_rvm_api_key_here"
RVM_ID = 1
```

## 📊 Hasil Testing

Script akan menampilkan:
- ✅ Status setiap test (PASS/FAIL)
- 📋 Summary hasil testing
- 🔧 Troubleshooting tips jika ada error

## 🚀 Contoh Penggunaan API

### Upload dengan RVM
```python
import requests

headers = {"X-RVM-API-Key": "your_api_key"}
files = {'files': open('image.jpg', 'rb')}
data = {'rvm_id': 1, 'user_id': 'user123'}

response = requests.post(
    'http://100.117.234.2:5000/api/upload',
    headers=headers,
    files=files,
    data=data
)
```

### Get Detections
```python
headers = {"X-RVM-API-Key": "your_api_key"}
params = {'rvm_id': 1, 'page': 1, 'limit': 20}

response = requests.get(
    'http://100.117.234.2:5000/api/detections',
    headers=headers,
    params=params
)
```

## 📞 Troubleshooting

Jika test gagal:
1. Pastikan MyCV-Platform API running
2. Cek konfigurasi RVM Platform
3. Verifikasi API keys
4. Check network connectivity
