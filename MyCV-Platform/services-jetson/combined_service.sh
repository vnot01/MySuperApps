#!/bin/bash

# MyCV-Edge-API Combined Service Manager (Jetson)
# Mengelola API dan Web Service secara bersamaan untuk Jetson

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configuration (Jetson)
PROJECT_DIR="/home/my/MySuperApps/MyCV-Platform"
SERVICES_DIR="${PROJECT_DIR}/services-jetson"
API_SERVICE="${SERVICES_DIR}/api_service.sh"
WEB_SERVICE="${SERVICES_DIR}/web_service.sh"
API_DIR="/home/my/MySuperApps/MyCV-Platform/direct/app/api-hybrid-detection-jetson"

# Function to get dynamic API host from config
get_api_host() {
    local config_file="$API_DIR/rvm_config.env"
    if [ -f "$config_file" ]; then
        # Source the config file to get API_HOST
        source "$config_file"
        echo "${API_HOST:-100.117.234.2}"
    else
        echo "100.117.234.2"  # Default fallback
    fi
}

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

# Function to check if API service is running
api_running() {
    if [ -f "/tmp/mycv-edge-api.pid" ]; then
        local pid=$(cat "/tmp/mycv-edge-api.pid")
        if ps -p "$pid" > /dev/null 2>&1; then
            # Check if it's actually the API service (port 5000)
            if ss -tlnp 2>/dev/null | grep ":5000" | grep -q "pid=$pid"; then
                return 0
            else
                rm -f "/tmp/mycv-edge-api.pid"
            fi
        else
            rm -f "/tmp/mycv-edge-api.pid"
        fi
    fi
    
    # Check for process listening on port 5000
    local api_pid=$(ss -tlnp 2>/dev/null | grep ":5000" | grep -o 'pid=[0-9]*' | cut -d'=' -f2)
    if [ -n "$api_pid" ] && ps -p "$api_pid" > /dev/null 2>&1; then
        echo "$api_pid" > "/tmp/mycv-edge-api.pid"
        return 0
    fi
    
    return 1
}

# Function to check if Web service is running
web_running() {
    if [ -f "/tmp/mycv_web.pid" ]; then
        local pid=$(cat "/tmp/mycv_web.pid")
        if ps -p "$pid" > /dev/null 2>&1; then
            # Check if it's actually the Web service (port 5002)
            if ss -tlnp 2>/dev/null | grep ":5002" | grep -q "pid=$pid"; then
                return 0
            else
                rm -f "/tmp/mycv_web.pid"
            fi
        else
            rm -f "/tmp/mycv_web.pid"
        fi
    fi
    
    # Check for process listening on port 5002
    local web_pid=$(ss -tlnp 2>/dev/null | grep ":5002" | grep -o 'pid=[0-9]*' | cut -d'=' -f2)
    if [ -n "$web_pid" ] && ps -p "$web_pid" > /dev/null 2>&1; then
        echo "$web_pid" > "/tmp/mycv_web.pid"
        return 0
    fi
    
    return 1
}

# Function to stop all processes on specific ports
stop_all_ports() {
    print_status "🛑 Stopping all processes on ports 5000, 5001, 5002..."
    
    # Stop processes on port 5000
    local port5000_pid=$(ss -tlnp 2>/dev/null | grep ":5000" | grep -o 'pid=[0-9]*' | cut -d'=' -f2)
    if [ -n "$port5000_pid" ]; then
        print_status "Stopping process on port 5000 (PID: $port5000_pid)..."
        kill "$port5000_pid" 2>/dev/null || true
        sleep 1
    fi
    
    # Stop processes on port 5001
    local port5001_pid=$(ss -tlnp 2>/dev/null | grep ":5001" | grep -o 'pid=[0-9]*' | cut -d'=' -f2)
    if [ -n "$port5001_pid" ]; then
        print_status "Stopping process on port 5001 (PID: $port5001_pid)..."
        kill "$port5001_pid" 2>/dev/null || true
        sleep 1
    fi
    
    # Stop processes on port 5002
    local port5002_pid=$(ss -tlnp 2>/dev/null | grep ":5002" | grep -o 'pid=[0-9]*' | cut -d'=' -f2)
    if [ -n "$port5002_pid" ]; then
        print_status "Stopping process on port 5002 (PID: $port5002_pid)..."
        kill "$port5002_pid" 2>/dev/null || true
        sleep 1
    fi
    
    # Clean up PID files
    rm -f /tmp/mycv-edge-api.pid /tmp/mycv_web.pid
    
    print_success "✅ All processes on ports 5000, 5001, 5002 stopped"
    echo ""
}

# Function to start all services
start_all() {
    print_status "🚀 Starting MyCV-Edge-API Combined Services..."
    echo ""
    
    # Stop all processes on target ports first
    stop_all_ports
    
    # Start API Service
    print_status "📡 Starting Combined API Service..."
    if $API_SERVICE start; then
        print_success "✅ Combined API Service started"
    else
        print_error "❌ Failed to start Combined API Service"
        return 1
    fi
    
    echo ""
    
    # Start Web Service
    print_status "🌐 Starting Combined Web Service..."
    if $WEB_SERVICE start; then
        print_success "✅ Combined Web Service started"
    else
        print_error "❌ Failed to start Combined Web Service"
        return 1
    fi
    
    echo ""
    print_success "🎉 All Combined services started successfully!"
    local api_host=$(get_api_host)
    print_status "📡 API URL: http://$api_host:5000"
    print_status "🌐 Web URL: http://$api_host:5002"
}

# Function to stop all services
stop_all() {
    print_status "🛑 Stopping MyCV-Edge-API Combined Services..."
    echo ""
    
    # Stop all processes on target ports
    stop_all_ports
    
    print_success "✅ All Combined services stopped"
}

# Function to restart all services
restart_all() {
    print_status "🔄 Restarting MyCV-Edge-API Combined Services..."
    stop_all
    sleep 3
    start_all
}

# Function to show status of all services
status_all() {
    print_status "📊 MyCV-Edge-API Combined Services Status"
    echo "=============================================="
    echo ""
    
    # API Service Status
    print_status "📡 API Service:"
    if api_running; then
        local api_pid=$(cat "/tmp/mycv-edge-api.pid" 2>/dev/null || echo "unknown")
        print_success "  ✅ RUNNING (PID: $api_pid)"
        local api_host=$(get_api_host)
        print_status "  📡 URL: http://$api_host:5000"
        
        # Test API endpoint
        if curl -s http://$api_host:5000/api/health > /dev/null 2>&1; then
            print_success "  ✅ API endpoint responding"
        else
            print_warning "  ⚠️ API endpoint not responding"
        fi
    else
        print_error "  ❌ NOT RUNNING"
    fi
    
    echo ""
    
    # Web Service Status
    print_status "🌐 Web Service:"
    if web_running; then
        local web_pid=$(cat "/tmp/mycv_web.pid" 2>/dev/null || echo "unknown")
        print_success "  ✅ RUNNING (PID: $web_pid)"
        local api_host=$(get_api_host)
        print_status "  🌐 URL: http://$api_host:5002"
        
        # Test Web endpoint
        if curl -s http://$api_host:5002/health > /dev/null 2>&1; then
            print_success "  ✅ Web endpoint responding"
        else
            print_warning "  ⚠️ Web endpoint not responding"
        fi
    else
        print_error "  ❌ NOT RUNNING"
    fi
    
    echo ""
}

# Function to show logs
show_logs() {
    print_status "📄 MyCV-Edge-API Combined Services Logs"
    echo "============================================="
    echo ""
    
    # API Logs
    print_status "📡 API Service Logs:"
    echo "----------------------"
    if [ -f "/tmp/mycv-edge-api.log" ]; then
        tail -20 "/tmp/mycv-edge-api.log"
    else
        print_warning "No API logs found"
    fi
    
    echo ""
    
    # Web Logs
    print_status "🌐 Web Service Logs:"
    echo "----------------------"
    if [ -f "/tmp/mycv_web.log" ]; then
        tail -20 "/tmp/mycv_web.log"
    else
        print_warning "No Web logs found"
    fi
}

# Function to setup auto-start
setup_autostart() {
    print_status "🔧 Setting up auto-start for all services..."
    
    # Add API auto-start to crontab
    if ! crontab -l 2>/dev/null | grep -q "auto_start_api.sh"; then
        (crontab -l 2>/dev/null; echo "@reboot $SERVICES_DIR/auto_start_api.sh") | crontab -
        print_success "✅ API auto-start added to crontab"
    else
        print_warning "⚠️ API auto-start already exists in crontab"
    fi
    
    # Add Web auto-start to crontab
    if ! crontab -l 2>/dev/null | grep -q "auto_start_web.sh"; then
        (crontab -l 2>/dev/null; echo "@reboot $SERVICES_DIR/auto_start_web.sh") | crontab -
        print_success "✅ Web auto-start added to crontab"
    else
        print_warning "⚠️ Web auto-start already exists in crontab"
    fi
    
    echo ""
    print_success "🎉 Auto-start setup completed!"
    print_status "Both services will start automatically on boot"
}

# Function to remove auto-start
remove_autostart() {
    print_status "🗑️ Removing auto-start for all services..."
    
    # Remove API auto-start from crontab
    crontab -l 2>/dev/null | grep -v "auto_start_api.sh" | crontab -
    print_success "✅ API auto-start removed from crontab"
    
    # Remove Web auto-start from crontab
    crontab -l 2>/dev/null | grep -v "auto_start_web.sh" | crontab -
    print_success "✅ Web auto-start removed from crontab"
    
    echo ""
    print_success "🎉 Auto-start removed!"
}

# Main function
main() {
    case "${1:-}" in
        start)
            start_all
            ;;
        stop)
            stop_all
            ;;
        restart)
            restart_all
            ;;
        status)
            status_all
            ;;
        logs)
            show_logs
            ;;
        setup-autostart)
            setup_autostart
            ;;
        remove-autostart)
            remove_autostart
            ;;
        *)
            echo "MyCV-Edge-API Combined Service Manager"
            echo "====================================="
            echo ""
            echo "Usage: $0 {start|stop|restart|status|logs|setup-autostart|remove-autostart}"
            echo ""
            echo "Commands:"
            echo "  start            - Start all services (API + Web)"
            echo "  stop             - Stop all services"
            echo "  restart          - Restart all services"
            echo "  status           - Show status of all services"
            echo "  logs             - Show logs of all services"
            echo "  setup-autostart  - Setup auto-start on boot"
            echo "  remove-autostart - Remove auto-start"
            echo ""
            echo "Examples:"
            echo "  $0 start            # Start all services"
            echo "  $0 status           # Check status"
            echo "  $0 logs             # View logs"
            echo "  $0 stop             # Stop all services"
            echo "  $0 setup-autostart  # Setup auto-start"
            echo ""
            echo "Individual Service Management:"
            echo "  $API_SERVICE {start|stop|restart|status|logs}"
            echo "  $WEB_SERVICE {start|stop|restart|status|logs}"
            ;;
    esac
}

# Run main function
main "$@"
