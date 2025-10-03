# 📊 **ANALISIS KOMPREHENSIF RVM MONITOR & HARDWARE REQUIREMENTS**

## 📋 **OVERVIEW**

**Project**: MyRVM-Platform (Reverse Vending Machine Monitoring System)  
**Status**: ✅ **RVM Monitor sudah menggunakan basis data PostgreSQL**  
**Data Source**: Seeding (mock) data, siap untuk real data  
**Date**: 10 September 2025  

---

## 🗄️ **ANALISIS RVM MONITOR - BASIS DATA**

### ✅ **Status Database:**
**Ya, RVM Monitor sudah menggunakan basis data PostgreSQL** dengan struktur yang lengkap:

#### **Tabel Utama:**
1. **`reverse_vending_machines`** - Data RVM
2. **`deposits`** - Data deposit dengan AI analysis
3. **`users`** - Data pengguna
4. **`transactions`** - Transaksi ekonomi
5. **`user_balances`** - Saldo pengguna

#### **Field Database RVM:**
```sql
CREATE TABLE reverse_vending_machines (
    id BIGSERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,           -- Nama RVM (RVM-001, RVM-002, dll)
    location VARCHAR(255) NOT NULL,       -- Lokasi RVM
    status VARCHAR(50) DEFAULT 'active',  -- Status RVM
    capacity INTEGER DEFAULT 0,           -- Kapasitas (0-100%)
    special_status VARCHAR(50),           -- Status khusus (maintenance, inactive, error, unknown)
    last_capacity_update TIMESTAMP,       -- Update terakhir kapasitas
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW()
);
```

#### **Field Database Deposits:**
```sql
CREATE TABLE deposits (
    id BIGSERIAL PRIMARY KEY,
    user_id BIGINT REFERENCES users(id),
    rvm_id BIGINT REFERENCES reverse_vending_machines(id),
    session_token VARCHAR(255),
    status VARCHAR(50) DEFAULT 'pending',
    waste_type VARCHAR(50),               -- Jenis sampah (plastic, glass, metal, paper, mixed)
    weight DECIMAL(8,3),                  -- Berat sampah
    quality_grade VARCHAR(10),            -- Grade kualitas (A, B, C, D)
    ai_confidence DECIMAL(5,2),           -- AI confidence score
    cv_confidence DECIMAL(5,2),           -- Computer Vision confidence
    reward_amount DECIMAL(10,2),          -- Jumlah reward
    ai_analysis JSONB,                    -- Analisis AI lengkap
    cv_analysis JSONB,                    -- Analisis Computer Vision
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW()
);
```

---

## 🔧 **HARDWARE REQUIREMENTS - JETSON ORIN NANO**

### **Hardware yang Dikontrol Jetson Orin Nano (REVISI):**

#### **1. Camera Module** (USB/CSI) ✅ **DIPERTAHANKAN**
- **Fungsi**: Object detection (YOLO + SAM2), QR code scanning
- **Interface**: USB/CSI (tidak pakai GPIO)
- **AI Processing**: Real-time image processing untuk deteksi objek

#### **2. Weight Sensor** ❌ **DIHAPUS**
- **Alasan**: Tidak perlu menimbang objek individual
- **Alternatif**: Sensor Ultrasonic untuk monitoring kapasitas box

#### **3. Sensor Ultrasonic** ✅ **DITAMBAHKAN**
- **Fungsi**: Monitoring kapasitas box penyimpanan (penuh/tidak)
- **Interface**: GPIO digital (2 pins)
- **Range**: 2-400cm
- **Accuracy**: ±3mm

#### **4. Door Motor (Stepper)** ✅ **DIPERTAHANKAN - FUNGSI DIUBAH**
- **Fungsi BARU**: Membuka celah untuk menjatuhkan objek ke box
- **Interface**: GPIO 4 pins (Stepper motor)
- **Action**: Buka celah → objek jatuh → tutup celah

#### **5. Conveyor Motor** ❌ **DIHAPUS**
- **Alasan**: Objek langsung dijatuhkan ke box, tidak perlu conveyor

#### **6. Compression Motor** ❌ **DIHAPUS**
- **Alasan**: Tidak menggunakan sistem kompresi

#### **7. Proximity Sensors** ❌ **DIHAPUS**
- **Alasan**: Deteksi objek sudah melalui kamera (YOLO + SAM2)
- **Kamera lebih akurat**: Bisa deteksi metallic & non-metallic objects

#### **8. LED Status Indicators** ✅ **DIPERTAHANKAN**
- **Fungsi**: Status indicator untuk user
- **Interface**: GPIO digital (8-12 pins)

#### **9. Touch Screen/LCD** ✅ **DIPERTAHANKAN**
- **Fungsi**: User interface, QR code display
- **Interface**: HDMI/USB-C (tidak pakai GPIO)

#### **10. Additional Sensors** ✅ **DIPERTAHANKAN**
- **Temperature Sensor**: I2C interface
- **Humidity Sensor**: I2C interface
- **Emergency Stop Button**: GPIO input

---

## 📍 **GPIO PIN ALLOCATION REVISI**

```
Pin Allocation untuk RVM (REVISI):
├── Camera: USB/CSI (tidak pakai GPIO)
├── Ultrasonic Sensor: GPIO 2, 3 (Digital I/O)
├── Door Motor: GPIO 4, 5, 6, 7 (Stepper - 4 pins)
├── Status LEDs: GPIO 8-15 (8 pins)
├── RGB LEDs: GPIO 16, 17, 18 (PWM - 3 pins)
├── Temperature Sensor: GPIO 19, 20 (I2C - 2 pins)
├── Humidity Sensor: GPIO 21, 22 (I2C - 2 pins)
├── Emergency Stop: GPIO 23 (Input - 1 pin)
├── Power Management: GPIO 24, 25 (Power control - 2 pins)
├── Reserved: GPIO 26-40 (15 pins)
└── Total Used: 25 pins dari 40 pins available ✅
```

**Total GPIO Used**: 25 pins dari 40 pins available ✅ (lebih efisien!)

---

## ⚡ **POWER SUPPLY REQUIREMENTS REVISI**

### **Total Power Calculation (REVISI):**
```
Jetson Orin Nano:     15W (peak)
Touch Screen (15"):   25W
Stepper Motor:        5W (1 motor saja)
LEDs:                 8W (reduced)
Sensors:              3W (ultrasonic + temp/humidity)
Cooling Fan:          5W
Reserve (20%):        12W
─────────────────────────
Total Required:       73W (turun dari 115W!)
```

### **Recommended Power Supply (REVISI):**
- **Input**: 220V AC
- **Output**: 12V/8A (96W) + 5V/4A (20W)
- **Backup**: UPS 300VA (cukup untuk 73W)
- **Efficiency**: Lebih efisien 36% dari desain sebelumnya

---

## 🏗️ **HARDWARE ARCHITECTURE REVISI**

```
┌─────────────────────────────────────────┐
│           Jetson Orin Nano              │
│  ┌─────────────────────────────────────┐ │
│  │  Python App (MyRVM-EdgeControl)    │ │
│  │  - YOLO + SAM2 AI Processing       │ │
│  │  - WebSocket Client                │ │
│  │  - Hardware Control (Simplified)   │ │
│  └─────────────────────────────────────┘ │
└─────────────────────────────────────────┘
           │
    ┌──────┼──────┐
    │      │      │
┌───▼───┐ ┌▼───┐ ┌▼─────────┐
│Camera │ │GPIO│ │USB/HDMI  │
│Module │ │25  │ │Display   │
└───────┘ └────┘ └──────────┘
    │
┌───▼─────────────────────────┐
│     Simplified Hardware     │
│  ┌─────┐ ┌─────┐ ┌─────┐   │
│  │Stepper│ │LEDs │ │Ultrasonic│ │
│  │Motor │ │     │ │Sensor   │ │
│  └─────┘ └─────┘ └─────┘   │
└─────────────────────────────┘
```

---

## 🔄 **WORKFLOW REVISI**

### **Alur Kerja RVM (REVISI):**

1. **User Interface**:
   - User scan QR code atau pilih "Guest Mode"
   - Touch screen menampilkan instruksi

2. **Object Detection**:
   - Camera mendeteksi objek (YOLO + SAM2)
   - AI menganalisis jenis, kondisi, kualitas
   - Confidence score dihitung

3. **Object Processing**:
   - Stepper motor membuka celah
   - Objek dijatuhkan ke box penyimpanan
   - Stepper motor menutup celah

4. **Capacity Monitoring**:
   - Ultrasonic sensor monitor kapasitas box
   - Jika penuh, sistem notifikasi maintenance

5. **Reward Calculation**:
   - Berdasarkan AI analysis (jenis, kualitas, confidence)
   - Tidak perlu timbangan individual
   - Reward ditambahkan ke user balance

---

## 💰 **SISTEM REWARD - TIDAK BERUBAH**

### **Formula Reward Tetap Sama:**
```php
// Base reward rates per kg (estimasi dari AI)
$baseRates = [
    'plastic' => 5000, // Rp 5,000 per kg
    'glass' => 3000,   // Rp 3,000 per kg
    'metal' => 8000,   // Rp 8,000 per kg
    'paper' => 2000,   // Rp 2,000 per kg
    'mixed' => 1500,   // Rp 1,500 per kg
];

// Quality multipliers
$qualityMultipliers = [
    'A' => 1.2, // Premium quality
    'B' => 1.0, // Good quality
    'C' => 0.8, // Fair quality
    'D' => 0.5, // Poor quality
];

// Formula: base_rate * estimated_weight * quality_multiplier * confidence_factor
$reward = $baseRate * $estimatedWeight * $qualityMultiplier * $confidenceFactor;
```

### **Perubahan pada Reward Calculation:**
- **Weight Estimation**: AI mengestimasi berat berdasarkan volume dan jenis objek
- **No Physical Weighing**: Tidak ada timbangan fisik
- **AI-Based**: Semua perhitungan berdasarkan AI analysis

---

## 📊 **COMPARISON: BEFORE vs AFTER**

| Component | Before | After | Change |
|-----------|--------|-------|---------|
| **GPIO Used** | 33 pins | 25 pins | -8 pins ✅ |
| **Power Required** | 115W | 73W | -36% ✅ |
| **Motors** | 3 motors | 1 motor | -2 motors ✅ |
| **Sensors** | 8 sensors | 4 sensors | -4 sensors ✅ |
| **Complexity** | High | Medium | Simplified ✅ |
| **Cost** | High | Medium | Reduced ✅ |
| **Maintenance** | High | Low | Easier ✅ |

---

## 🎯 **KESIMPULAN & REKOMENDASI REVISI**

### **Hardware Jetson Orin Nano (REVISI):**
1. ✅ **GPIO lebih efisien** - 25 pins dari 40 pins (62% utilization)
2. ✅ **Power lebih efisien** - 73W vs 115W (36% reduction)
3. ✅ **Komponen lebih sederhana** - 1 motor vs 3 motors
4. ✅ **Maintenance lebih mudah** - fewer moving parts
5. ✅ **Cost lebih rendah** - fewer components

### **Sistem Reward:**
1. ✅ **Formula tetap komprehensif** - berdasarkan AI analysis
2. ✅ **No physical weighing** - AI estimation lebih praktis
3. ✅ **Real-time processing** - langsung dari camera detection

### **Next Steps (REVISI):**
1. **Implementasi reward configuration system** (tidak berubah)
2. **Hardware procurement** (Jetson Orin Nano + simplified components)
3. **Python app development** untuk simplified hardware control
4. **AI weight estimation** development
5. **Ultrasonic sensor integration** untuk capacity monitoring

---

## 🔧 **HARDWARE SHOPPING LIST (REVISI)**

### **Essential Components:**
- ✅ **Jetson Orin Nano** - Main controller
- ✅ **Camera Module** - Object detection
- ✅ **Touch Screen (15")** - User interface
- ✅ **Stepper Motor** - Door control
- ✅ **Ultrasonic Sensor** - Capacity monitoring
- ✅ **LED Strips** - Status indicators
- ✅ **Temperature/Humidity Sensors** - Environmental monitoring
- ✅ **Power Supply (96W)** - Power management
- ✅ **UPS (300VA)** - Power backup

### **Optional Components:**
- ✅ **Cooling Fan** - Thermal management
- ✅ **Emergency Stop Button** - Safety
- ✅ **RGB LED Strips** - Enhanced status display

**Total Estimated Cost**: 30-40% lebih murah dari desain sebelumnya! 🎉

---

## 📱 **ALUR KERJA USER & IDENTIFIKASI PENGGUNA**

### **Skenario B: Pengguna Memindai QR Code yang Ditampilkan di Layar RVM (REKOMENDASI)**

#### **Alur Pengguna:**
1. Pengguna tiba di RVM
2. Layar sentuh RVM menampilkan sebuah **QR Code unik dan sementara**
3. Pengguna membuka Aplikasi User (`MyRVM-UserApp`) di ponselnya dan masuk ke fitur "Pindai" (Scan)
4. Pengguna menggunakan **kamera ponselnya** untuk memindai QR Code di layar RVM
5. Aplikasi ponsel mengirimkan data dari QR tersebut ke backend untuk mengotorisasi sesi
6. Layar RVM (melalui WebSocket) menerima konfirmasi dan menyapa pengguna dengan namanya

#### **Analisis Kelebihan:**
- **Keandalan Pemindaian yang Jauh Lebih Tinggi**: Kamera ponsel modern umumnya jauh lebih superior
- **Memungkinkan Alur "Guest" / Donasi**: Tombol "Lanjutkan sebagai Tamu (Donasi)" untuk pengguna tanpa akun
- **Menurunkan Hambatan Adopsi secara Drastis**: Siapa pun bisa langsung menggunakan mesin

#### **Implikasi Teknis:**
- **Perangkat Keras RVM**: Tidak lagi memerlukan kamera khusus untuk QR. Satu kamera untuk deteksi objek sudah cukup. **Wajib memiliki layar**
- **Antarmuka RVM (Front Office - Blade/Vue)**: Menampilkan QR Code dinamis dan merespons event WebSocket
- **Aplikasi User (Flutter)**: Fitur pemindai QR menggunakan kamera ponsel
- **Backend**: Endpoint untuk session management dan WebSocket communication

---

## 🚀 **STATUS IMPLEMENTASI SAAT INI**

### **✅ Yang Sudah Selesai:**
1. **RVM Monitor Dashboard** - Menggunakan basis data PostgreSQL
2. **Database Schema** - Struktur lengkap dengan field untuk AI analysis
3. **Hardware Analysis** - Revisi yang lebih efisien dan cost-effective
4. **Sistem Reward** - Formula komprehensif berdasarkan AI analysis
5. **User Flow** - Skenario B yang optimal untuk adopsi

### **🔄 Yang Sedang Dikerjakan:**
1. **RVM UI Front Office** - Interface touch screen untuk customer
2. **WebSocket Integration** - Real-time communication
3. **Session Management** - Database-based session handling

### **⏳ Yang Akan Dikerjakan:**
1. **Python App Development** - MyRVM-EdgeControl untuk hardware control
2. **AI Integration** - YOLO + SAM2 untuk object detection
3. **Hardware Procurement** - Jetson Orin Nano + components
4. **Testing & Deployment** - Comprehensive testing dan production setup

---

## 📚 **REFERENSI & DOKUMENTASI**

### **Dokumentasi yang Sudah Ada:**
- ✅ `database-schema.sql` - Struktur database lengkap
- ✅ `RVM_STATUS_LOGIC.md` - Logika status RVM
- ✅ `Catatan 7-ANALISIS SISTEM REWARD & HARDWARE.md`
- ✅ `Catatan 8-ANALISIS ULANG SISTEM REWARD & HARDWARE.md`
- ✅ `Alur Users Apps1.md` - Analisis alur kerja user
- ✅ `catatan-Fase 3-Antarmuka & Kontrol RVM.md`

### **Testing & Development:**
- ✅ `test-cv-yolo11-sam2-camera/` - Testing Computer Vision
- ✅ `routes http://localhost:8001/gemini/dashboard` - Gemini Vision Playground

---

**Status**: ✅ **ANALISIS SELESAI**  
**Next Phase**: 🚀 **IMPLEMENTASI HARDWARE & AI INTEGRATION**  
**Estimated Timeline**: 2-3 weeks untuk full implementation

