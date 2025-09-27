#!/bin/bash

# MyCV-Platform Docker Folder Compressor
# Mengompres folder docker dengan timestamp

echo "📦 MyCV-Platform Docker Folder Compressor"
echo "=========================================="
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

# Check if we're in the right directory
if [ ! -d "docker" ]; then
    print_error "Folder 'docker' tidak ditemukan!"
    echo "Pastikan Anda berada di root directory MyCV-Platform"
    exit 1
fi

# Create backups directory if it doesn't exist
if [ ! -d "../backups" ]; then
    print_status "Membuat folder backups..."
    mkdir -p ../backups
fi

# Generate timestamp
TIMESTAMP=$(date +"%Y%m%d_%H%M%S")
ARCHIVE_NAME="MyCV-Platform-Docker-${TIMESTAMP}.tar.gz"
ARCHIVE_PATH="../backups/${ARCHIVE_NAME}"

print_status "Mengompres folder docker..."
print_status "Archive: ${ARCHIVE_NAME}"
print_status "Path: ${ARCHIVE_PATH}"

# Compress the docker folder
tar -czf "${ARCHIVE_PATH}" \
    --exclude="docker/__pycache__" \
    --exclude="docker/.git" \
    --exclude="docker/*.pyc" \
    --exclude="docker/**/*.pyc" \
    docker/

if [ $? -eq 0 ]; then
    print_success "✅ Kompresi berhasil!"
    
    # Get file size
    FILE_SIZE=$(du -h "${ARCHIVE_PATH}" | cut -f1)
    print_success "📁 Ukuran file: ${FILE_SIZE}"
    print_success "📍 Lokasi: ${ARCHIVE_PATH}"
    
    # Show archive contents
    print_status "📋 Isi archive:"
    tar -tzf "${ARCHIVE_PATH}" | head -20
    if [ $(tar -tzf "${ARCHIVE_PATH}" | wc -l) -gt 20 ]; then
        echo "... dan $(($(tar -tzf "${ARCHIVE_PATH}" | wc -l) - 20)) file lainnya"
    fi
    
    echo ""
    print_success "🎉 Folder docker berhasil dikompres!"
    print_success "📦 Archive: ${ARCHIVE_NAME}"
    print_success "💾 Ukuran: ${FILE_SIZE}"
    print_success "📁 Lokasi: ${ARCHIVE_PATH}"
    
else
    print_error "❌ Gagal mengompres folder docker"
    exit 1
fi
