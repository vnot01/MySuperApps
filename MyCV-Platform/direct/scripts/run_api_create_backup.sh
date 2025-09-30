#!/bin/bash

# MyCV-Platform Session Backup Script
# Creates TAR.GZ backup for a specific session directory under ./data/output/remote/<timestamp>/<user_id>

set -e

echo "📦 MyCV-Platform Session Backup"
echo "================================="

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

# Determine target session directory and write backup into it
BASE_DIR="data/output/remote"

# Optional args: timestamp and user_id
ARG_TIMESTAMP="$1"
ARG_USER_ID="$2"

if [ -n "$ARG_TIMESTAMP" ] && [ -n "$ARG_USER_ID" ]; then
    TIMESTAMP="$ARG_TIMESTAMP"
    USER_ID="$ARG_USER_ID"
else
    # Auto-detect latest session
    if [ ! -d "$BASE_DIR" ]; then
        print_error "Output base directory not found: $BASE_DIR"
        exit 1
    fi
    TIMESTAMP=$(find "$BASE_DIR" -mindepth 1 -maxdepth 1 -type d -printf '%f\n' 2>/dev/null | sort | tail -1)
    if [ -z "$TIMESTAMP" ]; then
        print_error "No timestamp directory found in $BASE_DIR"
        exit 1
    fi
    USER_ID=$(find "$BASE_DIR/$TIMESTAMP" -mindepth 1 -maxdepth 1 -type d -printf '%f\n' 2>/dev/null | sort | tail -1)
    if [ -z "$USER_ID" ]; then
        print_error "No user_id directory found in $BASE_DIR/$TIMESTAMP"
        exit 1
    fi
fi

SESSION_DIR="$BASE_DIR/$TIMESTAMP/$USER_ID"
if [ ! -d "$SESSION_DIR" ]; then
    print_error "Session directory not found: $SESSION_DIR"
    exit 1
fi

print_status "Creating session backup for: $SESSION_DIR"

BACKUP_NAME="session_backup_${TIMESTAMP}_${USER_ID}"
TAR_FILE="$SESSION_DIR/${BACKUP_NAME}.tar.gz"
TAR_TMP="/tmp/${BACKUP_NAME}.tar.gz"

# Create tar from contents of session dir (exclude any existing backups)
print_status "Creating TAR.GZ backup at: $TAR_FILE"

# Write archive to tmp to avoid 'file changed as we read it' and then move it
tar -C "$SESSION_DIR" --exclude='*.tar.gz' -czf "$TAR_TMP" .
mv -f "$TAR_TMP" "$TAR_FILE"

if [ $? -eq 0 ]; then
    TAR_SIZE=$(du -sh "$TAR_FILE" | cut -f1)
    print_success "✅ TAR.GZ backup created: $TAR_FILE ($TAR_SIZE)"
    echo ""
    print_status "Download via API (replace <session_id>): /api/download/<session_id>/${BACKUP_NAME}.tar.gz"
else
    print_error "❌ Failed to create TAR.GZ backup"
    exit 1
fi
