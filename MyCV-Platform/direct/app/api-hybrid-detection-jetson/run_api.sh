#!/bin/bash

# MyCV-Platform Hybrid Detection API Launcher (Jetson)
# Starts the RESTful API server for Jetson edge device

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

print_status "🚀 MyCV-Platform Hybrid Detection API Launcher (Jetson)"
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

# Check if required models exist for Jetson
print_status "Checking required models for Jetson..."
MODELS_OK=true

if [ ! -f "$DIRECT_DIR/data-jetson/models/yolo/active/yolo11m.pt" ]; then
    print_warning "YOLO11m model not found in data-jetson"
    MODELS_OK=false
fi

if [ ! -f "$DIRECT_DIR/data-jetson/models/sam/active/sam2_b.pt" ]; then
    print_warning "SAM2_b model not found in data-jetson"
    MODELS_OK=false
fi

if [ ! -f "$DIRECT_DIR/data-jetson/models/trained/active/best.pt" ]; then
    print_warning "best.pt model not found in data-jetson"
    MODELS_OK=false
fi

if [ "$MODELS_OK" = false ]; then
    print_warning "Some models are missing. API will download them automatically when needed."
fi

# Check if detection script exists for Jetson
if [ ! -f "$DIRECT_DIR/run_api_hybrid_detection-jetson.py" ]; then
    print_error "Jetson detection script not found: $DIRECT_DIR/run_api_hybrid_detection-jetson.py"
    exit 1
fi

# Create necessary directories for Jetson
print_status "Creating necessary directories for Jetson..."
mkdir -p "$DIRECT_DIR/data-jetson/input/remote"
mkdir -p "$DIRECT_DIR/data-jetson/output/remote"
print_success "✅ Directories created"

# Check if Flask is installed
print_status "Checking Flask installation..."
if ! python3 -c "import flask" 2>/dev/null; then
    print_warning "Flask not found, installing requirements..."
    pip install -r "$DIRECT_DIR/requirements.txt"
    print_success "✅ Requirements installed"
else
    print_success "✅ Flask is available"
fi

# Display API information
print_success "🎉 API Setup Complete!"
echo ""
print_status "📡 API Information (Jetson):"
echo "   Host: 100.117.234.2"
echo "   Port: 5000"
echo "   URL: http://100.117.234.2:5000"
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
print_status "📁 Directory Structure (Jetson):"
echo "   API Directory: $API_DIR"
echo "   Direct Directory: $DIRECT_DIR"
echo "   Upload Directory: $DIRECT_DIR/data-jetson/input/remote"
echo "   Output Directory: $DIRECT_DIR/data-jetson/output/remote"
echo ""
print_status "🔧 Usage Example (Jetson):"
echo "   curl -X POST -F 'files=@image.jpg' -F 'user_id=my_user' http://100.117.234.2:5000/api/upload"
echo ""

# Start the API server
print_status "🚀 Starting API server..."
print_status "Press Ctrl+C to stop the server"
echo ""

cd "$API_DIR"
python3 app.py





