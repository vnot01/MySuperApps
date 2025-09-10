# Network Configuration & Twingate Setup

## 📋 **OVERVIEW**

**Server**: UNUY1  
**Network**: 10.3.52.0/23  
**Date**: 10 September 2025  

---

## 🌐 **NETWORK CONFIGURATION**

### **Local IP Addresses:**

| Service | Hostname | IP Address | Purpose |
|---------|----------|------------|---------|
| **PVE** | pve | 10.3.52.160 | Proxmox Virtual Environment Host |
| **Docker Host** | docker-host | 10.3.52.161 | MyRVM-Platform (VM 100) |
| **Net Host** | net-host | 10.3.52.136 | Network Services (VM 101) |
| **CV Host** | cv-host | 10.3.52.179 | MyCV-Platform (VM 102) |

### **Network Details:**
- **Subnet**: 10.3.52.0/23
- **Gateway**: 10.3.52.1 (assumed)
- **DNS**: 10.3.52.1 (assumed)
- **Network Range**: 10.3.52.0 - 10.3.53.255

---

## 🔐 **TWINGATE SETUP**

### **What is Twingate?**
Twingate is a zero-trust network access solution that provides secure remote access to private networks without VPN.

### **Installation Steps:**

#### **1. Download Twingate Client**
```bash
# For Ubuntu/Debian
wget https://binaries.twingate.com/client/linux/twingate-amd64.deb
sudo dpkg -i twingate-amd64.deb

# For macOS
brew install --cask twingate

# For Windows
# Download from: https://www.twingate.com/download
```

#### **2. Login with Email**
```bash
# Command line login
twingate login feri.febria2017@gmail.com

# Or use GUI application
# Open Twingate app and login with email
```

#### **3. Network Configuration**
```bash
# Add network to Twingate
twingate network add 10.3.52.0/23

# Or configure in Twingate dashboard
# Networks → Add Network → 10.3.52.0/23
```

---

## 🚀 **ACCESS METHODS**

### **Method 1: Direct IP Access (Local Network)**
```bash
# Proxmox VE
http://10.3.52.160:8006

# MyRVM-Platform
http://10.3.52.161:8000

# MyCV-Platform
http://10.3.52.179:8000

# Network Services
http://10.3.52.136:80
```

### **Method 2: Twingate Remote Access**
```bash
# After Twingate setup, access via:
# https://your-twingate-domain.com

# Or use Twingate client to connect
twingate connect
```

### **Method 3: SSH Access**
```bash
# SSH to hosts
ssh root@10.3.52.160  # PVE Host
ssh user@10.3.52.161  # Docker Host
ssh user@10.3.52.179  # CV Host
ssh user@10.3.52.136  # Net Host
```

---

## 🔧 **TWINGATE CONFIGURATION**

### **Team Setup:**
1. **Login to Twingate Dashboard**
   - URL: https://admin.twingate.com
   - Email: feri.febria2017@gmail.com

2. **Add Team Members**
   - Go to "Users" → "Invite Users"
   - Add email addresses
   - Assign roles (Admin, User, etc.)

3. **Configure Networks**
   - Go to "Networks" → "Add Network"
   - Network: 10.3.52.0/23
   - Name: "UNUY1 Network"

4. **Setup Resources**
   - Go to "Resources" → "Add Resource"
   - Add each service:
     - PVE: 10.3.52.160:8006
     - MyRVM-Platform: 10.3.52.161:8000
     - MyCV-Platform: 10.3.52.179:8000
     - Net Services: 10.3.52.136:80

### **Client Configuration:**
```bash
# Install Twingate client
# Login with email
twingate login feri.febria2017@gmail.com

# Connect to network
twingate connect

# Check status
twingate status

# Disconnect
twingate disconnect
```

---

## 📊 **SERVICE MAPPING**

### **MyRVM-Platform (10.3.52.161)**
- **Main App**: http://10.3.52.161:8000
- **Admin Dashboard**: http://10.3.52.161:8000/admin
- **CV Playground**: http://10.3.52.161:8000/cv-playground
- **API**: http://10.3.52.161:8000/api/v2

### **MyCV-Platform (10.3.52.179)**
- **CV Dashboard**: http://10.3.52.179:8000
- **API**: http://10.3.52.179:8000/api/v1
- **Health Check**: http://10.3.52.179:8000/api/v1/health
- **Model Management**: http://10.3.52.179:8000/models

### **Proxmox VE (10.3.52.160)**
- **Web Interface**: https://10.3.52.160:8006
- **SSH**: ssh root@10.3.52.160
- **Console**: Available via web interface

### **Network Services (10.3.52.136)**
- **Web Services**: http://10.3.52.136:80
- **SSH**: ssh user@10.3.52.136

---

## 🔒 **SECURITY CONSIDERATIONS**

### **Firewall Rules:**
```bash
# Allow Twingate traffic
ufw allow from 10.3.52.0/23
ufw allow 443/tcp  # HTTPS
ufw allow 80/tcp   # HTTP
ufw allow 22/tcp   # SSH
ufw allow 8006/tcp # Proxmox
ufw allow 8000/tcp # Applications
```

### **SSL/TLS Certificates:**
```bash
# Generate self-signed certificates for local access
openssl req -x509 -newkey rsa:4096 -keyout key.pem -out cert.pem -days 365 -nodes

# Or use Let's Encrypt for public domains
certbot --nginx -d your-domain.com
```

### **Access Control:**
- **Twingate**: Zero-trust network access
- **VPN**: Alternative to Twingate
- **SSH Keys**: For secure shell access
- **API Keys**: For service authentication

---

## 📱 **MOBILE ACCESS**

### **Twingate Mobile App:**
1. **Download**: iOS/Android app
2. **Login**: feri.febria2017@gmail.com
3. **Connect**: Tap to connect to network
4. **Access**: Use mobile browser to access services

### **Mobile URLs:**
```
# MyRVM-Platform Mobile
http://10.3.52.161:8000

# MyCV-Platform Mobile
http://10.3.52.179:8000

# Proxmox Mobile (if accessible)
https://10.3.52.160:8006
```

---

## 🛠️ **TROUBLESHOOTING**

### **Common Issues:**

#### **1. Cannot Access Services**
```bash
# Check network connectivity
ping 10.3.52.160
ping 10.3.52.161
ping 10.3.52.179
ping 10.3.52.136

# Check port accessibility
telnet 10.3.52.161 8000
telnet 10.3.52.179 8000
```

#### **2. Twingate Connection Issues**
```bash
# Check Twingate status
twingate status

# Restart Twingate service
sudo systemctl restart twingate

# Check logs
twingate logs
```

#### **3. DNS Resolution**
```bash
# Add to /etc/hosts
echo "10.3.52.160 pve" >> /etc/hosts
echo "10.3.52.161 docker-host" >> /etc/hosts
echo "10.3.52.179 cv-host" >> /etc/hosts
echo "10.3.52.136 net-host" >> /etc/hosts
```

---

## 📋 **QUICK REFERENCE**

### **Access Commands:**
```bash
# Twingate
twingate login feri.febria2017@gmail.com
twingate connect

# SSH Access
ssh root@10.3.52.160    # PVE
ssh user@10.3.52.161    # Docker Host
ssh user@10.3.52.179    # CV Host
ssh user@10.3.52.136    # Net Host

# Web Access
curl http://10.3.52.161:8000/api/v1/health
curl http://10.3.52.179:8000/api/v1/health
```

### **Service URLs:**
- **PVE**: https://10.3.52.160:8006
- **MyRVM**: http://10.3.52.161:8000
- **MyCV**: http://10.3.52.179:8000
- **Net**: http://10.3.52.136:80

---

## 🔗 **USEFUL LINKS**

- **Twingate Dashboard**: https://admin.twingate.com
- **Twingate Download**: https://www.twingate.com/download
- **Proxmox Documentation**: https://pve.proxmox.com/wiki
- **Network Troubleshooting**: https://docs.twingate.com/docs

---

**Status**: ✅ **CONFIGURED**  
**Last Updated**: 10 September 2025  
**Network**: 10.3.52.0/23  
**Twingate Email**: feri.febria2017@gmail.com
