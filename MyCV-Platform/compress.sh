#!/bin/bash

# MyCV-Platform Master Compressor
# Script utama untuk mengompres project

echo "📦 MyCV-Platform Master Compressor"
echo "==================================="
echo ""

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Function to print colored output
print_status() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

print_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Show menu
echo "Pilih opsi kompresi:"
echo "1. Kompres folder direct saja"
echo "2. Kompres folder docker saja"
echo "3. Kompres seluruh project"
echo "4. Lihat semua backup"
echo "5. Keluar"
echo ""

read -p "Masukkan pilihan (1-5): " choice

case $choice in
    1)
        print_status "Mengompres folder direct..."
        ./compress_direct.sh
        ;;
    2)
        print_status "Mengompres folder docker..."
        ./compress_docker.sh
        ;;
    3)
        print_status "Mengompres seluruh project..."
        ./compress_all.sh
        ;;
    4)
        print_status "Menampilkan semua backup..."
        echo ""
        echo "📋 Semua backup yang tersedia:"
        ls -lh ../backups/ | grep -E "\.(tar\.gz|zip)$"
        echo ""
        echo "💾 Total ukuran backup:"
        du -sh ../backups/
        ;;
    5)
        print_status "Keluar..."
        exit 0
        ;;
    *)
        print_error "Pilihan tidak valid!"
        exit 1
        ;;
esac

echo ""
print_success "✅ Selesai!"
