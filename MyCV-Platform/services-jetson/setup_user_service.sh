#!/bin/bash

# Setup MyCV-Edge-API as User Systemd Service
# Tidak perlu sudo, menggunakan user systemd

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
SERVICE_NAME="mycv-api"
SERVICE_FILE="$HOME/.config/systemd/user/${SERVICE_NAME}.service"
PROJECT_DIR="/home/my/MySuperApps/MyCV-Platform"
API_DIR="${PROJECT_DIR}/direct/app/api-hybrid-detection"
VENV_DIR="${PROJECT_DIR}/direct/venv"

print_status "🔧 Setting up MyCV-Edge-API as User Systemd Service..."

# Create user systemd directory
mkdir -p "$HOME/.config/systemd/user"

# Create systemd service file
print_status "Creating user systemd service file..."

cat > "$SERVICE_FILE" << EOF
[Unit]
Description=MyCV-Edge-API Service
After=network.target
Wants=network.target

[Service]
Type=simple
WorkingDirectory=${API_DIR}
Environment=PATH=${VENV_DIR}/bin
ExecStart=${VENV_DIR}/bin/python3 ${API_DIR}/app.py
Restart=always
RestartSec=10
StandardOutput=journal
StandardError=journal
SyslogIdentifier=mycv-api

[Install]
WantedBy=default.target
EOF

print_success "✅ User service file created: $SERVICE_FILE"

# Reload user systemd
print_status "Reloading user systemd daemon..."
systemctl --user daemon-reload

# Enable service (auto-start on boot)
print_status "Enabling service for auto-start on boot..."
systemctl --user enable "$SERVICE_NAME"

# Enable lingering (start user services on boot)
print_status "Enabling user service lingering..."
sudo loginctl enable-linger "$USER"

print_success "✅ MyCV-Edge-API User Service installed successfully!"
echo ""
print_status "📋 Service Management Commands:"
echo "  systemctl --user start $SERVICE_NAME    # Start service"
echo "  systemctl --user stop $SERVICE_NAME     # Stop service"
echo "  systemctl --user restart $SERVICE_NAME  # Restart service"
echo "  systemctl --user status $SERVICE_NAME   # Check status"
echo "  systemctl --user enable $SERVICE_NAME   # Enable auto-start"
echo "  systemctl --user disable $SERVICE_NAME  # Disable auto-start"
echo ""
print_status "📄 Log Commands:"
echo "  journalctl --user -u $SERVICE_NAME -f   # Follow logs"
echo "  journalctl --user -u $SERVICE_NAME      # View logs"
echo ""
print_status "🚀 To start the service now:"
echo "  systemctl --user start $SERVICE_NAME"
echo ""
print_status "📡 API will be available at: http://100.98.142.94:5000"
