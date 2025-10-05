#!/bin/bash

# Laravel Application Deployment Script
# Run this script on your VPS after uploading your Laravel project

echo "🚀 Starting Laravel Application Deployment..."

# Set variables
APP_DIR="/var/www/laravel"
DOMAIN="your-domain.com"  # Replace with your actual domain
DB_NAME="hakimyar_fusion"
DB_USER="laravel_user"
DB_PASS="your_secure_password_here"  # Change this to a secure password

# Navigate to application directory
cd $APP_DIR

# Set proper permissions
echo "🔐 Setting proper permissions..."
chown -R www-data:www-data $APP_DIR
chmod -R 755 $APP_DIR
chmod -R 775 $APP_DIR/storage
chmod -R 775 $APP_DIR/bootstrap/cache

# Install Composer dependencies
echo "📦 Installing Composer dependencies..."
sudo -u www-data composer install --no-dev --optimize-autoloader

# Create environment file
echo "⚙️ Creating environment file..."
cp env.production.example .env

# Generate application key
echo "🔑 Generating application key..."
sudo -u www-data php artisan key:generate

# Generate JWT secret
echo "🔐 Generating JWT secret..."
sudo -u www-data php artisan jwt:secret

# Create database
echo "🗄️ Creating database..."
mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS $DB_NAME CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -u root -p -e "CREATE USER IF NOT EXISTS '$DB_USER'@'localhost' IDENTIFIED BY '$DB_PASS';"
mysql -u root -p -e "GRANT ALL PRIVILEGES ON $DB_NAME.* TO '$DB_USER'@'localhost';"
mysql -u root -p -e "FLUSH PRIVILEGES;"

# Update environment file with database credentials
echo "📝 Updating environment file..."
sed -i "s/DB_DATABASE=hakimyar_fusion/DB_DATABASE=$DB_NAME/" .env
sed -i "s/DB_USERNAME=laravel_user/DB_USERNAME=$DB_USER/" .env
sed -i "s/DB_PASSWORD=your_secure_password_here/DB_PASSWORD=$DB_PASS/" .env
sed -i "s/your-domain.com/$DOMAIN/g" .env

# Run database migrations
echo "🗄️ Running database migrations..."
sudo -u www-data php artisan migrate --force

# Seed database (optional)
echo "🌱 Seeding database..."
sudo -u www-data php artisan db:seed --force

# Clear and cache configuration
echo "🧹 Clearing and caching configuration..."
sudo -u www-data php artisan config:clear
sudo -u www-data php artisan config:cache
sudo -u www-data php artisan route:cache
sudo -u www-data php artisan view:cache

# Create storage link
echo "🔗 Creating storage link..."
sudo -u www-data php artisan storage:link

# Configure Nginx
echo "🌐 Configuring Nginx..."
cp nginx-config.conf /etc/nginx/sites-available/laravel
sed -i "s/your-domain.com/$DOMAIN/g" /etc/nginx/sites-available/laravel
ln -sf /etc/nginx/sites-available/laravel /etc/nginx/sites-enabled/
rm -f /etc/nginx/sites-enabled/default

# Test Nginx configuration
echo "🧪 Testing Nginx configuration..."
nginx -t

# Restart services
echo "🔄 Restarting services..."
systemctl restart nginx
systemctl restart php8.2-fpm

# Set up Supervisor for queue workers
echo "👨‍💼 Setting up Supervisor for queue workers..."
cat > /etc/supervisor/conf.d/laravel-worker.conf << EOF
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php $APP_DIR/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=$APP_DIR/storage/logs/worker.log
stopwaitsecs=3600
EOF

# Start Supervisor
systemctl restart supervisor
supervisorctl reread
supervisorctl update
supervisorctl start laravel-worker:*

# Set up log rotation
echo "📋 Setting up log rotation..."
cat > /etc/logrotate.d/laravel << EOF
$APP_DIR/storage/logs/*.log {
    daily
    missingok
    rotate 14
    compress
    notifempty
    create 644 www-data www-data
    postrotate
        systemctl reload php8.2-fpm
    endscript
}
EOF

# Install SSL certificate with Let's Encrypt (optional)
echo "🔒 To install SSL certificate, run:"
echo "apt install certbot python3-certbot-nginx"
echo "certbot --nginx -d $DOMAIN -d www.$DOMAIN"

echo "✅ Laravel deployment completed successfully!"
echo "🌐 Your application should be accessible at: http://$DOMAIN"
echo "📋 Don't forget to:"
echo "1. Configure your domain DNS to point to this server"
echo "2. Install SSL certificate for HTTPS"
echo "3. Configure your mail settings in .env"
echo "4. Set up monitoring and backups"

