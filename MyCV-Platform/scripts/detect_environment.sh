#!/bin/bash

# MyCV-Platform Environment Detection Script
# Detects virtual environment, GPU/CPU mode, and mock data capabilities

set -e

echo "🔍 MyCV-Platform Environment Detection"
echo "====================================="

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

# Check if virtual environment exists
check_venv() {
    if [ ! -d "venv" ]; then
        print_error "Virtual environment not found! Please run ./scripts/setup.sh first"
        exit 1
    fi
    
    if [ ! -f "venv/bin/activate" ]; then
        print_error "Virtual environment activation script not found!"
        exit 1
    fi
    
    print_success "Virtual environment found and ready"
}

# Activate virtual environment
activate_venv() {
    print_status "Activating virtual environment..."
    source venv/bin/activate
    
    if [[ "$VIRTUAL_ENV" != "" ]]; then
        print_success "✅ Virtual environment activated: $VIRTUAL_ENV"
    else
        print_warning "⚠️  Virtual environment activation failed"
    fi
}

# Run Python environment detection
run_detection() {
    print_status "Running comprehensive environment detection..."
    
    # Check if environment detector exists
    if [ -f "app/utils/environment_detector.py" ]; then
        python3 app/utils/environment_detector.py
    else
        print_warning "Environment detector not found, running basic detection..."
        
        # Basic detection
        python3 -c "
import torch
import sys
import os
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

# Test with mock data
log_message('🧪 Testing with mock data...', 'info')
import numpy as np
mock_image = np.random.randint(0, 255, (640, 640, 3), dtype=np.uint8)
mock_tensor = torch.from_numpy(mock_image).permute(2, 0, 1).float().unsqueeze(0)
if torch.cuda.is_available():
    mock_tensor = mock_tensor.cuda()
    log_message('✅ Mock data created on GPU', 'success')
else:
    log_message('✅ Mock data created on CPU', 'success')

log_message('🧪 MOCK DATA MODE: Test completed successfully', 'success')
"
    fi
}

# Main function
main() {
    print_status "Starting environment detection..."
    
    # Check virtual environment
    check_venv
    
    # Activate virtual environment
    activate_venv
    
    # Run detection
    run_detection
    
    print_success "Environment detection completed!"
    
    echo ""
    echo "📊 Quick Summary:"
    echo "=================="
    
    # Show virtual environment status
    if [[ "$VIRTUAL_ENV" != "" ]]; then
        echo "✅ Virtual Environment: $VIRTUAL_ENV"
    else
        echo "❌ Virtual Environment: Not active"
    fi
    
    # Show GPU status
    if command -v nvidia-smi &> /dev/null; then
        echo "✅ NVIDIA GPU: Available"
        nvidia-smi --query-gpu=name --format=csv,noheader | head -1 | while read gpu_name; do
            echo "   GPU: $gpu_name"
        done
    else
        echo "⚠️  NVIDIA GPU: Not available (CPU mode)"
    fi
    
    # Show Python version
    echo "🐍 Python: $(python3 --version)"
    
    # Show PyTorch version
    python3 -c "import torch; print(f'🔥 PyTorch: {torch.__version__}')" 2>/dev/null || echo "❌ PyTorch: Not available"
    
    echo ""
    echo "🎉 Ready to use MyCV-Platform!"
}

# Run main function
main "$@"
