#!/bin/bash

# MyCV-Platform Fresh Integration Test Script
# Runs YOLO + SAM2 integration test with clean environment

set -e

echo "🧪 MyCV-Platform Fresh Integration Test"
echo "======================================="

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
if [ ! -f "run_yolo_sam_integration.py" ]; then
    print_error "Please run this script from the MyCV-Platform root directory"
    exit 1
fi

# Check virtual environment
if [ ! -d "venv" ]; then
    print_error "Virtual environment not found! Please run ./scripts/setup.sh first"
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
if [ ! -f "data/models/yolo/active/yolo11m.pt" ]; then
    print_warning "YOLO11m model not found, downloading..."
    python3 -c "
from ultralytics import YOLO
import os
print('Downloading YOLO11m...')
yolo11m = YOLO('yolo11m.pt')
os.makedirs('data/models/yolo/active', exist_ok=True)
yolo11m.save('data/models/yolo/active/yolo11m.pt')
print('YOLO11m downloaded and saved')
"
    # Clean up downloaded file from root directory
    rm -f yolo11m.pt
    print_success "✅ YOLO11m downloaded and cleaned up"
fi

if [ ! -f "data/models/sam/active/sam2_b.pt" ]; then
    print_warning "SAM2_b model not found, downloading..."
    python3 -c "
from ultralytics import SAM
import os
import shutil
print('Downloading SAM2_b...')
sam2_b = SAM('sam2_b.pt')
os.makedirs('data/models/sam/active', exist_ok=True)
if os.path.exists('sam2_b.pt'):
    shutil.copy('sam2_b.pt', 'data/models/sam/active/sam2_b.pt')
    print('SAM2_b downloaded and saved')
"
    # Clean up downloaded file from root directory
    rm -f sam2_b.pt
    print_success "✅ SAM2_b downloaded and cleaned up"
fi

if [ ! -f "data/models/trained/best.pt" ]; then
    print_warning "best.pt model not found, downloading from GitHub..."
    print_status "Downloading best.pt from https://github.com/vnot01/MySuperApps/releases/download/v1.0.0/best.pt"
    mkdir -p data/models/trained
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

# Download best.pt
os.makedirs('data/models/trained', exist_ok=True)
download_file('https://github.com/vnot01/MySuperApps/releases/download/v1.0.0/best.pt', 'data/models/trained/best.pt')
"
    print_success "best.pt downloaded from GitHub"
fi

print_success "All required models are available"

# Clean previous results
print_status "Cleaning previous test results..."
rm -rf data/output/integration_results/*
rm -rf data/output/visualizations/*
rm -rf data/output/detections/*
rm -rf data/output/segmentations/*
print_success "Previous results cleaned"

# Run integration test
print_status "Running YOLO + SAM2 integration test..."
python3 run_yolo_sam_integration.py

if [ $? -eq 0 ]; then
    print_success "✅ Integration test completed successfully"
else
    print_error "❌ Integration test failed"
    exit 1
fi

# Generate visualizations
print_status "Generating visualizations..."
python3 visualize_results.py

if [ $? -eq 0 ]; then
    print_success "✅ Visualizations generated successfully"
else
    print_warning "⚠️  Visualization generation failed, but integration test passed"
fi

# Show results summary
print_status "Test Results Summary:"
echo "========================"
echo "📁 Generated files:"
find data/output -name "*.png" -o -name "*.json" | wc -l | xargs echo "Total files:"
echo ""
echo "📊 Detection results:"
for file in data/output/integration_results/*_detections.json; do
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

print_success "🎉 Fresh integration test completed successfully!"
print_status "📊 Check 'data/output/' for all results"
print_status "🖼️  Check 'data/output/visualizations/' for visualization images"
