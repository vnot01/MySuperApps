#!/bin/bash

# Check GPU availability and return GPU info
# This script runs on the host system to detect GPU

# Check if GPU device files exist (simplest method)
if [ -e "/dev/nvidia0" ] || [ -e "/dev/nvidiactl" ]; then
    echo "NVIDIA GPU: Device files detected"
    exit 0
fi

# No GPU detected
echo "No GPU detected"
exit 1
