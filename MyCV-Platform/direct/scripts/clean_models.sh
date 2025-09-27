#!/bin/bash

# MyCV-Platform Model Cleanup Script
# Removes all downloaded models to free up disk space

set -e

echo "🗑️ MyCV-Platform Model Cleanup"
echo "==============================="

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

print_status "Checking current disk usage..."
df -h /

echo ""
print_status "Scanning for model files..."

# Find all .pt files
model_files=$(find . -name "*.pt" -type f 2>/dev/null || true)

if [ -z "$model_files" ]; then
    print_warning "No .pt model files found"
else
    echo "Found model files:"
    echo "$model_files" | while read -r file; do
        if [ -f "$file" ]; then
            size=$(du -h "$file" | cut -f1)
            echo "  $file ($size)"
        fi
    done
fi

echo ""
print_status "Cleaning up model directories..."

# Clean YOLO models
print_status "Cleaning YOLO models..."
rm -f data/models/yolo/active/*.pt 2>/dev/null || true
rm -f data/models/yolo/downloads/*.pt 2>/dev/null || true
print_success "YOLO models cleaned"

# Clean SAM models
print_status "Cleaning SAM models..."
rm -f data/models/sam/active/*.pt 2>/dev/null || true
rm -f data/models/sam/downloads/*.pt 2>/dev/null || true
print_success "SAM models cleaned"

# Clean trained models
print_status "Cleaning trained models..."
rm -f data/models/trained/*.pt 2>/dev/null || true
print_success "Trained models cleaned"

# Clean root directory models
print_status "Cleaning root directory models..."
rm -f *.pt 2>/dev/null || true
print_success "Root directory models cleaned"

# Clean any other model files
print_status "Cleaning any other model files..."
find . -name "*.pt" -type f -delete 2>/dev/null || true
print_success "All model files cleaned"

echo ""
print_status "Verifying cleanup..."

# Check directories
echo "YOLO models directory:"
ls -la data/models/yolo/active/ 2>/dev/null || echo "  (empty)"

echo "SAM models directory:"
ls -la data/models/sam/active/ 2>/dev/null || echo "  (empty)"

echo "Trained models directory:"
ls -la data/models/trained/ 2>/dev/null || echo "  (empty)"

echo "Root directory:"
ls -la *.pt 2>/dev/null || echo "  (no .pt files found)"

echo ""
print_status "Final disk usage:"
df -h /

# Calculate space freed
print_status "Calculating space freed..."
initial_usage=$(df / | tail -1 | awk '{print $3}')
current_usage=$(df / | tail -1 | awk '{print $3}')
freed_space=$((initial_usage - current_usage))

if [ $freed_space -gt 0 ]; then
    print_success "Freed approximately $freed_space KB of disk space"
else
    print_warning "No significant space was freed (models may have been already deleted)"
fi

echo ""
print_success "🎉 Model cleanup completed successfully!"
print_status "All model files have been removed from the system"
print_status "You can now run fresh tests to download models as needed"
