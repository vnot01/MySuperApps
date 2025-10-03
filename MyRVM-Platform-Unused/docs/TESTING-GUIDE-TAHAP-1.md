# 🧪 **PANDUAN TESTING TAHAP 1: Production-ready Authentication**

## 🎯 **OVERVIEW**

Panduan lengkap untuk menguji implementasi authentication system yang telah selesai di TAHAP 1.

---

## 🚀 **CARA TESTING AUTHENTICATION SYSTEM**

### **1. PREPARATION - Pastikan Server Running**

```bash
# Pastikan Docker container running
docker compose up -d

# Cek status container
docker compose ps

# Cek logs jika ada error
docker compose logs app
```

### **2. TEST LOGIN PAGE**

#### **Step 1: Akses Login Page**
```
URL: http://localhost:8000/login
Expected: Login page dengan form email/password
```

#### **Step 2: Test Form Validation**
- ✅ **Kosongkan email** → Should show validation error
- ✅ **Kosongkan password** → Should show validation error  
- ✅ **Invalid email format** → Should show validation error
- ✅ **Valid input** → Should proceed to login

#### **Step 3: Test Login dengan Credentials**

| Role | Email | Password | Expected Result |
|------|-------|----------|-----------------|
| **Super Admin** | admin@myrvm.com | password | ✅ Login success, redirect to dashboard |
| **Admin** | admin2@myrvm.com | password | ✅ Login success, redirect to dashboard |
| **Operator** | operator@myrvm.com | password | ✅ Login success, redirect to dashboard |
| **Technician** | technician@myrvm.com | password | ✅ Login success, redirect to dashboard |
| **Tenant** | tenant@myrvm.com | password | ✅ Login success, redirect to dashboard |
| **Invalid** | wrong@email.com | wrongpass | ❌ Login failed, show error message |

### **3. TEST DASHBOARD AUTHENTICATION**

#### **Step 1: Direct Access Test**
```
URL: http://localhost:8000/admin/rvm-dashboard
Expected: Redirect to /login (jika tidak authenticated)
```

#### **Step 2: Authenticated Access Test**
1. Login dengan credentials yang valid
2. Akses dashboard: `http://localhost:8000/admin/rvm-dashboard`
3. **Expected Results:**
   - ✅ Dashboard loads successfully
   - ✅ User name dan role ditampilkan di header
   - ✅ Logout button tersedia
   - ✅ RVM monitoring data loads

#### **Step 3: User Info Display Test**
- ✅ **User Name** ditampilkan di header
- ✅ **User Role** ditampilkan di header
- ✅ **Logout Button** berfungsi

### **4. TEST LOGOUT FUNCTIONALITY**

#### **Step 1: Logout Test**
1. Click logout button
2. Confirm logout dialog
3. **Expected:** Redirect to login page

#### **Step 2: Session Cleanup Test**
1. After logout, try to access dashboard directly
2. **Expected:** Redirect to login page

### **5. TEST API AUTHENTICATION**

#### **Step 1: Get Auth Token**
```bash
# Login via API
curl -X POST http://localhost:8000/api/v2/auth/login \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "email": "admin@myrvm.com",
    "password": "password"
  }'
```

**Expected Response:**
```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "user": {
      "id": 1,
      "name": "Super Admin",
      "email": "admin@myrvm.com",
      "role": "Super Admin"
    },
    "token": "1|abc123...",
    "token_type": "Bearer"
  }
}
```

#### **Step 2: Test Protected API Endpoints**
```bash
# Test /auth/me endpoint
curl -X GET http://localhost:8000/api/v2/auth/me \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json"
```

**Expected Response:**
```json
{
  "success": true,
  "data": {
    "user": {
      "id": 1,
      "name": "Super Admin",
      "email": "admin@myrvm.com",
      "role": "Super Admin"
    }
  }
}
```

#### **Step 3: Test RVM Monitoring API**
```bash
# Test RVM monitoring endpoint
curl -X GET http://localhost:8000/api/v2/admin/rvm/monitoring \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json"
```

**Expected Response:**
```json
{
  "success": true,
  "data": {
    "total_rvms": 8,
    "status_counts": {...},
    "active_sessions": 1,
    "total_sessions_today": 2,
    "total_deposits_today": 2,
    "rvms": [...]
  }
}
```

### **6. TEST ROLE-BASED ACCESS CONTROL**

#### **Step 1: Test Different User Roles**
1. Login dengan **Super Admin** → Should access all features
2. Login dengan **Admin** → Should access admin features
3. Login dengan **Operator** → Should access operator features
4. Login dengan **Technician** → Should access technician features
5. Login dengan **Tenant** → Should access tenant features

#### **Step 2: Test Unauthorized Access**
```bash
# Test without token
curl -X GET http://localhost:8000/api/v2/admin/rvm/monitoring \
  -H "Accept: application/json"
```

**Expected Response:**
```json
{
  "success": false,
  "message": "Unauthenticated"
}
```

### **7. TEST FRONTEND AUTHENTICATION**

#### **Step 1: Browser Console Test**
1. Open browser developer tools (F12)
2. Go to Console tab
3. Type: `window.authManager.getCurrentUser()`
4. **Expected:** Return current user object

#### **Step 2: Token Storage Test**
1. Check localStorage in browser dev tools
2. **Expected:** `auth_token` dan `auth_user` stored

#### **Step 3: Auto-redirect Test**
1. Clear localStorage
2. Try to access dashboard
3. **Expected:** Auto-redirect to login page

---

## 🐛 **TROUBLESHOOTING**

### **Common Issues & Solutions:**

#### **1. Permission Denied Error**
```bash
# Fix storage permissions
docker compose exec app chmod -R 775 storage
docker compose exec app chown -R www-data:www-data storage
```

#### **2. Cache Issues**
```bash
# Clear all caches
docker compose exec app php artisan cache:clear
docker compose exec app php artisan view:clear
docker compose exec app php artisan config:clear
```

#### **3. Database Issues**
```bash
# Run migrations and seeders
docker compose exec app php artisan migrate:fresh --seed
docker compose exec app php artisan db:seed --class=UserRoleSeeder
```

#### **4. Token Issues**
- Clear browser localStorage
- Check if token is properly stored
- Verify token format in API requests

---

## ✅ **TESTING CHECKLIST**

### **Authentication Flow:**
- [ ] Login page loads correctly
- [ ] Form validation works
- [ ] Login with valid credentials succeeds
- [ ] Login with invalid credentials fails
- [ ] Dashboard redirects unauthenticated users
- [ ] Dashboard loads for authenticated users
- [ ] User info displays correctly
- [ ] Logout functionality works
- [ ] Session cleanup after logout

### **API Authentication:**
- [ ] Login API returns token
- [ ] Protected endpoints require token
- [ ] Token validation works
- [ ] Unauthorized access blocked
- [ ] User info API works

### **Role-based Access:**
- [ ] Different roles can access appropriate features
- [ ] Unauthorized access blocked
- [ ] Role middleware works correctly

### **Frontend Integration:**
- [ ] AuthManager class works
- [ ] Token storage works
- [ ] Auto-redirect works
- [ ] API calls include authentication

---

## 🎯 **EXPECTED RESULTS**

### **Success Indicators:**
- ✅ Login page accessible at `/login`
- ✅ Dashboard accessible at `/admin/rvm-dashboard` (after login)
- ✅ User info displayed in dashboard header
- ✅ Logout button functional
- ✅ API endpoints protected
- ✅ Role-based access working
- ✅ No permission errors
- ✅ Smooth user experience

### **Performance Indicators:**
- ✅ Login response time < 2 seconds
- ✅ Dashboard load time < 3 seconds
- ✅ API response time < 1 second
- ✅ No memory leaks
- ✅ Proper error handling

---

## 📝 **TESTING NOTES**

1. **Test dengan browser yang berbeda** (Chrome, Firefox, Safari)
2. **Test dengan incognito/private mode**
3. **Test dengan network throttling** (slow 3G)
4. **Test dengan multiple tabs** open
5. **Test session timeout** (jika ada)

---

**Status**: ✅ **READY FOR TESTING**  
**Next**: Tunggu perintah untuk melanjutkan ke **TAHAP 2: Real WebSocket Integration**
