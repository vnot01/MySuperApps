#!/bin/bash

# MyCV-Platform Web Application Launcher
# Menjalankan aplikasi web real-time detection

echo "🌐 MyCV-Platform Web Application"
echo "================================="
echo ""

# Cek apakah folder web ada
if [ ! -d "direct/app/web" ]; then
    echo "❌ Error: Folder 'direct/app/web' tidak ditemukan!"
    echo "Pastikan Anda berada di root directory MyCV-Platform"
    exit 1
fi

# Masuk ke folder web
cd direct/app/web

# Cek virtual environment
if [ ! -d "../../venv" ]; then
    echo "❌ Error: Virtual environment tidak ditemukan!"
    echo "Pastikan virtual environment sudah dibuat di folder direct/"
    exit 1
fi

# Aktifkan virtual environment
echo "🔧 Mengaktifkan virtual environment..."
source ../../venv/bin/activate

# Install dependencies
echo "📥 Menginstall dependencies..."
pip install -r requirements.txt

# Jalankan aplikasi web
echo "🚀 Menjalankan aplikasi web..."
echo ""
echo "🌐 Web application akan tersedia di: http://localhost:5000"
echo "📱 Buka browser dan navigasi ke URL di atas"
echo "⚠️  Tekan Ctrl+C untuk menghentikan aplikasi"
echo ""

./run_web_app.sh
