#!/bin/bash

# MyCV-Platform Setup Script
# Computer Vision Processing Service

set -e

echo "🚀 MyCV-Platform Setup Starting..."

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

# Check if running as root
if [[ $EUID -eq 0 ]]; then
   print_error "This script should not be run as root"
   exit 1
fi

# Check Ubuntu version
if ! grep -q "Ubuntu 22.04" /etc/os-release; then
    print_warning "This script is designed for Ubuntu 22.04. Current OS:"
    cat /etc/os-release | grep PRETTY_NAME
fi

print_status "Updating system packages..."
sudo apt update && sudo apt upgrade -y

print_status "Installing system dependencies..."
sudo apt install -y \
    curl \
    wget \
    git \
    build-essential \
    python3.11 \
    python3.11-dev \
    python3.11-venv \
    python3-pip \
    docker.io \
    docker-compose \
    nvidia-container-toolkit \
    nvidia-docker2

print_status "Adding user to docker group..."
sudo usermod -aG docker $USER

print_status "Creating Python virtual environment..."
python3.11 -m venv venv
source venv/bin/activate

print_status "Installing Python dependencies..."
pip install --upgrade pip
pip install -r requirements.txt

print_status "Installing PyTorch with CUDA support..."
pip install torch torchvision torchaudio --index-url https://download.pytorch.org/whl/cu118

print_status "Installing ultralytics..."
pip install ultralytics

print_status "Creating necessary directories..."
mkdir -p data/models/yolo/{active,downloads}
mkdir -p data/models/sam/{active,downloads}
mkdir -p data/input/test_images
mkdir -p data/output/{detections,segmentations,visualizations}
mkdir -p logs
mkdir -p config

print_status "Setting up environment file..."
if [ ! -f .env ]; then
    cat > .env << EOF
# MyCV-Platform Environment Configuration
ENVIRONMENT=development
LOG_LEVEL=INFO
API_HOST=0.0.0.0
API_PORT=8000

# Model Configuration
DEFAULT_YOLO_MODEL=yolo11s.pt
DEFAULT_SAM_MODEL=sam2.1_l.pt
DEFAULT_CONFIDENCE=0.5

# Storage Paths
DATA_PATH=/app/data
MODELS_PATH=/app/data/models
OUTPUT_PATH=/app/data/output
LOGS_PATH=/app/logs

# GPU Configuration
CUDA_VISIBLE_DEVICES=0
USE_GPU=true

# API Configuration
API_KEY=your-secret-api-key-here
CORS_ORIGINS=["http://localhost:3000", "http://localhost:8000"]

# Redis Configuration
REDIS_HOST=redis
REDIS_PORT=6379
REDIS_DB=0

# Monitoring
PROMETHEUS_ENABLED=true
GRAFANA_ENABLED=true
EOF
    print_success "Created .env file"
else
    print_warning ".env file already exists, skipping..."
fi

print_status "Setting up Docker..."
sudo systemctl enable docker
sudo systemctl start docker

print_status "Testing Docker installation..."
docker --version
docker-compose --version

print_status "Checking NVIDIA GPU support..."
if command -v nvidia-smi &> /dev/null; then
    nvidia-smi
    print_success "NVIDIA GPU detected and working"
else
    print_warning "NVIDIA GPU not detected or drivers not installed"
fi

print_status "Setting up systemd service..."
sudo tee /etc/systemd/system/mycv-platform.service > /dev/null << EOF
[Unit]
Description=MyCV-Platform Computer Vision Service
After=network.target docker.service
Requires=docker.service

[Service]
Type=oneshot
RemainAfterExit=yes
WorkingDirectory=$(pwd)
ExecStart=/usr/bin/docker-compose up -d
ExecStop=/usr/bin/docker-compose down
TimeoutStartSec=0
User=$USER
Group=$USER

[Install]
WantedBy=multi-user.target
EOF

sudo systemctl daemon-reload
sudo systemctl enable myc-v-platform.service

print_success "Setup completed successfully!"

echo ""
echo "🎉 MyCV-Platform is ready!"
echo ""
echo "Next steps:"
echo "1. Run: ./scripts/install_models.sh"
echo "2. Run: docker-compose up -d"
echo "3. Access: http://localhost:8000"
echo ""
echo "To start the service:"
echo "  sudo systemctl start myc-v-platform"
echo ""
echo "To check status:"
echo "  sudo systemctl status myc-v-platform"
echo ""
echo "To view logs:"
echo "  docker-compose logs -f"
echo ""

print_warning "Please log out and log back in for Docker group changes to take effect"
