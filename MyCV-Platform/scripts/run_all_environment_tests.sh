#!/bin/bash

# MyCV-Platform Complete Environment Testing Script
# Runs all environment detection and testing

set -e

echo "🧪 MyCV-Platform Complete Environment Testing"
echo "============================================="

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

# Test results
TESTS_PASSED=0
TESTS_FAILED=0
TOTAL_TESTS=0

# Function to run test and track results
run_test() {
    local test_name="$1"
    local test_command="$2"
    
    TOTAL_TESTS=$((TOTAL_TESTS + 1))
    print_status "Running test: $test_name"
    
    if eval "$test_command"; then
        print_success "✅ $test_name: PASSED"
        TESTS_PASSED=$((TESTS_PASSED + 1))
    else
        print_error "❌ $test_name: FAILED"
        TESTS_FAILED=$((TESTS_FAILED + 1))
    fi
    
    echo ""
}

# Check if running in Docker
if [ -f /.dockerenv ]; then
    print_status "Running inside Docker container"
    PYTHON_CMD="/app/venv/bin/python"
    VENV_PATH="/app/venv"
    IS_DOCKER=true
else
    print_status "Running on host system"
    PYTHON_CMD="python3"
    VENV_PATH="venv"
    IS_DOCKER=false
fi

# Test 1: Virtual Environment Check
run_test "Virtual Environment Check" "
if [ -d '$VENV_PATH' ]; then
    if [ -f '$VENV_PATH/bin/activate' ]; then
        source '$VENV_PATH/bin/activate'
        if [[ '$VIRTUAL_ENV' != '' ]]; then
            echo 'Virtual environment is active: $VIRTUAL_ENV'
            exit 0
        else
            echo 'Virtual environment not active'
            exit 1
        fi
    else
        echo 'Virtual environment activation script not found'
        exit 1
    fi
else
    echo 'Virtual environment directory not found'
    exit 1
fi
"

# Test 2: Python Version Check
run_test "Python Version Check" "
$PYTHON_CMD --version
"

# Test 3: PyTorch Installation Check
run_test "PyTorch Installation Check" "
$PYTHON_CMD -c 'import torch; print(f\"PyTorch version: {torch.__version__}\")'
"

# Test 4: CUDA Availability Check
run_test "CUDA Availability Check" "
$PYTHON_CMD -c '
import torch
if torch.cuda.is_available():
    print(f\"CUDA available: {torch.cuda.get_device_name(0)}\")
    print(f\"CUDA version: {torch.version.cuda}\")
    print(f\"GPU count: {torch.cuda.device_count()}\")
    print(f\"GPU memory: {torch.cuda.get_device_properties(0).total_memory / 1024**3:.1f} GB\")
else:
    print(\"CUDA not available - using CPU mode\")
    print(f\"CPU threads: {torch.get_num_threads()}\")
'
"

# Test 5: Mock Data Tensor Operations
run_test "Mock Data Tensor Operations" "
$PYTHON_CMD -c '
import torch
import numpy as np

# Test basic tensor operations
mock_tensor = torch.randn(1, 3, 224, 224)
if torch.cuda.is_available():
    mock_tensor = mock_tensor.cuda()
    device = \"GPU\"
else:
    device = \"CPU\"

result = torch.nn.functional.relu(mock_tensor)
print(f\"Tensor operations successful on {device}\")
print(f\"Tensor shape: {mock_tensor.shape}\")
print(f\"Tensor device: {mock_tensor.device}\")
'
"

# Test 6: Mock Data Image Processing
run_test "Mock Data Image Processing" "
$PYTHON_CMD -c '
import torch
import numpy as np

# Test image processing
mock_image = np.random.randint(0, 255, (640, 640, 3), dtype=np.uint8)
mock_tensor = torch.from_numpy(mock_image).permute(2, 0, 1).float().unsqueeze(0)

if torch.cuda.is_available():
    mock_tensor = mock_tensor.cuda()
    device = \"GPU\"
else:
    device = \"CPU\"

print(f\"Image processing successful on {device}\")
print(f\"Image shape: {mock_tensor.shape}\")
print(f\"Image device: {mock_tensor.device}\")
'
"

# Test 7: Ultralytics Import Check
run_test "Ultralytics Import Check" "
$PYTHON_CMD -c '
try:
    from ultralytics import YOLO, SAM
    print(\"Ultralytics imported successfully\")
    print(f\"YOLO available: {YOLO is not None}\")
    print(f\"SAM available: {SAM is not None}\")
except ImportError as e:
    print(f\"Ultralytics import failed: {e}\")
    exit(1)
'
"

# Test 8: Model Files Check (if available)
run_test "Model Files Check" "
if [ -d 'data/models/yolo/active' ] && [ -d 'data/models/sam/active' ]; then
    yolo_models=\$(ls data/models/yolo/active/ 2>/dev/null | wc -l)
    sam_models=\$(ls data/models/sam/active/ 2>/dev/null | wc -l)
    echo \"YOLO models available: \$yolo_models\"
    echo \"SAM models available: \$sam_models\"
    
    if [ \$yolo_models -gt 0 ] || [ \$sam_models -gt 0 ]; then
        echo \"Models found\"
        exit 0
    else
        echo \"No models found\"
        exit 1
    fi
else
    echo \"Model directories not found\"
    exit 1
fi
"

# Test 9: Environment Detector Utility
run_test "Environment Detector Utility" "
if [ -f 'app/utils/environment_detector.py' ]; then
    $PYTHON_CMD app/utils/environment_detector.py
else
    echo \"Environment detector not found\"
    exit 1
fi
"

# Test 10: Docker-specific tests (if running in Docker)
if [ "$IS_DOCKER" = true ]; then
    run_test "Docker Container Check" "
    echo \"Running in Docker container\"
    echo \"Container ID: \$(hostname)\"
    echo \"Container OS: \$(cat /etc/os-release | grep PRETTY_NAME)\"
    "
    
    run_test "Docker GPU Access Check" "
    if command -v nvidia-smi &> /dev/null; then
        nvidia-smi --query-gpu=name,memory.total --format=csv,noheader
    else
        echo \"nvidia-smi not available in container\"
        exit 1
    fi
    "
fi

# Print final results
echo "============================================="
echo "🧪 Test Results Summary"
echo "============================================="
echo "Total Tests: $TOTAL_TESTS"
echo "Passed: $TESTS_PASSED"
echo "Failed: $TESTS_FAILED"

if [ $TESTS_FAILED -eq 0 ]; then
    print_success "🎉 All tests passed! MyCV-Platform is ready to use."
    exit 0
else
    print_error "❌ Some tests failed. Please check the output above."
    exit 1
fi
