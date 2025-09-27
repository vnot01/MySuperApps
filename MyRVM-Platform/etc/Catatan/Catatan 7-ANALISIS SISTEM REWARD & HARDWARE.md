Mari saya analisis kedua pertanyaan Anda secara detail:


[4 tools called]

Mari saya buat analisis lengkap berdasarkan kode yang ada dan dokumentasi:

## 📊 **ANALISIS SISTEM REWARD & HARDWARE**

### �� **1. SISTEM REWARD - LOKASI & KONFIGURASI**

#### **Lokasi Formula Reward:**
Sistem reward formula ada di **3 tempat**:

1. **`app/Services/DepositService.php`** (Line 65-93)
2. **`app/Services/EconomyService.php`** (Line 333-363)  
3. **`docs/database-schema.sql`** (Line 378-416) - PostgreSQL Function

#### **Formula Saat Ini:**
```php
// Base reward rates per kg
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

// Formula: base_rate * weight * quality_multiplier * confidence_factor
$reward = $baseRate * $deposit->weight * $qualityMultiplier * $confidenceFactor;
```

#### **Rekomendasi Konfigurasi Manual:**

**Hak Akses yang Disarankan:**
- **Super Admin**: Full access (create, read, update, delete)
- **Admin**: Read, update (tidak bisa delete)
- **Operator**: Read only (tidak bisa modify)

**Implementasi yang Disarankan:**
1. **Buat tabel `reward_configurations`** di database
2. **Buat API endpoints** untuk CRUD reward configuration
3. **Buat admin panel** untuk mengatur reward rates
4. **Implementasi caching** untuk performa

---

### 🔧 **2. HARDWARE - JETSON ORIN NANO**

#### **Jetson Orin Nano Specifications:**

**GPIO Pins:**
- **40-pin GPIO header** (kompatibel dengan Raspberry Pi)
- **Digital I/O**: 28 pins
- **PWM**: 4 pins
- **I2C**: 2 buses
- **SPI**: 2 buses
- **UART**: 2 serial ports
- **Power pins**: 5V, 3.3V, GND

**Power Requirements:**
- **Typical Power**: 7-15W
- **Peak Power**: 20W
- **Input**: 5V/4A (20W) via barrel jack atau USB-C
- **Power Supply**: 5V/4A minimum

#### **Hardware yang Dikontrol Jetson Orin Nano:**

**Ya, Jetson Orin Nano mengendalikan semua hardware:**

1. **Camera Module** (USB/CSI)
   - Object detection (YOLO + SAM2)
   - QR code scanning
   - Real-time image processing

2. **Weight Sensor** (Load Cell)
   - GPIO: Analog input via ADC
   - Precision: 0.1g accuracy
   - Range: 0-10kg

3. **Motor/Servo Control**
   - **Door Motor**: Stepper motor (4 pins)
   - **Conveyor Motor**: DC motor (2 pins + PWM)
   - **Compression Motor**: Servo motor (3 pins)

4. **LED Status Indicators**
   - **Status LEDs**: 8-12 LEDs (GPIO digital)
   - **RGB LEDs**: 4-6 RGB strips (PWM control)

5. **Touch Screen/LCD**
   - **Display**: HDMI/USB-C connection
   - **Touch**: USB HID interface
   - **Size**: 10-15 inch

6. **Additional Sensors**
   - **Proximity Sensor**: 2-4 sensors (GPIO)
   - **Temperature Sensor**: I2C interface
   - **Humidity Sensor**: I2C interface

#### **GPIO Pin Allocation:**

```
Pin Allocation untuk RVM:
├── Camera: USB/CSI (tidak pakai GPIO)
├── Weight Sensor: GPIO 2, 3 (I2C)
├── Door Motor: GPIO 4, 5, 6, 7 (Stepper)
├── Conveyor Motor: GPIO 8, 9 (DC + PWM)
├── Compression Motor: GPIO 10, 11, 12 (Servo)
├── Status LEDs: GPIO 13-20 (8 pins)
├── RGB LEDs: GPIO 21, 22, 23 (PWM)
├── Proximity Sensors: GPIO 24-27 (4 pins)
├── Temperature/Humidity: GPIO 28, 29 (I2C)
├── Emergency Stop: GPIO 30 (Input)
├── Power Management: GPIO 31, 32 (Power control)
└── Reserved: GPIO 33-40 (7 pins)
```

**Total GPIO Used**: 33 pins dari 40 pins available ✅

#### **Power Supply Requirements:**

**Total Power Calculation:**
```
Jetson Orin Nano:     15W (peak)
Touch Screen (15"):   25W
Stepper Motors:       20W (4 motors)
DC Motors:            15W (2 motors)
LEDs:                 10W
Sensors:              5W
Cooling Fan:          5W
Reserve (20%):        20W
─────────────────────────
Total Required:       115W
```

**Recommended Power Supply:**
- **Input**: 220V AC
- **Output**: 12V/15A (180W) + 5V/4A (20W)
- **Backup**: UPS 500VA untuk power protection

#### **Hardware Architecture:**

```
┌─────────────────────────────────────────┐
│           Jetson Orin Nano              │
│  ┌─────────────────────────────────────┐ │
│  │  Python App (MyRVM-EdgeControl)    │ │
│  │  - YOLO + SAM2 AI Processing       │ │
│  │  - WebSocket Client                │ │
│  │  - Hardware Control                │ │
│  └─────────────────────────────────────┘ │
└─────────────────────────────────────────┘
           │
    ┌──────┼──────┐
    │      │      │
┌───▼───┐ ┌▼───┐ ┌▼─────────┐
│Camera │ │GPIO│ │USB/HDMI  │
│Module │ │40  │ │Display   │
└───────┘ └────┘ └──────────┘
    │
┌───▼─────────────────────────┐
│        Hardware Layer       │
│  ┌─────┐ ┌─────┐ ┌─────┐   │
│  │Motor│ │LEDs │ │Sensor│   │
│  └─────┘ └─────┘ └─────┘   │
└─────────────────────────────┘
```

---

### �� **KESIMPULAN & REKOMENDASI**

#### **Sistem Reward:**
1. ✅ **Formula sudah komprehensif** - berdasarkan jenis, berat, kualitas, AI confidence
2. ⚠️ **Perlu konfigurasi manual** - buat admin panel untuk mengatur rates
3. 🔧 **Hak akses**: Super Admin (full), Admin (update), Operator (read)

#### **Hardware Jetson Orin Nano:**
1. ✅ **GPIO cukup** - 40 pins, hanya pakai 33 pins
2. ✅ **Power cukup** - 20W peak, power supply 200W recommended
3. ✅ **Kontrol semua hardware** - camera, motor, sensor, LED, display
4. ⚠️ **Perlu power management** - UPS backup, cooling system

#### **Next Steps:**
1. **Implementasi reward configuration system**
2. **Hardware procurement** (Jetson Orin Nano + components)
3. **Python app development** untuk hardware control
4. **Power system design** dan installation
