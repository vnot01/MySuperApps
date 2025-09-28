#!/bin/bash

# MyCV-Platform Hybrid Detection API Launcher
# Starts the RESTful API server for public access

set -e

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

# Get script directory
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
API_DIR="$SCRIPT_DIR"
DIRECT_DIR="$(dirname "$(dirname "$SCRIPT_DIR")")"

print_status "🚀 MyCV-Platform Hybrid Detection API Launcher"
print_status "======================================================"

# Check if we're in the right directory
if [ ! -f "$API_DIR/app.py" ]; then
    print_error "API app.py not found in $API_DIR"
    exit 1
fi

# Check if direct directory exists
if [ ! -d "$DIRECT_DIR" ]; then
    print_error "Direct directory not found: $DIRECT_DIR"
    exit 1
fi

# Check virtual environment
if [ ! -d "$DIRECT_DIR/venv" ]; then
    print_error "Virtual environment not found! Please run setup.sh first"
    exit 1
fi

print_status "Activating virtual environment..."
source "$DIRECT_DIR/venv/bin/activate"

if [[ "$VIRTUAL_ENV" != "" ]]; then
    print_success "✅ Virtual environment activated: $VIRTUAL_ENV"
else
    print_error "❌ Failed to activate virtual environment"
    exit 1
fi

# Check if required models exist
print_status "Checking required models..."
MODELS_OK=true

if [ ! -f "$DIRECT_DIR/data/models/yolo/active/yolo11m.pt" ]; then
    print_warning "YOLO11m model not found"
    MODELS_OK=false
fi

if [ ! -f "$DIRECT_DIR/data/models/sam/active/sam2_b.pt" ]; then
    print_warning "SAM2_b model not found"
    MODELS_OK=false
fi

if [ ! -f "$DIRECT_DIR/data/models/trained/active/best.pt" ]; then
    print_warning "best.pt model not found"
    MODELS_OK=false
fi

if [ "$MODELS_OK" = false ]; then
    print_warning "Some models are missing. API will download them automatically when needed."
fi

# Check if detection script exists
if [ ! -f "$DIRECT_DIR/run_api_hybrid_detection.py" ]; then
    print_error "Detection script not found: $DIRECT_DIR/run_api_hybrid_detection.py"
    exit 1
fi

# Create necessary directories
print_status "Creating necessary directories..."
mkdir -p "$DIRECT_DIR/data/input/remote"
mkdir -p "$DIRECT_DIR/data/output/remote"
print_success "✅ Directories created"

# Check if Flask is installed
print_status "Checking Flask installation..."
if ! python3 -c "import flask" 2>/dev/null; then
    print_warning "Flask not found, installing requirements..."
    pip install -r "$API_DIR/requirements.txt"
    print_success "✅ Requirements installed"
else
    print_success "✅ Flask is available"
fi

# Display API information
print_success "🎉 API Setup Complete!"
echo ""
print_status "📡 API Information:"
echo "   Host: 100.98.142.94"
echo "   Port: 5000"
echo "   URL: http://100.98.142.94:5000"
echo ""
print_status "📋 Available Endpoints:"
echo "   GET  /api/health - Health check"
echo "   GET  /api/status - API status"
echo "   POST /api/upload - Upload images for detection"
echo "   GET  /api/process/<session_id> - Get processing status"
echo "   GET  /api/results/<session_id> - Get detection results"
echo "   GET  /api/download/<session_id>/<filename> - Download result files"
echo "   GET  /api/detections - Get all recent detections"
echo ""
print_status "📁 Directory Structure:"
echo "   API Directory: $API_DIR"
echo "   Direct Directory: $DIRECT_DIR"
echo "   Upload Directory: $DIRECT_DIR/data/input/remote"
echo "   Output Directory: $DIRECT_DIR/data/output/remote"
echo ""
print_status "🔧 Usage Example:"
echo "   curl -X POST -F 'files=@image.jpg' -F 'user_id=my_user' http://100.98.142.94:5000/api/upload"
echo ""

# Start the API server
print_status "🚀 Starting API server..."
print_status "Press Ctrl+C to stop the server"
echo ""

cd "$API_DIR"
python3 app.py
