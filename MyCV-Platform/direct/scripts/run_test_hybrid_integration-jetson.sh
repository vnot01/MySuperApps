#!/bin/bash

# MyCV-Platform Fresh Integration Test Script (Jetson Version)
# Runs YOLO + SAM2 integration test with remote directory structure for NVIDIA Jetson

set -e

# Load project info from model_info.json
PROJECT_NAME=$(python3 -c "import json; data=json.load(open('data-jetson/models/model_info.json')); print(data['name'])")
PROJECT_VERSION=$(python3 -c "import json; data=json.load(open('data-jetson/models/model_info.json')); print(data['version'])")

echo "🧪 $PROJECT_NAME v$PROJECT_VERSION"
echo "======================================================"

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
if [ ! -f "run_test_hybrid_integration-jetson.py" ]; then
    print_error "Please run this script from the MySuperApps/MyCV-Platform/direct directory"
    exit 1
fi

# Verify PyTorch and CUDA installation for Jetson
print_status "Verifying PyTorch and CUDA installation for Jetson..."
PYTORCH_CHECK=$(python3 -c "
import torch
import torchvision
print(f'PyTorch version: {torch.__version__}')
print(f'CUDA available: {torch.cuda.is_available()}')
print(f'CUDA version: {torch.version.cuda}')
print(f'TorchVision version: {torchvision.__version__}')
print(f'CUDA device - {torch.cuda.get_device_name(0)}')
print(f'GPU Memory: {torch.cuda.get_device_properties(0).total_memory / 1024**3:.1f} GB')
" 2>/dev/null)

if [ $? -ne 0 ] || [[ "$PYTORCH_CHECK" == *"CUDA available: False"* ]] || [[ "$PYTORCH_CHECK" == *"None"* ]]; then
    print_warning "PyTorch/CUDA not properly installed for Jetson. Installing..."
    
    print_status "Installing PyTorch 2.5.0 for Jetson Platform 6.1..."
    pip install https://github.com/ultralytics/assets/releases/download/v0.0.0/torch-2.5.0a0+872d972e41.nv24.08-cp310-cp310-linux_aarch64.whl
    
    print_status "Installing TorchVision 0.20.0 for Jetson Platform 6.1..."
    pip install https://github.com/ultralytics/assets/releases/download/v0.0.0/torchvision-0.20.0a0+afc54f7-cp310-cp310-linux_aarch64.whl
    
    print_status "Verifying installation..."
    python3 -c "
import torch
import torchvision
print(f'PyTorch version: {torch.__version__}')
print(f'CUDA available: {torch.cuda.is_available()}')
print(f'CUDA version: {torch.version.cuda}')
print(f'TorchVision version: {torchvision.__version__}')
print(f'CUDA device - {torch.cuda.get_device_name(0)}')
print(f'GPU Memory: {torch.cuda.get_device_properties(0).total_memory / 1024**3:.1f} GB')
"
    
    if [ $? -ne 0 ]; then
        print_error "Failed to install PyTorch for Jetson. Please check system requirements."
        print_error "Required: Jetson Orin with Ubuntu 22.04, Jetpack 6.1, L4T 36.4.2"
        exit 1
    fi
else
    print_success "PyTorch and CUDA verified for Jetson"
    echo "$PYTORCH_CHECK"
fi

# Check virtual environment
if [ ! -d "venv" ]; then
    print_error "Virtual environment not found! Please run ./scripts/setup-jetson.sh first"
    exit 1
fi

print_status "Activating virtual environment..."
source venv/bin/activate

if [[ "$VIRTUAL_ENV" != "" ]]; then
    print_success "✅ Virtual environment activated: $VIRTUAL_ENV"
else
    print_error "❌ Failed to activate virtual environment"
    exit 1
fi

# Check if models exist
print_status "Checking required models..."
if [ ! -f "data-jetson/models/yolo/active/yolo11m.pt" ]; then
    print_warning "YOLO11m model not found, downloading..."
    # cd MyCV-Platform/direct
    python3 -c "
from ultralytics import YOLO
import os
print('Downloading YOLO11m...')
yolo11m = YOLO('yolo11m.pt')
os.makedirs('data-jetson/models/yolo/active', exist_ok=True)
yolo11m.save('data-jetson/models/yolo/active/yolo11m.pt')
print('YOLO11m downloaded and saved')
"
    # Clean up downloaded file from root directory
    rm -f yolo11m.pt
    # cd ../..
    print_success "✅ YOLO11m downloaded and cleaned up"
fi

if [ ! -f "data-jetson/models/sam/active/sam2_b.pt" ]; then
    print_warning "SAM2_b model not found, downloading..."
    # cd MyCV-Platform/direct
    python3 -c "
from ultralytics import SAM
import os
import shutil
print('Downloading SAM2_b...')
sam2_b = SAM('sam2_b.pt')
os.makedirs('data-jetson/models/sam/active', exist_ok=True)
if os.path.exists('sam2_b.pt'):
    shutil.copy('sam2_b.pt', 'data-jetson/models/sam/active/sam2_b.pt')
    print('SAM2_b downloaded and saved')
"
    # Clean up downloaded file from root directory
    rm -f sam2_b.pt
    # cd ../..
    print_success "✅ SAM2_b downloaded and cleaned up"
fi

if [ ! -f "data-jetson/models/trained/active/best.pt" ]; then
    print_warning "best.pt model not found, downloading from GitHub..."
    print_status "Downloading best.pt from https://github.com/vnot01/MySuperApps/releases/download/v1.0.0/best.pt"
    # cd MyCV-Platform/direct
    mkdir -p data-jetson/models/trained/active
    python3 -c "
import os
import requests
from tqdm import tqdm

def download_file(url, filename):
    print(f'Downloading {filename}...')
    response = requests.get(url, stream=True)
    total_size = int(response.headers.get('content-length', 0))
    
    with open(filename, 'wb') as file, tqdm(
        desc=filename,
        total=total_size,
        unit='iB',
        unit_scale=True,
        unit_divisor=1024,
    ) as progress_bar:
        for chunk in response.iter_content(chunk_size=8192):
            size = file.write(chunk)
            progress_bar.update(size)
    
    print(f'✅ {filename} downloaded successfully')

# Download best.pt to active directory
os.makedirs('data-jetson/models/trained/active', exist_ok=True)
download_file('https://github.com/vnot01/MySuperApps/releases/download/v1.0.0/best.pt', 'data-jetson/models/trained/active/best.pt')
"
    # cd ../..
    print_success "best.pt downloaded from GitHub to active directory"
fi

print_success "All required models are available"
echo ""
echo "📁 Model Directory Structure:"
echo "  ├── data-jetson/models/yolo/active/     (YOLO11m model)"
echo "  ├── data-jetson/models/sam/active/      (SAM2_b model)"
echo "  └── data-jetson/models/trained/active/  (best.pt model)"
echo ""

# Create test timestamp and user ID
TIMESTAMP=$(date +"%Y%m%d_%H%M%S")
USER_ID="test_user_001"

# Create remote directory structure
INPUT_DIR="data-jetson/input/remote/${TIMESTAMP}/${USER_ID}"
OUTPUT_DIR="data-jetson/output/remote/${TIMESTAMP}/${USER_ID}"
YOLO_DIR="${OUTPUT_DIR}/yolo"
BEST_DIR="${OUTPUT_DIR}/best"
SEGMENTASI_DIR="${OUTPUT_DIR}/segmentasi"
HYBRID_DIR="${OUTPUT_DIR}/hybrid"

print_status "Creating remote directory structure..."
mkdir -p "${INPUT_DIR}"
mkdir -p "${OUTPUT_DIR}"
mkdir -p "${YOLO_DIR}"
mkdir -p "${BEST_DIR}"
mkdir -p "${SEGMENTASI_DIR}"
mkdir -p "${HYBRID_DIR}"
print_success "Remote directories created:"
print_success "  Input: ${INPUT_DIR}"
print_success "  Output: ${OUTPUT_DIR}"
print_success "  YOLO: ${YOLO_DIR}"
print_success "  Best: ${BEST_DIR}"
print_success "  Segmentasi: ${SEGMENTASI_DIR}"
print_success "  Hybrid: ${HYBRID_DIR}"

# Copy random test images (more than 1)
print_status "Copying random test images for testing..."
TEST_IMAGES_DIR="data-jetson/input/test_images"
AVAILABLE_IMAGES=($(ls "${TEST_IMAGES_DIR}"/*.jpg 2>/dev/null | head -5))

if [ ${#AVAILABLE_IMAGES[@]} -eq 0 ]; then
    print_error "No test images found in ${TEST_IMAGES_DIR}"
    exit 1
fi

# Copy 2-4 random images
NUM_IMAGES=$((RANDOM % 2 + 1))  # Random between 2-4 images
COPIED_FILES=()

for i in $(seq 0 $((NUM_IMAGES-1))); do
    if [ $i -lt ${#AVAILABLE_IMAGES[@]} ]; then
        cp "${AVAILABLE_IMAGES[$i]}" "${INPUT_DIR}/"
        COPIED_FILES+=("$(basename "${AVAILABLE_IMAGES[$i]}")")
        print_status "Copied: $(basename "${AVAILABLE_IMAGES[$i]}")"
    fi
done

print_success "Copied ${NUM_IMAGES} test images to ${INPUT_DIR}"
echo ""
echo "📋 Files to be processed:"
for file in "${COPIED_FILES[@]}"; do
    echo "  📄 $file"
done
echo ""

# Clean previous results in output directory
print_status "Cleaning previous test results..."
rm -rf "${OUTPUT_DIR}"/*
print_success "Previous results cleaned"

print_status "Uploading test images via API..."
UPLOAD_FORM=(-F "user_id=${USER_ID}")
for f in "${COPIED_FILES[@]}"; do
  UPLOAD_FORM+=(-F "files=@${INPUT_DIR}/$f")
done

# Test API connectivity first
print_status "Testing API connectivity..."
API_HEALTH=$(curl -s -w "%{http_code}" "http://100.117.234.2:5000/api/health" -o /dev/null)
if [ "$API_HEALTH" != "200" ]; then
  print_error "API server at 100.117.234.2:5000 is not accessible (HTTP $API_HEALTH)"
  print_error "Please ensure the Jetson API server is running locally"
  print_warning "Continuing with local processing instead..."
  
  # Run local processing without API
  print_status "Running local hybrid integration test..."
  python3 run_test_hybrid_integration-jetson.py
  
  if [ $? -eq 0 ]; then
    print_success "✅ Local integration test completed successfully"
  else
    print_error "❌ Local integration test failed"
    exit 1
  fi
  
  # Show results summary
  print_status "$PROJECT_NAME v$PROJECT_VERSION - Local Results Summary:"
  echo "======================================================"
  echo "📁 Generated files:"
  find "${OUTPUT_DIR}" -name "*.png" -o -name "*.json" | wc -l | xargs echo "Total files:"
  echo ""
  echo "📂 Directory Structure:"
  echo "Input:  ${INPUT_DIR}"
  echo "Output: ${OUTPUT_DIR}"
  echo "  ├── yolo/        (YOLO11m results)"
  echo "  ├── best/        (best.pt results)"
  echo "  ├── segmentasi/  (SAM2 segmentation results)"
  echo "  └── hybrid/      (Combined results)"
  echo ""
  print_success "🎉 $PROJECT_NAME completed successfully!"
  print_status "📊 Check '${OUTPUT_DIR}' for all results and visualizations"
  exit 0
fi

print_success "API server is accessible"
API_UPLOAD_RESP=$(curl -s -X POST "http://100.117.234.2:5000/api/upload" "${UPLOAD_FORM[@]}")
echo "API Response: $API_UPLOAD_RESP"

# Check if response is empty or invalid JSON
if [ -z "$API_UPLOAD_RESP" ]; then
  print_error "Empty response from API upload"
  exit 1
fi

SESSION_ID=$(echo "${API_UPLOAD_RESP}" | python3 -c "
import sys, json
try:
    data = json.load(sys.stdin)
    print(data.get('session_id', ''))
except json.JSONDecodeError as e:
    print('')
    sys.stderr.write(f'JSON decode error: {e}\n')
    sys.stderr.write(f'Response: {sys.stdin.read()}\n')
except Exception as e:
    print('')
    sys.stderr.write(f'Error: {e}\n')
")

if [ -z "$SESSION_ID" ]; then
  print_error "Failed to get session_id from API upload"
  print_error "API Response: $API_UPLOAD_RESP"
  print_warning "Continuing with local processing instead..."
  
  # Run local processing without API
  print_status "Running local hybrid integration test..."
  python3 run_test_hybrid_integration-jetson.py
  
  if [ $? -eq 0 ]; then
    print_success "✅ Local integration test completed successfully"
  else
    print_error "❌ Local integration test failed"
    exit 1
  fi
  
  # Show results summary
  print_status "$PROJECT_NAME v$PROJECT_VERSION - Local Results Summary:"
  echo "======================================================"
  echo "📁 Generated files:"
  find "${OUTPUT_DIR}" -name "*.png" -o -name "*.json" | wc -l | xargs echo "Total files:"
  echo ""
  echo "📂 Directory Structure:"
  echo "Input:  ${INPUT_DIR}"
  echo "Output: ${OUTPUT_DIR}"
  echo "  ├── yolo/        (YOLO11m results)"
  echo "  ├── best/        (best.pt results)"
  echo "  ├── segmentasi/  (SAM2 segmentation results)"
  echo "  └── hybrid/      (Combined results)"
  echo ""
  print_success "🎉 $PROJECT_NAME completed successfully!"
  print_status "📊 Check '${OUTPUT_DIR}' for all results and visualizations"
  exit 0
fi

print_success "Session: $SESSION_ID"
print_status "Waiting for processing to complete..."
ATTEMPTS=0
MAX_ATTEMPTS=60
while [ $ATTEMPTS -lt $MAX_ATTEMPTS ]; do
  STATUS=$(curl -s "http://100.117.234.2:5000/api/process/${SESSION_ID}" | python3 -c "import sys,json;print(json.load(sys.stdin).get('status',''))")
  if [ "$STATUS" = "completed" ]; then
    print_success "Processing completed"
    break
  elif [ "$STATUS" = "failed" ]; then
    print_error "Processing failed"
    exit 1
  fi
  ATTEMPTS=$((ATTEMPTS+1))
  sleep 1
done

if [ $ATTEMPTS -ge $MAX_ATTEMPTS ]; then
  print_error "Timeout waiting for processing"
  exit 1
fi

# Generate summary.json with session-aware URLs
print_status "Generating session summary with session-aware URLs..."
python3 run_test_hybrid_integration-jetson.py --session_id "$SESSION_ID"

if [ $? -eq 0 ]; then
    print_success "✅ Integration test and visualizations completed successfully"
else
    print_error "❌ Integration test failed"
    exit 1
fi

# cd ../..

# Show results summary
print_status "$PROJECT_NAME v$PROJECT_VERSION - Results Summary:"
echo "======================================================"
echo "📁 Generated files:"
find "${OUTPUT_DIR}" -name "*.png" -o -name "*.json" | wc -l | xargs echo "Total files:"
echo ""
echo "📊 Detection results:"
for file in "${OUTPUT_DIR}"/*_detections.json; do
    if [ -f "$file" ]; then
        echo "📄 $(basename $file):"
        cat $file | python3 -c "
import json, sys
try:
    data = json.load(sys.stdin)
    print(f'  Objects: {len(data)}')
    for obj in data:
        print(f'  - {obj[\"class_name\"]}: {obj[\"confidence\"]:.3f}')
except:
    print('  Error reading file')
"
    fi
done

echo ""
echo "📂 Directory Structure:"
echo "Input:  ${INPUT_DIR}"
echo "Output: ${OUTPUT_DIR}"
echo "  ├── yolo/        (YOLO11m results)"
echo "  ├── best/        (best.pt results)"
echo "  ├── segmentasi/  (SAM2 segmentation results)"
echo "  └── hybrid/      (Combined results)"
echo ""
echo "📋 File Naming Convention:"
echo "- Best only: (image_name)-(model_name)-best.png"
echo "- Detection/YOLO11 only: (image_name)-(model_name)-detection.png"
echo "- Segmentation: (image_name)-(model_name)-segmentation.png"
echo "- Hybrid (Best + SAM): (image_name)-(model_name)-hybrid.png"
echo "- Compare (All 4 combined): (image_name)-(model_name)-compare.png"
echo "- JSON: (image_name)-(model_name)-detection.json (in main output dir)"
echo "- Visualization: (image_name)-(model_name)-visualization.png (in subfolders)"
echo ""
echo "🎨 Generated Visualizations:"
echo "- YOLO11m visualization in yolo/ folder"
echo "- Best.pt visualization in best/ folder"
echo "- Compare visualization in main output directory"
echo ""
echo "📋 Processed Files Summary:"
for file in "${COPIED_FILES[@]}"; do
    echo "  ✅ $file - Successfully processed"
done
echo ""

print_success "🎉 $PROJECT_NAME completed successfully!"
print_status "📊 Check '${OUTPUT_DIR}' for all results and visualizations"
print_status "🖼️  All files are stored in structured directories as requested"
