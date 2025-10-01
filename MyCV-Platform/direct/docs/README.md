# MyCV-Platform Documentation

## 📚 **Documentation Overview**

Folder `docs/` berisi dokumentasi teknis untuk MyCV-Platform yang mencakup arsitektur, deployment, dan best practices.

## 📁 **Available Documentation**

### **Architecture Documentation**
- **[ARCHITECTURE.md](ARCHITECTURE.md)** - Arsitektur sistem dan komponen
- **[DEPLOYMENT.md](DEPLOYMENT.md)** - Panduan deployment dan konfigurasi

## 🎯 **Documentation Purpose**

### **For Developers:**
- Memahami arsitektur sistem
- Setup development environment
- Integrasi dengan RVM Platform

### **For DevOps:**
- Deployment strategies
- Production configuration
- Monitoring dan maintenance

### **For System Administrators:**
- Service management
- Troubleshooting guides
- Security considerations

## 🔧 **Quick Reference**

### **Setup Commands:**
```bash
# Setup environment
./setup.sh

# Run services
source venv/bin/activate
cd app/[service-name] && python app.py
```

### **Configuration Files:**
- **Main Config**: `requirements.txt` (unified dependencies)
- **RVM Config**: `app/api-hybrid-detection-jetson/rvm_config.env`
- **Setup Script**: `setup.sh`

### **Service Ports:**
- **Web Interface**: 5000
- **GPU Server API**: 5000 (default)
- **Jetson API**: 5000 (default)

## 📖 **Reading Guide**

1. **Start with**: [ARCHITECTURE.md](ARCHITECTURE.md) untuk memahami sistem
2. **Then read**: [DEPLOYMENT.md](DEPLOYMENT.md) untuk deployment
3. **Reference**: Service-specific README files di `app/` folders

## 🔄 **Documentation Updates**

Dokumentasi ini akan diupdate sesuai dengan perubahan sistem. Untuk versi terbaru, selalu refer ke repository GitHub.

---

**Status**: ✅ **CURRENT**  
**Version**: 1.0.0  
**Last Updated**: 15 January 2025

