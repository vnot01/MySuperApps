# 🖥️ **01_SERVER - MyRVM-Platform**

## 📋 **Overview**

Central server untuk ekosistem MyRVM yang berfungsi sebagai:
- Monitoring dashboard untuk semua RVM
- API server untuk mobile applications
- Management system untuk tenants
- Data analytics dan reporting

## 🏗️ **Architecture**

- **Framework**: Laravel 12 (Latest)
- **Frontend**: Vue.js 3 + Inertia.js SPA
- **Styling**: Tailwind CSS + Custom Components
- **Build Tool**: Vite (Fast Development & Production)
- **Database**: PostgreSQL 15
- **Cache**: Redis 7
- **Web Server**: Nginx (Dockerized)
- **Deployment**: Docker Compose
- **Network**: Tailscale VPN

## 🌐 **Network Configuration**

- **Server IP**: `100.123.143.87`
- **Port**: `8001` (Web), `5432` (PostgreSQL), `6379` (Redis), `9000` (MinIO)
- **VPN**: Tailscale network untuk secure access

## 🔌 **API Endpoints**

### **Authentication:**
- `POST /api/login` - Generate API token
- `GET /api/user` - Get authenticated user
- `POST /api/logout` - Revoke API token
- `GET /api/health` - API health check

### **RVM Management:**
- `GET /api/rvms` - List all RVMs (with filtering)
- `POST /api/rvms` - Create new RVM
- `GET /api/rvms/{id}` - Get specific RVM
- `PUT /api/rvms/{id}` - Update RVM
- `DELETE /api/rvms/{id}` - Delete RVM
- `POST /api/rvms/{id}/status` - Update RVM status
- `POST /api/rvms/{id}/metrics` - Update RVM metrics
- `POST /api/rvms/{id}/ping` - RVM heartbeat
- `GET /api/rvms-statistics` - Get RVM statistics

## 📁 **Documentation Structure**

### **Requirements**
- **MyRVM_Ecosystem_v2_Requirements.md** - Complete requirements untuk MyRVM-Ecosystem-v2
- **Deep_Code_Analysis.md** - Analisis mendalam integrasi Jetson
- **Implementation_Roadmap.md** - Roadmap implementasi step-by-step
- **Jetson_Integration_Analysis.md** - Analisis kebutuhan integrasi
- **Hardware_Requirements.md** - Spesifikasi hardware server

### **To-Do**
- Tasks yang perlu dikerjakan berdasarkan requirements
- Feature requests
- Bug fixes

### **Implementation**
- Setup guides
- Configuration
- Deployment instructions
- Troubleshooting

### **Done**
- Completed features
- Deployed components
- Tested functionality

## 🔐 **User Accounts**

- **Admin**: `admin@myrvm.com` / `password`
- **Demo**: `demo@myrvm.com` / `password`  
- **Operator**: `operator@myrvm.com` / `password`

*All accounts are ready for testing and have been seeded to the database.*

## 🚀 **Quick Start**

```bash
# Clone repository
git clone <repository-url> MyRVM-Ecosystem

# Navigate to server
cd MyRVM-Ecosystem/01_SERVER

# Setup Docker
docker-compose up -d

# Access application
http://100.123.143.87:8001
```

## 📊 **Current Status**

- **Development**: ✅ Active
- **Docker**: ✅ Configured & Running
- **Database**: ✅ PostgreSQL + Redis
- **Laravel 12**: ✅ Fresh Installation
- **Landing Page**: ✅ Modern responsive design
- **Login Page**: ✅ SPA with Vue.js 3 + Inertia.js
- **SPA Dashboard**: ✅ Vue.js 3 + Inertia.js
- **Authentication**: ✅ Laravel Sanctum (Web + API)
- **Basic APIs**: ✅ RVM Management RESTful APIs
- **Database**: ✅ RVM Model + Seeder + Real Data
- **Frontend Build**: ✅ Vite + Tailwind CSS + Bootstrap 5
- **User Management**: ✅ Login/Logout System
- **Phase 1 Core**: ✅ Detection Results + API Integration
- **Capacity System**: ✅ 0-100% percentage-based
- **Modern Dashboard**: ✅ Professional UI with charts and analytics
- **Jetson Integration**: ✅ API endpoints ready for Jetson
- **Production Ready**: ✅ Live on http://100.123.143.87:8001

---

**Last Updated**: 2025-10-02  
**Version**: 2.1  
**Status**: ✅ Basic APIs Completed - Ready for Jetson Integration