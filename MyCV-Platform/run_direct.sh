#!/bin/bash

# MyCV-Platform Direct Execution Script
# Menjalankan aplikasi langsung di VM tanpa Docker

echo "🚀 MyCV-Platform Direct Execution"
echo "=================================="
echo ""

# Cek apakah folder direct ada
if [ ! -d "direct" ]; then
    echo "❌ Error: Folder 'direct' tidak ditemukan!"
    echo "Pastikan Anda berada di root directory MyCV-Platform"
    exit 1
fi

# Masuk ke folder direct
cd direct

# Cek virtual environment
if [ ! -d "venv" ]; then
    echo "📦 Membuat virtual environment..."
    python3 -m venv venv
fi

# Aktifkan virtual environment
echo "🔧 Mengaktifkan virtual environment..."
source venv/bin/activate

# Install dependencies jika belum ada
if [ ! -f "venv/pyvenv.cfg" ] || [ ! -d "venv/lib" ]; then
    echo "📥 Menginstall dependencies..."
    pip install -r requirements.txt
fi

# Install missing dependencies
echo "📥 Menginstall dependencies yang hilang..."
pip install termcolor

# Jalankan fresh integration test
echo "🧪 Menjalankan Fresh Integration Test..."
echo ""
./scripts/run_fresh_integration_test.sh

echo ""
echo "✅ Selesai! Cek hasil di folder 'data/output/'"