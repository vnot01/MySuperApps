#!/bin/bash

# MyCV-Platform Apply All Changes Script
# Applies all environment detection and virtual environment changes

set -e

echo "🚀 MyCV-Platform Apply All Changes"
echo "=================================="

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

# Check if we're in the right directory
if [ ! -f "README.md" ] || [ ! -d "scripts" ]; then
    print_error "Please run this script from the MyCV-Platform root directory"
    exit 1
fi

print_status "Starting to apply all changes..."

# Step 1: Make all scripts executable
print_status "Making scripts executable..."
chmod +x scripts/*.sh
print_success "Scripts made executable"

# Step 2: Check if virtual environment exists
print_status "Checking virtual environment..."
if [ ! -d "venv" ]; then
    print_warning "Virtual environment not found. Please run ./scripts/setup.sh first"
    print_status "You can run: ./scripts/setup.sh"
    exit 1
else
    print_success "Virtual environment found"
fi

# Step 3: Activate virtual environment
print_status "Activating virtual environment..."
source venv/bin/activate
print_success "Virtual environment activated"

# Step 4: Install required packages if not already installed
print_status "Checking required packages..."
python3 -c "
import sys
required_packages = ['torch', 'ultralytics', 'numpy', 'opencv-python', 'Pillow', 'termcolor']
missing_packages = []

for package in required_packages:
    try:
        __import__(package.replace('-', '_'))
    except ImportError:
        missing_packages.append(package)

if missing_packages:
    print(f'Missing packages: {missing_packages}')
    sys.exit(1)
else:
    print('All required packages are installed')
"

if [ $? -eq 0 ]; then
    print_success "All required packages are installed"
else
    print_warning "Some packages are missing. Installing..."
    pip install torch ultralytics numpy opencv-python Pillow termcolor
    print_success "Required packages installed"
fi

# Step 5: Test environment detection
print_status "Testing environment detection..."
if [ -f "app/utils/environment_detector.py" ]; then
    python3 app/utils/environment_detector.py
    print_success "Environment detection test passed"
else
    print_error "Environment detector not found"
    exit 1
fi

# Step 6: Test mock data functionality
print_status "Testing mock data functionality..."
python3 -c "
import torch
import numpy as np
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

try:
    # Test mock data
    log_message('Testing mock data...', 'info')
    mock_image = np.random.randint(0, 255, (640, 640, 3), dtype=np.uint8)
    mock_tensor = torch.from_numpy(mock_image).permute(2, 0, 1).float().unsqueeze(0)
    
    if torch.cuda.is_available():
        mock_tensor = mock_tensor.cuda()
        log_message('Mock data created on GPU', 'success')
    else:
        log_message('Mock data created on CPU', 'success')
    
    log_message('Mock data test passed', 'success')
except Exception as e:
    log_message(f'Mock data test failed: {e}', 'error')
    exit(1)
"

if [ $? -eq 0 ]; then
    print_success "Mock data test passed"
else
    print_error "Mock data test failed"
    exit 1
fi

# Step 7: Test all environment detection scripts
print_status "Testing environment detection scripts..."

# Test detect_environment.sh
if [ -f "scripts/detect_environment.sh" ]; then
    print_status "Testing detect_environment.sh..."
    ./scripts/detect_environment.sh
    print_success "detect_environment.sh test passed"
else
    print_error "detect_environment.sh not found"
    exit 1
fi

# Step 8: Show summary
print_status "Showing summary of changes..."
echo ""
echo "📊 Changes Applied Successfully:"
echo "================================"
echo "✅ Virtual environment support added"
echo "✅ GPU/CPU mode detection implemented"
echo "✅ Mock data testing implemented"
echo "✅ Environment detection utility created"
echo "✅ All scripts made executable"
echo "✅ Required packages verified"
echo "✅ Environment detection tested"
echo "✅ Mock data functionality tested"

echo ""
echo "🎯 Available Commands:"
echo "====================="
echo "• ./scripts/detect_environment.sh - Detect environment capabilities"
echo "• ./scripts/docker_detect_environment.sh - Detect in Docker container"
echo "• ./scripts/run_all_environment_tests.sh - Run all environment tests"
echo "• python3 app/utils/environment_detector.py - Use Python utility"

echo ""
echo "📚 Documentation:"
echo "================"
echo "• README.md - Updated with environment detection features"
echo "• docs/ENVIRONMENT_DETECTION.md - Comprehensive guide"
echo "• CHANGELOG.md - Detailed changelog"

echo ""
echo "🚀 Next Steps:"
echo "============="
echo "1. Run: ./scripts/setup.sh (if not already done)"
echo "2. Run: ./scripts/install_models.sh"
echo "3. Run: ./scripts/run_all_environment_tests.sh"
echo "4. Run: docker-compose up -d"

print_success "All changes applied successfully!"
print_success "MyCV-Platform is ready with environment detection!"
