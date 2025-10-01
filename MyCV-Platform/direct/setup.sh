#!/bin/bash
# MyCV-Platform Simple Setup Script
# One requirements.txt for all environments

set -e

# Colors
GREEN='\033[0;32m'
BLUE='\033[0;34m'
NC='\033[0m'

print_status() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

print_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

# Get script directory
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$SCRIPT_DIR"

print_status "🚀 MyCV-Platform Setup"
print_status "======================"

# Check Python version
print_status "Checking Python version..."
python3 --version

# Verify python3 is available
if ! command -v python3 &> /dev/null; then
    echo "❌ python3 not found. Please install Python 3.x"
    exit 1
fi

# Check if python command works (after venv activation)
print_status "Checking Python commands..."
if command -v python &> /dev/null; then
    print_status "✅ 'python' command available"
    PYTHON_CMD="python"
else
    print_status "⚠️  'python' command not available, using 'python3'"
    PYTHON_CMD="python3"
fi

# Create virtual environment if it doesn't exist
if [ ! -d "venv" ]; then
    print_status "Creating virtual environment..."
    python3 -m venv venv
    print_success "✅ Virtual environment created"
else
    print_status "Virtual environment already exists"
fi

# Activate virtual environment
print_status "Activating virtual environment..."
source venv/bin/activate

if [[ "$VIRTUAL_ENV" != "" ]]; then
    print_success "✅ Virtual environment activated: $VIRTUAL_ENV"
else
    echo "❌ Failed to activate virtual environment"
    exit 1
fi

# Upgrade pip
print_status "Upgrading pip..."
pip install --upgrade pip

# Install all requirements
print_status "Installing all requirements..."
pip install -r requirements.txt

# Check which python command works in venv
print_status "Checking Python commands in virtual environment..."
if command -v python &> /dev/null; then
    print_status "✅ 'python' command available in venv"
    PYTHON_CMD="python"
else
    print_status "⚠️  'python' command not available in venv, using 'python3'"
    PYTHON_CMD="python3"
fi

print_success "🎉 Setup completed successfully!"
print_status ""
print_status "📋 Available services:"
print_status "1. Web Interface: cd app/web && $PYTHON_CMD app.py"
print_status "2. GPU Server: cd app/api-hybrid-detection && $PYTHON_CMD app.py"
print_status "3. Jetson API: cd app/api-hybrid-detection-jetson && $PYTHON_CMD app.py"
print_status ""
print_status "🔧 To activate environment:"
print_status "   source venv/bin/activate"
