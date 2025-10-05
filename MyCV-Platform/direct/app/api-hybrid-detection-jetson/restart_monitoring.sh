#!/bin/bash

echo "🔄 Restarting Jetson API with automatic monitoring..."

# Kill existing processes
pkill -f "python.*app.py"
pkill -f "uvicorn.*app:app"

# Wait a moment
sleep 2

# Start the API with automatic monitoring
cd /home/my/MySuperApps/MyCV-Platform/direct/app/api-hybrid-detection-jetson

# Load environment variables
source rvm_config.env

# Start the API
python3 app.py &

echo "✅ Jetson API restarted with automatic monitoring"
echo "📊 Monitoring will send data every 30 seconds to server"
echo "🔗 Server: $RVM_API_BASE_URL"
echo "🔑 RVM IDs: $RVM_IDS"
