# MyCV-Platform Services

Folder ini berisi semua service manager untuk MyCV-Platform yang memungkinkan aplikasi berjalan di background dan auto-start pada boot.

## 📁 File Structure

```
services/
├── api_service.sh           # API Service Manager
├── web_service.sh           # Web Service Manager
├── combined_service.sh      # Combined Service Manager
├── auto_start_api.sh        # Auto-start script untuk API
├── auto_start_web.sh        # Auto-start script untuk Web
├── install_service.sh       # Systemd service installer
├── setup_user_service.sh    # User systemd service installer
└── README.md               # Dokumentasi ini
```

## 🚀 Service Managers

### 1. API Service Manager (`api_service.sh`)

Mengelola API Hybrid Detection service.

**Commands:**
```bash
./api_service.sh start    # Start API service
./api_service.sh stop     # Stop API service
./api_service.sh restart  # Restart API service
./api_service.sh status   # Check status
./api_service.sh logs     # View logs
```

**Features:**
- Background running dengan PID tracking
- Auto-recovery dengan process detection
- Centralized logging di `/tmp/mycv_api.log`
- Health check endpoint testing
- Production mode (debug=False)

### 2. Web Service Manager (`web_service.sh`)

Mengelola Web Application service.

**Commands:**
```bash
./web_service.sh start    # Start Web service
./web_service.sh stop     # Stop Web service
./web_service.sh restart  # Restart Web service
./web_service.sh status   # Check status
./web_service.sh logs     # View logs
```

**Features:**
- Background running dengan PID tracking
- Auto-recovery dengan process detection
- Centralized logging di `/tmp/mycv_web.log`
- Health check endpoint testing
- Manual camera activation support

### 3. Combined Service Manager (`combined_service.sh`)

Mengelola kedua service (API + Web) secara bersamaan.

**Commands:**
```bash
./combined_service.sh start            # Start all services
./combined_service.sh stop             # Stop all services
./combined_service.sh restart          # Restart all services
./combined_service.sh status           # Show status of all services
./combined_service.sh logs             # Show logs of all services
./combined_service.sh setup-autostart  # Setup auto-start on boot
./combined_service.sh remove-autostart # Remove auto-start
```

**Features:**
- Unified management untuk API dan Web services
- Combined status monitoring
- Combined logs viewing
- Auto-start setup untuk kedua services
- Individual service fallback

## 🔧 Auto-Start Scripts

### API Auto-Start (`auto_start_api.sh`)
- Script untuk auto-start API service pada boot
- Delay 30 detik untuk system ready
- Logging di `/tmp/mycv_api_auto_start.log`

### Web Auto-Start (`auto_start_web.sh`)
- Script untuk auto-start Web service pada boot
- Delay 30 detik untuk system ready
- Logging di `/tmp/mycv_web_auto_start.log`

## 🚀 Root Level Launchers

### API Service Launcher (`../run_api_service.sh`)
```bash
cd /home/my/MySuperApps/MyCV-Platform
./run_api_service.sh start    # Start API service
./run_api_service.sh status   # Check API status
```

### Web Service Launcher (`../run_web_service.sh`)
```bash
cd /home/my/MySuperApps/MyCV-Platform
./run_web_service.sh start    # Start Web service
./run_web_service.sh status   # Check Web status
```

### Combined Services Launcher (`../run_services.sh`)
```bash
cd /home/my/MySuperApps/MyCV-Platform
./run_services.sh start       # Start all services
./run_services.sh status      # Check all services status
./run_services.sh setup-autostart  # Setup auto-start
```

## 📊 Port Configuration

| Service | Port | URL | Manager |
|---------|------|-----|---------|
| **API Hybrid Detection** | 5000 | http://100.98.142.94:5000 | `api_service.sh` |
| **Web Application** | 5002 | http://100.98.142.94:5002 | `web_service.sh` |

## 🔧 Setup Auto-Start

### Method 1: Crontab (Recommended)
```bash
# Setup auto-start untuk semua services
./run_services.sh setup-autostart

# Atau manual
echo "@reboot /home/my/MySuperApps/MyCV-Platform/services/auto_start_api.sh" | crontab -
echo "@reboot /home/my/MySuperApps/MyCV-Platform/services/auto_start_web.sh" | crontab -
```

### Method 2: Systemd Service
```bash
# Install systemd service (requires sudo)
sudo ./install_service.sh

# Atau user systemd service
./setup_user_service.sh
```

## 📋 Usage Examples

### Start All Services
```bash
cd /home/my/MySuperApps/MyCV-Platform
./run_services.sh start
```

### Check Status
```bash
./run_services.sh status
```

### View Logs
```bash
./run_services.sh logs
```

### Setup Auto-Start
```bash
./run_services.sh setup-autostart
```

### Stop All Services
```bash
./run_services.sh stop
```

## 🔍 Troubleshooting

### Service Not Starting
```bash
# Check logs
./run_services.sh logs

# Check individual service
./run_api_service.sh status
./run_web_service.sh status
```

### Port Conflicts
```bash
# Check port usage
sudo lsof -i :5000  # API port
sudo lsof -i :5002  # Web port

# Kill conflicting processes
sudo kill -9 <PID>
```

### Auto-Start Issues
```bash
# Check crontab
crontab -l

# Check auto-start logs
tail -f /tmp/mycv_api_auto_start.log
tail -f /tmp/mycv_web_auto_start.log
```

### PID File Issues
```bash
# Clean up stale PID files
rm -f /tmp/mycv_api.pid
rm -f /tmp/mycv_web.pid

# Restart services
./run_services.sh restart
```

## 📄 Log Files

| Service | Log File | Auto-Start Log |
|---------|----------|----------------|
| **API** | `/tmp/mycv_api.log` | `/tmp/mycv_api_auto_start.log` |
| **Web** | `/tmp/mycv_web.log` | `/tmp/mycv_web_auto_start.log` |

## 🎯 Benefits

1. **Background Running**: Services berjalan independent dari terminal
2. **Auto-Start**: Services start otomatis setelah VM restart
3. **PID Management**: Process tracking dengan auto-recovery
4. **Centralized Logging**: Semua logs tersimpan di lokasi yang sama
5. **Health Monitoring**: Real-time status checking
6. **Unified Management**: Satu command untuk manage semua services
7. **Production Ready**: Disabled debug mode untuk production

---

**Last Updated:** September 28, 2025  
**Version:** 1.3.0-service  
**Status:** ✅ Production Ready
