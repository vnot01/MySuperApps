#!/bin/bash

# MyCV-Platform Model Manager
# Manages model files including best.pt and other trained models

set -e

echo "🤖 MyCV-Platform Model Manager"
echo "=============================="

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

# Configuration
MODELS_DIR="data/models"
TRAINED_MODELS_DIR="data/models/trained"
BACKUP_DIR="data/models/backups"
CLOUD_STORAGE_DIR="data/models/cloud"
GITIGNORE_FILE=".gitignore"

# Create directories
create_directories() {
    print_status "Creating model directories..."
    mkdir -p "$TRAINED_MODELS_DIR"
    mkdir -p "$BACKUP_DIR"
    mkdir -p "$CLOUD_STORAGE_DIR"
    print_success "Model directories created"
}

# Setup .gitignore for model files
setup_gitignore() {
    print_status "Setting up .gitignore for model files..."
    
    if [ ! -f "$GITIGNORE_FILE" ]; then
        touch "$GITIGNORE_FILE"
    fi
    
    # Add model file patterns to .gitignore
    cat >> "$GITIGNORE_FILE" << EOF

# Model files (too large for Git)
*.pt
*.pth
*.onnx
*.engine
*.trt
*.h5
*.pb

# Model directories
data/models/trained/
data/models/backups/
data/models/cloud/
data/models/active/
data/models/downloads/

# Training outputs
runs/
weights/
checkpoints/

# Large data files
*.zip
*.tar.gz
*.tar.bz2
*.7z
EOF
    
    print_success ".gitignore updated for model files"
}

# Upload model to cloud storage
upload_to_cloud() {
    local model_file="$1"
    local cloud_name="$2"
    
    if [ ! -f "$model_file" ]; then
        print_error "Model file not found: $model_file"
        return 1
    fi
    
    print_status "Uploading $model_file to cloud storage..."
    
    # Create cloud storage directory if not exists
    mkdir -p "$CLOUD_STORAGE_DIR"
    
    # Copy to cloud directory
    cp "$model_file" "$CLOUD_STORAGE_DIR/$cloud_name"
    
    # Create metadata file
    cat > "$CLOUD_STORAGE_DIR/${cloud_name}.meta" << EOF
{
    "filename": "$cloud_name",
    "original_path": "$model_file",
    "upload_date": "$(date -u +%Y-%m-%dT%H:%M:%SZ)",
    "file_size": "$(du -h "$model_file" | cut -f1)",
    "file_size_bytes": "$(stat -f%z "$model_file")",
    "checksum": "$(md5sum "$model_file" | cut -d' ' -f1)",
    "description": "Trained model uploaded to cloud storage"
}
EOF
    
    print_success "Model uploaded to cloud storage: $cloud_name"
}

# Download model from cloud storage
download_from_cloud() {
    local cloud_name="$1"
    local target_path="$2"
    
    if [ ! -f "$CLOUD_STORAGE_DIR/$cloud_name" ]; then
        print_error "Model not found in cloud storage: $cloud_name"
        return 1
    fi
    
    print_status "Downloading $cloud_name from cloud storage..."
    
    # Create target directory if not exists
    mkdir -p "$(dirname "$target_path")"
    
    # Copy from cloud directory
    cp "$CLOUD_STORAGE_DIR/$cloud_name" "$target_path"
    
    # Verify checksum if metadata exists
    if [ -f "$CLOUD_STORAGE_DIR/${cloud_name}.meta" ]; then
        local expected_checksum=$(grep -o '"checksum": "[^"]*"' "$CLOUD_STORAGE_DIR/${cloud_name}.meta" | cut -d'"' -f4)
        local actual_checksum=$(md5sum "$target_path" | cut -d' ' -f1)
        
        if [ "$expected_checksum" = "$actual_checksum" ]; then
            print_success "Model downloaded and verified: $target_path"
        else
            print_error "Checksum verification failed for $target_path"
            return 1
        fi
    else
        print_success "Model downloaded: $target_path"
    fi
}

# List available models
list_models() {
    print_status "Available models:"
    echo ""
    
    echo "📁 Trained Models:"
    if [ -d "$TRAINED_MODELS_DIR" ] && [ "$(ls -A "$TRAINED_MODELS_DIR" 2>/dev/null)" ]; then
        ls -la "$TRAINED_MODELS_DIR" | grep -E '\.(pt|pth|onnx)$' | while read line; do
            echo "   $line"
        done
    else
        echo "   No trained models found"
    fi
    
    echo ""
    echo "☁️  Cloud Storage:"
    if [ -d "$CLOUD_STORAGE_DIR" ] && [ "$(ls -A "$CLOUD_STORAGE_DIR" 2>/dev/null)" ]; then
        ls -la "$CLOUD_STORAGE_DIR" | grep -E '\.(pt|pth|onnx)$' | while read line; do
            echo "   $line"
        done
    else
        echo "   No models in cloud storage"
    fi
    
    echo ""
    echo "💾 Backups:"
    if [ -d "$BACKUP_DIR" ] && [ "$(ls -A "$BACKUP_DIR" 2>/dev/null)" ]; then
        ls -la "$BACKUP_DIR" | grep -E '\.(pt|pth|onnx)$' | while read line; do
            echo "   $line"
        done
    else
        echo "   No backup models found"
    fi
}

# Backup model
backup_model() {
    local model_file="$1"
    local backup_name="$2"
    
    if [ ! -f "$model_file" ]; then
        print_error "Model file not found: $model_file"
        return 1
    fi
    
    if [ -z "$backup_name" ]; then
        backup_name="$(basename "$model_file" .pt)_backup_$(date +%Y%m%d_%H%M%S).pt"
    fi
    
    print_status "Backing up model: $model_file -> $backup_name"
    
    # Create backup directory if not exists
    mkdir -p "$BACKUP_DIR"
    
    # Copy to backup directory
    cp "$model_file" "$BACKUP_DIR/$backup_name"
    
    # Create backup metadata
    cat > "$BACKUP_DIR/${backup_name}.meta" << EOF
{
    "original_file": "$model_file",
    "backup_name": "$backup_name",
    "backup_date": "$(date -u +%Y-%m-%dT%H:%M:%SZ)",
    "file_size": "$(du -h "$model_file" | cut -f1)",
    "file_size_bytes": "$(stat -f%z "$model_file")",
    "checksum": "$(md5sum "$model_file" | cut -d' ' -f1)",
    "description": "Model backup"
}
EOF
    
    print_success "Model backed up: $backup_name"
}

# Restore model from backup
restore_model() {
    local backup_name="$1"
    local target_path="$2"
    
    if [ ! -f "$BACKUP_DIR/$backup_name" ]; then
        print_error "Backup not found: $backup_name"
        return 1
    fi
    
    if [ -z "$target_path" ]; then
        target_path="data/models/trained/$backup_name"
    fi
    
    print_status "Restoring model from backup: $backup_name -> $target_path"
    
    # Create target directory if not exists
    mkdir -p "$(dirname "$target_path")"
    
    # Copy from backup
    cp "$BACKUP_DIR/$backup_name" "$target_path"
    
    print_success "Model restored: $target_path"
}

# Create model download script
create_download_script() {
    print_status "Creating model download script..."
    
    cat > "scripts/download_models.sh" << 'EOF'
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
MODEL_URLS[best.pt]="https://your-cloud-storage.com/models/best.pt"
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
EOF
    
    chmod +x "scripts/download_models.sh"
    print_success "Model download script created"
}

# Main function
main() {
    case "${1:-help}" in
        "setup")
            print_status "Setting up model management..."
            create_directories
            setup_gitignore
            create_download_script
            print_success "Model management setup completed!"
            ;;
        "upload")
            if [ -z "$2" ]; then
                print_error "Usage: $0 upload <model_file> [cloud_name]"
                exit 1
            fi
            upload_to_cloud "$2" "${3:-$(basename "$2")}"
            ;;
        "download")
            if [ -z "$2" ]; then
                print_error "Usage: $0 download <cloud_name> [target_path]"
                exit 1
            fi
            download_from_cloud "$2" "${3:-data/models/trained/$2}"
            ;;
        "backup")
            if [ -z "$2" ]; then
                print_error "Usage: $0 backup <model_file> [backup_name]"
                exit 1
            fi
            backup_model "$2" "$3"
            ;;
        "restore")
            if [ -z "$2" ]; then
                print_error "Usage: $0 restore <backup_name> [target_path]"
                exit 1
            fi
            restore_model "$2" "$3"
            ;;
        "list")
            list_models
            ;;
        "help"|*)
            echo "🤖 MyCV-Platform Model Manager"
            echo "=============================="
            echo ""
            echo "Usage: $0 <command> [options]"
            echo ""
            echo "Commands:"
            echo "  setup                    - Setup model management system"
            echo "  upload <file> [name]     - Upload model to cloud storage"
            echo "  download <name> [path]   - Download model from cloud storage"
            echo "  backup <file> [name]     - Backup model locally"
            echo "  restore <name> [path]    - Restore model from backup"
            echo "  list                     - List available models"
            echo "  help                     - Show this help message"
            echo ""
            echo "Examples:"
            echo "  $0 setup"
            echo "  $0 upload best.pt my_trained_model.pt"
            echo "  $0 download my_trained_model.pt data/models/trained/"
            echo "  $0 backup best.pt"
            echo "  $0 restore best_backup_20231201_120000.pt"
            echo "  $0 list"
            ;;
    esac
}

# Run main function
main "$@"
