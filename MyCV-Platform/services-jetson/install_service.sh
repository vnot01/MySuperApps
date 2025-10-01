#!/bin/bash

# Install MyCV-Edge-API as System Service
# Auto-start on boot dan bisa di-manage dengan systemctl

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

# Configuration
SERVICE_NAME="mycv-edge-api"
SERVICE_FILE="/etc/systemd/system/${SERVICE_NAME}.service"
PROJECT_DIR="/home/my/MySuperApps/MyCV-Platform"
API_DIR="${PROJECT_DIR}/direct/app/api-hybrid-detection"
VENV_DIR="${PROJECT_DIR}/direct/venv"

print_status "🔧 Installing MyCV-Edge-API as System Service..."

# Check if running as root
if [ "$EUID" -ne 0 ]; then
    print_error "This script must be run as root (use sudo)"
    exit 1
fi

# Create systemd service file
print_status "Creating systemd service file..."

cat > "$SERVICE_FILE" << EOF
[Unit]
Description=MyCV-Edge-API Service
After=network.target
Wants=network.target

[Service]
Type=simple
User=my
Group=my
WorkingDirectory=${API_DIR}
Environment=PATH=${VENV_DIR}/bin
ExecStart=${VENV_DIR}/bin/python3 ${API_DIR}/app.py
Restart=always
RestartSec=10
StandardOutput=journal
StandardError=journal
SyslogIdentifier=mycv-edge-api

# Security settings
NoNewPrivileges=true
PrivateTmp=true
ProtectSystem=strict
ProtectHome=true
ReadWritePaths=${PROJECT_DIR}

[Install]
WantedBy=multi-user.target
EOF

print_success "✅ Service file created: $SERVICE_FILE"

# Reload systemd
print_status "Reloading systemd daemon..."
systemctl daemon-reload

# Enable service (auto-start on boot)
print_status "Enabling service for auto-start on boot..."
systemctl enable "$SERVICE_NAME"

print_success "✅ MyCV-Edge-API Service installed successfully!"
echo ""
print_status "📋 Service Management Commands:"
echo "  sudo systemctl start $SERVICE_NAME    # Start service"
echo "  sudo systemctl stop $SERVICE_NAME     # Stop service"
echo "  sudo systemctl restart $SERVICE_NAME  # Restart service"
echo "  sudo systemctl status $SERVICE_NAME   # Check status"
echo "  sudo systemctl enable $SERVICE_NAME   # Enable auto-start"
echo "  sudo systemctl disable $SERVICE_NAME  # Disable auto-start"
echo ""
print_status "📄 Log Commands:"
echo "  sudo journalctl -u $SERVICE_NAME -f   # Follow logs"
echo "  sudo journalctl -u $SERVICE_NAME      # View logs"
echo ""
print_status "🚀 To start the service now:"
echo "  sudo systemctl start $SERVICE_NAME"
echo ""
print_status "📡 API will be available at: http://100.98.142.94:5000"
