#!/bin/bash

# Auto-start Web Service on boot
# Tambahkan ke crontab atau startup script

# Configuration
PROJECT_DIR="/home/my/MySuperApps/MyCV-Platform"
WEB_SERVICE="$PROJECT_DIR/services/web_service.sh"

# Wait for system to be ready
sleep 30

# Start Web service
cd "$PROJECT_DIR"
./services/web_service.sh start

echo "$(date): MyCV-Platform Web Service auto-started" >> /tmp/mycv_web_auto_start.log





