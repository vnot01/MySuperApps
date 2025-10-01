# Template Files untuk MyRVM-Platform

Folder ini berisi template file yang harus diimplementasikan di MyRVM-Platform.

## 📁 File Template

### 1. Database Migration
- **detection_results_migration.php**
- **Lokasi di MyRVM-Platform**: `database/migrations/`
- **Fungsi**: Membuat tabel `detection_results`

### 2. Laravel Model
- **DetectionResult_model.php**
- **Lokasi di MyRVM-Platform**: `app/Models/DetectionResult.php`
- **Fungsi**: Model untuk mengelola data detection results

### 3. API Controller
- **rvm_platform_endpoints_example.php**
- **Lokasi di MyRVM-Platform**: `app/Http/Controllers/Api/RvmIntegrationController.php`
- **Fungsi**: Endpoint API untuk integrasi dengan MyCV-Platform

### 4. API Routes
- **rvm_routes_example.php**
- **Lokasi di MyRVM-Platform**: `routes/api.php` (tambahkan routes)
- **Fungsi**: Definisi route untuk API endpoints

## 🚀 Implementasi

### Manual Setup
1. Copy file ke lokasi yang sesuai di MyRVM-Platform
2. Sesuaikan namespace dan import
3. Jalankan migration: `php artisan migrate`
4. Test endpoints

### Otomatis Setup
Gunakan script di folder `../setup/` untuk setup otomatis.

## 📋 Endpoints yang Dibuat

- `POST /api/rvm/validate-api-key` - Validasi API key RVM
- `GET /api/rvm/{id}` - Get informasi RVM
- `GET /api/rvm/{id}/stats` - Get statistik RVM
- `POST /api/detections/store` - Simpan hasil deteksi

## ⚠️ Catatan

- Pastikan tabel `reverse_vending_machines` sudah ada
- Sesuaikan field name sesuai dengan database yang ada
- Test semua endpoints setelah implementasi
