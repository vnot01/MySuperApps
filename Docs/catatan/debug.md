# Debug Report - MyRVM-Ecosystem v2.0

## 🚨 **Masalah Utama**

### 1. **Container App Tidak Bisa Start**
- **Status**: Container `myrvm_ecosystem_app_dev` terus restart
- **Error**: Entrypoint script stuck di database check
- **Root Cause**: Environment variable `DB_CONNECTION=sqlite` tidak terbaca di entrypoint script

### 2. **502 Bad Gateway**
- **Status**: Nginx tidak bisa connect ke PHP-FPM
- **Error**: `HTTP/1.1 502 Bad Gateway`
- **Root Cause**: Container app tidak berjalan dengan baik

### 3. **Database Connection Issue**
- **Status**: PostgreSQL container terus restart
- **Error**: `initdb: error: directory "/var/lib/postgresql/data" exists but is not empty`
- **Root Cause**: Volume database corrupt, entrypoint tidak skip check untuk SQLite

## 🔍 **Debugging Steps yang Sudah Dilakukan**

### Step 1: Build Docker Image
```bash
docker build -t myrvm-ecosystem-app .
```
✅ **Status**: Berhasil

### Step 2: Setup Database
```bash
# Coba PostgreSQL dulu
docker compose up -d
# Error: Database volume corrupt
```

### Step 3: Switch ke SQLite
```bash
# Edit .env file
DB_CONNECTION=sqlite
DB_DATABASE=/var/www/html/database/database.sqlite

# Hapus PostgreSQL config
# DB_HOST=db
# DB_PORT=5432
# DB_USERNAME=docker
# DB_PASSWORD=docker
```
✅ **Status**: File .env berhasil diubah

### Step 4: Fix Nginx Configuration
```bash
# Edit docker/nginx/default.conf
fastcgi_pass myrvm_ecosystem_app_dev:9000;
```
✅ **Status**: Nginx config diperbaiki

### Step 5: Fix Entrypoint Script
```bash
# Edit docker/app/entrypoint.sh
# Tambahkan check untuk SQLite
if [ "$DB_CONNECTION" = "sqlite" ]; then
    echo "Entrypoint: Using SQLite database, skipping PostgreSQL wait..."
else
    # PostgreSQL wait logic
fi
```
❌ **Status**: Environment variable tidak terbaca di entrypoint

### Step 6: Fix Docker Compose Environment
```yaml
environment:
  CONTAINER_ROLE: app
  DB_CONNECTION: sqlite  # Hardcode value
  DB_HOST: ${DB_HOST:-db}
  DB_DATABASE: ${DB_DATABASE:-myrvm_ecosystem}
  DB_USERNAME: ${DB_USERNAME:-docker}
  DB_PASSWORD: ${DB_PASSWORD:-docker}
```
❌ **Status**: Masih tidak terbaca

## 🐛 **Error Logs**

### Container App Log:
```
Entrypoint: Waiting for PostgreSQL port 5432 to open...
Waiting for database connection...
Waiting for database connection...
nc: bad address 'db'
```

### Container Database Log:
```
initdb: error: directory "/var/lib/postgresql/data" exists but is not empty
initdb: hint: If you want to create a new database system, either remove or empty the directory "/var/lib/postgresql/data" or run initdb with an argument other than "/var/lib/postgresql/data".
```

### Nginx Log:
```
2025/10/01 18:33:34 [emerg] 1#1: host not found in upstream "myrvm_app_dev" in /etc/nginx/conf.d/default.conf:28
nginx: [emerg] host not found in upstream "myrvm_app_dev" in /etc/nginx/conf.d/default.conf:28
```

## 🔧 **Solusi yang Dicoba**

1. ✅ **Rebuild Docker Image** - Berhasil
2. ✅ **Fix Nginx Config** - Berhasil  
3. ✅ **Switch ke SQLite** - Berhasil
4. ❌ **Fix Entrypoint Script** - Gagal (env var tidak terbaca)
5. ❌ **Fix Environment Variables** - Gagal

## 🎯 **Next Steps**

1. **Stop semua container**
2. **Simplify approach** - Hapus entrypoint script yang kompleks
3. **Manual setup** - Jalankan artisan commands manual
4. **Test step by step** - Pastikan setiap step berhasil

## 📊 **Current Status**

```
✅ Docker Compose: Running
✅ Nginx: Running (Port 8001)
❌ App Container: Restarting (Stuck)
❌ Database: PostgreSQL restarting
✅ Redis: Running
✅ MinIO: Running
✅ Mailpit: Running
```

## 🕐 **Timestamp**
- **Date**: 2025-10-01
- **Time**: 18:39 UTC+7
- **Duration**: ~2 hours debugging
