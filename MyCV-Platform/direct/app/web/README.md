# MyCV-Platform Web Application

Aplikasi web real-time untuk deteksi objek menggunakan kamera dengan YOLO + SAM2 integration.

## 🚀 Features

- **Real-time Detection**: Deteksi objek secara real-time dari kamera
- **Dual Model**: Menggunakan YOLO11m dan best.pt untuk deteksi
- **SAM2 Segmentation**: Segmentasi objek menggunakan SAM2_b
- **Live Visualization**: Tampilan langsung dengan bounding box dan mask
- **Web Interface**: Antarmuka web yang user-friendly
- **GPU Support**: Mendukung GPU acceleration
- **Heartbeat Polling**: Loader dengan polling status tiap 2 detik sampai selesai
- **Galleries & Thumbnails**: Keempat frame menampilkan list gambar yang bisa diklik
- **Load Last Results**: Memuat ulang hasil sesi sebelumnya via `/api/results/<session_id>`
- **New Session**: Reset UI untuk mulai sesi baru

## 📋 Requirements

- Python 3.10+
- Virtual environment aktif
- Kamera webcam
- Model YOLO dan SAM2 sudah terinstall

## 🛠️ Installation

### 1. Pastikan Virtual Environment Aktif
```bash
cd /home/my/MySuperApps/MyCV-Platform/direct
source venv/bin/activate
```

### 2. Install Dependencies
```bash
cd app/web
pip install -r requirements.txt
```

### 3. Pastikan Model Tersedia
```bash
# Cek model YOLO11m
ls ../../data/models/yolo/active/yolo11m.pt

# Cek model best.pt
ls ../../data/models/trained/best.pt

# Cek model SAM2_b
ls ../../data/models/sam/active/sam2_b.pt
```

## 🚀 Usage

### Quick Start
```bash
cd /home/my/MySuperApps/MyCV-Platform/direct/app/web
./run_web_app.sh
```

### Manual Start
```bash
cd /home/my/MySuperApps/MyCV-Platform/direct/app/web
source ../../venv/bin/activate
python3 app.py
```

## 🌐 Access

Setelah aplikasi berjalan, buka browser dan akses:
- **URL**: http://100.98.142.94:5002
- Frontend akan berkomunikasi dengan API di http://100.98.142.94:5000

## 🎮 Controls

### Web Interface
- **Start Detection**: Mulai deteksi real-time
- **Stop Detection**: Hentikan deteksi
- **Refresh Status**: Update status sistem
- **Live Feed**: Tampilan kamera real-time
  
### Upload & Process Tab
- **Proses Computer Vision**: Upload dan mulai proses; tombol otomatis disabled saat processing
- **Download All Result**: Mengunduh backup sesi via `/api/backup/<session_id>`
- **Load Last Results**: Memuat hasil dari `session_id` tertentu
- **New Session**: Membersihkan file, hasil, dan state UI

### Detection Results
- **YOLO11m Detections**: Hasil deteksi dari model YOLO11m
- **Best.pt Detections**: Hasil deteksi dari model best.pt
- **Segmentation Masks**: Jumlah mask yang dihasilkan SAM2

## 📊 System Status

Aplikasi menampilkan status:
- **Detection**: Status deteksi (Running/Stopped)
- **Camera**: Status kamera (Active/Inactive)
- **Models**: Jumlah model yang dimuat
- **Device**: Device yang digunakan (CPU/GPU)

## 🔧 Configuration

### Camera Settings
- **Default Camera**: Camera index 0
- **Resolution**: Auto-detect
- **FPS**: Real-time

### Model Settings
- **YOLO11m**: Deteksi objek umum
- **Best.pt**: Deteksi objek khusus
- **SAM2_b**: Segmentasi objek

## 🐛 Troubleshooting

### Camera Issues
```bash
# Cek kamera tersedia
python3 -c "import cv2; print('Cameras:', [i for i in range(10) if cv2.VideoCapture(i).isOpened()])"
```

### Model Issues
```bash
# Cek model tersedia
ls ../../data/models/*/active/*.pt
```

### Port Issues
```bash
# Cek port 5000 digunakan
lsof -i :5000
```

## 📁 File Structure

```
web/
├── app.py                 # Main Flask application
├── requirements.txt       # Python dependencies
├── run_web_app.sh        # Launcher script
├── README.md             # Documentation
└── templates/
    └── index.html        # Web interface template
```

## 🎯 Features Detail

### Real-time Detection
- Deteksi objek secara real-time
- Bounding box dengan confidence score
- Class name untuk setiap objek

### Segmentation
- Mask segmentasi untuk setiap objek
- Overlay mask pada video feed
- Warna berbeda untuk setiap mask

### Web Interface
- Responsive design
- Real-time updates
- Status monitoring
- Error handling

## 🔒 Security Notes

- Aplikasi berjalan di localhost (127.0.0.1)
- Tidak ada autentikasi (untuk development)
- Kamera hanya bisa diakses oleh satu aplikasi

## 📈 Performance

### GPU Mode
- Menggunakan CUDA jika tersedia
- Deteksi lebih cepat
- Segmentasi lebih akurat

### CPU Mode
- Fallback ke CPU jika GPU tidak tersedia
- Lebih lambat tapi tetap berfungsi
- Cocok untuk testing

## 🆘 Support

Jika ada masalah:
1. Cek log aplikasi
2. Pastikan kamera terhubung
3. Pastikan model tersedia
4. Cek virtual environment aktif
