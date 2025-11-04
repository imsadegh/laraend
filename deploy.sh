#!/bin/bash

###############################################################################
# Laraend Deployment Script
#
# This script automates the deployment process for Laraend Laravel backend.
# Run this script on the VPS server after uploading new code.
#
# Usage: ./deploy.sh
###############################################################################

set -e  # Exit on any error

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Configuration
APP_DIR="/var/www/laraend"
PHP_FPM_SERVICE="php8.4-fpm"
BACKUP_DIR="/home/deploy/backups/deployments"
DATE=$(date +"%Y%m%d_%H%M%S")

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

# Check if running as deploy user
if [ "$USER" != "deploy" ]; then
    print_error "This script must be run as 'deploy' user"
    exit 1
fi

# Check if in correct directory
if [ ! -f "$APP_DIR/artisan" ]; then
    print_error "Laravel application not found in $APP_DIR"
    exit 1
fi

# Ensure we're in the correct directory
cd "$APP_DIR"
print_info "Current directory: $(pwd)"
print_info "Starting deployment process..."
echo ""

# Step 1: Create backup
print_info "Creating backup..."
mkdir -p "$BACKUP_DIR"
BACKUP_FILE="$BACKUP_DIR/pre-deploy-${DATE}.tar.gz"
tar -czf "$BACKUP_FILE" \
    --exclude='vendor' \
    --exclude='node_modules' \
    --exclude='storage/framework/cache/*' \
    --exclude='storage/framework/sessions/*' \
    --exclude='storage/framework/views/*' \
    --exclude='storage/logs/*' \
    -C /var/www laraend 2>/dev/null || true
print_success "Backup created: $BACKUP_FILE"
echo ""

# Step 2: Install/Update dependencies first (before maintenance mode)
print_info "Installing Composer dependencies..."
cd "$APP_DIR"
composer install --no-dev --optimize-autoloader --no-interaction
print_success "Dependencies installed"
echo ""

# Step 3: Put application in maintenance mode
print_info "Enabling maintenance mode..."
php artisan down --render="errors::503" --retry=60 2>/dev/null || true
print_success "Maintenance mode enabled (or skipped if already down)"
echo ""

# Step 4: Pull latest changes (if using git)
if [ -d "$APP_DIR/.git" ]; then
    print_info "Pulling latest changes from git..."
    git pull origin main
    print_success "Code updated from git"
    echo ""
fi

# Step 5: Clear caches
print_info "Clearing application caches..."
cd "$APP_DIR"
php artisan config:clear || true
php artisan cache:clear || true
php artisan route:clear || true
php artisan view:clear || true
print_success "Caches cleared"
echo ""

# Step 6: Run migrations
print_info "Running database migrations..."
cd "$APP_DIR"
php artisan migrate --force
print_success "Migrations completed"
echo ""

# Step 7: Optimize autoloader
print_info "Optimizing autoloader..."
cd "$APP_DIR"
composer dump-autoload --optimize
print_success "Autoloader optimized"
echo ""

# Step 8: Rebuild caches
print_info "Rebuilding caches..."
cd "$APP_DIR"
php artisan config:cache
php artisan route:cache
print_success "Caches rebuilt"
echo ""

# Step 9: Set permissions
print_info "Setting correct permissions..."
sudo chown -R deploy:www-data "$APP_DIR"
sudo chmod -R 755 "$APP_DIR"
sudo chmod -R 775 "$APP_DIR/storage" "$APP_DIR/bootstrap/cache"
print_success "Permissions set"
echo ""

# Step 10: Restart services
print_info "Restarting PHP-FPM..."
sudo systemctl restart "$PHP_FPM_SERVICE"
print_success "PHP-FPM restarted"
echo ""

print_info "Restarting queue workers..."
if sudo supervisorctl status laraend-worker:* &>/dev/null; then
    sudo supervisorctl restart laraend-worker:*
    print_success "Queue workers restarted"
else
    print_info "Queue workers not configured yet (run Step 8 from laraend.md to set up)"
fi
echo ""

# Step 11: Disable maintenance mode
print_info "Disabling maintenance mode..."
php artisan up
print_success "Application is now live"
echo ""

# Step 12: Health check
print_info "Running health check..."
HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" https://api.ithdp.ir)
if [ "$HTTP_CODE" = "200" ] || [ "$HTTP_CODE" = "302" ]; then
    print_success "Health check passed (HTTP $HTTP_CODE)"
else
    print_error "Health check failed (HTTP $HTTP_CODE)"
    print_info "Check logs: tail -f $APP_DIR/storage/logs/laravel.log"
fi
echo ""

# Step 13: Clean old backups (keep last 5)
print_info "Cleaning old deployment backups..."
cd "$BACKUP_DIR"
ls -t pre-deploy-*.tar.gz 2>/dev/null | tail -n +6 | xargs rm -f 2>/dev/null || true
print_success "Old backups cleaned"
echo ""

# Summary
echo "============================================"
print_success "Deployment completed successfully!"
echo "============================================"
echo ""
echo "Backup location: $BACKUP_FILE"
echo "Deployment time: $(date)"
echo ""
echo "Useful commands:"
echo "  View logs:        tail -f $APP_DIR/storage/logs/laravel.log"
echo "  View Nginx logs:  tail -f /var/log/nginx/laraend-error.log"
echo "  Check services:   sudo systemctl status $PHP_FPM_SERVICE"
echo "  Check workers:    sudo supervisorctl status"
echo ""
echo "If issues occur, rollback with:"
echo "  cd $APP_DIR && php artisan down"
echo "  tar -xzf $BACKUP_FILE -C /var/www"
echo "  cd $APP_DIR && php artisan up"
echo ""

