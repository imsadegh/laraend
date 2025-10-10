# Laraend - Laravel Backend Deployment Guide

## Project Overview
**Laraend** is the Laravel backend for the HakimyarFusion LMS (Learning Management System) project. This backend serves as the API for both the Vue.js frontend (Vueend) and Flutter mobile application (HekmatSara).

### Tech Stack
- **Framework**: Laravel 12.x
- **PHP Version**: 8.4.13 (Development) / 8.4+ (Production)
- **Database**: PostgreSQL 16
- **Cache/Queue**: Redis
- **Web Server**: Nginx
- **Process Manager**: Supervisor
- **Authentication**: JWT (tymon/jwt-auth)
- **SMS Service**: IPE SMS-IR

### Project Structure
- **Domain**: api.ithdp.ir (Backend API)
- **Related Projects**:
  - Vueend: Vue.js frontend
  - HekmatSara: Flutter mobile app

---

## VPS Deployment Guide

### Server Information
- **VPS IP**: 5.182.44.108
- **OS**: Ubuntu 24.04 LTS
- **Domain**: api.ithdp.ir
- **Database**: PostgreSQL (same VPS)
- **Web Server**: Nginx
- **SSL**: Let's Encrypt (Certbot)

---

## Step 1: Initial Server Setup

### 1.1 Connect to VPS
```bash
ssh root@5.182.44.108
```

### 1.2 Update System
```bash
apt update && apt upgrade -y
```

### 1.3 Set Timezone
```bash
timedatectl set-timezone Asia/Tehran
```

### 1.4 Create Deployment User
```bash
# Create deploy user with home directory
adduser deploy

# Add deploy user to sudo group
usermod -aG sudo deploy

# Switch to deploy user
su - deploy
```

---

## Step 2: Install Required Software

### 2.1 Install PHP 8.4 and Extensions
```bash
sudo apt install -y software-properties-common
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update

# Install PHP 8.4 and required extensions
sudo apt install -y php8.4 php8.4-fpm php8.4-cli php8.4-common \
    php8.4-pgsql php8.4-mbstring php8.4-xml php8.4-curl \
    php8.4-zip php8.4-gd php8.4-bcmath php8.4-intl \
    php8.4-redis php8.4-opcache

# Verify PHP installation
php -v
```

### 2.2 Install PostgreSQL 16
```bash
# Install PostgreSQL
sudo apt install -y postgresql postgresql-contrib

# Start and enable PostgreSQL
sudo systemctl start postgresql
sudo systemctl enable postgresql

# Verify installation
sudo systemctl status postgresql
```

### 2.3 Install Redis
```bash
sudo apt install -y redis-server

# Configure Redis to start on boot
sudo systemctl enable redis-server
sudo systemctl start redis-server

# Verify Redis
redis-cli ping
# Should return: PONG
```

### 2.4 Install Nginx
```bash
sudo apt install -y nginx

# Start and enable Nginx
sudo systemctl start nginx
sudo systemctl enable nginx
```

### 2.5 Install Composer
```bash
# Download and install Composer
cd ~
curl -sS https://getcomposer.org/installer -o composer-setup.php
sudo php composer-setup.php --install-dir=/usr/local/bin --filename=composer

# Verify installation
composer --version
```

### 2.6 Install Supervisor
```bash
sudo apt install -y supervisor

# Enable and start supervisor
sudo systemctl enable supervisor
sudo systemctl start supervisor
```

### 2.7 Install Certbot for SSL
```bash
sudo apt install -y certbot python3-certbot-nginx
```

---

## Step 3: Configure PostgreSQL

### 3.1 Create Database and User
```bash
# Switch to postgres user
sudo -u postgres psql

# In PostgreSQL prompt, run:
CREATE DATABASE laraend_db;
CREATE USER laraend_user WITH ENCRYPTED PASSWORD 'LaraEnd2025!SecurePass';
GRANT ALL PRIVILEGES ON DATABASE laraend_db TO laraend_user;

# Grant schema privileges (PostgreSQL 15+)
\c laraend_db
GRANT ALL ON SCHEMA public TO laraend_user;
GRANT ALL PRIVILEGES ON ALL TABLES IN SCHEMA public TO laraend_user;
GRANT ALL PRIVILEGES ON ALL SEQUENCES IN SCHEMA public TO laraend_user;
ALTER DEFAULT PRIVILEGES IN SCHEMA public GRANT ALL ON TABLES TO laraend_user;
ALTER DEFAULT PRIVILEGES IN SCHEMA public GRANT ALL ON SEQUENCES TO laraend_user;

# Exit PostgreSQL
\q
```

### 3.2 Configure PostgreSQL for Remote Access (if needed)
```bash
# Edit postgresql.conf
sudo nano /etc/postgresql/16/main/postgresql.conf

# Find and modify:
listen_addresses = 'localhost'

# Edit pg_hba.conf
sudo nano /etc/postgresql/16/main/pg_hba.conf

# Add this line for local connections:
local   all             laraend_user                              scram-sha-256

# Restart PostgreSQL
sudo systemctl restart postgresql
```

---

## Step 4: Setup Laravel Application

### 4.1 Create Application Directory
```bash
# Create web directory
sudo mkdir -p /var/www/laraend
sudo chown -R deploy:deploy /var/www/laraend
cd /var/www/laraend
```

### 4.2 Clone or Upload Project
**Option A: Using Git (Recommended)**
```bash
# If using Git repository
git clone <your-git-repository-url> .

# Or initialize git and push from local
# On your local machine:
cd /Users/sadeghmbp/Downloads/myDocuments/_develop/_fs_dev/hakimyarFusion/laraend
git remote add production deploy@5.182.44.108:/var/www/laraend.git
git push production main
```

**Option B: Using SCP (Manual Upload)**
```bash
# On your local machine, from project directory:
cd /Users/sadeghmbp/Downloads/myDocuments/_develop/_fs_dev/hakimyarFusion/laraend

# Upload files (excluding vendor, node_modules, .env)
rsync -avz --exclude='vendor' --exclude='node_modules' \
    --exclude='.env' --exclude='storage/logs/*' \
    --exclude='storage/framework/cache/*' \
    --exclude='storage/framework/sessions/*' \
    --exclude='storage/framework/views/*' \
    --exclude='.git' \
    ./ deploy@5.182.44.108:/var/www/laraend/
```

### 4.3 Install Dependencies
```bash
cd /var/www/laraend

# Install Composer dependencies (production mode)
composer install --no-dev --optimize-autoloader

# If you need dev dependencies for initial setup:
# composer install
```

### 4.4 Configure Environment File
```bash
# Create .env file
cd /var/www/laraend
cp .env.example .env
nano .env
```

**Production .env Configuration:**
```env
# Application Configuration
APP_NAME=Laravel
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_TIMEZONE=Asia/Tehran
APP_URL=https://api.ithdp.ir
FRONTEND_URL=https://ithdp.ir

# SMS-IR Configuration (get from https://sms.ir)
SMSIR_API_KEY=your_smsir_api_key_here

APP_LOCALE=en
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=en_US

APP_MAINTENANCE_DRIVER=file
# APP_MAINTENANCE_STORE=database

PHP_CLI_SERVER_WORKERS=4

BCRYPT_ROUNDS=12

# Logging Configuration (error level for production)
LOG_CHANNEL=stack
LOG_STACK=single
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=error

# PostgreSQL Database Configuration
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=laraend_db
DB_USERNAME=laraend_user
DB_PASSWORD=LaraEnd2025!SecurePass

# Session Configuration (Redis for production performance)
SESSION_DRIVER=redis
SESSION_LIFETIME=120
SESSION_ENCRYPT=true
SESSION_PATH=/
SESSION_DOMAIN=.ithdp.ir

# Broadcasting, Filesystem, Queue
BROADCAST_CONNECTION=log
FILESYSTEM_DISK=local
QUEUE_CONNECTION=redis

# Cache Configuration (Redis for production performance)
CACHE_STORE=redis
CACHE_PREFIX=laraend_cache

MEMCACHED_HOST=127.0.0.1

# Redis Configuration
REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# Mail Configuration
MAIL_MAILER=log
MAIL_SCHEME=null
MAIL_HOST=127.0.0.1
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_FROM_ADDRESS="noreply@ithdp.ir"
MAIL_FROM_NAME="${APP_NAME}"

# AWS S3 Configuration (Optional)
AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=
AWS_USE_PATH_STYLE_ENDPOINT=false

# Vite Configuration
VITE_APP_NAME="${APP_NAME}"

# JWT Authentication
JWT_SECRET=
```

**Key Changes from Development to Production:**
- `APP_ENV=production` (was: `local`)
- `APP_DEBUG=false` (was: `true`)
- `APP_URL=https://api.ithdp.ir` (was: `http://localhost:8000`)
- `FRONTEND_URL=https://ithdp.ir` (was: `http://localhost:5173`)
- `LOG_LEVEL=error` (was: `debug`)
- `SESSION_DRIVER=redis` (was: `database`) - Better performance
- `SESSION_ENCRYPT=true` (was: `false`) - Security
- `SESSION_DOMAIN=.ithdp.ir` (was: `null`) - For CORS
- `QUEUE_CONNECTION=redis` (was: `database`) - Better performance
- `CACHE_STORE=redis` (was: `database`) - Better performance
- `CACHE_PREFIX=laraend_cache` (was: empty) - Avoid conflicts
- `MAIL_FROM_ADDRESS=noreply@ithdp.ir` (was: `hello@example.com`)

### 4.5 Generate Application Keys
```bash
cd /var/www/laraend

# Generate Laravel application key
php artisan key:generate

# Generate JWT secret key
php artisan jwt:secret

# Clear and cache configuration
php artisan config:clear
php artisan config:cache
php artisan route:cache
php artisan view:clear
```

### 4.6 Set Permissions
```bash
cd /var/www/laraend

# Set ownership
sudo chown -R deploy:www-data .

# Set directory permissions
sudo find . -type d -exec chmod 755 {} \;
sudo find . -type f -exec chmod 644 {} \;

# Set storage and cache permissions
sudo chmod -R 775 storage bootstrap/cache
sudo chown -R deploy:www-data storage bootstrap/cache
```

### 4.7 Run Migrations and Seeders
```bash
cd /var/www/laraend

# Run migrations
php artisan migrate --force

# Run seeders (if needed)
php artisan db:seed --force

# Or run specific seeders
php artisan db:seed --class=RoleSeeder --force
php artisan db:seed --class=CategorySeeder --force
php artisan db:seed --class=UserSeeder --force
```

**Note about Seeders in Production:**
If you encounter `Class "Faker\Factory" not found` error, it's because Faker is a dev dependency. You have two options:

1. **Temporary Install** (for initial setup):
   ```bash
   composer require fakerphp/faker --dev
   php artisan db:seed --force
   composer remove fakerphp/faker --dev
   ```

2. **Production-Safe Seeders** (recommended):
   The UserSeeder has been updated to work without Faker in production. Upload the updated seeder file and it will create only the essential demo users without requiring Faker.

---

## Step 5: Configure Nginx

### 5.1 Create Nginx Configuration
```bash
sudo nano /etc/nginx/sites-available/laraend
```

**Working Nginx Configuration:**
```nginx
# Laraend Nginx Configuration (Without SSL - for testing)
server {
    listen 80;
    listen [::]:80;
    server_name api.ithdp.ir 5.182.44.108;

    root /var/www/laraend/public;
    index index.php index.html;

    # Logging
    access_log /var/log/nginx/laraend-access.log;
    error_log /var/log/nginx/laraend-error.log;

    # Client body size (for file uploads)
    client_max_body_size 100M;

    # Gzip Compression
    gzip on;
    gzip_vary on;
    gzip_min_length 1024;
    gzip_types text/plain text/css text/xml text/javascript application/x-javascript application/xml+rss application/json application/javascript;

    # Laravel public directory
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # PHP-FPM Configuration
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.4-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;

        # Increase timeouts for long-running requests
        fastcgi_read_timeout 300;
        fastcgi_send_timeout 300;
    }

    # Deny access to hidden files
    location ~ /\. {
        deny all;
    }

    # Cache static assets
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|svg|woff|woff2|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
}
```

**For SSL Configuration (After Domain Setup):**
```nginx
# HTTP - Redirect to HTTPS
server {
    listen 80;
    listen [::]:80;
    server_name api.ithdp.ir;

    # Redirect all HTTP to HTTPS
    return 301 https://$server_name$request_uri;
}

# HTTPS
server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name api.ithdp.ir;

    root /var/www/laraend/public;
    index index.php index.html;

    # SSL Configuration (will be configured by Certbot)
    ssl_certificate /etc/letsencrypt/live/api.ithdp.ir/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/api.ithdp.ir/privkey.pem;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;
    ssl_prefer_server_ciphers on;

    # Security Headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "strict-origin-when-cross-origin" always;

    # Logging
    access_log /var/log/nginx/laraend-access.log;
    error_log /var/log/nginx/laraend-error.log;

    # Client body size (for file uploads)
    client_max_body_size 100M;

    # Gzip Compression
    gzip on;
    gzip_vary on;
    gzip_min_length 1024;
    gzip_types text/plain text/css text/xml text/javascript application/x-javascript application/xml+rss application/json application/javascript;

    # Laravel public directory
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # PHP-FPM Configuration
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.4-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;

        # Increase timeouts for long-running requests
        fastcgi_read_timeout 300;
        fastcgi_send_timeout 300;
    }

    # Deny access to hidden files
    location ~ /\. {
        deny all;
    }

    # Deny access to sensitive files
    location ~ /\.(?!well-known).* {
        deny all;
    }

    # Cache static assets
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|svg|woff|woff2|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
}
```

### 5.2 Enable Site and Test Configuration
```bash
# Create symbolic link to enable site
sudo ln -s /etc/nginx/sites-available/laraend /etc/nginx/sites-enabled/

# Remove default site if exists
sudo rm -f /etc/nginx/sites-enabled/default

# Test Nginx configuration
sudo nginx -t

# Restart Nginx
sudo systemctl restart nginx
```

---

## Step 6: Configure PHP-FPM

### 6.1 Optimize PHP-FPM Configuration
```bash
sudo nano /etc/php/8.4/fpm/pool.d/www.conf
```

**Key configurations to modify:**
```ini
user = deploy
group = www-data
listen.owner = www-data
listen.group = www-data
listen.mode = 0660

; Performance tuning (adjust based on your VPS resources)
pm = dynamic
pm.max_children = 50
pm.start_servers = 5
pm.min_spare_servers = 5
pm.max_spare_servers = 35
pm.max_requests = 500
```

### 6.2 Optimize PHP Configuration
```bash
sudo nano /etc/php/8.4/fpm/php.ini
```

**Key settings:**
```ini
memory_limit = 256M
upload_max_filesize = 100M
post_max_size = 100M
max_execution_time = 300
max_input_time = 300
date.timezone = Asia/Tehran

; OPcache settings
opcache.enable=1
opcache.memory_consumption=128
opcache.interned_strings_buffer=8
opcache.max_accelerated_files=10000
opcache.revalidate_freq=2
opcache.fast_shutdown=1
```

### 6.3 Restart PHP-FPM
```bash
sudo systemctl restart php8.4-fpm
```

---

## Step 7: Configure SSL with Let's Encrypt

### 7.1 DNS Configuration
Before obtaining SSL certificate, ensure your domain `api.ithdp.ir` points to your VPS IP `5.182.44.108`.

**In your DirectAdmin panel or DNS provider:**
1. Add an A record: `api.ithdp.ir` → `5.182.44.108`
2. Wait for DNS propagation (can take up to 48 hours, usually much faster)
3. Verify DNS: `dig api.ithdp.ir` or `nslookup api.ithdp.ir`

### 7.2 Obtain SSL Certificate
```bash
# First, temporarily modify Nginx config to work without SSL
sudo nano /etc/nginx/sites-available/laraend

# Comment out SSL-related lines temporarily, then:
sudo nginx -t
sudo systemctl reload nginx

# Obtain SSL certificate
sudo certbot --nginx -d api.ithdp.ir

# Follow the prompts:
# - Enter email address
# - Agree to terms
# - Choose whether to redirect HTTP to HTTPS (choose Yes)

# Certbot will automatically update your Nginx configuration
```

### 7.3 Auto-Renewal Setup
```bash
# Test auto-renewal
sudo certbot renew --dry-run

# Certbot creates a systemd timer for auto-renewal
sudo systemctl status certbot.timer
```

---

## Step 8: Configure Queue Workers with Supervisor

### 8.1 Create Supervisor Configuration
```bash
sudo nano /etc/supervisor/conf.d/laraend-worker.conf
```

**Supervisor Configuration:**
```ini
[program:laraend-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/laraend/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600 --timeout=60
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=deploy
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/laraend/storage/logs/worker.log
stopwaitsecs=3600
```

### 8.2 Start Queue Workers
```bash
# Reread supervisor configuration
sudo supervisorctl reread

# Update supervisor
sudo supervisorctl update

# Start workers
sudo supervisorctl start laraend-worker:*

# Check status
sudo supervisorctl status
```

### 8.3 Managing Queue Workers
```bash
# Restart workers
sudo supervisorctl restart laraend-worker:*

# Stop workers
sudo supervisorctl stop laraend-worker:*

# View logs
tail -f /var/www/laraend/storage/logs/worker.log
```

---

## Step 9: Configure Scheduled Tasks (Cron)

### 9.1 Setup Laravel Scheduler
```bash
# Edit crontab for deploy user
crontab -e

# Add this line:
* * * * * cd /var/www/laraend && php artisan schedule:run >> /dev/null 2>&1
```

---

## Step 10: Security Hardening

### 10.1 Configure Firewall (UFW)
```bash
# Enable UFW
sudo ufw enable

# Allow SSH
sudo ufw allow 22/tcp

# Allow HTTP and HTTPS
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp

# Check status
sudo ufw status
```

### 10.2 Secure Redis
```bash
sudo nano /etc/redis/redis.conf
```

**Add/modify:**
```conf
bind 127.0.0.1 ::1
requirepass YourStrongRedisPassword123!
```

**Update .env file:**
```env
REDIS_PASSWORD=YourStrongRedisPassword123!
```

**Restart Redis:**
```bash
sudo systemctl restart redis-server
```

### 10.3 Disable Directory Listing
Already handled in Nginx configuration.

### 10.4 Secure PostgreSQL
```bash
# PostgreSQL is already configured to listen only on localhost
# Ensure strong password is set (already done in Step 3.1)
```

---

## Step 11: Monitoring and Logging

### 11.1 Laravel Logs
```bash
# View Laravel logs
tail -f /var/www/laraend/storage/logs/laravel.log

# Clear old logs periodically
cd /var/www/laraend/storage/logs
rm laravel-*.log
```

### 11.2 Nginx Logs
```bash
# Access logs
tail -f /var/log/nginx/laraend-access.log

# Error logs
tail -f /var/log/nginx/laraend-error.log
```

### 11.3 System Monitoring
```bash
# Check disk space
df -h

# Check memory usage
free -m

# Check CPU usage
top

# Check running processes
ps aux | grep php
ps aux | grep nginx
```

---

## Step 12: Deployment Workflow

### 12.1 Initial Deployment
Follow all steps 1-11 above.

### 12.2 Updating Application

**When you make changes locally and need to deploy:**

```bash
# On your local machine:
cd /Users/sadeghmbp/Downloads/myDocuments/_develop/_fs_dev/hakimyarFusion/laraend

# Upload changes via rsync
rsync -avz --exclude='vendor' --exclude='node_modules' \
    --exclude='.env' --exclude='storage/logs/*' \
    --exclude='storage/framework/cache/*' \
    --exclude='storage/framework/sessions/*' \
    --exclude='storage/framework/views/*' \
    --exclude='.git' \
    ./ deploy@5.182.44.108:/var/www/laraend/

# SSH into server
ssh deploy@5.182.44.108

# Navigate to project
cd /var/www/laraend

# Put application in maintenance mode
php artisan down

# Update dependencies
composer install --no-dev --optimize-autoloader

# Run migrations
php artisan migrate --force

# Clear and cache
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
php artisan config:cache
php artisan route:cache

# Restart services
sudo systemctl restart php8.4-fpm
sudo supervisorctl restart laraend-worker:*

# Bring application back online
php artisan up

# Check application status
curl -I https://api.ithdp.ir
```

### 12.3 Rollback Procedure
```bash
# If deployment fails, restore from backup
cd /var/www/laraend

# Restore database backup
sudo -u postgres psql laraend_db < /path/to/backup.sql

# Restore application files
rsync -avz /path/to/backup/ /var/www/laraend/

# Run migrations rollback if needed
php artisan migrate:rollback --force

# Restart services
sudo systemctl restart php8.4-fpm
sudo supervisorctl restart laraend-worker:*
```

---

## Step 13: Backup Strategy

### 13.1 Database Backup Script
```bash
# Create backup directory
mkdir -p /home/deploy/backups/database

# Create backup script
nano /home/deploy/backups/backup-db.sh
```

**Backup Script:**
```bash
#!/bin/bash

# Configuration
BACKUP_DIR="/home/deploy/backups/database"
DB_NAME="laraend_db"
DB_USER="laraend_user"
DATE=$(date +"%Y%m%d_%H%M%S")
BACKUP_FILE="$BACKUP_DIR/${DB_NAME}_${DATE}.sql"

# Create backup
export PGPASSWORD='LaraEnd2025!SecurePass'
pg_dump -U $DB_USER -h localhost $DB_NAME > $BACKUP_FILE

# Compress backup
gzip $BACKUP_FILE

# Delete backups older than 7 days
find $BACKUP_DIR -name "*.sql.gz" -mtime +7 -delete

echo "Backup completed: ${BACKUP_FILE}.gz"
```

**Make script executable:**
```bash
chmod +x /home/deploy/backups/backup-db.sh
```

**Schedule daily backups:**
```bash
crontab -e

# Add this line for daily backup at 2 AM
0 2 * * * /home/deploy/backups/backup-db.sh >> /home/deploy/backups/backup.log 2>&1
```

### 13.2 Application Files Backup
```bash
# Create application backup script
nano /home/deploy/backups/backup-app.sh
```

**Application Backup Script:**
```bash
#!/bin/bash

BACKUP_DIR="/home/deploy/backups/application"
APP_DIR="/var/www/laraend"
DATE=$(date +"%Y%m%d_%H%M%S")
BACKUP_FILE="$BACKUP_DIR/laraend_${DATE}.tar.gz"

# Create backup directory if not exists
mkdir -p $BACKUP_DIR

# Create backup (exclude vendor, node_modules, cache)
tar -czf $BACKUP_FILE \
    --exclude='vendor' \
    --exclude='node_modules' \
    --exclude='storage/framework/cache/*' \
    --exclude='storage/framework/sessions/*' \
    --exclude='storage/framework/views/*' \
    --exclude='storage/logs/*' \
    -C /var/www laraend

# Delete backups older than 7 days
find $BACKUP_DIR -name "laraend_*.tar.gz" -mtime +7 -delete

echo "Application backup completed: $BACKUP_FILE"
```

**Make script executable:**
```bash
chmod +x /home/deploy/backups/backup-app.sh
```

**Schedule weekly backups:**
```bash
crontab -e

# Add this line for weekly backup (every Sunday at 3 AM)
0 3 * * 0 /home/deploy/backups/backup-app.sh >> /home/deploy/backups/backup.log 2>&1
```

---

## Step 14: Testing Deployment

### 14.1 Test API Endpoints
```bash
# Test health check
curl https://api.ithdp.ir/api/health

# Test authentication endpoint
curl -X POST https://api.ithdp.ir/api/auth/login \
    -H "Content-Type: application/json" \
    -d '{"email":"test@example.com","password":"password"}'

# Test courses endpoint (requires authentication)
curl https://api.ithdp.ir/api/courses \
    -H "Authorization: Bearer YOUR_JWT_TOKEN"
```

### 14.2 Check Service Status
```bash
# Check Nginx
sudo systemctl status nginx

# Check PHP-FPM
sudo systemctl status php8.4-fpm

# Check PostgreSQL
sudo systemctl status postgresql

# Check Redis
sudo systemctl status redis-server

# Check Supervisor
sudo supervisorctl status
```

### 14.3 Monitor Logs
```bash
# Laravel logs
tail -f /var/www/laraend/storage/logs/laravel.log

# Nginx error logs
tail -f /var/log/nginx/laraend-error.log

# PHP-FPM logs
tail -f /var/log/php8.4-fpm.log
```

---

## Step 15: CORS Configuration for Frontend

### 15.1 Update .env for Frontend URL
```env
# In /var/www/laraend/.env
FRONTEND_URL=https://ithdp.ir
```

### 15.2 Verify CORS Configuration
The CORS configuration is already set in `config/cors.php`:
- Allowed origins: `FRONTEND_URL` from .env
- Allowed methods: All
- Allowed headers: All
- Supports credentials: true

### 15.3 Test CORS
```bash
# Test CORS from frontend domain
curl -H "Origin: https://ithdp.ir" \
     -H "Access-Control-Request-Method: POST" \
     -H "Access-Control-Request-Headers: Content-Type" \
     -X OPTIONS \
     https://api.ithdp.ir/api/auth/login -v
```

---

## Troubleshooting

### Issue: Class "Faker\Factory" not found (During Seeding)
**Problem:** Faker is a dev dependency and not installed with `--no-dev` flag.

**Solution 1 - Temporary Install:**
```bash
cd /var/www/laraend
composer require fakerphp/faker --dev
php artisan db:seed --force
composer remove fakerphp/faker --dev
```

**Solution 2 - Upload Updated Seeder (Recommended):**
```bash
# On your Mac, upload the updated UserSeeder.php
rsync -avz database/seeders/UserSeeder.php deploy@5.182.44.108:/var/www/laraend/database/seeders/

# On VPS, run seeder
php artisan db:seed --class=UserSeeder --force
```

### Issue: 502 Bad Gateway
**Solution:**
```bash
# Check PHP-FPM status
sudo systemctl status php8.4-fpm

# Check PHP-FPM logs
tail -f /var/log/php8.4-fpm.log

# Restart PHP-FPM
sudo systemctl restart php8.4-fpm
```

### Issue: 500 Internal Server Error
**Solution:**
```bash
# Check Laravel logs
tail -f /var/www/laraend/storage/logs/laravel.log

# Check Nginx error logs
tail -f /var/log/nginx/laraend-error.log

# Clear cache
cd /var/www/laraend
php artisan config:clear
php artisan cache:clear
php artisan route:clear
```

### Issue: Database Connection Failed
**Solution:**
```bash
# Check PostgreSQL status
sudo systemctl status postgresql

# Test database connection
sudo -u postgres psql -U laraend_user -d laraend_db -h localhost

# Check .env database credentials
cat /var/www/laraend/.env | grep DB_
```

### Issue: Queue Jobs Not Processing
**Solution:**
```bash
# Check supervisor status
sudo supervisorctl status

# Restart workers
sudo supervisorctl restart laraend-worker:*

# Check worker logs
tail -f /var/www/laraend/storage/logs/worker.log
```

### Issue: Permission Denied
**Solution:**
```bash
cd /var/www/laraend

# Fix ownership
sudo chown -R deploy:www-data .

# Fix permissions
sudo find . -type d -exec chmod 755 {} \;
sudo find . -type f -exec chmod 644 {} \;
sudo chmod -R 775 storage bootstrap/cache
```

### Issue: SSL Certificate Not Working
**Solution:**
```bash
# Renew certificate
sudo certbot renew

# Check certificate status
sudo certbot certificates

# Restart Nginx
sudo systemctl restart nginx
```

---

## Performance Optimization

### 1. Enable OPcache
Already configured in Step 6.2.

### 2. Use Redis for Sessions and Cache
Already configured in .env.

### 3. Optimize Composer Autoloader
```bash
cd /var/www/laraend
composer dump-autoload --optimize
```

### 4. Cache Configuration and Routes
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 5. Database Query Optimization
- Use eager loading to prevent N+1 queries
- Add indexes to frequently queried columns
- Use database query caching

### 6. Enable Gzip Compression
Already configured in Nginx configuration.

---

## Maintenance Commands

### Clear All Caches
```bash
cd /var/www/laraend
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### Rebuild Caches
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### View Queue Jobs
```bash
php artisan queue:work --once
php artisan queue:failed
php artisan queue:retry all
```

### Database Maintenance
```bash
# Run migrations
php artisan migrate --force

# Rollback last migration
php artisan migrate:rollback --step=1 --force

# Reset database (CAUTION: Deletes all data)
php artisan migrate:fresh --force --seed
```

---

## Database Schema

### Tables Overview

1. **users** - User accounts (students, instructors, admins)
2. **roles** - User roles (admin, instructor, student)
3. **categories** - Course categories
4. **courses** - Course information
5. **course_modules** - Course content modules
6. **course_enrollments** - Student course enrollments
7. **assignments** - Course assignments
8. **assignment_submissions** - Student assignment submissions
9. **exams** - Course exams
10. **questions** - Question bank
11. **exam_questions** - Questions assigned to exams
12. **exam_attempts** - Student exam attempts
13. **exam_attempt_answers** - Student answers for exam attempts
14. **exam_scores** - Exam scores and feedback
15. **tuition_history** - Payment records
16. **user_activity_logs** - User activity tracking
17. **course_watch_time** - Video watch time tracking
18. **badges** - Achievement badges
19. **user_badges** - User earned badges

### Key Relationships

- Users → Roles (Many-to-One)
- Courses → Categories (Many-to-One)
- Courses → Users (Instructor) (Many-to-One)
- Course Modules → Courses (Many-to-One)
- Course Enrollments → Users + Courses (Many-to-Many)
- Assignments → Courses (Many-to-One)
- Assignment Submissions → Assignments + Users (Many-to-Many)
- Exams → Courses (Many-to-One)
- Exam Questions → Exams + Questions (Many-to-Many)
- Exam Attempts → Exams + Users (Many-to-Many)
- Exam Attempt Answers → Exam Attempts + Exam Questions (Many-to-Many)

---

## API Endpoints Overview

### Authentication
- `POST /api/signup` - User registration
- `POST /api/auth/login` - User login
- `POST /api/otp/send` - Send OTP
- `POST /api/otp/verify` - Verify OTP

### Courses
- `GET /api/courses` - List all courses
- `POST /api/courses` - Create course (auth required)
- `GET /api/courses/{id}` - Get course details
- `PUT /api/courses/{id}` - Update course (auth required)
- `GET /api/instructor/courses` - Get instructor's courses
- `GET /api/student/courses` - Get enrolled courses

### Course Modules
- `GET /api/courses/{course}/modules` - List course modules
- `POST /api/courses/{course}/modules` - Create module
- `GET /api/modules/{id}` - Get module details
- `PUT /api/modules/{id}` - Update module
- `DELETE /api/modules/{id}` - Delete module

### Assignments
- `GET /api/instructor/assignments` - List instructor assignments
- `POST /api/courses/{course}/assignments` - Create assignment
- `GET /api/assignments/{id}` - Get assignment details
- `PUT /api/assignments/{id}` - Update assignment
- `DELETE /api/assignments/{id}` - Delete assignment
- `POST /api/assignments/{id}/submissions` - Submit assignment
- `GET /api/instructor/assignment-submissions` - List submissions
- `PUT /api/instructor/assignment-submissions/{id}/review` - Review submission

### Exams
- `GET /api/exams` - List exams
- `POST /api/exams` - Create exam
- `GET /api/exams/{id}` - Get exam details
- `PUT /api/exams/{id}` - Update exam
- `DELETE /api/exams/{id}` - Delete exam
- `POST /api/exams/{exam}/attempts` - Start exam attempt
- `GET /api/exams/{exam}/attempts/{attempt}/next` - Get next question
- `POST /api/attempts/{attempt}/answers` - Submit answer

### Enrollments (Admin)
- `GET /api/admin/enrollments` - List enrollments
- `POST /api/admin/enrollments` - Enroll student
- `PUT /api/admin/enrollments/{id}` - Update enrollment

### Categories
- `GET /api/categories` - List categories

### Tuition
- `POST /api/tuition/pay` - Process payment
- `GET /api/tuition/summary` - Get payment summary

---

## Environment Variables Reference

| Variable | Description | Example |
|----------|-------------|---------|
| `APP_NAME` | Application name | Laraend API |
| `APP_ENV` | Environment | production |
| `APP_DEBUG` | Debug mode | false |
| `APP_URL` | Application URL | https://api.ithdp.ir |
| `FRONTEND_URL` | Frontend URL for CORS | https://ithdp.ir |
| `DB_CONNECTION` | Database driver | pgsql |
| `DB_HOST` | Database host | 127.0.0.1 |
| `DB_PORT` | Database port | 5432 |
| `DB_DATABASE` | Database name | laraend_db |
| `DB_USERNAME` | Database user | laraend_user |
| `DB_PASSWORD` | Database password | LaraEnd2025!SecurePass |
| `REDIS_HOST` | Redis host | 127.0.0.1 |
| `REDIS_PASSWORD` | Redis password | null or your password |
| `REDIS_PORT` | Redis port | 6379 |
| `CACHE_STORE` | Cache driver | redis |
| `SESSION_DRIVER` | Session driver | redis |
| `QUEUE_CONNECTION` | Queue driver | redis |
| `JWT_SECRET` | JWT secret key | Generated by artisan |
| `JWT_TTL` | JWT token lifetime (minutes) | 60 |
| `SMSIR_API_KEY` | SMS service API key | Your API key |
| `SMSIR_LINE_NUMBER` | SMS line number | Your line number |

---

## Quick Reference Commands

### Service Management
```bash
# Restart all services
sudo systemctl restart nginx php8.4-fpm postgresql redis-server
sudo supervisorctl restart laraend-worker:*

# Check all services status
sudo systemctl status nginx php8.4-fpm postgresql redis-server
sudo supervisorctl status
```

### Laravel Artisan
```bash
# Clear all caches
php artisan optimize:clear

# Rebuild all caches
php artisan optimize

# Run migrations
php artisan migrate --force

# Run seeders
php artisan db:seed --force

# Maintenance mode
php artisan down
php artisan up
```

### Logs
```bash
# View Laravel logs
tail -f /var/www/laraend/storage/logs/laravel.log

# View Nginx logs
tail -f /var/log/nginx/laraend-error.log
tail -f /var/log/nginx/laraend-access.log

# View worker logs
tail -f /var/www/laraend/storage/logs/worker.log
```

---

## Additional Notes

### SMS Service Configuration
This project uses IPE SMS-IR for OTP verification. To configure:
1. Sign up at https://sms.ir
2. Get your API key and line number
3. Update `.env` with your credentials
4. Test OTP functionality

### File Upload Limits
- Maximum upload size: 100MB
- Configured in both PHP and Nginx
- Adjust if needed for larger files

### Queue Workers
- 2 workers configured by default
- Adjust `numprocs` in supervisor config based on load
- Monitor worker logs for issues

### Database Backups
- Automated daily database backups at 2 AM
- Backups stored in `/home/deploy/backups/database`
- Retention: 7 days
- Backups are compressed with gzip

### SSL Certificate Renewal
- Automatic renewal via Certbot
- Renewal attempts twice daily
- Certificate valid for 90 days
- Monitor renewal: `sudo certbot certificates`

### Connecting Frontend and Mobile App
- Frontend (Vueend): Configure API base URL to `https://api.ithdp.ir`
- Mobile App (HekmatSara): Configure API endpoint to `https://api.ithdp.ir`
- Ensure CORS is properly configured for frontend domain

---

## Support and Maintenance

### Regular Maintenance Tasks
1. **Daily**: Monitor logs for errors
2. **Weekly**: Check disk space and server resources
3. **Monthly**: Review and update dependencies
4. **Quarterly**: Security audit and updates

### System Updates
```bash
# Update system packages
sudo apt update && sudo apt upgrade -y

# Update Composer dependencies
cd /var/www/laraend
composer update --no-dev

# Update Laravel
# Check for updates and follow Laravel upgrade guide
```

### Security Updates
- Keep PHP, PostgreSQL, Nginx updated
- Monitor Laravel security advisories
- Update dependencies regularly
- Review and rotate credentials periodically

---

**Last Updated**: October 10, 2025
**Laravel Version**: 12.x
**PHP Version**: 8.4+
**PostgreSQL Version**: 16

