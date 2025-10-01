#!/bin/bash

# install_v4l_utils.sh
# Install v4l-utils for camera detection

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

print_status "Installing v4l-utils for camera detection..."

# Check if v4l-utils is already installed
if command -v v4l2-ctl &> /dev/null; then
    print_success "v4l-utils is already installed"
    v4l2-ctl --version
    exit 0
fi

# Update package list
print_status "Updating package list..."
sudo apt update

# Install v4l-utils
print_status "Installing v4l-utils..."
sudo apt install -y v4l-utils

# Verify installation
if command -v v4l2-ctl &> /dev/null; then
    print_success "v4l-utils installed successfully"
    v4l2-ctl --version
    
    # List available video devices
    print_status "Available video devices:"
    ls /dev/video* 2>/dev/null || print_warning "No video devices found"
    
    # List devices with v4l2-ctl
    print_status "Video device information:"
    v4l2-ctl --list-devices 2>/dev/null || print_warning "No video devices detected"
    
else
    print_error "Failed to install v4l-utils"
    exit 1
fi

print_success "v4l-utils installation completed!"
