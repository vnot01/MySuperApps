#!/bin/bash

# MyCV-Platform Complete Project Compressor
# Mengompres seluruh project dengan timestamp

echo "📦 MyCV-Platform Complete Project Compressor"
echo "============================================="
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
if [ ! -f "README.md" ]; then
    print_error "File README.md tidak ditemukan!"
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
ARCHIVE_NAME="MyCV-Platform-Complete-${TIMESTAMP}.tar.gz"
ARCHIVE_PATH="../backups/${ARCHIVE_NAME}"

print_status "Mengompres seluruh project..."
print_status "Archive: ${ARCHIVE_NAME}"
print_status "Path: ${ARCHIVE_PATH}"

# Compress the entire project
tar -czf "${ARCHIVE_PATH}" \
    --exclude="direct/venv" \
    --exclude="direct/data/output" \
    --exclude="direct/data/models/downloaded" \
    --exclude="direct/logs" \
    --exclude="direct/__pycache__" \
    --exclude="direct/app/__pycache__" \
    --exclude="direct/app/web/__pycache__" \
    --exclude="direct/scripts/__pycache__" \
    --exclude="direct/.pytest_cache" \
    --exclude="direct/.git" \
    --exclude="direct/*.pyc" \
    --exclude="direct/**/*.pyc" \
    --exclude="docker/__pycache__" \
    --exclude="docker/.git" \
    --exclude="docker/*.pyc" \
    --exclude="docker/**/*.pyc" \
    --exclude=".git" \
    --exclude="__pycache__" \
    --exclude="*.pyc" \
    --exclude="**/*.pyc" \
    --exclude="*.log" \
    --exclude="**/*.log" \
    .

if [ $? -eq 0 ]; then
    print_success "✅ Kompresi berhasil!"
    
    # Get file size
    FILE_SIZE=$(du -h "${ARCHIVE_PATH}" | cut -f1)
    print_success "📁 Ukuran file: ${FILE_SIZE}"
    print_success "📍 Lokasi: ${ARCHIVE_PATH}"
    
    # Show archive contents
    print_status "📋 Isi archive:"
    tar -tzf "${ARCHIVE_PATH}" | head -30
    if [ $(tar -tzf "${ARCHIVE_PATH}" | wc -l) -gt 30 ]; then
        echo "... dan $(($(tar -tzf "${ARCHIVE_PATH}" | wc -l) - 30)) file lainnya"
    fi
    
    echo ""
    print_success "🎉 Seluruh project berhasil dikompres!"
    print_success "📦 Archive: ${ARCHIVE_NAME}"
    print_success "💾 Ukuran: ${FILE_SIZE}"
    print_success "📁 Lokasi: ${ARCHIVE_PATH}"
    
    # Show all backups
    print_status "📋 Semua backup yang tersedia:"
    ls -lh ../backups/ | grep -E "\.(tar\.gz|zip)$"
    
else
    print_error "❌ Gagal mengompres project"
    exit 1
fi
