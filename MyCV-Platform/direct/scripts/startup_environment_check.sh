#!/bin/bash

# MyCV-Platform Startup Environment Check
# Runs environment detection on startup

set -e

echo "🚀 MyCV-Platform Startup Environment Check"
echo "=========================================="

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

# Check if running in Docker
if [ -f /.dockerenv ]; then
    print_status "Running inside Docker container"
    PYTHON_CMD="/app/venv/bin/python"
    VENV_PATH="/app/venv"
else
    print_status "Running on host system"
    PYTHON_CMD="python3"
    VENV_PATH="venv"
    
    # Check if virtual environment exists
    if [ ! -d "$VENV_PATH" ]; then
        print_error "Virtual environment not found! Please run ./scripts/setup.sh first"
        exit 1
    fi
    
    # Activate virtual environment
    source "$VENV_PATH/bin/activate"
fi

# Run environment detection
print_status "Running environment detection..."

if [ -f "app/utils/environment_detector.py" ]; then
    $PYTHON_CMD app/utils/environment_detector.py
else
    print_warning "Environment detector not found, running basic detection..."
    
    $PYTHON_CMD -c "
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
    log_message(f'   CUDA Version: {torch.version.cuda}', 'info')
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

print_success "Startup environment check completed!"

# Show quick summary
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
else
    echo "⚠️  NVIDIA GPU: Not available (CPU mode)"
fi

# Show Python version
echo "🐍 Python: $($PYTHON_CMD --version)"

# Show PyTorch version
$PYTHON_CMD -c "import torch; print(f'🔥 PyTorch: {torch.__version__}')" 2>/dev/null || echo "❌ PyTorch: Not available"

echo ""
echo "🎉 MyCV-Platform is ready to start!"
