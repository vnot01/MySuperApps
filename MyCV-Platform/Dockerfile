# MyCV-Platform Dockerfile
# Computer Vision Processing Service

FROM nvidia/cuda:11.8-devel-ubuntu22.04

# Set environment variables
ENV DEBIAN_FRONTEND=noninteractive
ENV PYTHONUNBUFFERED=1
ENV PYTHONDONTWRITEBYTECODE=1
ENV CUDA_VISIBLE_DEVICES=1

# Install system dependencies
RUN apt-get update && apt-get install -y \
    python3.11 \
    python3.11-dev \
    python3-pip \
    python3.11-venv \
    curl \
    wget \
    git \
    build-essential \
    libgl1-mesa-glx \
    libglib2.0-0 \
    libsm6 \
    libxext6 \
    libxrender-dev \
    libgomp1 \
    libgcc-s1 \
    && rm -rf /var/lib/apt/lists/*

# Create symbolic link for python
RUN ln -s /usr/bin/python3.11 /usr/bin/python

# Set working directory
WORKDIR /app

# Create virtual environment
RUN python3.11 -m venv /app/venv

# Activate virtual environment and upgrade pip
RUN /app/venv/bin/pip install --no-cache-dir --upgrade pip

# Copy requirements first for better caching
COPY requirements.txt .

# Install Python dependencies in virtual environment
RUN /app/venv/bin/pip install --no-cache-dir -r requirements.txt

# Install PyTorch with CUDA support in virtual environment
RUN /app/venv/bin/pip install --no-cache-dir torch torchvision torchaudio --index-url https://download.pytorch.org/whl/cu118

# Install ultralytics in virtual environment
RUN /app/venv/bin/pip install --no-cache-dir ultralytics

# Create environment detection script
RUN echo '#!/bin/bash\n\
# Environment Detection Script\n\
echo "🔍 MyCV-Platform Environment Detection"\n\
echo "====================================="\n\
\n\
# Check virtual environment\n\
if [[ "$VIRTUAL_ENV" != "" ]]; then\n\
    echo "✅ Running in virtual environment: $VIRTUAL_ENV"\n\
else\n\
    echo "⚠️  Not running in virtual environment"\n\
fi\n\
\n\
# Check Python version\n\
echo "🐍 Python Version:"\n\
/app/venv/bin/python --version\n\
\n\
# Check PyTorch and CUDA\n\
echo "🔥 PyTorch & CUDA Status:"\n\
/app/venv/bin/python -c "\n\
import torch\n\
import sys\n\
from termcolor import colored\n\
\n\
def log_message(message, level):\n\
    if level == \"info\":\n\
        print(colored(\"INFO: \" + message, \"blue\"))\n\
    elif level == \"warning\":\n\
        print(colored(\"WARNING: \" + message, \"yellow\"))\n\
    elif level == \"error\":\n\
        print(colored(\"ERROR: \" + message, \"red\"))\n\
    elif level == \"success\":\n\
        print(colored(\"SUCCESS: \" + message, \"green\"))\n\
\n\
try:\n\
    if torch.cuda.is_available():\n\
        log_message(f\"✅ PyTorch CUDA available - {torch.cuda.get_device_name(0)}\", \"success\")\n\
        log_message(f\"   CUDA Version: {torch.version.cuda}\", \"info\")\n\
        log_message(f\"   GPU Count: {torch.cuda.device_count()}\", \"info\")\n\
        log_message(f\"   GPU Memory: {torch.cuda.get_device_properties(0).total_memory / 1024**3:.1f} GB\", \"info\")\n\
        log_message(\"🚀 GPU MODE: Ready for GPU acceleration\", \"success\")\n\
    else:\n\
        log_message(\"⚠️  PyTorch CUDA not available - will use CPU mode\", \"warning\")\n\
        log_message(f\"   PyTorch Version: {torch.__version__}\", \"info\")\n\
        log_message(f\"   CPU Threads: {torch.get_num_threads()}\", \"info\")\n\
        log_message(\"💻 CPU MODE: Using CPU for inference\", \"warning\")\n\
except Exception as e:\n\
    log_message(f\"❌ Error checking PyTorch: {e}\", \"error\")\n\
"\n\
\n\
# Test with mock data\n\
echo "🧪 Testing with Mock Data:"\n\
/app/venv/bin/python -c "\n\
import torch\n\
import numpy as np\n\
import sys\n\
import os\n\
from termcolor import colored\n\
\n\
def log_message(message, level):\n\
    if level == \"info\":\n\
        print(colored(\"INFO: \" + message, \"blue\"))\n\
    elif level == \"warning\":\n\
        print(colored(\"WARNING: \" + message, \"yellow\"))\n\
    elif level == \"error\":\n\
        print(colored(\"ERROR: \" + message, \"red\"))\n\
    elif level == \"success\":\n\
        print(colored(\"SUCCESS: \" + message, \"green\"))\n\
\n\
try:\n\
    # Test PyTorch with mock data\n\
    log_message(\"🧪 Testing PyTorch with mock data...\", \"info\")\n\
    mock_tensor = torch.randn(1, 3, 224, 224)\n\
    if torch.cuda.is_available():\n\
        mock_tensor = mock_tensor.cuda()\n\
        log_message(\"✅ Mock tensor created on GPU\", \"success\")\n\
    else:\n\
        log_message(\"✅ Mock tensor created on CPU\", \"success\")\n\
    \n\
    # Test basic operations\n\
    result = torch.nn.functional.relu(mock_tensor)\n\
    log_message(\"✅ Basic tensor operations successful\", \"success\")\n\
    \n\
    # Test with random image data\n\
    log_message(\"🧪 Testing with mock image data...\", \"info\")\n\
    mock_image = np.random.randint(0, 255, (640, 640, 3), dtype=np.uint8)\n\
    mock_tensor = torch.from_numpy(mock_image).permute(2, 0, 1).float().unsqueeze(0)\n\
    if torch.cuda.is_available():\n\
        mock_tensor = mock_tensor.cuda()\n\
    \n\
    log_message(\"✅ Mock image processing successful\", \"success\")\n\
    log_message(f\"   Image shape: {mock_tensor.shape}\", \"info\")\n\
    log_message(f\"   Device: {mock_tensor.device}\", \"info\")\n\
    log_message(\"🧪 MOCK DATA MODE: All tests passed\", \"success\")\n\
    \n\
except Exception as e:\n\
    log_message(f\"❌ Mock data test failed: {e}\", \"error\")\n\
    sys.exit(1)\n\
"\n\
\n\
echo "====================================="\n\
echo "🎉 Environment detection completed!"\n\
' > /app/detect_environment.sh && chmod +x /app/detect_environment.sh

# Copy application code
COPY . .

# Create necessary directories
RUN mkdir -p /app/data/models/yolo/active \
    /app/data/models/yolo/downloads \
    /app/data/models/sam/active \
    /app/data/models/sam/downloads \
    /app/data/input/test_images \
    /app/data/output/detections \
    /app/data/output/segmentations \
    /app/data/output/visualizations \
    /app/logs

# Set permissions
RUN chmod +x /app/scripts/*.sh

# Expose port
EXPOSE 8000

# Health check
HEALTHCHECK --interval=30s --timeout=10s --start-period=40s --retries=3 \
    CMD curl -f http://localhost:8000/api/v1/health || exit 1

# Set environment variables for virtual environment
ENV VIRTUAL_ENV=/app/venv
ENV PATH="$VIRTUAL_ENV/bin:$PATH"

# Create startup script
RUN echo '#!/bin/bash\n\
echo "🚀 MyCV-Platform Starting..."\n\
echo "============================="\n\
\n\
# Run environment detection\n\
/app/scripts/startup_environment_check.sh\n\
\n\
echo ""\n\
echo "🌐 Starting FastAPI server..."\n\
echo "============================="\n\
\n\
# Start the application\n\
exec /app/venv/bin/uvicorn app.main:app --host 0.0.0.0 --port 8000 --reload\n\
' > /app/start.sh && chmod +x /app/start.sh

# Start command using startup script
CMD ["/app/start.sh"]
