#!/bin/bash

# MyCV-Platform Docker Shell Access Script
# Provides easy access to Docker container for manual testing

set -e

echo "🐳 MyCV-Platform Docker Shell Access"
echo "===================================="

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

CONTAINER_NAME="myc-v-platform-test"

# Check if container is running
if ! docker ps | grep -q "$CONTAINER_NAME"; then
    print_warning "Container is not running. Starting it first..."
    docker-compose -f docker-compose.test.yml up -d
    sleep 5
fi

if docker ps | grep -q "$CONTAINER_NAME"; then
    print_success "✅ Container is running: $CONTAINER_NAME"
else
    print_error "❌ Failed to start container"
    exit 1
fi

print_status "Available commands:"
echo "======================"
echo "1. Access container shell:"
echo "   docker exec -it $CONTAINER_NAME bash"
echo ""
echo "2. Run environment check:"
echo "   docker exec $CONTAINER_NAME /app/venv/bin/python -c \"import torch; print('CUDA available:', torch.cuda.is_available())\""
echo ""
echo "3. Run Fresh Integration Test:"
echo "   docker exec $CONTAINER_NAME /app/venv/bin/python /app/run_yolo_sam_integration.py"
echo ""
echo "4. Run visualization:"
echo "   docker exec $CONTAINER_NAME /app/venv/bin/python /app/visualize_results.py"
echo ""
echo "5. Check results:"
echo "   docker exec $CONTAINER_NAME ls -la /app/data/output/"
echo ""
echo "6. Run individual tests:"
echo "   docker exec $CONTAINER_NAME /app/scripts/run_yolo11m_test.sh"
echo "   docker exec $CONTAINER_NAME /app/scripts/run_best_pt_test.sh"
echo ""
echo "7. Stop container:"
echo "   docker-compose -f docker-compose.test.yml down"
echo ""

print_status "Quick environment check:"
docker exec "$CONTAINER_NAME" /app/venv/bin/python -c "
import torch
import os
print('🐍 Python Environment:')
print(f'  Virtual Env: {os.environ.get(\"VIRTUAL_ENV\", \"Not set\")}')
print(f'  Python Path: {os.environ.get(\"PATH\", \"Not set\")}')
print(f'  CUDA Available: {torch.cuda.is_available()}')
if torch.cuda.is_available():
    print(f'  GPU Device: {torch.cuda.get_device_name(0)}')
    print(f'  GPU Memory: {torch.cuda.get_device_properties(0).total_memory / 1024**3:.1f} GB')
"

print_status "Accessing container shell..."
echo "Type 'exit' to leave the container"
echo ""

# Access container shell
docker exec -it "$CONTAINER_NAME" bash
