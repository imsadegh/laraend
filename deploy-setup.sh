#!/bin/bash

# Laravel Backend Deployment Script for Ubuntu 20.04 VPS
# Run this script on your VPS as root or with sudo privileges

echo "🚀 Starting Laravel Backend Deployment Setup..."

# Update system packages
echo "📦 Updating system packages..."
apt update && apt upgrade -y

# Install essential packages
echo "🔧 Installing essential packages..."
apt install -y curl wget git unzip software-properties-common apt-transport-https ca-certificates gnupg lsb-release

# Add PHP 8.2 repository
echo "🐘 Adding PHP 8.2 repository..."
add-apt-repository ppa:ondrej/php -y
apt update

# Install PHP 8.2 and required extensions
echo "📦 Installing PHP 8.2 and extensions..."
apt install -y php8.2 php8.2-fpm php8.2-mysql php8.2-xml php8.2-mbstring php8.2-curl php8.2-zip php8.2-bcmath php8.2-gd php8.2-intl php8.2-redis

# Install Composer
echo "🎼 Installing Composer..."
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer
chmod +x /usr/local/bin/composer

# Install MySQL
echo "🗄️ Installing MySQL..."
apt install -y mysql-server mysql-client
systemctl start mysql
systemctl enable mysql

# Secure MySQL installation
echo "🔒 Securing MySQL installation..."
mysql_secure_installation

# Install Nginx
echo "🌐 Installing Nginx..."
apt install -y nginx
systemctl start nginx
systemctl enable nginx

# Install Redis (optional, for caching and sessions)
echo "🔴 Installing Redis..."
apt install -y redis-server
systemctl start redis-server
systemctl enable redis-server

# Install Node.js and npm (for Laravel Mix/Vite if needed)
echo "📦 Installing Node.js..."
curl -fsSL https://deb.nodesource.com/setup_18.x | bash -
apt install -y nodejs

# Install Supervisor (for queue workers)
echo "👨‍💼 Installing Supervisor..."
apt install -y supervisor

# Create application directory
echo "📁 Creating application directory..."
mkdir -p /var/www/laravel
chown -R www-data:www-data /var/www/laravel
chmod -R 755 /var/www/laravel

# Configure PHP-FPM
echo "⚙️ Configuring PHP-FPM..."
sed -i 's/;cgi.fix_pathinfo=1/cgi.fix_pathinfo=0/' /etc/php/8.2/fpm/php.ini
sed -i 's/upload_max_filesize = 2M/upload_max_filesize = 100M/' /etc/php/8.2/fpm/php.ini
sed -i 's/post_max_size = 8M/post_max_size = 100M/' /etc/php/8.2/fpm/php.ini
sed -i 's/max_execution_time = 30/max_execution_time = 300/' /etc/php/8.2/fpm/php.ini

# Restart services
echo "🔄 Restarting services..."
systemctl restart php8.2-fpm
systemctl restart nginx

echo "✅ VPS setup completed successfully!"
echo "📋 Next steps:"
echo "1. Create MySQL database and user"
echo "2. Upload your Laravel project"
echo "3. Configure environment variables"
echo "4. Set up SSL certificate"

