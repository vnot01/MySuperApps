#!/bin/bash

# MyCV-Platform Combined Services Launcher
# Root level launcher untuk combined services

cd "$(dirname "$0")"
./services/combined_service.sh "$@"
