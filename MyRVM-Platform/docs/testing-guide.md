# Panduan Testing POS System untuk RVM UI

## 🚀 Quick Start Testing

### 1. Setup Environment
```bash
# Pastikan Docker container berjalan
docker compose up -d

# Jalankan migration dan seeder
docker compose exec app php artisan migrate
docker compose exec app php artisan db:seed --class=RvmPosSystemSeeder
```

### 2. Akses Dashboard
```
URL: http://localhost:8000/admin/rvm-dashboard
```

## 🧪 Test Scenarios

### Test 1: Dashboard Loading
**Tujuan**: Memastikan dashboard dapat dimuat tanpa error authentication

**Langkah**:
1. Buka browser dan akses `http://localhost:8000/admin/rvm-dashboard`
2. Pastikan halaman dimuat tanpa error
3. Verifikasi data RVM ditampilkan dengan benar

**Expected Result**:
- ✅ Dashboard dimuat tanpa error 401 Unauthorized
- ✅ Data RVM ditampilkan (5 RVM test)
- ✅ Statistics cards menampilkan angka yang benar
- ✅ Chart status distribution ditampilkan

### Test 2: RVM Status Monitoring
**Tujuan**: Memastikan monitoring real-time berfungsi

**Langkah**:
1. Buka dashboard
2. Perhatikan statistics cards:
   - Total RVM: 5
   - Active Sessions: 0 (karena tidak ada session aktif)
   - Deposits Today: 0
   - Issues: 2 (Full + Error status)
3. Perhatikan chart status distribution
4. Klik tombol "Refresh" untuk test manual refresh

**Expected Result**:
- ✅ Statistics menampilkan data yang benar
- ✅ Chart menampilkan distribusi status
- ✅ Auto-refresh bekerja setiap 30 detik
- ✅ Manual refresh berfungsi

### Test 3: Remote Access Control
**Tujuan**: Test fitur remote access dengan PIN authentication

**Langkah**:
1. Di dashboard, klik tombol remote access (icon desktop) pada RVM pertama
2. Masukkan PIN: `1234`
3. Klik "Connect"
4. Verifikasi window baru terbuka dengan RVM UI

**Expected Result**:
- ✅ Modal remote access terbuka
- ✅ PIN validation berfungsi
- ✅ Window baru terbuka dengan RVM UI
- ✅ RVM UI menampilkan interface yang benar

### Test 4: Status Update
**Tujuan**: Test fitur update status RVM

**Langkah**:
1. Di dashboard, klik tombol edit status (icon edit) pada RVM pertama
2. Pilih status baru: "Maintenance"
3. Klik "Update"
4. Verifikasi status berubah di dashboard

**Expected Result**:
- ✅ Modal status update terbuka
- ✅ Status berhasil diupdate
- ✅ Dashboard refresh otomatis
- ✅ Status baru ditampilkan dengan warna yang sesuai

### Test 5: Bulk Operations
**Tujuan**: Test fitur bulk update status

**Langkah**:
1. Klik tombol "Set All to Maintenance Mode"
2. Konfirmasi dialog
3. Verifikasi semua RVM berubah ke status "Maintenance"
4. Klik tombol "Set All to Active"
5. Konfirmasi dialog
6. Verifikasi semua RVM berubah ke status "Active"

**Expected Result**:
- ✅ Dialog konfirmasi muncul
- ✅ Semua RVM berhasil diupdate
- ✅ Dashboard refresh otomatis
- ✅ Status semua RVM berubah sesuai perintah

### Test 6: Data Export
**Tujuan**: Test fitur export data monitoring

**Langkah**:
1. Klik tombol "Export Monitoring Data"
2. Verifikasi file JSON terdownload
3. Buka file dan periksa struktur data

**Expected Result**:
- ✅ File JSON terdownload
- ✅ File berisi data monitoring lengkap
- ✅ Struktur data sesuai dengan format yang ditentukan

### Test 7: Kiosk Mode
**Tujuan**: Test fitur kiosk mode pada RVM UI

**Langkah**:
1. Akses remote RVM UI (dari Test 3)
2. Verifikasi fullscreen mode aktif
3. Test keyboard shortcuts:
   - F11: Toggle fullscreen
   - Ctrl+Alt+E: Tampilkan exit button
   - F12: Harus disabled
4. Test exit protection dengan PIN

**Expected Result**:
- ✅ Fullscreen mode aktif otomatis
- ✅ Browser shortcuts disabled
- ✅ Exit button muncul dengan Ctrl+Alt+E
- ✅ Exit memerlukan PIN verification

## 🔧 API Testing

### Test API Endpoints dengan Postman/curl

#### 1. Get Monitoring Data
```bash
curl -X GET http://localhost:8000/api/v2/admin/rvm/monitoring \
  -H "Accept: application/json"
```

#### 2. Get RVM List
```bash
curl -X GET http://localhost:8000/api/v2/admin/rvm/list \
  -H "Accept: application/json"
```

#### 3. Remote Access Request
```bash
curl -X POST http://localhost:8000/api/v2/admin/rvm/1/remote-access \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"access_pin": "1234"}'
```

#### 4. Update RVM Status
```bash
curl -X POST http://localhost:8000/api/v2/admin/rvm/1/status \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"status": "maintenance"}'
```

## 🐛 Troubleshooting

### Common Issues dan Solutions:

#### 1. Dashboard Tidak Load Data
**Error**: "Failed to load monitoring data"
**Solution**:
- Pastikan seeder sudah dijalankan
- Check database connection
- Verifikasi route API accessible

#### 2. Remote Access Failed
**Error**: "Invalid access pin"
**Solution**:
- Gunakan PIN yang benar: 1234, 5678, 9999, 0000
- Pastikan RVM remote_access_enabled = true

#### 3. Status Update Failed
**Error**: "Failed to update status"
**Solution**:
- Check database connection
- Verifikasi RVM ID exists
- Check API endpoint accessibility

#### 4. Kiosk Mode Tidak Bekerja
**Error**: Browser shortcuts masih aktif
**Solution**:
- Pastikan kiosk_mode_enabled = true
- Check browser permissions untuk fullscreen
- Verifikasi JavaScript console untuk errors

## 📊 Test Data

### RVM Test Data:
1. **RVM Mall Central** (ID: 1)
   - Status: Active
   - PIN: 1234
   - Remote Access: Enabled
   - Kiosk Mode: Enabled

2. **RVM Office Building A** (ID: 2)
   - Status: Maintenance
   - PIN: 5678
   - Remote Access: Enabled
   - Kiosk Mode: Enabled

3. **RVM University Campus** (ID: 3)
   - Status: Active
   - PIN: 9999
   - Remote Access: Enabled
   - Kiosk Mode: Disabled

4. **RVM Hospital Main** (ID: 4)
   - Status: Full
   - PIN: 0000
   - Remote Access: Disabled
   - Kiosk Mode: Enabled

5. **RVM Airport Terminal** (ID: 5)
   - Status: Error
   - PIN: 1111
   - Remote Access: Enabled
   - Kiosk Mode: Enabled

### Valid PINs untuk Testing:
- **0000**: Super Admin
- **1234**: Admin
- **5678**: Operator
- **9999**: Technician
- **1111**: Custom PIN (Airport)

## 🎯 Success Criteria

### Dashboard Testing:
- ✅ Dashboard dimuat tanpa error authentication
- ✅ Data RVM ditampilkan dengan benar
- ✅ Statistics cards menampilkan angka yang akurat
- ✅ Chart status distribution berfungsi
- ✅ Auto-refresh bekerja setiap 30 detik
- ✅ Manual refresh berfungsi
- ✅ Tidak ada infinite scrolling
- ✅ Background tidak putih

### Remote Access Testing:
- ✅ PIN authentication berfungsi
- ✅ Remote access window terbuka
- ✅ RVM UI menampilkan interface yang benar
- ✅ Kiosk mode berfungsi dengan baik

### Control Testing:
- ✅ Status update individual berfungsi
- ✅ Bulk operations berfungsi
- ✅ Data export berfungsi
- ✅ Settings update berfungsi

## 📝 Test Report Template

```
Test Date: ___________
Tester: ___________
Environment: Docker (localhost:8000)

Test Results:
□ Dashboard Loading - PASS/FAIL
□ RVM Status Monitoring - PASS/FAIL
□ Remote Access Control - PASS/FAIL
□ Status Update - PASS/FAIL
□ Bulk Operations - PASS/FAIL
□ Data Export - PASS/FAIL
□ Kiosk Mode - PASS/FAIL

Issues Found:
1. ________________
2. ________________
3. ________________

Overall Status: PASS/FAIL
```

## 🚀 Next Steps

Setelah semua test passed:
1. ✅ Enable authentication untuk production
2. ✅ Configure proper user roles dan permissions
3. ✅ Setup HTTPS untuk security
4. ✅ Configure proper logging dan monitoring
5. ✅ Deploy ke production environment

---

**Happy Testing! 🧪**
