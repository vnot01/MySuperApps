#!/usr/bin/env bash
set -euo pipefail

echo "=== RESTART ALL MYCV-PLATFORM SERVICES ==="

echo "[1/6] Stop all MyCV-Platform services"
cd ~/MySuperApps/MyCV-Platform

# Stop API service
if [ -f "services-jetson/api_service.sh" ]; then
  echo "Stopping API service..."
  ./services-jetson/api_service.sh stop || true
fi

# Stop Web service  
if [ -f "services-jetson/web_service.sh" ]; then
  echo "Stopping Web service..."
  ./services-jetson/web_service.sh stop || true
fi

# Stop combined service
if [ -f "services-jetson/combined_service.sh" ]; then
  echo "Stopping combined service..."
  ./services-jetson/combined_service.sh stop || true
fi

echo "[2/6] Wait 5 seconds for clean shutdown"
sleep 5

echo "[3/6] Kill any remaining processes"
sudo pkill -f "run_api_hybrid_detection-jetson.py" 2>/dev/null || true
sudo pkill -f "app.py.*api-hybrid-detection-jetson" 2>/dev/null || true
sudo pkill -f "app.py.*web" 2>/dev/null || true

echo "[4/6] Wait 3 seconds"
sleep 3

echo "[5/6] Start all services"
if [ -f "services-jetson/combined_service.sh" ]; then
  echo "Starting combined service..."
  ./services-jetson/combined_service.sh start
else
  echo "Starting individual services..."
  if [ -f "services-jetson/api_service.sh" ]; then
    ./services-jetson/api_service.sh start
  fi
  if [ -f "services-jetson/web_service.sh" ]; then
    ./services-jetson/web_service.sh start
  fi
fi

echo "[6/6] Verify services status"
echo "=== API Service Status ==="
if [ -f "services-jetson/api_service.sh" ]; then
  ./services-jetson/api_service.sh status || true
fi

echo "=== Web Service Status ==="
if [ -f "services-jetson/web_service.sh" ]; then
  ./services-jetson/web_service.sh status || true
fi

echo "=== Process Check ==="
ps aux | grep -E "(run_api_hybrid_detection-jetson|app\.py)" | grep -v grep || echo "No MyCV-Platform processes found"

echo "=== Port Check ==="
netstat -tlnp | grep -E ":(5000|5002)" || echo "No services listening on ports 5000/5002"

echo "Done! All MyCV-Platform services restarted."
