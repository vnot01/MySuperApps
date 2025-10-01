#!/bin/bash

# MyCV-Edge-API Service Manager
# Toggle On/Off untuk API Hybrid Detection

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

# Configuration (Jetson)
API_DIR="/home/my/MySuperApps/MyCV-Platform/direct/app/api-hybrid-detection-jetson"
PID_FILE="/tmp/mycv-edge-api.pid"
LOG_FILE="/tmp/mycv-edge-api.log"
API_SCRIPT="app.py"

# Function to check if API is running
is_running() {
    # First check if PID file exists and process is running
    if [ -f "$PID_FILE" ]; then
        local pid=$(cat "$PID_FILE")
        if ps -p "$pid" > /dev/null 2>&1; then
            # Double check if it's actually our API process
            if ps -p "$pid" -o cmd= | grep -q "python3.*app.py"; then
                return 0  # Running
            else
                rm -f "$PID_FILE"  # Clean up stale PID file
            fi
        else
            rm -f "$PID_FILE"  # Clean up stale PID file
        fi
    fi
    
    # If PID file doesn't exist or process not found, check by process name
    if pgrep -f "python3.*app.py" > /dev/null 2>&1; then
        # Find the PID and recreate PID file
        local pid=$(pgrep -f "python3.*app.py")
        echo "$pid" > "$PID_FILE"
        return 0  # Running
    fi
    
    return 1  # Not running
}

# Function to start API
start_api() {
    if is_running; then
        print_warning "API is already running (PID: $(cat $PID_FILE))"
        return 0
    fi
    
    print_status "🚀 Starting MyCV-Edge-API Service (Jetson)..."
    
    # Change to API directory
    cd "$API_DIR"
    
    # Activate virtual environment
    source ../../venv/bin/activate
    
    # Start API in background
    nohup python3 "$API_SCRIPT" > "$LOG_FILE" 2>&1 &
    local pid=$!
    
    # Save PID
    echo "$pid" > "$PID_FILE"
    
    # Wait a moment for the service to start
    sleep 3
    
    # Check if process is still running
    # if ps -p "$pid" > /dev/null 2>&1; then
    #     print_success "✅ API Service started successfully!"
    #     print_status "📡 API URL: http://100.117.234.2:5000"
    #     print_status "📋 PID: $pid"
    #     print_status "📄 Log: $LOG_FILE"
    #     print_status "🔧 Use './api_service.sh stop' to stop the service"
        
    #     # Test API endpoint
    #     sleep 2
    #     if curl -s http://100.117.234.2:5000/api/health > /dev/null 2>&1; then
    #         print_success "✅ API endpoint is responding"
    #     else
    #         print_warning "⚠️  API started but endpoint not responding yet"
    #     fi
    # else
    #     print_error "❌ Failed to start API Service"
    #     print_error "📄 Check log: $LOG_FILE"
    #     rm -f "$PID_FILE"
        
    #     # Show last few lines of log
    #     if [ -f "$LOG_FILE" ]; then
    #         print_error "Last log entries:"
    #         tail -5 "$LOG_FILE" | sed 's/^/   /'
    #     fi
    #     return 1
    # fi
}

# Function to stop API
stop_api() {
    if ! is_running; then
        print_warning "⚠️ API is not running"
        return 0
    fi
    
    local pid=$(cat "$PID_FILE")
    print_status "🛑 Stopping MyCV-Edge-API Service (PID: $pid)..."
    
    # Kill the process
    kill "$pid" 2>/dev/null || true
    
    # Wait for graceful shutdown
    local count=0
    while [ $count -lt 10 ] && ps -p "$pid" > /dev/null 2>&1; do
        sleep 1
        count=$((count + 1))
    done
    
    # Force kill if still running
    if ps -p "$pid" > /dev/null 2>&1; then
        print_warning "Force killing API process..."
        kill -9 "$pid" 2>/dev/null || true
    fi
    
    # Clean up PID file
    rm -f "$PID_FILE"
    
    print_success "✅ API Service stopped successfully!"
}

# Function to restart API
restart_api() {
    print_status "🔄 Restarting MyCV-Edge-API Service..."
    stop_api
    sleep 2
    start_api
}

# Function to show status
show_status() {
    if is_running; then
        local pid=$(cat "$PID_FILE")
        print_success "✅ API Service is RUNNING"
        print_status "📋 PID: $pid"
        print_status "📡 URL: http://100.98.142.94:5000"
        print_status "📄 Log: $LOG_FILE"
        
        # Show recent log entries
        if [ -f "$LOG_FILE" ]; then
            print_status "📋 Recent log entries:"
            tail -5 "$LOG_FILE" | sed 's/^/   /'
        fi
    else
        print_warning "❌ API Service is NOT RUNNING"
        print_status "🔧 Use './api_service.sh start' to start the service"
    fi
}

# Function to show logs
show_logs() {
    if [ -f "$LOG_FILE" ]; then
        print_status "📄 API Service Logs:"
        echo "----------------------------------------"
        tail -20 "$LOG_FILE"
        echo "----------------------------------------"
        print_status "📋 Use 'tail -f $LOG_FILE' to follow logs in real-time"
    else
        print_warning "No log file found. API may not have been started yet."
    fi
}

# Function to show help
show_help() {
            echo "MyCV-Edge-API Service Manager"
    echo "=================================="
    echo ""
    echo "Usage: $0 {start|stop|restart|status|logs|help}"
    echo ""
    echo "Commands:"
    echo "  start   - Start API service in background"
    echo "  stop    - Stop API service"
    echo "  restart - Restart API service"
    echo "  status  - Show service status"
    echo "  logs    - Show recent logs"
    echo "  help    - Show this help message"
    echo ""
    echo "Examples:"
    echo "  $0 start    # Start API service"
    echo "  $0 stop     # Stop API service"
    echo "  $0 status   # Check if running"
    echo "  $0 logs     # View logs"
    echo ""
    echo "API will be available at: http://100.98.142.94:5000"
    echo "Logs are stored at: $LOG_FILE"
    echo "PID file: $PID_FILE"
}

# Main script logic
case "$1" in
    start)
        start_api
        ;;
    stop)
        stop_api
        ;;
    restart)
        restart_api
        ;;
    status)
        show_status
        ;;
    logs)
        show_logs
        ;;
    help|--help|-h)
        show_help
        ;;
    *)
        print_error "Invalid command: $1"
        echo ""
        show_help
        exit 1
        ;;
esac
