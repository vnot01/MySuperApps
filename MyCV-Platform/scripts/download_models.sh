#!/bin/bash

# MyCV-Platform Model Download Script
# Downloads models from cloud storage or external sources

set -e

echo "📥 MyCV-Platform Model Download"
echo "==============================="

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

print_status() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

print_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Model URLs (update these with your actual model URLs)
declare -A MODEL_URLS
MODEL_URLS[best.pt]="https://github.com/vnot01/MySuperApps/releases/download/trained-models/best.pt"
MODEL_URLS[yolo11s.pt]="https://github.com/ultralytics/assets/releases/download/v8.3.0/yolo11s.pt"
MODEL_URLS[yolo11m.pt]="https://github.com/ultralytics/assets/releases/download/v8.3.0/yolo11m.pt"
MODEL_URLS[sam2_b.pt]="https://github.com/ultralytics/assets/releases/download/v8.3.0/sam2_b.pt"

# Download model function
download_model() {
    local model_name="$1"
    local target_dir="$2"
    
    if [ -z "${MODEL_URLS[$model_name]}" ]; then
        print_error "Model URL not found for: $model_name"
        return 1
    fi
    
    local url="${MODEL_URLS[$model_name]}"
    local target_path="$target_dir/$model_name"
    
    print_status "Downloading $model_name from $url"
    
    # Create target directory if not exists
    mkdir -p "$target_dir"
    
    # Download with progress bar
    if wget --progress=bar:force -O "$target_path" "$url" 2>&1; then
        print_success "Downloaded $model_name successfully"
        
        # Get file size
        local size=$(du -h "$target_path" | cut -f1)
        print_status "File size: $size"
        
        # Validate file
        if [ -s "$target_path" ]; then
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

# Main function
main() {
    print_status "Starting model download..."
    
    # Create directories
    mkdir -p data/models/trained
    mkdir -p data/models/cloud
    
    # Download models
    for model in "${!MODEL_URLS[@]}"; do
        if [ "$model" = "best.pt" ]; then
            download_model "$model" "data/models/trained"
        else
            download_model "$model" "data/models/cloud"
        fi
    done
    
    print_success "Model download completed!"
}

# Run main function
main "$@"
