#!/bin/bash

# Local Deployment Helper Script
# Run this script from your local machine (macOS) to deploy to VPS
# Usage: ./deploy-from-local.sh [vps-ip] [action]
# Actions: setup, deploy, update

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
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

print_header() {
    echo -e "${BLUE}=========================================="
    echo -e "$1"
    echo -e "==========================================${NC}"
    echo ""
}

# Check if VPS IP is provided
if [ -z "$1" ]; then
    print_error "Usage: ./deploy-from-local.sh [vps-ip] [action]"
    echo "Actions:"
    echo "  setup   - Initial server setup (uploads and runs setup script)"
    echo "  deploy  - Deploy application for the first time"
    echo "  update  - Update existing deployment"
    echo ""
    echo "Example: ./deploy-from-local.sh 192.168.1.100 setup"
    exit 1
fi

VPS_IP=$1
ACTION=${2:-deploy}
VPS_USER=${VPS_USER:-root}
APP_DIR="/var/www/hakimyar-fusion"
LOCAL_DIR="/Users/sadeghmbp/Downloads/myDocuments/_develop/_fs_dev/hakimyarFusion/laraend"

print_header "Laravel Deployment from Local Machine"

# Function to check SSH connection
check_ssh() {
    print_info "Checking SSH connection to $VPS_IP..."
    if ssh -o ConnectTimeout=5 -o BatchMode=yes $VPS_USER@$VPS_IP exit 2>/dev/null; then
        print_success "SSH connection successful"
        return 0
    else
        print_error "Cannot connect to $VPS_IP via SSH"
        echo "Please ensure:"
        echo "1. The VPS IP address is correct"
        echo "2. SSH is enabled on the VPS"
        echo "3. You have SSH access (try: ssh $VPS_USER@$VPS_IP)"
        exit 1
    fi
}

# Function to setup server
setup_server() {
    print_header "Initial Server Setup"

    check_ssh

    print_info "Uploading setup script to VPS..."
    scp $LOCAL_DIR/deploy-server-setup.sh $VPS_USER@$VPS_IP:/root/
    print_success "Setup script uploaded"

    print_info "Running setup script on VPS..."
    echo "Note: You will be asked for configuration details..."
    ssh -t $VPS_USER@$VPS_IP "chmod +x /root/deploy-server-setup.sh && /root/deploy-server-setup.sh"
    print_success "Server setup completed"

    echo ""
    print_info "Next step: Run './deploy-from-local.sh $VPS_IP deploy' to deploy your application"
}

# Function to deploy application
deploy_application() {
    print_header "Deploying Application"

    check_ssh

    # Check if .env exists locally
    if [ ! -f "$LOCAL_DIR/.env" ]; then
        print_error ".env file not found locally"
        echo "Please create a .env file before deploying"
        exit 1
    fi

    print_info "Syncing application files to VPS..."
    rsync -avz --progress \
        --exclude 'vendor' \
        --exclude 'node_modules' \
        --exclude '.env' \
        --exclude 'storage/logs/*' \
        --exclude 'storage/framework/cache/*' \
        --exclude 'storage/framework/sessions/*' \
        --exclude 'storage/framework/views/*' \
        --exclude '.git' \
        --exclude '.DS_Store' \
        --exclude 'database/database.sqlite' \
        $LOCAL_DIR/ $VPS_USER@$VPS_IP:$APP_DIR/
    print_success "Files synced"

    print_info "Uploading deployment script..."
    scp $LOCAL_DIR/deploy-app.sh $VPS_USER@$VPS_IP:$APP_DIR/
    ssh $VPS_USER@$VPS_IP "chmod +x $APP_DIR/deploy-app.sh"
    print_success "Deployment script uploaded"

    print_info "Setting up environment file..."
    # Copy local .env as template, user will need to modify it
    scp $LOCAL_DIR/.env $VPS_USER@$VPS_IP:$APP_DIR/.env.local
    print_success "Environment file uploaded as .env.local"

    print_info "Installing dependencies and configuring application..."
    ssh -t $VPS_USER@$VPS_IP << 'ENDSSH'
cd /var/www/hakimyar-fusion

# Check if .env exists, if not copy from template
if [ ! -f ".env" ]; then
    if [ -f ".env.template" ]; then
        cp .env.template .env
        echo "Created .env from template"
    elif [ -f ".env.local" ]; then
        cp .env.local .env
        echo "Created .env from local copy"
        echo "WARNING: Please review and update database credentials in .env"
    fi
fi

# Install dependencies
composer install --optimize-autoloader --no-dev --no-interaction

# Generate keys if not set
php artisan key:generate --force || true
php artisan jwt:secret --force || true

# Set permissions
chown -R www-data:www-data .
chmod -R 755 .
chmod -R 775 storage bootstrap/cache

# Run migrations
php artisan migrate --force

# Cache configuration
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Restart services
systemctl restart php8.3-fpm
systemctl restart nginx

echo ""
echo "✓ Deployment completed successfully!"
ENDSSH

    print_success "Application deployed"

    echo ""
    print_header "Deployment Complete!"
    print_info "Your application should now be accessible at:"
    echo "  http://$VPS_IP"
    echo ""
    print_info "Important: Review the .env file on the server and update if needed:"
    echo "  ssh $VPS_USER@$VPS_IP"
    echo "  nano $APP_DIR/.env"
}

# Function to update application
update_application() {
    print_header "Updating Application"

    check_ssh

    print_info "Syncing updated files to VPS..."
    rsync -avz --progress \
        --exclude 'vendor' \
        --exclude 'node_modules' \
        --exclude '.env' \
        --exclude 'storage/logs/*' \
        --exclude 'storage/framework/cache/*' \
        --exclude 'storage/framework/sessions/*' \
        --exclude 'storage/framework/views/*' \
        --exclude '.git' \
        --exclude '.DS_Store' \
        --exclude 'database/database.sqlite' \
        $LOCAL_DIR/ $VPS_USER@$VPS_IP:$APP_DIR/
    print_success "Files synced"

    print_info "Running deployment script on VPS..."
    ssh -t $VPS_USER@$VPS_IP "cd $APP_DIR && ./deploy-app.sh"
    print_success "Application updated"

    echo ""
    print_header "Update Complete!"
    print_info "Your application has been updated at:"
    echo "  http://$VPS_IP"
}

# Main execution
case $ACTION in
    setup)
        setup_server
        ;;
    deploy)
        deploy_application
        ;;
    update)
        update_application
        ;;
    *)
        print_error "Unknown action: $ACTION"
        echo "Valid actions: setup, deploy, update"
        exit 1
        ;;
esac

echo ""
print_success "All done! 🚀"


