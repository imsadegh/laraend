#!/bin/bash

# Laravel Backend Deployment Script for Ubuntu 20.04
# This script automates the server setup process
# Run this script on your Ubuntu 20.04 VPS

set -e

echo "=========================================="
echo "Laravel Backend Deployment Setup"
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

# Check if running as root
if [ "$EUID" -ne 0 ]; then
    print_error "Please run this script as root or with sudo"
    exit 1
fi

# Get configuration from user
print_info "Please provide the following information:"
echo ""

read -p "Enter your domain name (or press Enter to skip): " DOMAIN_NAME
read -p "Enter database name [hakimyar_fusion]: " DB_NAME
DB_NAME=${DB_NAME:-hakimyar_fusion}

read -p "Enter database username [hakimyar_user]: " DB_USER
DB_USER=${DB_USER:-hakimyar_user}

read -sp "Enter database password: " DB_PASSWORD
echo ""

read -p "Enter application directory [/var/www/hakimyar-fusion]: " APP_DIR
APP_DIR=${APP_DIR:-/var/www/hakimyar-fusion}

echo ""
print_info "Starting deployment setup..."
echo ""

# Step 1: Update system
print_info "Step 1: Updating system packages..."
apt update && apt upgrade -y
print_success "System updated"

# Step 2: Install dependencies
print_info "Step 2: Installing required dependencies..."
apt install -y software-properties-common apt-transport-https ca-certificates curl gnupg lsb-release unzip git
print_success "Dependencies installed"

# Step 3: Install PHP 8.3
print_info "Step 3: Installing PHP 8.3..."
add-apt-repository ppa:ondrej/php -y
apt update
apt install -y php8.3 php8.3-fpm php8.3-cli php8.3-common php8.3-curl \
php8.3-mbstring php8.3-xml php8.3-zip php8.3-pgsql php8.3-bcmath \
php8.3-gd php8.3-intl php8.3-redis php8.3-tokenizer
print_success "PHP 8.3 installed"

# Step 4: Install PostgreSQL
print_info "Step 4: Installing PostgreSQL..."
apt install -y postgresql postgresql-contrib
systemctl start postgresql
systemctl enable postgresql
print_success "PostgreSQL installed"

# Step 5: Create database and user
print_info "Step 5: Creating database and user..."
sudo -u postgres psql <<EOF
CREATE DATABASE $DB_NAME;
CREATE USER $DB_USER WITH ENCRYPTED PASSWORD '$DB_PASSWORD';
GRANT ALL PRIVILEGES ON DATABASE $DB_NAME TO $DB_USER;
ALTER DATABASE $DB_NAME OWNER TO $DB_USER;
EOF
print_success "Database and user created"

# Step 6: Install Composer
print_info "Step 6: Installing Composer..."
cd /tmp
curl -sS https://getcomposer.org/installer -o composer-setup.php
php composer-setup.php --install-dir=/usr/local/bin --filename=composer
rm composer-setup.php
print_success "Composer installed"

# Step 7: Install Nginx
print_info "Step 7: Installing Nginx..."
apt install -y nginx
systemctl start nginx
systemctl enable nginx
print_success "Nginx installed"

# Step 8: Create application directory
print_info "Step 8: Creating application directory..."
mkdir -p $APP_DIR
print_success "Application directory created: $APP_DIR"

# Step 9: Configure PHP-FPM
print_info "Step 9: Configuring PHP-FPM..."
sed -i 's/upload_max_filesize = .*/upload_max_filesize = 100M/' /etc/php/8.3/fpm/php.ini
sed -i 's/post_max_size = .*/post_max_size = 100M/' /etc/php/8.3/fpm/php.ini
sed -i 's/memory_limit = .*/memory_limit = 512M/' /etc/php/8.3/fpm/php.ini
sed -i 's/max_execution_time = .*/max_execution_time = 300/' /etc/php/8.3/fpm/php.ini
sed -i 's/expose_php = .*/expose_php = Off/' /etc/php/8.3/fpm/php.ini
systemctl restart php8.3-fpm
print_success "PHP-FPM configured"

# Step 10: Create Nginx configuration
print_info "Step 10: Creating Nginx configuration..."
if [ -z "$DOMAIN_NAME" ]; then
    SERVER_NAME="_"
else
    SERVER_NAME="$DOMAIN_NAME www.$DOMAIN_NAME"
fi

cat > /etc/nginx/sites-available/hakimyar-fusion <<EOF
server {
    listen 80;
    listen [::]:80;
    server_name $SERVER_NAME;
    root $APP_DIR/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    client_max_body_size 100M;
}
EOF

ln -sf /etc/nginx/sites-available/hakimyar-fusion /etc/nginx/sites-enabled/
rm -f /etc/nginx/sites-enabled/default
nginx -t
systemctl restart nginx
print_success "Nginx configured"

# Step 11: Setup firewall
print_info "Step 11: Configuring firewall..."
ufw --force enable
ufw allow OpenSSH
ufw allow 'Nginx Full'
print_success "Firewall configured"

# Step 12: Create backup directory
print_info "Step 12: Creating backup directory..."
mkdir -p /var/backups/hakimyar-fusion
print_success "Backup directory created"

# Step 13: Create backup script
print_info "Step 13: Creating backup script..."
cat > /usr/local/bin/backup-hakimyar.sh <<EOF
#!/bin/bash
TIMESTAMP=\$(date +"%Y%m%d_%H%M%S")
BACKUP_DIR="/var/backups/hakimyar-fusion"
DB_NAME="$DB_NAME"
DB_USER="$DB_USER"

mkdir -p \$BACKUP_DIR

# Backup Database
PGPASSWORD="$DB_PASSWORD" pg_dump -U \$DB_USER -h localhost \$DB_NAME > \$BACKUP_DIR/db_\$TIMESTAMP.sql

# Backup Files
tar -czf \$BACKUP_DIR/files_\$TIMESTAMP.tar.gz $APP_DIR/storage

# Keep only last 7 days of backups
find \$BACKUP_DIR -type f -mtime +7 -delete

echo "Backup completed: \$TIMESTAMP"
EOF

chmod +x /usr/local/bin/backup-hakimyar.sh
print_success "Backup script created"

# Step 14: Create .env template
print_info "Step 14: Creating .env template..."
cat > $APP_DIR/.env.template <<EOF
APP_NAME="Hakimyar Fusion"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=http://${DOMAIN_NAME:-your-domain.com}

LOG_CHANNEL=stack
LOG_LEVEL=error

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=$DB_NAME
DB_USERNAME=$DB_USER
DB_PASSWORD=$DB_PASSWORD

BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120

JWT_SECRET=
JWT_ALGO=HS256
JWT_TTL=60
JWT_REFRESH_TTL=20160

SMSIR_API_KEY=
EOF
print_success ".env template created"

# Step 15: Install Supervisor (optional)
print_info "Step 15: Installing Supervisor..."
apt install -y supervisor
systemctl enable supervisor
systemctl start supervisor
print_success "Supervisor installed"

echo ""
echo "=========================================="
print_success "Server setup completed successfully!"
echo "=========================================="
echo ""
print_info "Next steps:"
echo "1. Upload your Laravel application to: $APP_DIR"
echo "2. Copy .env.template to .env and update if needed"
echo "3. Run: cd $APP_DIR && composer install --optimize-autoloader --no-dev"
echo "4. Run: php artisan key:generate"
echo "5. Run: php artisan jwt:secret"
echo "6. Set permissions: chown -R www-data:www-data $APP_DIR"
echo "7. Set permissions: chmod -R 755 $APP_DIR"
echo "8. Set permissions: chmod -R 775 $APP_DIR/storage $APP_DIR/bootstrap/cache"
echo "9. Run migrations: php artisan migrate --force"
echo "10. Cache config: php artisan config:cache"
echo ""
if [ ! -z "$DOMAIN_NAME" ]; then
    print_info "To setup SSL certificate, run:"
    echo "apt install -y certbot python3-certbot-nginx"
    echo "certbot --nginx -d $DOMAIN_NAME -d www.$DOMAIN_NAME"
    echo ""
fi
print_info "Database credentials:"
echo "Database: $DB_NAME"
echo "Username: $DB_USER"
echo "Password: [hidden]"
echo ""
print_info "Application directory: $APP_DIR"
echo ""
print_success "Happy deploying! 🚀"


