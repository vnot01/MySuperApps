#!/bin/bash

# Camera Control Script for Jetson Orin
# Provides shell interface for camera management

# Configuration
API_BASE_URL="http://localhost:5000"
CAMERA_API_BASE="${API_BASE_URL}/api/cameras"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Helper functions
log_info() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

log_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

log_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

log_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Check if API is running
check_api() {
    if ! curl -s "${API_BASE_URL}/api/health" > /dev/null 2>&1; then
        log_error "API server is not running at ${API_BASE_URL}"
        return 1
    fi
    return 0
}

# List all cameras
list_cameras() {
    log_info "Listing available cameras..."
    if ! check_api; then
        return 1
    fi
    
    response=$(curl -s "${CAMERA_API_BASE}")
    if [ $? -eq 0 ]; then
        echo "$response" | python3 -m json.tool
    else
        log_error "Failed to get camera list"
        return 1
    fi
}

# Get camera info
get_camera_info() {
    local camera_id=$1
    if [ -z "$camera_id" ]; then
        log_error "Camera ID is required"
        return 1
    fi
    
    log_info "Getting info for camera $camera_id..."
    if ! check_api; then
        return 1
    fi
    
    response=$(curl -s "${CAMERA_API_BASE}/${camera_id}/info")
    if [ $? -eq 0 ]; then
        echo "$response" | python3 -m json.tool
    else
        log_error "Failed to get camera info"
        return 1
    fi
}

# Start camera
start_camera() {
    local camera_id=$1
    if [ -z "$camera_id" ]; then
        log_error "Camera ID is required"
        return 1
    fi
    
    log_info "Starting camera $camera_id..."
    if ! check_api; then
        return 1
    fi
    
    response=$(curl -s -X POST "${CAMERA_API_BASE}/${camera_id}/start")
    if [ $? -eq 0 ]; then
        echo "$response" | python3 -m json.tool
    else
        log_error "Failed to start camera"
        return 1
    fi
}

# Stop camera
stop_camera() {
    local camera_id=$1
    if [ -z "$camera_id" ]; then
        log_error "Camera ID is required"
        return 1
    fi
    
    log_info "Stopping camera $camera_id..."
    if ! check_api; then
        return 1
    fi
    
    response=$(curl -s -X POST "${CAMERA_API_BASE}/${camera_id}/stop")
    if [ $? -eq 0 ]; then
        echo "$response" | python3 -m json.tool
    else
        log_error "Failed to stop camera"
        return 1
    fi
}

# Restart camera
restart_camera() {
    local camera_id=$1
    if [ -z "$camera_id" ]; then
        log_error "Camera ID is required"
        return 1
    fi
    
    log_info "Restarting camera $camera_id..."
    if ! check_api; then
        return 1
    fi
    
    response=$(curl -s -X POST "${CAMERA_API_BASE}/${camera_id}/restart")
    if [ $? -eq 0 ]; then
        echo "$response" | python3 -m json.tool
    else
        log_error "Failed to restart camera"
        return 1
    fi
}

# Capture image
capture_image() {
    local camera_id=$1
    local save_path=$2
    if [ -z "$camera_id" ]; then
        log_error "Camera ID is required"
        return 1
    fi
    
    log_info "Capturing image from camera $camera_id..."
    if ! check_api; then
        return 1
    fi
    
    if [ -n "$save_path" ]; then
        # Save to file
        response=$(curl -s -X POST "${CAMERA_API_BASE}/${camera_id}/capture" \
            -H "Content-Type: application/json" \
            -d "{\"save_path\": \"$save_path\"}")
    else
        # Return base64
        response=$(curl -s -X POST "${CAMERA_API_BASE}/${camera_id}/capture/base64")
    fi
    
    if [ $? -eq 0 ]; then
        echo "$response" | python3 -m json.tool
    else
        log_error "Failed to capture image"
        return 1
    fi
}

# Get camera status
get_camera_status() {
    local camera_id=$1
    if [ -z "$camera_id" ]; then
        log_error "Camera ID is required"
        return 1
    fi
    
    log_info "Getting status for camera $camera_id..."
    if ! check_api; then
        return 1
    fi
    
    response=$(curl -s "${CAMERA_API_BASE}/${camera_id}/status")
    if [ $? -eq 0 ]; then
        echo "$response" | python3 -m json.tool
    else
        log_error "Failed to get camera status"
        return 1
    fi
}

# Get all cameras status
get_all_status() {
    log_info "Getting status for all cameras..."
    if ! check_api; then
        return 1
    fi
    
    response=$(curl -s "${CAMERA_API_BASE}/status")
    if [ $? -eq 0 ]; then
        echo "$response" | python3 -m json.tool
    else
        log_error "Failed to get cameras status"
        return 1
    fi
}

# Start streaming
start_streaming() {
    local camera_id=$1
    if [ -z "$camera_id" ]; then
        log_error "Camera ID is required"
        return 1
    fi
    
    log_info "Starting streaming for camera $camera_id..."
    if ! check_api; then
        return 1
    fi
    
    response=$(curl -s -X POST "${CAMERA_API_BASE}/${camera_id}/stream/start")
    if [ $? -eq 0 ]; then
        echo "$response" | python3 -m json.tool
    else
        log_error "Failed to start streaming"
        return 1
    fi
}

# Stop streaming
stop_streaming() {
    local camera_id=$1
    if [ -z "$camera_id" ]; then
        log_error "Camera ID is required"
        return 1
    fi
    
    log_info "Stopping streaming for camera $camera_id..."
    if ! check_api; then
        return 1
    fi
    
    response=$(curl -s -X POST "${CAMERA_API_BASE}/${camera_id}/stream/stop")
    if [ $? -eq 0 ]; then
        echo "$response" | python3 -m json.tool
    else
        log_error "Failed to stop streaming"
        return 1
    fi
}

# Test camera
test_camera() {
    local camera_id=$1
    if [ -z "$camera_id" ]; then
        log_error "Camera ID is required"
        return 1
    fi
    
    log_info "Testing camera $camera_id..."
    
    # Start camera
    if ! start_camera "$camera_id" > /dev/null 2>&1; then
        log_error "Failed to start camera for testing"
        return 1
    fi
    
    # Capture test image
    if capture_image "$camera_id" "/tmp/test_camera_${camera_id}.jpg" > /dev/null 2>&1; then
        log_success "Camera $camera_id test passed"
        rm -f "/tmp/test_camera_${camera_id}.jpg"
    else
        log_error "Camera $camera_id test failed"
        return 1
    fi
    
    # Stop camera
    stop_camera "$camera_id" > /dev/null 2>&1
}

# Show help
show_help() {
    echo "Camera Control Script for Jetson Orin"
    echo ""
    echo "Usage: $0 <command> [options]"
    echo ""
    echo "Commands:"
    echo "  list                    List all available cameras"
    echo "  info <camera_id>        Get camera information"
    echo "  start <camera_id>       Start camera"
    echo "  stop <camera_id>        Stop camera"
    echo "  restart <camera_id>     Restart camera"
    echo "  capture <camera_id> [path]  Capture image (optional save path)"
    echo "  status <camera_id>      Get camera status"
    echo "  all-status              Get status of all cameras"
    echo "  stream-start <camera_id>  Start camera streaming"
    echo "  stream-stop <camera_id>   Stop camera streaming"
    echo "  test <camera_id>        Test camera functionality"
    echo "  help                    Show this help"
    echo ""
    echo "Examples:"
    echo "  $0 list"
    echo "  $0 start 0"
    echo "  $0 capture 0 /tmp/image.jpg"
    echo "  $0 test 0"
}

# Main script logic
case "$1" in
    "list")
        list_cameras
        ;;
    "info")
        get_camera_info "$2"
        ;;
    "start")
        start_camera "$2"
        ;;
    "stop")
        stop_camera "$2"
        ;;
    "restart")
        restart_camera "$2"
        ;;
    "capture")
        capture_image "$2" "$3"
        ;;
    "status")
        get_camera_status "$2"
        ;;
    "all-status")
        get_all_status
        ;;
    "stream-start")
        start_streaming "$2"
        ;;
    "stream-stop")
        stop_streaming "$2"
        ;;
    "test")
        test_camera "$2"
        ;;
    "help"|"-h"|"--help")
        show_help
        ;;
    *)
        log_error "Unknown command: $1"
        show_help
        exit 1
        ;;
esac
