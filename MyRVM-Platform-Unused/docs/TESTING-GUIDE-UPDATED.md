# 🧪 **PANDUAN TESTING TAHAP 1 - UPDATED**

## 🎯 **OVERVIEW**

Panduan testing yang telah diperbaiki untuk **TAHAP 1: Production-ready Authentication** dengan route yang benar dan Docker entrypoint yang sudah diintegrasikan.

---

## 🔧 **PERBAIKAN YANG TELAH DILAKUKAN**

### **1. Route Conflict Fixed** ✅
- ✅ **Login route** diubah dari `/login` ke `/admin/login` (menghindari konflik dengan Laravel Breeze)
- ✅ **Dashboard redirects** diupdate ke `/admin/login`
- ✅ **AuthManager** diupdate untuk redirect ke route yang benar

### **2. Docker Entrypoint Integration** ✅
- ✅ **Permission commands** sudah diintegrasikan ke `docker/app/entrypoint.sh`
- ✅ **Cache clear commands** sudah ditambahkan ke entrypoint
- ✅ **Automatic setup** saat container start

### **3. User Database Verified** ✅
- ✅ **Test users** sudah tersedia di database
- ✅ **API login** sudah berfungsi dengan baik
- ✅ **Role system** sudah bekerja

---

## 🚀 **CARA TESTING YANG BENAR**

### **1. PREPARATION**

```bash
# Container sudah running dengan entrypoint yang sudah diperbaiki
docker compose ps

# Jika perlu restart
docker compose down && docker compose up -d
```

### **2. TEST LOGIN PAGE**

#### **Step 1: Akses Login Page yang Benar**
```
URL: http://localhost:8000/admin/login
Expected: Login page dengan form email/password
```

#### **Step 2: Test Login dengan Credentials yang Benar**

| Role | Email | Password | Expected Result |
|------|-------|----------|-----------------|
| **Super Admin** | admin@myrvm.com | password | ✅ Login success, redirect to dashboard |
| **Admin** | admin2@myrvm.com | password | ✅ Login success, redirect to dashboard |
| **Operator** | operator@myrvm.com | password | ✅ Login success, redirect to dashboard |
| **Technician** | technician@myrvm.com | password | ✅ Login success, redirect to dashboard |
| **Tenant** | tenant@myrvm.com | password | ✅ Login success, redirect to dashboard |
| **John Doe** | john@test.com | password123 | ✅ Login success, redirect to dashboard |

### **3. TEST DASHBOARD AUTHENTICATION**

#### **Step 1: Direct Access Test**
```
URL: http://localhost:8000/admin/rvm-dashboard
Expected: Redirect to /admin/login (jika tidak authenticated)
```

#### **Step 2: Authenticated Access Test**
1. Login dengan credentials yang valid di `/admin/login`
2. Akan redirect ke dashboard: `http://localhost:8000/admin/rvm-dashboard`
3. **Expected Results:**
   - ✅ Dashboard loads successfully
   - ✅ User name dan role ditampilkan di header
   - ✅ Logout button tersedia
   - ✅ RVM monitoring data loads

### **4. TEST API AUTHENTICATION**

#### **Step 1: Test Login API**
```bash
# Test dengan PowerShell
Invoke-WebRequest -Uri "http://localhost:8000/api/v2/auth/login" -Method POST -Headers @{"Content-Type"="application/json"; "Accept"="application/json"} -Body '{"email":"john@test.com","password":"password123"}'
```

**Expected Response:**
```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "user": {
      "id": 4,
      "name": "John Doe",
      "email": "john@test.com",
      "role": "User"
    },
    "token": "6|wraM9bFliAFms6MB7guc0lWQXLJObS700rwXJzAa3f1fc76c",
    "token_type": "Bearer"
  }
}
```

#### **Step 2: Test Protected API Endpoints**
```bash
# Test /auth/me endpoint (ganti YOUR_TOKEN dengan token dari login)
Invoke-WebRequest -Uri "http://localhost:8000/api/v2/auth/me" -Method GET -Headers @{"Authorization"="Bearer YOUR_TOKEN"; "Accept"="application/json"}
```

### **5. TEST DOCKER ENTRYPOINT**

#### **Step 1: Check Container Logs**
```bash
# Cek apakah entrypoint berjalan dengan baik
docker compose logs app --tail=20

# Expected: Permission setup dan cache clear berjalan
```

#### **Step 2: Test Permission**
```bash
# Test apakah storage permission sudah benar
docker compose exec app ls -la storage/

# Expected: Permission 777 atau 775 untuk storage directory
```

---

## 🐛 **TROUBLESHOOTING**

### **Common Issues & Solutions:**

#### **1. Login Page Not Found (404)**
```bash
# Pastikan menggunakan URL yang benar
http://localhost:8000/admin/login  # ✅ BENAR
http://localhost:8000/login        # ❌ SALAH (konflik dengan Breeze)
```

#### **2. Permission Denied Error**
```bash
# Entrypoint sudah mengatasi ini, tapi jika masih ada:
docker compose exec app chmod -R 777 storage
docker compose exec app chown -R www-data:www-data storage
```

#### **3. Cache Issues**
```bash
# Entrypoint sudah mengatasi ini, tapi jika masih ada:
docker compose exec app php artisan cache:clear
docker compose exec app php artisan view:clear
docker compose exec app php artisan config:clear
```

#### **4. Database Issues**
```bash
# Cek apakah user ada di database
docker compose exec app php check_users.php
```

---

## ✅ **TESTING CHECKLIST**

### **Authentication Flow:**
- [ ] Login page loads at `/admin/login`
- [ ] Form validation works
- [ ] Login with valid credentials succeeds
- [ ] Login with invalid credentials fails
- [ ] Dashboard redirects unauthenticated users to `/admin/login`
- [ ] Dashboard loads for authenticated users
- [ ] User info displays correctly in header
- [ ] Logout functionality works
- [ ] Session cleanup after logout

### **API Authentication:**
- [ ] Login API returns token
- [ ] Protected endpoints require token
- [ ] Token validation works
- [ ] Unauthorized access blocked
- [ ] User info API works

### **Docker Integration:**
- [ ] Container starts without permission errors
- [ ] Entrypoint runs successfully
- [ ] Storage permissions set correctly
- [ ] Cache cleared automatically
- [ ] No manual intervention needed

---

## 🎯 **EXPECTED RESULTS**

### **Success Indicators:**
- ✅ Login page accessible at `/admin/login`
- ✅ Dashboard accessible at `/admin/rvm-dashboard` (after login)
- ✅ User info displayed in dashboard header
- ✅ Logout button functional
- ✅ API endpoints protected
- ✅ Role-based access working
- ✅ No permission errors
- ✅ Smooth user experience
- ✅ Docker entrypoint working automatically

### **Performance Indicators:**
- ✅ Login response time < 2 seconds
- ✅ Dashboard load time < 3 seconds
- ✅ API response time < 1 second
- ✅ Container startup time < 30 seconds
- ✅ No manual permission fixes needed

---

## 📝 **TESTING NOTES**

1. **URL yang Benar**: Selalu gunakan `/admin/login` bukan `/login`
2. **Credentials yang Benar**: Gunakan credentials dari seeder yang sudah dibuat
3. **Docker Integration**: Permission dan cache sudah diatasi otomatis
4. **Browser Testing**: Test dengan browser yang berbeda
5. **API Testing**: Gunakan PowerShell untuk test API

---

## 🔗 **LINKS UNTUK TESTING**

- **Login Page**: http://localhost:8000/admin/login
- **Dashboard**: http://localhost:8000/admin/rvm-dashboard
- **API Login**: http://localhost:8000/api/v2/auth/login
- **API Me**: http://localhost:8000/api/v2/auth/me

---

**Status**: ✅ **READY FOR TESTING**  
**Next**: Tunggu perintah untuk melanjutkan ke **TAHAP 2: Real WebSocket Integration**
