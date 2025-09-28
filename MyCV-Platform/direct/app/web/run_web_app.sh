#!/bin/bash

# MyCV-Platform Web Application Launcher
# Real-time camera detection with YOLO + SAM2

echo "🌐 MyCV-Platform Web Application"
echo "================================="
echo ""

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
if [ ! -f "app.py" ]; then
    print_error "Please run this script from the web directory"
    exit 1
fi

# Check if virtual environment exists
if [ ! -d "../../venv" ]; then
    print_error "Virtual environment not found! Please run setup first"
    exit 1
fi

# Activate virtual environment
print_status "Activating virtual environment..."
source ../../venv/bin/activate

if [[ "$VIRTUAL_ENV" != "" ]]; then
    print_success "✅ Virtual environment activated: $VIRTUAL_ENV"
else
    print_error "❌ Failed to activate virtual environment"
    exit 1
fi

# Install web dependencies
print_status "Installing web dependencies..."
pip install -r requirements.txt

if [ $? -eq 0 ]; then
    print_success "✅ Dependencies installed successfully"
else
    print_error "❌ Failed to install dependencies"
    exit 1
fi

# Check if models exist
print_status "Checking models..."
if [ ! -f "../../data/models/yolo/active/yolo11m.pt" ]; then
    print_error "❌ YOLO11m model not found. Please run model installation first."
    exit 1
fi

if [ ! -f "../../data/models/trained/best.pt" ]; then
    print_error "❌ best.pt model not found. Please run model installation first."
    exit 1
fi

if [ ! -f "../../data/models/sam/active/sam2_b.pt" ]; then
    print_error "❌ SAM2_b model not found. Please run model installation first."
    exit 1
fi

print_success "✅ All models found"

# Check camera availability (optional)
print_status "Checking camera availability..."
python3 -c "
import cv2
try:
    camera = cv2.VideoCapture(0)
    if camera.isOpened():
        print('✅ Camera is available')
        camera.release()
    else:
        print('⚠️ Camera is not available - will start without camera')
    exit(0)
except Exception as e:
    print('⚠️ Camera check failed - will start without camera')
    exit(0)
"

print_success "✅ Camera check completed (optional)"

# Start web application
print_status "Starting web application..."
print_success "🌐 Web application will be available at: http://100.98.142.94:5002"
print_success "📱 Open your browser and navigate to the URL above"
print_warning "⚠️  Press Ctrl+C to stop the application"
echo ""

python3 app.py
