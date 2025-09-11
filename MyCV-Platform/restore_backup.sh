#!/bin/bash

# MyCV-Platform Backup Restoration Script
# Restores the backup package to a working state

set -e

echo "🔄 MyCV-Platform Backup Restoration"
echo "==================================="

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

# Check if running as root
if [[ $EUID -eq 0 ]]; then
   print_error "This script should not be run as root"
   exit 1
fi

# Check if we're in the backup directory
if [ ! -f "BACKUP_README.md" ]; then
    print_error "Please run this script from the MyCV-Platform-Backup directory"
    exit 1
fi

print_status "Starting backup restoration..."

# Step 1: Check system requirements
print_status "Checking system requirements..."
if ! command -v python3 &> /dev/null; then
    print_error "Python3 is not installed. Please install Python 3.11+ first."
    exit 1
fi

if ! command -v docker &> /dev/null; then
    print_warning "Docker is not installed. Some features may not work."
fi

print_success "System requirements check completed"

# Step 2: Create virtual environment
print_status "Creating Python virtual environment..."
if [ ! -d "venv" ]; then
    python3 -m venv venv
    print_success "Virtual environment created"
else
    print_warning "Virtual environment already exists"
fi

# Step 3: Activate virtual environment
print_status "Activating virtual environment..."
source venv/bin/activate
print_success "Virtual environment activated"

# Step 4: Install Python dependencies
print_status "Installing Python dependencies..."
pip install --upgrade pip
pip install -r requirements.txt
print_success "Python dependencies installed"

# Step 5: Install PyTorch with CUDA support
print_status "Installing PyTorch with CUDA support..."
pip install torch torchvision torchaudio --index-url https://download.pytorch.org/whl/cu118
print_success "PyTorch installed"

# Step 6: Install ultralytics
print_status "Installing ultralytics..."
pip install ultralytics
print_success "Ultralytics installed"

# Step 7: Verify models
print_status "Verifying models..."
if [ -f "data/models/yolo/active/yolo11m.pt" ]; then
    print_success "YOLO11m model found"
else
    print_warning "YOLO11m model not found"
fi

if [ -f "data/models/sam/active/sam2_b.pt" ]; then
    print_success "SAM2_b model found"
else
    print_warning "SAM2_b model not found"
fi

if [ -f "data/models/trained/best.pt" ]; then
    print_success "best.pt model found"
else
    print_warning "best.pt model not found"
fi

# Step 8: Test environment
print_status "Testing environment..."
python -c "
import torch
import sys
from termcolor import colored

def log_message(message, level):
    if level == 'info':
        print(colored('INFO: ' + message, 'blue'))
    elif level == 'warning':
        print(colored('WARNING: ' + message, 'yellow'))
    elif level == 'error':
        print(colored('ERROR: ' + message, 'red'))
    elif level == 'success':
        print(colored('SUCCESS: ' + message, 'green'))

# Check virtual environment
import os
if 'VIRTUAL_ENV' in os.environ:
    log_message(f'✅ Running in virtual environment: {os.environ[\"VIRTUAL_ENV\"]}', 'success')
else:
    log_message('⚠️  Not running in virtual environment', 'warning')

# Check GPU/CPU mode
if torch.cuda.is_available():
    log_message(f'🚀 GPU MODE: Using CUDA device - {torch.cuda.get_device_name(0)}', 'success')
    log_message(f'   GPU Memory: {torch.cuda.get_device_properties(0).total_memory / 1024**3:.1f} GB', 'info')
else:
    log_message('💻 CPU MODE: Using CPU for inference', 'warning')
    log_message(f'   CPU Threads: {torch.get_num_threads()}', 'info')

# Test imports
try:
    from ultralytics import YOLO, SAM
    log_message('✅ Ultralytics imported successfully', 'success')
except ImportError as e:
    log_message(f'❌ Ultralytics import failed: {e}', 'error')
    sys.exit(1)
"

if [ $? -eq 0 ]; then
    print_success "Environment test passed"
else
    print_error "Environment test failed"
    exit 1
fi

# Step 9: Make scripts executable
print_status "Making scripts executable..."
chmod +x scripts/*.sh
print_success "Scripts made executable"

# Step 10: Test integration
print_status "Testing YOLO + SAM2 integration..."
if [ -f "run_yolo_sam_integration.py" ]; then
    python run_yolo_sam_integration.py
    if [ $? -eq 0 ]; then
        print_success "Integration test passed"
    else
        print_warning "Integration test failed, but backup is still usable"
    fi
else
    print_warning "Integration script not found"
fi

print_success "Backup restoration completed successfully!"

echo ""
echo "🎉 MyCV-Platform Backup Restored!"
echo "================================="
echo ""
echo "📊 What's Available:"
echo "• YOLO11m + SAM2_b integration"
echo "• Custom best.pt model"
echo "• Test images and results"
echo "• Complete visualization system"
echo ""
echo "🚀 Quick Commands:"
echo "• python run_yolo_sam_integration.py  # Run integration"
echo "• python visualize_results.py         # Generate visualizations"
echo "• ./scripts/detect_environment.sh     # Check environment"
echo "• docker-compose up -d                # Start with Docker"
echo ""
echo "📚 Documentation:"
echo "• README.md - Main documentation"
echo "• BACKUP_README.md - Backup information"
echo "• docs/ENVIRONMENT_DETECTION.md - Environment guide"
echo ""
echo "✅ Ready to use!"
