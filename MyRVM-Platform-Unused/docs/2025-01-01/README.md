# Multi-Jetson Orin Integration - Documentation

## Tanggal: 1 Januari 2025
## Versi: 1.0.0
## Status: Completed

## Overview
Dokumentasi lengkap untuk integrasi Multi-Jetson Orin ke dalam MyRVM-Platform dashboard. Perubahan ini menghapus status cards individual dan mengintegrasikan status Jetson ke dalam RVM cards yang sudah ada.

## Struktur Dokumentasi

### 📁 frontend-changes/
Perubahan pada tampilan dan interaksi user
- `DASHBOARD_UI_CHANGES.md` - Perubahan UI dashboard dan RVM cards

### 📁 backend-changes/
Perubahan pada controller dan business logic
- `CONTROLLER_UPDATES.md` - Update AdminRvmController dan RvmController

### 📁 api-changes/
Perubahan pada API endpoints dan responses
- `API_ENDPOINTS.md` - Dokumentasi lengkap API endpoints

### 📁 integration-changes/
Perubahan pada service dan integrasi
- `JETSON_SERVICE_INTEGRATION.md` - JetsonOrinService dan konfigurasi

### 📁 removed-features/
Fitur yang dihapus dan alasan penghapusan
- `REMOVED_UI_COMPONENTS.md` - Komponen UI yang dihapus

### 📄 MULTI_JETSON_INTEGRATION.md
Dokumentasi utama dan overview lengkap

## Quick Start

### 1. Baca Overview
Mulai dengan `MULTI_JETSON_INTEGRATION.md` untuk memahami konsep dan arsitektur.

### 2. Pahami Perubahan
- Frontend: `frontend-changes/DASHBOARD_UI_CHANGES.md`
- Backend: `backend-changes/CONTROLLER_UPDATES.md`
- API: `api-changes/API_ENDPOINTS.md`

### 3. Implementasi
- Service: `integration-changes/JETSON_SERVICE_INTEGRATION.md`
- Removed: `removed-features/REMOVED_UI_COMPONENTS.md`

## Key Changes Summary

### ✅ Added
- Multi-Jetson Orin support
- Status Jetson di RVM cards
- Response time tracking
- Individual RVM Jetson health
- Comprehensive API endpoints

### ❌ Removed
- Individual Jetson Orin status card
- CV Server status card
- `jetson-integration.js` file
- CV Server dari dashboard statistics

### 🔄 Modified
- RVM cards untuk menampilkan status Jetson
- AdminRvmController untuk multi-Jetson
- Statistics untuk menghitung Jetson terhubung
- API responses untuk include Jetson data

## Testing

### Manual Testing
1. Buka dashboard: `http://100.123.143.87:8001/admin/dashboard`
2. Login dengan kredensial admin
3. Verifikasi RVM cards menampilkan status Jetson
4. Test ping individual RVM

### API Testing
```bash
# Test RVM monitoring
curl -X GET "http://100.123.143.87:8001/admin/rvm/monitoring" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <token>"

# Test individual Jetson health
curl -X GET "http://100.123.143.87:8001/api/admin/jetson/1/health" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <token>"
```

## Configuration

### Environment Variables
```env
JETSON_API_BASE_URL=http://100.117.234.2:5000
JETSON_TIMEOUT=5
CV_SERVER_API_BASE_URL=http://100.98.142.94:5000
CV_SERVER_TIMEOUT=5
```

### RVM Configuration
- Set `ip_address` field di `reverse_vending_machines` table
- Format: `100.117.234.2` (tanpa port)
- Port 5000 digunakan untuk API Jetson

## Troubleshooting

### Jetson Tidak Terhubung
1. Cek IP address RVM
2. Cek koneksi network ke Jetson
3. Cek status API Jetson: `http://{ip}:5000/api/health`
4. Cek logs Laravel

### Performance Issues
1. Cek response time Jetson API
2. Cek timeout configuration
3. Cek concurrent requests

## Support

### Documentation
- Lihat file dokumentasi di folder masing-masing
- API documentation di `api-changes/API_ENDPOINTS.md`
- Service documentation di `integration-changes/JETSON_SERVICE_INTEGRATION.md`

### Issues
- Cek logs Laravel untuk error details
- Cek network connectivity ke Jetson devices
- Cek configuration di `config/services.php`

## Changelog

### v1.0.0 (2025-01-01)
- ✅ Initial release
- ✅ Multi-Jetson Orin support
- ✅ Integrated status di RVM cards
- ✅ Comprehensive API endpoints
- ✅ Removed individual status cards
- ✅ CV Server tidak ditampilkan di dashboard

## Future Enhancements

### Planned Features
- [ ] Real-time status updates via WebSocket
- [ ] Jetson health history charts
- [ ] Bulk Jetson operations
- [ ] Jetson performance metrics
- [ ] Auto-discovery of Jetson devices

### Configuration Options
- [ ] Customizable timeout values
- [ ] Retry mechanisms
- [ ] Health check intervals
- [ ] Alert thresholds
