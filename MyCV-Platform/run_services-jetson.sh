#!/bin/bash

# MyCV-Platform Combined Services Launcher (Jetson)
# Root level launcher untuk combined services pada Jetson

cd "$(dirname "$0")"
./services-jetson/combined_service.sh "$@"
