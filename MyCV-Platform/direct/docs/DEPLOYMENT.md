# MyCV-Platform Deployment Guide

## 🚀 **Deployment Overview**

MyCV-Platform dapat di-deploy dalam berbagai konfigurasi sesuai kebutuhan:

### **Deployment Options:**
1. **Single Service** - Deploy satu service saja
2. **Multi-Service** - Deploy multiple services pada server yang sama
3. **Distributed** - Deploy services di server yang berbeda

## 🔧 **Prerequisites**

### **System Requirements:**
- **OS**: Ubuntu 20.04+ atau compatible
- **Python**: 3.10+
- **GPU**: NVIDIA GPU (untuk GPU Server dan Jetson API)
- **Memory**: Minimum 4GB RAM
- **Storage**: Minimum 10GB free space

### **Dependencies:**
- NVIDIA drivers (untuk GPU support)
- CUDA toolkit (untuk GPU processing)
- Python virtual environment

## 📋 **Deployment Steps**

### **1. Environment Setup**

```bash
# Clone repository
git clone https://github.com/vnot01/MySuperApps.git
cd MySuperApps/MyCV-Platform/direct

# Setup environment
chmod +x setup.sh
./setup.sh

# Verify setup
source venv/bin/activate
python --version
```

### **2. Service Selection**

#### **Option A: Web Interface Only**
```bash
# Activate environment
source venv/bin/activate

# Run web interface
cd app/web
python app.py
```

#### **Option B: GPU Server API Only**
```bash
# Activate environment
source venv/bin/activate

# Run GPU server
cd app/api-hybrid-detection
python app.py
```

#### **Option C: Jetson API Only**
```bash
# Activate environment
source venv/bin/activate

# Configure RVM integration
cd app/api-hybrid-detection-jetson
cp rvm_config.example rvm_config.env
# Edit rvm_config.env dengan konfigurasi yang sesuai

# Run Jetson API
python app.py
```

### **3. Multi-Service Deployment**

#### **Using Different Ports:**
```bash
# Terminal 1: Web Interface (Port 5000)
source venv/bin/activate
cd app/web
python app.py

# Terminal 2: GPU Server (Port 5001)
source venv/bin/activate
cd app/api-hybrid-detection
python app.py --port 5001

# Terminal 3: Jetson API (Port 5002)
source venv/bin/activate
cd app/api-hybrid-detection-jetson
python app.py --port 5002
```

## 🔗 **RVM Integration Setup**

### **1. MyRVM-Platform Setup**

```bash
# Setup MyRVM-Platform database
cd /path/to/MyRVM-Platform
php artisan migrate

# Copy RVM integration files
cp MyCV-Platform/direct/app/api-hybrid-detection-jetson/rvm-integration/templates/* database/migrations/
cp MyCV-Platform/direct/app/api-hybrid-detection-jetson/rvm-integration/templates/* app/Models/
cp MyCV-Platform/direct/app/api-hybrid-detection-jetson/rvm-integration/templates/* app/Http/Controllers/Api/
```

### **2. Jetson API Configuration**

```bash
# Edit RVM configuration
cd app/api-hybrid-detection-jetson
nano rvm_config.env

# Set RVM API URL dan credentials
RVM_API_BASE_URL=http://your-rvm-platform.com/api
RVM_API_KEY=your_master_api_key_here
API_HOST=your_jetson_ip
API_PORT=5000
```

## 🌐 **Production Deployment**

### **1. Systemd Service (Recommended)**

#### **Create Service File:**
```bash
sudo nano /etc/systemd/system/mycv-platform.service
```

#### **Service Configuration:**
```ini
[Unit]
Description=MyCV-Platform Service
After=network.target

[Service]
Type=simple
User=my
WorkingDirectory=/home/my/MySuperApps/MyCV-Platform/direct
Environment=PATH=/home/my/MySuperApps/MyCV-Platform/direct/venv/bin
ExecStart=/home/my/MySuperApps/MyCV-Platform/direct/venv/bin/python app/api-hybrid-detection-jetson/app.py
Restart=always
RestartSec=10

[Install]
WantedBy=multi-user.target
```

#### **Enable Service:**
```bash
sudo systemctl daemon-reload
sudo systemctl enable mycv-platform
sudo systemctl start mycv-platform
sudo systemctl status mycv-platform
```

### **2. Nginx Reverse Proxy**

#### **Nginx Configuration:**
```nginx
server {
    listen 80;
    server_name your-domain.com;

    location / {
        proxy_pass http://127.0.0.1:5000;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }
}
```

## 📊 **Monitoring & Maintenance**

### **1. Health Checks**

```bash
# Check service status
curl http://localhost:5000/api/health

# Check GPU status
nvidia-smi

# Check disk space
df -h
```

### **2. Log Management**

```bash
# View service logs
sudo journalctl -u mycv-platform -f

# View application logs
tail -f app/api-hybrid-detection-jetson/logs/app.log
```

### **3. Performance Monitoring**

```bash
# Monitor GPU usage
watch -n 1 nvidia-smi

# Monitor CPU usage
htop

# Monitor memory usage
free -h
```

## 🔧 **Troubleshooting**

### **Common Issues:**

#### **1. Service Won't Start**
```bash
# Check logs
sudo journalctl -u mycv-platform -n 50

# Check Python path
which python
python --version
```

#### **2. GPU Not Detected**
```bash
# Check NVIDIA drivers
nvidia-smi

# Check CUDA
python -c "import torch; print(torch.cuda.is_available())"
```

#### **3. RVM Integration Failed**
```bash
# Check RVM API connectivity
curl http://your-rvm-platform.com/api/health

# Check API key
curl -H "X-RVM-API-Key: your_key" http://your-rvm-platform.com/api/rvm/validate
```

## 🎯 **Best Practices**

1. **Use Virtual Environment**: Selalu gunakan virtual environment
2. **Monitor Resources**: Monitor GPU memory dan CPU usage
3. **Backup Data**: Regular backup detection results
4. **Update Dependencies**: Keep requirements.txt updated
5. **Test Integration**: Test RVM integration sebelum production
6. **Use HTTPS**: Use HTTPS untuk production deployment
7. **Set Proper Permissions**: Set proper file permissions untuk security

---

**Status**: ✅ **READY FOR DEPLOYMENT**  
**Version**: 1.0.0  
**Last Updated**: 15 January 2025

