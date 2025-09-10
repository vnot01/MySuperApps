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
YOLO_MODELS[yolo11l.pt]="https://github.com/ultralytics/assets/releases/download/v8.3.0/yolo11l.pt"
YOLO_MODELS[yolo11x.pt]="https://github.com/ultralytics/assets/releases/download/v8.3.0/yolo11x.pt"

declare -A SAM_MODELS
SAM_MODELS[sam2.1_b.pt]="https://github.com/ultralytics/assets/releases/download/v8.3.0/sam2.1_b.pt"
SAM_MODELS[sam2.1_l.pt]="https://github.com/ultralytics/assets/releases/download/v8.3.0/sam2.1_l.pt"

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
    
    # Download default model (sam2.1_l)
    local default_model="sam2.1_l.pt"
    
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
        "sam2.1_b": {
            "filename": "sam2.1_b.pt",
            "size": "358MB",
            "params": "91.0M",
            "description": "SAM2 Base - Fastest segmentation"
        },
        "sam2.1_l": {
            "filename": "sam2.1_l.pt",
            "size": "2.4GB",
            "params": "308.0M",
            "description": "SAM2 Large - Best segmentation quality"
        }
    },
    "installation_date": "$(date -u +%Y-%m-%dT%H:%M:%SZ)",
    "version": "1.0.0"
}
EOF
    
    print_success "Model information file created"
}

# Function to test model loading
test_models() {
    print_status "Testing model loading..."
    
    # Test YOLO model
    if [ -f "data/models/yolo/active/yolo11s.pt" ]; then
        print_status "Testing YOLO11s model..."
        python3 -c "
from ultralytics import YOLO
import sys
try:
    model = YOLO('data/models/yolo/active/yolo11s.pt')
    print('✅ YOLO11s model loaded successfully')
    print(f'Model info: {model.info()}')
except Exception as e:
    print(f'❌ YOLO11s model test failed: {e}')
    sys.exit(1)
"
    fi
    
    # Test SAM model
    if [ -f "data/models/sam/active/sam2.1_l.pt" ]; then
        print_status "Testing SAM2.1_l model..."
        python3 -c "
from ultralytics import SAM
import sys
try:
    model = SAM('data/models/sam/active/sam2.1_l.pt')
    print('✅ SAM2.1_l model loaded successfully')
    print(f'Model info: {model.info()}')
except Exception as e:
    print(f'❌ SAM2.1_l model test failed: {e}')
    sys.exit(1)
"
    fi
    
    print_success "Model testing completed"
}

# Main installation process
main() {
    print_status "Starting model installation process..."
    
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
