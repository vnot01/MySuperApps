# MyCV-Platform Docker Testing Guide

## Overview

This guide explains how to run MyCV-Platform Fresh Integration Test inside Docker containers using `docker exec` commands.

## Docker Setup

### Files Created

1. **`Dockerfile.test`** - Simplified Dockerfile for testing (CPU-only)
2. **`docker-compose.cpu.yml`** - Docker Compose configuration for CPU testing
3. **`scripts/run_docker_test.sh`** - Automated Docker test script
4. **`scripts/docker_test_commands.sh`** - Manual Docker test commands

### Key Features

- **Base Image**: Ubuntu 22.04
- **Python**: 3.11 with virtual environment (`/app/venv`)
- **PyTorch**: 2.8.0+cu128 (CPU mode)
- **Ultralytics**: Installed and working
- **Container**: `myc-v-platform-test`

## Quick Start

### 1. Build and Start Container

```bash
# Build Docker image
docker-compose -f docker-compose.cpu.yml build

# Start container
docker-compose -f docker-compose.cpu.yml up -d

# Check container status
docker ps | grep myc-v-platform-test
```

### 2. Run Fresh Integration Test

```bash
# Download models first
docker exec myc-v-platform-test /app/venv/bin/python -c "
from ultralytics import YOLO, SAM
import os
import requests
from tqdm import tqdm

# Download YOLO11m
yolo11m = YOLO('yolo11m.pt')
os.makedirs('/app/data/models/yolo/active', exist_ok=True)
yolo11m.save('/app/data/models/yolo/active/yolo11m.pt')

# Download SAM2_b
sam2_b = SAM('sam2_b.pt')
os.makedirs('/app/data/models/sam/active', exist_ok=True)
if os.path.exists('sam2_b.pt'):
    import shutil
    shutil.copy('sam2_b.pt', '/app/data/models/sam/active/sam2_b.pt')

# Download best.pt
def download_file(url, filename):
    response = requests.get(url, stream=True)
    total_size = int(response.headers.get('content-length', 0))
    with open(filename, 'wb') as file, tqdm(desc=filename, total=total_size, unit='iB', unit_scale=True, unit_divisor=1024) as progress_bar:
        for chunk in response.iter_content(chunk_size=8192):
            size = file.write(chunk)
            progress_bar.update(size)

os.makedirs('/app/data/models/trained', exist_ok=True)
download_file('https://github.com/vnot01/MySuperApps/releases/download/trained-models/best.pt', '/app/data/models/trained/best.pt')
"

# Run integration test
docker exec myc-v-platform-test /app/venv/bin/python /app/run_yolo_sam_integration.py

# Generate visualizations
docker exec myc-v-platform-test /app/venv/bin/python /app/visualize_results.py
```

## Manual Commands

### Environment Detection

```bash
# Check virtual environment
docker exec myc-v-platform-test bash -c 'echo "Virtual Env: $VIRTUAL_ENV"; which python; python --version'

# Check PyTorch and CUDA
docker exec myc-v-platform-test /app/venv/bin/python -c "
import torch
print('PyTorch Version:', torch.__version__)
print('CUDA Available:', torch.cuda.is_available())
print('CPU Threads:', torch.get_num_threads())
"
```

### Interactive Shell

```bash
# Access container shell
docker exec -it myc-v-platform-test bash

# Inside container, activate virtual environment
source /app/venv/bin/activate

# Run Python scripts
python /app/run_yolo_sam_integration.py
python /app/visualize_results.py
```

### Check Results

```bash
# List generated files
docker exec myc-v-platform-test find /app/data/output -name "*.png" -o -name "*.json"

# Check detection results
docker exec myc-v-platform-test find /app/data/output/integration_results -name "*_detections.json" -exec basename {} \;
```

## Test Results

### Successful Test Output

```
✅ Environment Detection: SUCCESS
✅ Model Download: SUCCESS  
✅ YOLO11m Detection: SUCCESS
✅ best.pt Detection: SUCCESS
✅ SAM2 Segmentation: SUCCESS
✅ Visualization: SUCCESS
```

### Generated Files

- **Detection JSONs**: 5 files
- **Segmentation Masks**: 5 files  
- **Visualizations**: 5 files
- **Total**: 15 output files

## Container Management

### Start Container

```bash
docker-compose -f docker-compose.cpu.yml up -d
```

### Stop Container

```bash
docker-compose -f docker-compose.cpu.yml down
```

### Remove Container

```bash
docker-compose -f docker-compose.cpu.yml down
docker container prune -f
```

### View Logs

```bash
docker logs myc-v-platform-test
```

## Troubleshooting

### Container Won't Start

```bash
# Check Docker status
docker info

# Clean up containers
docker container prune -f

# Rebuild image
docker-compose -f docker-compose.cpu.yml build --no-cache
```

### Models Not Found

```bash
# Check model directories
docker exec myc-v-platform-test ls -la /app/data/models/

# Re-download models
docker exec myc-v-platform-test /app/venv/bin/python -c "
from ultralytics import YOLO, SAM
# ... download commands ...
"
```

### Virtual Environment Issues

```bash
# Check virtual environment
docker exec myc-v-platform-test bash -c 'echo $VIRTUAL_ENV; which python'

# Activate manually
docker exec myc-v-platform-test bash -c 'source /app/venv/bin/activate && python --version'
```

## Notes

- **CPU Mode**: This setup runs in CPU mode for compatibility
- **Virtual Environment**: Always use `/app/venv/bin/python` for commands
- **Working Directory**: All operations happen in `/app`
- **Volume Mounts**: Host directories are mounted to container
- **No GPU**: GPU support requires NVIDIA Docker setup

## Success Criteria

✅ Container starts successfully  
✅ Virtual environment is active  
✅ PyTorch is installed and working  
✅ Models download successfully  
✅ Integration test runs without errors  
✅ Visualizations are generated  
✅ Results are saved to output directory  

---

**MyCV-Platform Fresh Integration Test works perfectly in Docker!** 🐳
