#!/bin/bash

# MyCV-Platform Docker Test Commands
# Individual commands for testing in Docker container

set -e

echo "🐳 MyCV-Platform Docker Test Commands"
echo "====================================="

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

# Function to run command in container
run_in_container() {
    local cmd="$1"
    local description="$2"
    
    print_status "$description"
    echo "Command: $cmd"
    echo "----------------------------------------"
    
    if docker exec $CONTAINER_NAME $cmd; then
        print_success "✅ Command completed successfully"
    else
        print_error "❌ Command failed"
        return 1
    fi
    echo ""
}

# Check if container is running
if ! docker ps | grep -q "$CONTAINER_NAME"; then
    print_error "Container $CONTAINER_NAME is not running"
    print_status "Please start the container first:"
    echo "docker-compose -f docker-compose.test.yml up -d"
    exit 1
fi

print_success "Container $CONTAINER_NAME is running"

echo ""
print_status "Available Docker Test Commands:"
echo "====================================="
echo "1. Environment Detection"
echo "2. Check Virtual Environment"
echo "3. Check Python Packages"
echo "4. Check GPU/CUDA"
echo "5. Run Fresh Integration Test"
echo "6. Generate Visualizations"
echo "7. Check Results"
echo "8. Interactive Shell"
echo ""

# Example commands
print_status "Example Commands:"
echo "===================="
echo ""
echo "1. Environment Detection:"
echo "   docker exec $CONTAINER_NAME /app/venv/bin/python -c \"import torch; print('PyTorch:', torch.__version__); print('CUDA available:', torch.cuda.is_available())\""
echo ""
echo "2. Check Virtual Environment:"
echo "   docker exec $CONTAINER_NAME bash -c 'echo \"Virtual Env: \$VIRTUAL_ENV\"; which python; python --version'"
echo ""
echo "3. Run Fresh Integration Test:"
echo "   docker exec $CONTAINER_NAME /app/venv/bin/python /app/run_yolo_sam_integration.py"
echo ""
echo "4. Generate Visualizations:"
echo "   docker exec $CONTAINER_NAME /app/venv/bin/python /app/visualize_results.py"
echo ""
echo "5. Interactive Shell:"
echo "   docker exec -it $CONTAINER_NAME bash"
echo ""
echo "6. Check Results:"
echo "   docker exec $CONTAINER_NAME ls -la /app/data/output/"
echo ""

print_status "Ready for Docker testing!"
print_status "Use the commands above to test MyCV-Platform in Docker container"
