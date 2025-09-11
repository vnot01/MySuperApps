#!/bin/bash

# MyCV-Platform Model Installation Script
# Download and setup YOLO11 and SAM2 models

set -e

echo "📥 MyCV-Platform Model Installation Starting..."

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

# Model URLs and info
declare -A YOLO_MODELS
YOLO_MODELS[yolo11n.pt]="https://github.com/ultralytics/assets/releases/download/v8.3.0/yolo11n.pt"
YOLO_MODELS[yolo11s.pt]="https://github.com/ultralytics/assets/releases/download/v8.3.0/yolo11s.pt"
YOLO_MODELS[yolo11m.pt]="https://github.com/ultralytics/assets/releases/download/v8.3.0/yolo11m.pt"
# YOLO_MODELS[yolo11l.pt]="https://github.com/ultralytics/assets/releases/download/v8.3.0/yolo11l.pt"
# YOLO_MODELS[yolo11x.pt]="https://github.com/ultralytics/assets/releases/download/v8.3.0/yolo11x.pt"

declare -A SAM_MODELS
SAM_MODELS[sam2_b.pt]="https://github.com/ultralytics/assets/releases/download/v8.3.0/sam2_b.pt"
# SAM_MODELS[sam2_l.pt]="https://github.com/ultralytics/assets/releases/download/v8.3.0/sam2_l.pt"
# SAM_MODELS[sam2.1_b.pt]="https://github.com/ultralytics/assets/releases/download/v8.3.0/sam2.1_b.pt"
# SAM_MODELS[sam2.1_l.pt]="https://github.com/ultralytics/assets/releases/download/v8.3.0/sam2.1_l.pt"

# Function to download model
download_model() {
    local model_name=$1
    local url=$2
    local target_dir=$3
    
    print_status "Downloading $model_name..."
    
    # Create target directory if it doesn't exist
    mkdir -p "$target_dir"
    
    # Download with progress bar
    if wget --progress=bar:force -O "$target_dir/$model_name" "$url" 2>&1; then
        print_success "Downloaded $model_name successfully"
        
        # Get file size
        local size=$(du -h "$target_dir/$model_name" | cut -f1)
        print_status "File size: $size"
        
        # Validate file (basic check)
        if [ -s "$target_dir/$model_name" ]; then
            print_success "File validation passed"
            return 0
        else
            print_error "File validation failed - empty file"
            return 1
        fi
    else
        print_error "Failed to download $model_name"
        return 1
    fi
}

# Function to install YOLO models
install_yolo_models() {
    print_status "Installing YOLO11 models..."
    
    local yolo_dir="data/models/yolo/active"
    local download_dir="data/models/yolo/downloads"
    
    # Download default models (yolo11s and yolo11m)
    local default_models=("yolo11s.pt" "yolo11m.pt")
    
    for model in "${default_models[@]}"; do
        if [ -n "${YOLO_MODELS[$model]}" ]; then
            if download_model "$model" "${YOLO_MODELS[$model]}" "$download_dir"; then
                # Move to active directory
                mv "$download_dir/$model" "$yolo_dir/"
                print_success "Installed $model to active directory"
            fi
        fi
    done
    
    print_status "YOLO11 models installation completed"
}

# Function to install SAM models
install_sam_models() {
    print_status "Installing SAM2 models..."
    
    local sam_dir="data/models/sam/active"
    local download_dir="data/models/sam/downloads"
    
    # Download default model (sam2_b)
    local default_model="sam2_b.pt"
    
    if [ -n "${SAM_MODELS[$default_model]}" ]; then
        if download_model "$default_model" "${SAM_MODELS[$default_model]}" "$download_dir"; then
            # Move to active directory
            mv "$download_dir/$default_model" "$sam_dir/"
            print_success "Installed $default_model to active directory"
        fi
    fi
    
    print_status "SAM2 models installation completed"
}

# Function to create model info file
create_model_info() {
    print_status "Creating model information file..."
    
    cat > data/models/model_info.json << EOF
{
    "yolo_models": {
        "yolo11n": {
            "filename": "yolo11n.pt",
            "size": "5.1MB",
            "params": "2.6M",
            "mAP": 39.5,
            "description": "YOLO11 Nano - Fastest, lowest accuracy"
        },
        "yolo11s": {
            "filename": "yolo11s.pt",
            "size": "21.5MB",
            "params": "9.4M",
            "mAP": 47.0,
            "description": "YOLO11 Small - Balanced speed/accuracy"
        },
        "yolo11m": {
            "filename": "yolo11m.pt",
            "size": "49.7MB",
            "params": "20.1M",
            "mAP": 51.5,
            "description": "YOLO11 Medium - Higher accuracy"
        },
        "yolo11l": {
            "filename": "yolo11l.pt",
            "size": "83.7MB",
            "params": "25.3M",
            "mAP": 53.2,
            "description": "YOLO11 Large - High accuracy"
        },
        "yolo11x": {
            "filename": "yolo11x.pt",
            "size": "136.0MB",
            "params": "68.2M",
            "mAP": 54.4,
            "description": "YOLO11 Extra Large - Highest accuracy"
        }
    },
    "sam_models": {
        "sam2_b": {
            "filename": "sam2_b.pt",
            "size": "358MB",
            "params": "91.0M",
            "description": "SAM2 Base - Fastest segmentation"
        }
    },
    "installation_date": "$(date -u +%Y-%m-%dT%H:%M:%SZ)",
    "version": "1.0.0"
}
EOF
    
    print_success "Model information file created"
}

# Function to check virtual environment
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

# Function to detect system capabilities
detect_system_capabilities() {
    print_status "Detecting system capabilities..."
    
    # Check if running in virtual environment
    if [[ "$VIRTUAL_ENV" != "" ]]; then
        print_success "✅ Running in virtual environment: $VIRTUAL_ENV"
    else
        print_warning "⚠️  Not running in virtual environment"
    fi
    
    # Check GPU availability
    if command -v nvidia-smi &> /dev/null; then
        print_success "✅ NVIDIA GPU detected"
        nvidia-smi --query-gpu=name,memory.total,memory.used --format=csv,noheader,nounits | while read line; do
            print_status "   GPU: $line"
        done
    else
        print_warning "⚠️  NVIDIA GPU not detected - will use CPU mode"
    fi
    
    # Check PyTorch CUDA support
    print_status "Checking PyTorch CUDA support..."
    source venv/bin/activate && python3 -c "
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

try:
    if torch.cuda.is_available():
        log_message(f'✅ PyTorch CUDA available - {torch.cuda.get_device_name(0)}', 'success')
        log_message(f'   CUDA Version: {torch.version.cuda}', 'info')
        log_message(f'   GPU Count: {torch.cuda.device_count()}', 'info')
    else:
        log_message('⚠️  PyTorch CUDA not available - will use CPU mode', 'warning')
        log_message(f'   PyTorch Version: {torch.__version__}', 'info')
        log_message(f'   CPU Threads: {torch.get_num_threads()}', 'info')
except Exception as e:
    log_message(f'❌ Error checking PyTorch: {e}', 'error')
    sys.exit(1)
"
}

# Function to test model loading
test_models() {
    print_status "Testing model loading..."
    
    # Check virtual environment first
    check_venv
    
    # Test YOLO model
    if [ -f "data/models/yolo/active/yolo11s.pt" ]; then
        print_status "Testing YOLO11s model..."
        source venv/bin/activate && python3 -c "
import torch
from ultralytics import YOLO
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

def check_environment():
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

try:
    check_environment()
    log_message('Loading YOLO11s model...', 'info')
    model = YOLO('data/models/yolo/active/yolo11s.pt')
    log_message('✅ YOLO11s model loaded successfully', 'success')
    
    # Test inference with mock data
    log_message('🧪 Testing with mock data...', 'info')
    import numpy as np
    mock_image = np.random.randint(0, 255, (640, 640, 3), dtype=np.uint8)
    results = model(mock_image, verbose=False)
    log_message('✅ Mock data inference successful', 'success')
    log_message(f'   Detected objects: {len(results[0].boxes) if results[0].boxes is not None else 0}', 'info')
    
except Exception as e:
    log_message(f'❌ YOLO11s model test failed: {e}', 'error')
    sys.exit(1)
"
    fi
    
    # Test SAM model
    if [ -f "data/models/sam/active/sam2_b.pt" ]; then
        print_status "Testing SAM2_b model..."
        source venv/bin/activate && python3 -c "
import torch
from ultralytics import SAM
import sys
import os
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

def check_environment():
    # Check virtual environment
    if 'VIRTUAL_ENV' in os.environ:
        log_message(f'✅ Running in virtual environment: {os.environ[\"VIRTUAL_ENV\"]}', 'success')
    else:
        log_message('⚠️  Not running in virtual environment', 'warning')
    
    # Check GPU/CPU mode
    if torch.cuda.is_available():
        log_message(f'🚀 GPU MODE: Using CUDA device - {torch.cuda.get_device_name(0)}', 'success')
    else:
        log_message('💻 CPU MODE: Using CPU for inference', 'warning')

try:
    check_environment()
    log_message('Loading SAM2_b model...', 'info')
    model = SAM('data/models/sam/active/sam2_b.pt')
    log_message('✅ SAM2_b model loaded successfully', 'success')
    
    # Test inference with mock data
    log_message('🧪 Testing with mock data...', 'info')
    mock_image = np.random.randint(0, 255, (640, 640, 3), dtype=np.uint8)
    results = model(mock_image, verbose=False)
    log_message('✅ Mock data inference successful', 'success')
    
except Exception as e:
    log_message(f'❌ SAM2_b model test failed: {e}', 'error')
    sys.exit(1)
"
    fi
    
    print_success "Model testing completed"
}

# Main installation process
main() {
    print_status "Starting model installation process..."
    
    # Check virtual environment first
    check_venv
    
    # Detect system capabilities
    detect_system_capabilities
    
    # Create directories
    mkdir -p data/models/yolo/{active,downloads}
    mkdir -p data/models/sam/{active,downloads}
    
    # Install models
    install_yolo_models
    install_sam_models
    
    # Create model info
    create_model_info
    
    # Test models
    test_models
    
    print_success "Model installation completed successfully!"
    
    echo ""
    echo "📊 Installed Models:"
    echo "YOLO Models:"
    ls -la data/models/yolo/active/
    echo ""
    echo "SAM Models:"
    ls -la data/models/sam/active/
    echo ""
    echo "Total disk usage:"
    du -sh data/models/
    echo ""
    echo "🎉 Ready to start MyCV-Platform!"
    echo "Run: docker-compose up -d"
}

# Run main function
main "$@"
