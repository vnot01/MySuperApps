#!/bin/bash

# MyCV-Web-Interface Service Manager
# Toggle on/off Web Application dengan background running dan auto-start

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configuration
# PROJECT_DIR="/home/my/MySuperApps/MyCV-Platform/direct/app/web"
PROJECT_DIR="/home/my/MySuperApps/MyCV-Platform"
WEB_DIR="${PROJECT_DIR}/direct/app/web"
VENV_DIR="${PROJECT_DIR}/direct/venv"
PID_FILE="/tmp/mycv_web.pid"
LOG_FILE="/tmp/mycv_web.log"
# MySuperApps/MyCV-Platform/direct/app/web/app.py
WEB_SCRIPT="${WEB_DIR}/app.py"
# WEB_SCRIPT="${WEB_DIR}/run_web_app.sh"

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

# Function to check if web service is running
is_running() {
    if [ -f "$PID_FILE" ]; then
        local pid=$(cat "$PID_FILE")
        if ps -p "$pid" > /dev/null 2>&1; then
            # Check if it's actually the Web service (port 5002)
            if ss -tlnp 2>/dev/null | grep ":5002" | grep -q "pid=$pid" && ps -p "$pid" -o cmd= | grep -q "python3.*app.py"; then
                return 0
            else
                rm -f "$PID_FILE"
            fi
        else
            rm -f "$PID_FILE"
        fi
    fi
    
    # Check for process listening on port 5002
    local web_pid=$(ss -tlnp 2>/dev/null | grep ":5002" | grep -o 'pid=[0-9]*' | cut -d'=' -f2)
    if [ -n "$web_pid" ] && ps -p "$web_pid" > /dev/null 2>&1; then
        # Verify it's actually a python3 app.py process
        if ps -p "$web_pid" -o cmd= | grep -q "python3.*app.py"; then
            echo "$web_pid" > "$PID_FILE"
            return 0
        fi
    fi
    
    return 1
}

# Function to start web service
start_web() {
    if is_running; then
        print_warning "⚠️ Web Service is already running (PID: $(cat $PID_FILE))"
        return 0
    fi
    
    print_status "🚀 Starting MyCV-Web-Interface Service..."
    
    # Check if web directory exists
    if [ ! -d "$WEB_DIR" ]; then
        print_error "❌ Web directory not found: $WEB_DIR"
        return 1
    fi
    
    # Check if virtual environment exists
    if [ ! -d "$VENV_DIR" ]; then
        print_error "❌ Virtual environment not found: $VENV_DIR"
        return 1
    fi
    
    # Check if web script exists
    if [ ! -f "$WEB_SCRIPT" ]; then
        print_error "❌ Web script not found: $WEB_SCRIPT"
        return 1
    fi
    
    # Start web service in background
    cd "$WEB_DIR"
    
    # Activate virtual environment
    nohup bash -c "source $VENV_DIR/bin/activate && python3 app.py" > "$LOG_FILE" 2>&1 &
    local pid=$!
    
    # Save PID
    echo "$pid" > "$PID_FILE"
    
    # Wait a moment for the service to start
    sleep 3
    
    # Check if service is actually running
    # if is_running; then
    #     print_success "✅ Web Service started successfully!"
    #     print_status "📡 Web URL: http://100.98.142.94:5002"
    #     print_status "📋 PID: $pid"
    #     print_status "📄 Log: $LOG_FILE"
    #     print_status "🔧 Use './web_service.sh stop' to stop the service"
        
    #     # Test web endpoint
    #     sleep 2
    #     if curl -s http://100.98.142.94:5002/health > /dev/null 2>&1; then
    #         print_success "✅ Web endpoint is responding"
    #     else
    #         print_warning "⚠️ Web endpoint not responding yet (may take a few more seconds)"
    #     fi
    # else
    #     print_error "❌ Failed to start Web Service"
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

# Function to stop web service
stop_web() {
    if ! is_running; then
        print_warning "⚠️ Web Service is not running"
        return 0
    fi
    
    print_status "🛑 Stopping MyCV-Web-Interface Service..."
    
    local pid=$(cat "$PID_FILE")
    if kill "$pid" 2>/dev/null; then
        print_success "✅ Web Service stopped successfully"
        rm -f "$PID_FILE"
    else
        print_error "❌ Failed to stop Web Service"
        return 1
    fi
}

# Function to restart web service
restart_web() {
    print_status "🔄 Restarting MyCV-Web-Interface Service..."
    stop_web
    sleep 2
    start_web
}

# Function to show web service status
status_web() {
    if is_running; then
        local pid=$(cat "$PID_FILE")
        print_success "✅ Web Service is RUNNING (PID: $pid)"
        print_status "📡 Web URL: http://100.98.142.94:5002"
        print_status "📄 Log: $LOG_FILE"
        
        # Test web endpoint
        if curl -s http://100.98.142.94:5002/health > /dev/null 2>&1; then
            print_success "✅ Web endpoint is responding"
        else
            print_warning "⚠️ Web endpoint not responding"
        fi
    else
        print_error "❌ Web Service is NOT RUNNING"
    fi
}

# Function to show web service logs
show_logs() {
    if [ -f "$LOG_FILE" ]; then
        print_status "📄 Web Service Logs (last 50 lines):"
        echo "----------------------------------------"
        tail -50 "$LOG_FILE"
    else
        print_warning "⚠️ No log file found: $LOG_FILE"
    fi
}

# Main function
main() {
    case "${1:-}" in
        start)
            start_web
            ;;
        stop)
            stop_web
            ;;
        restart)
            restart_web
            ;;
        status)
            status_web
            ;;
        logs)
            show_logs
            ;;
        *)
            echo "MyCV-Web-Interface Service Manager"
            echo "=================================="
            echo ""
            echo "Usage: $0 {start|stop|restart|status|logs}"
            echo ""
            echo "Commands:"
            echo "  start   - Start web service in background"
            echo "  stop    - Stop web service"
            echo "  restart - Restart web service"
            echo "  status  - Show web service status"
            echo "  logs    - Show web service logs"
            echo ""
            echo "Examples:"
            echo "  $0 start    # Start web service"
            echo "  $0 status   # Check status"
            echo "  $0 logs     # View logs"
            echo "  $0 stop     # Stop service"
            ;;
    esac
}

# Run main function
main "$@"
