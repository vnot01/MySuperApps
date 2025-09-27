#!/bin/bash

# MyCV-Platform Docker Test Script
# Runs Fresh Integration Test inside Docker container

set -e

echo "🐳 MyCV-Platform Docker Test"
echo "============================"

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

# Check if Docker is running
if ! docker info > /dev/null 2>&1; then
    print_error "Docker is not running. Please start Docker first."
    exit 1
fi

# Check if NVIDIA Docker is available
if ! docker run --rm --gpus all nvidia/cuda:11.8-base-ubuntu22.04 nvidia-smi > /dev/null 2>&1; then
    print_warning "NVIDIA Docker not available. Will run in CPU mode."
    GPU_AVAILABLE=false
else
    print_success "NVIDIA Docker is available. GPU mode enabled."
    GPU_AVAILABLE=true
fi

# Build Docker image
print_status "Building Docker image..."
docker-compose -f docker-compose.test.yml build

if [ $? -eq 0 ]; then
    print_success "✅ Docker image built successfully"
else
    print_error "❌ Failed to build Docker image"
    exit 1
fi

# Start container
print_status "Starting Docker container..."
docker-compose -f docker-compose.test.yml up -d

if [ $? -eq 0 ]; then
    print_success "✅ Docker container started successfully"
else
    print_error "❌ Failed to start Docker container"
    exit 1
fi

# Wait for container to be ready
print_status "Waiting for container to be ready..."
sleep 5

# Check container status
CONTAINER_NAME="myc-v-platform-test"
if docker ps | grep -q "$CONTAINER_NAME"; then
    print_success "✅ Container is running"
else
    print_error "❌ Container is not running"
    exit 1
fi

# Run environment detection in container
print_status "Running environment detection in container..."
docker exec $CONTAINER_NAME /app/venv/bin/python -c "
import torch
import os
from termcolor import colored

def log_message(message, level):
    colors = {
        'info': 'blue',
        'success': 'green',
        'warning': 'yellow',
        'error': 'red'
    }
    color = colors.get(level, 'white')
    print(colored(f'{level.upper()}: {message}', color))

log_message('🔍 MyCV-Platform Docker Environment Detection', 'info')
log_message('=' * 50, 'info')

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
    log_message(f'   PyTorch Version: {torch.__version__}', 'info')

log_message('🎉 Environment detection completed!', 'success')
"

# Run Fresh Integration Test in container
print_status "Running Fresh Integration Test in Docker container..."
docker exec $CONTAINER_NAME /app/venv/bin/python /app/run_yolo_sam_integration.py

if [ $? -eq 0 ]; then
    print_success "✅ Fresh Integration Test completed successfully in Docker"
else
    print_error "❌ Fresh Integration Test failed in Docker"
    print_status "Checking container logs..."
    docker logs $CONTAINER_NAME --tail 20
    exit 1
fi

# Generate visualizations in container
print_status "Generating visualizations in Docker container..."
docker exec $CONTAINER_NAME /app/venv/bin/python /app/visualize_results.py

if [ $? -eq 0 ]; then
    print_success "✅ Visualizations generated successfully in Docker"
else
    print_warning "⚠️  Visualization generation failed in Docker"
fi

# Show results summary
print_status "Test Results Summary from Docker container:"
echo "================================================"
docker exec $CONTAINER_NAME find /app/data/output -name "*.png" -o -name "*.json" | wc -l | xargs echo "Total files generated:"

# Show detection results
print_status "Detection results:"
docker exec $CONTAINER_NAME find /app/data/output/integration_results -name "*_detections.json" -exec basename {} \; | while read file; do
    echo "📄 $file:"
    docker exec $CONTAINER_NAME cat "/app/data/output/integration_results/$file" | python3 -c "
import json, sys
try:
    data = json.load(sys.stdin)
    print(f'  Objects: {len(data)}')
    for obj in data:
        print(f'  - {obj[\"class_name\"]}: {obj[\"confidence\"]:.3f}')
except:
    print('  Error reading file')
"
done

# Stop container
print_status "Stopping Docker container..."
docker-compose -f docker-compose.test.yml down

print_success "🎉 Docker test completed successfully!"
print_status "📊 Check 'data/output/' for results generated in Docker"
print_status "🖼️  Check 'data/output/visualizations/' for visualization images"