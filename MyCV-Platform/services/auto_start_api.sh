#!/bin/bash

# Auto-start API on boot
# Tambahkan ke crontab atau startup script

# Configuration
PROJECT_DIR="/home/my/MySuperApps/MyCV-Platform"
API_SERVICE="$PROJECT_DIR/api_service.sh"

# Wait for system to be ready
sleep 30

# Start API service
cd "$PROJECT_DIR"
./api_service.sh start

echo "$(date): MyCV-Platform API auto-started" >> /tmp/mycv_auto_start.log
