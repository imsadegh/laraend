#!/bin/bash

###############################################################################
# Laraend Upload Script
#
# This script uploads the Laravel application from your local Mac to the VPS.
# Run this script from your local development machine.
#
# Usage: ./upload-to-server.sh
###############################################################################

set -e  # Exit on any error

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Configuration
LOCAL_DIR="/Users/sadeghmbp/Downloads/myDocuments/_develop/_fs_dev/hakimyarFusion/laraend"
REMOTE_USER="deploy"
REMOTE_HOST="172.20.10.6"
REMOTE_PORT=2222
REMOTE_DIR="/var/www/laraend"

# Functions
print_success() {
    echo -e "${GREEN}✓ $1${NC}"
}

print_error() {
    echo -e "${RED}✗ $1${NC}"
}

print_info() {
    echo -e "${YELLOW}→ $1${NC}"
}

# Check if in correct directory
if [ ! -f "$LOCAL_DIR/artisan" ]; then
    print_error "Laravel application not found in $LOCAL_DIR"
    exit 1
fi

print_info "Starting upload process..."
echo ""

# Step 1: Test SSH connection
print_info "Testing SSH connection..."
if ssh -p "$REMOTE_PORT" -o ConnectTimeout=5 "$REMOTE_USER@$REMOTE_HOST" "echo 'Connection successful'" >/dev/null 2>&1; then
    print_success "SSH connection successful"
else
    print_error "Cannot connect to $REMOTE_USER@$REMOTE_HOST on port $REMOTE_PORT"
    print_info "Please check your SSH credentials, port, and VPS accessibility"
    exit 1
fi
echo ""

# Step 2: Confirm upload
echo "This will upload files from:"
echo "  Local:  $LOCAL_DIR"
echo "  Remote: $REMOTE_USER@$REMOTE_HOST:$REMOTE_DIR (port $REMOTE_PORT)"
echo ""
read -p "Continue? (y/N): " -n 1 -r
echo ""
if [[ ! $REPLY =~ ^[Yy]$ ]]; then
    print_info "Upload cancelled"
    exit 0
fi
echo ""

# Step 3: Upload files using rsync
print_info "Uploading files to server..."
rsync -avz --progress \
    --exclude='vendor/' \
    --exclude='node_modules/' \
    --exclude='.env' \
    --exclude='.env.*' \
    --exclude='storage/logs/*' \
    --exclude='storage/framework/cache/*' \
    --exclude='storage/framework/sessions/*' \
    --exclude='storage/framework/views/*' \
    --exclude='.git/' \
    --exclude='.gitignore' \
    --exclude='.cursorignore' \
    --exclude='database/database.sqlite' \
    --exclude='*.md' \
    --exclude='.DS_Store' \
    --exclude='.vscode' \
    --exclude='tests/' \
    --exclude='phpunit.xml' \
    --exclude='deploy.sh' \
    --exclude='upload-to-server.sh' \
    -e "ssh -p $REMOTE_PORT" \
    "$LOCAL_DIR/" "$REMOTE_USER@$REMOTE_HOST:$REMOTE_DIR/"

print_success "Files uploaded successfully"
echo ""

# Step 4: Ask if user wants to run deployment script
read -p "Run deployment script on server? (y/N): " -n 1 -r
echo ""
if [[ $REPLY =~ ^[Yy]$ ]]; then
    print_info "Running deployment script on server..."
    echo ""
    ssh -p "$REMOTE_PORT" -t "$REMOTE_USER@$REMOTE_HOST" "cd $REMOTE_DIR && bash deploy.sh"
else
    print_info "Deployment script not executed"
    echo ""
    echo "To deploy manually, run:"
    echo "  ssh -p $REMOTE_PORT $REMOTE_USER@$REMOTE_HOST"
    echo "  cd $REMOTE_DIR"
    echo "  bash deploy.sh"
fi

echo ""
print_success "Upload process completed!"
echo ""

