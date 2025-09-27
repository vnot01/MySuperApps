#!/bin/bash

# MyCV-Platform Backup Creation Script
# Creates compressed backup with timestamp

set -e

echo "📦 MyCV-Platform Backup Creation"
echo "================================="

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
if [ ! -f "run_yolo_sam_integration.py" ]; then
    print_error "Please run this script from the MyCV-Platform root directory"
    exit 1
fi

# Generate timestamp
TIMESTAMP=$(date +"%Y%m%d-%H%M%S")
BACKUP_NAME="MyCV-Platform-Backup-${TIMESTAMP}"
BACKUP_DIR="/home/my/MySuperApps/backups"

print_status "Creating backup with timestamp: ${TIMESTAMP}"

# Create backup directory if it doesn't exist
mkdir -p "$BACKUP_DIR"

# Check disk space
print_status "Checking available disk space..."
AVAILABLE_SPACE=$(df / | tail -1 | awk '{print $4}')
REQUIRED_SPACE=1000000  # 1GB in KB

if [ "$AVAILABLE_SPACE" -lt "$REQUIRED_SPACE" ]; then
    print_warning "Low disk space detected. Available: ${AVAILABLE_SPACE}KB, Required: ${REQUIRED_SPACE}KB"
    print_warning "Proceeding anyway, but backup might fail if insufficient space"
fi

# Get current directory size
print_status "Calculating project size..."
PROJECT_SIZE=$(du -sh . | cut -f1)
print_status "Project size: $PROJECT_SIZE"

# Create TAR.GZ backup
print_status "Creating TAR.GZ backup..."
TAR_FILE="${BACKUP_DIR}/${BACKUP_NAME}.tar.gz"

# Exclude unnecessary files and directories
tar --exclude='venv' \
    --exclude='__pycache__' \
    --exclude='*.pyc' \
    --exclude='.git' \
    --exclude='.DS_Store' \
    --exclude='*.log' \
    --exclude='data/output/integration_results/*' \
    --exclude='data/output/visualizations/*' \
    --exclude='data/output/detections/*' \
    --exclude='data/output/segmentations/*' \
    --exclude='logs/*' \
    -czf "$TAR_FILE" .

if [ $? -eq 0 ]; then
    TAR_SIZE=$(du -sh "$TAR_FILE" | cut -f1)
    print_success "✅ TAR.GZ backup created: $TAR_FILE ($TAR_SIZE)"
else
    print_error "❌ Failed to create TAR.GZ backup"
    exit 1
fi

# Create ZIP backup
print_status "Creating ZIP backup..."
ZIP_FILE="${BACKUP_DIR}/${BACKUP_NAME}.zip"

# Create temporary directory for ZIP
TEMP_DIR="/tmp/${BACKUP_NAME}"
mkdir -p "$TEMP_DIR"

# Copy files excluding unnecessary ones
rsync -av --exclude='venv' \
          --exclude='__pycache__' \
          --exclude='*.pyc' \
          --exclude='.git' \
          --exclude='.DS_Store' \
          --exclude='*.log' \
          --exclude='data/output/integration_results/*' \
          --exclude='data/output/visualizations/*' \
          --exclude='data/output/detections/*' \
          --exclude='data/output/segmentations/*' \
          --exclude='logs/*' \
          . "$TEMP_DIR/"

# Create ZIP file
cd "$TEMP_DIR"
zip -r "$ZIP_FILE" . > /dev/null 2>&1

if [ $? -eq 0 ]; then
    ZIP_SIZE=$(du -sh "$ZIP_FILE" | cut -f1)
    print_success "✅ ZIP backup created: $ZIP_FILE ($ZIP_SIZE)"
else
    print_error "❌ Failed to create ZIP backup"
    rm -rf "$TEMP_DIR"
    exit 1
fi

# Clean up temporary directory
rm -rf "$TEMP_DIR"

# Return to original directory
cd - > /dev/null

# Create backup info file
print_status "Creating backup info file..."
INFO_FILE="${BACKUP_DIR}/${BACKUP_NAME}-INFO.md"

cat > "$INFO_FILE" << EOF
# MyCV-Platform Backup Information

**Created**: $(date '+%B %d, %Y at %H:%M:%S')
**Timestamp**: ${TIMESTAMP}
**Location**: ${BACKUP_DIR}

## 📊 Archive Details

### TAR.GZ Archive
- **File**: ${BACKUP_NAME}.tar.gz
- **Size**: ${TAR_SIZE}
- **Format**: Compressed tar archive
- **Best for**: Linux, macOS, Unix systems

### ZIP Archive
- **File**: ${BACKUP_NAME}.zip
- **Size**: ${ZIP_SIZE}
- **Format**: ZIP archive
- **Best for**: Windows, Linux, macOS (universal)

## 📁 What's Included

- Complete MyCV-Platform source code
- All configuration files
- Documentation and scripts
- Test images
- Model configuration (models not included to save space)

## 🚀 How to Restore

### Extract Archive
\`\`\`bash
# For TAR.GZ
tar -xzf ${BACKUP_NAME}.tar.gz

# For ZIP
unzip ${BACKUP_NAME}.zip
\`\`\`

### Setup Environment
\`\`\`bash
cd MyCV-Platform-Backup-${TIMESTAMP}
./scripts/setup.sh
./scripts/install_models.sh
\`\`\`

### Run Tests
\`\`\`bash
source venv/bin/activate
./scripts/run_fresh_integration_test.sh
\`\`\`

---
**Backup Created**: $(date)
**Script Version**: 1.0.0
EOF

print_success "✅ Backup info file created: $INFO_FILE"

# Show summary
echo ""
print_status "📊 Backup Summary:"
echo "==================="
echo "📁 Backup Directory: $BACKUP_DIR"
echo "📦 TAR.GZ File: ${BACKUP_NAME}.tar.gz ($TAR_SIZE)"
echo "📦 ZIP File: ${BACKUP_NAME}.zip ($ZIP_SIZE)"
echo "📄 Info File: ${BACKUP_NAME}-INFO.md"
echo ""

# Show disk usage
print_status "📊 Disk Usage:"
echo "==============="
df -h / | tail -1 | awk '{print "Available space: " $4 " (" $5 " used)"}'

print_success "🎉 Backup creation completed successfully!"
print_status "📁 Check $BACKUP_DIR for backup files"
print_status "📄 Read $INFO_FILE for detailed information"
