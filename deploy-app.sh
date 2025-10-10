#!/bin/bash

# Laravel Application Deployment Script
# Run this script on your VPS after the initial server setup
# This script deploys and configures your Laravel application

set -e

echo "=========================================="
echo "Laravel Application Deployment"
echo "=========================================="
echo ""

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Function to print colored output
print_success() {
    echo -e "${GREEN}✓ $1${NC}"
}

print_error() {
    echo -e "${RED}✗ $1${NC}"
}

print_info() {
    echo -e "${YELLOW}→ $1${NC}"
}

# Configuration
APP_DIR="/var/www/hakimyar-fusion"
PHP_VERSION="8.3"

# Check if running in app directory
if [ ! -f "artisan" ]; then
    print_error "artisan file not found. Please run this script from your Laravel application directory."
    exit 1
fi

print_info "Deploying Laravel application..."
echo ""

# Step 1: Put application in maintenance mode
print_info "Step 1: Enabling maintenance mode..."
php artisan down || true
print_success "Maintenance mode enabled"

# Step 2: Pull latest changes (if using git)
if [ -d ".git" ]; then
    print_info "Step 2: Pulling latest changes from git..."
    git pull origin main || git pull origin master
    print_success "Latest changes pulled"
else
    print_info "Step 2: Skipping git pull (not a git repository)"
fi

# Step 3: Install/Update dependencies
print_info "Step 3: Installing/Updating Composer dependencies..."
composer install --optimize-autoloader --no-dev --no-interaction
print_success "Dependencies installed"

# Step 4: Run migrations
print_info "Step 4: Running database migrations..."
php artisan migrate --force
print_success "Migrations completed"

# Step 5: Clear and cache configuration
print_info "Step 5: Clearing and caching configuration..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

php artisan config:cache
php artisan route:cache
php artisan view:cache
print_success "Configuration cached"

# Step 6: Set proper permissions
print_info "Step 6: Setting proper permissions..."
sudo chown -R www-data:www-data .
sudo chmod -R 755 .
sudo chmod -R 775 storage bootstrap/cache
print_success "Permissions set"

# Step 7: Restart services
print_info "Step 7: Restarting services..."
sudo systemctl restart php${PHP_VERSION}-fpm
sudo systemctl restart nginx
print_success "Services restarted"

# Step 8: Restart queue workers (if using supervisor)
if command -v supervisorctl &> /dev/null; then
    print_info "Step 8: Restarting queue workers..."
    sudo supervisorctl restart all || true
    print_success "Queue workers restarted"
else
    print_info "Step 8: Supervisor not found, skipping queue worker restart"
fi

# Step 9: Disable maintenance mode
print_info "Step 9: Disabling maintenance mode..."
php artisan up
print_success "Application is now live"

echo ""
echo "=========================================="
print_success "Deployment completed successfully!"
echo "=========================================="
echo ""
print_info "Application is now running and accessible"
echo ""


