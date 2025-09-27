#!/bin/bash

# MyCV-Platform Docker Testing Script
# Menjalankan aplikasi dengan Docker (CPU-only)

echo "🐳 MyCV-Platform Docker Testing"
echo "================================"
echo ""

# Cek apakah folder docker ada
if [ ! -d "docker" ]; then
    echo "❌ Error: Folder 'docker' tidak ditemukan!"
    echo "Pastikan Anda berada di root directory MyCV-Platform"
    exit 1
fi

# Masuk ke folder docker
cd docker

echo "⚠️  Peringatan: Docker GPU saat ini TIDAK berfungsi!"
echo "   Menggunakan Docker CPU-only untuk testing"
echo ""

# Pilihan testing
echo "Pilih jenis testing:"
echo "1) CPU Testing (Berfungsi)"
echo "2) GPU Testing (Bermasalah - akan error)"
echo "3) Minimal Testing"
echo ""
read -p "Masukkan pilihan (1-3): " choice

case $choice in
    1)
        echo "🧪 Menjalankan CPU Testing..."
        docker-compose -f docker-compose.cpu.yml up --build
        ;;
    2)
        echo "⚠️  Menjalankan GPU Testing (akan error)..."
        docker-compose -f docker-compose.gpu.yml up --build
        ;;
    3)
        echo "🧪 Menjalankan Minimal Testing..."
        docker-compose -f docker-compose.test.yml up --build
        ;;
    *)
        echo "❌ Pilihan tidak valid!"
        exit 1
        ;;
esac

echo ""
echo "✅ Selesai! Cek hasil di container atau volume"