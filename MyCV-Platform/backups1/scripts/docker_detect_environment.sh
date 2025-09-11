#!/bin/bash

# MyCV-Platform Docker Environment Detection Script
# Detects environment capabilities inside Docker container

set -e

echo "🐳 MyCV-Platform Docker Environment Detection"
echo "============================================="

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

# Check if container is running
check_container() {
    if ! docker ps | grep -q "myc-v-platform"; then
        print_error "MyCV-Platform container is not running!"
        print_status "Please start the container first: docker-compose up -d"
        exit 1
    fi
    
    print_success "MyCV-Platform container is running"
}

# Run environment detection in container
run_detection() {
    print_status "Running environment detection inside container..."
    
    # Check if environment detector exists in container
    if docker exec myc-v-platform test -f /app/app/utils/environment_detector.py; then
        print_status "Using Python environment detector..."
        docker exec myc-v-platform /app/venv/bin/python /app/app/utils/environment_detector.py
    else
        print_warning "Environment detector not found, running basic detection..."
        
        # Basic detection
        docker exec myc-v-platform /app/venv/bin/python -c "
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
}

# Show container info
show_container_info() {
    print_status "Container Information:"
    echo "========================"
    
    # Container status
    docker ps --filter "name=myc-v-platform" --format "table {{.Names}}\t{{.Status}}\t{{.Ports}}"
    
    # Container resources
    print_status "Container Resources:"
    docker stats myc-v-platform --no-stream --format "table {{.Container}}\t{{.CPUPerc}}\t{{.MemUsage}}\t{{.MemPerc}}"
    
    # GPU info if available
    if docker exec myc-v-platform nvidia-smi &>/dev/null; then
        print_status "GPU Information:"
        docker exec myc-v-platform nvidia-smi --query-gpu=name,memory.total,memory.used --format=csv,noheader,nounits
    else
        print_warning "NVIDIA GPU not available in container"
    fi
}

# Main function
main() {
    print_status "Starting Docker environment detection..."
    
    # Check container
    check_container
    
    # Show container info
    show_container_info
    
    echo ""
    print_status "Running environment detection..."
    echo "====================================="
    
    # Run detection
    run_detection
    
    print_success "Docker environment detection completed!"
    
    echo ""
    echo "📊 Quick Summary:"
    echo "=================="
    
    # Show virtual environment status
    if docker exec myc-v-platform test -n "\$VIRTUAL_ENV"; then
        echo "✅ Virtual Environment: Active"
    else
        echo "❌ Virtual Environment: Not active"
    fi
    
    # Show GPU status
    if docker exec myc-v-platform nvidia-smi &>/dev/null; then
        echo "✅ NVIDIA GPU: Available in container"
    else
        echo "⚠️  NVIDIA GPU: Not available (CPU mode)"
    fi
    
    # Show Python version
    echo "🐍 Python: $(docker exec myc-v-platform /app/venv/bin/python --version)"
    
    # Show PyTorch version
    docker exec myc-v-platform /app/venv/bin/python -c "import torch; print(f'🔥 PyTorch: {torch.__version__}')" 2>/dev/null || echo "❌ PyTorch: Not available"
    
    echo ""
    echo "🎉 MyCV-Platform container is ready!"
}

# Run main function
main "$@"
