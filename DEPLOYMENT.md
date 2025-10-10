# Laravel Backend Deployment Guide for Ubuntu 20.04

## Prerequisites
- Ubuntu 20.04 VPS
- Root or sudo access
- Domain name (optional but recommended)
- SSH access to the server

## Server Specifications
- **Development PHP Version**: 8.4.13
- **Target PHP Version**: 8.3 (recommended for Ubuntu 20.04)
- **Database**: PostgreSQL
- **Composer Version**: 2.8.12
- **Laravel Version**: 12.0

---

## Step 1: Initial Server Setup

### 1.1 Update System Packages
```bash
sudo apt update && sudo apt upgrade -y
```

### 1.2 Install Required Dependencies
```bash
sudo apt install -y software-properties-common apt-transport-https ca-certificates curl gnupg lsb-release
```

---

## Step 2: Install PHP 8.3

### 2.1 Add Ondřej Surý's PPA (for latest PHP versions)
```bash
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update
```

### 2.2 Install PHP 8.3 and Required Extensions
```bash
sudo apt install -y php8.3 php8.3-fpm php8.3-cli php8.3-common php8.3-curl \
php8.3-mbstring php8.3-xml php8.3-zip php8.3-pgsql php8.3-bcmath \
php8.3-gd php8.3-intl php8.3-redis php8.3-tokenizer
```

### 2.3 Verify PHP Installation
```bash
php -v
```

---

## Step 3: Install PostgreSQL

### 3.1 Install PostgreSQL
```bash
sudo apt install -y postgresql postgresql-contrib
```

### 3.2 Start and Enable PostgreSQL
```bash
sudo systemctl start postgresql
sudo systemctl enable postgresql
```

### 3.3 Create Database and User
```bash
sudo -u postgres psql
```

Inside PostgreSQL prompt:
```sql
CREATE DATABASE hakimyar_fusion;
CREATE USER hakimyar_user WITH ENCRYPTED PASSWORD 'your_secure_password_here';
GRANT ALL PRIVILEGES ON DATABASE hakimyar_fusion TO hakimyar_user;
ALTER DATABASE hakimyar_fusion OWNER TO hakimyar_user;
\q
```

### 3.4 Configure PostgreSQL for Remote Access (if needed)
Edit PostgreSQL configuration:
```bash
sudo nano /etc/postgresql/12/main/postgresql.conf
```
Find and uncomment: `listen_addresses = 'localhost'`

Edit pg_hba.conf:
```bash
sudo nano /etc/postgresql/12/main/pg_hba.conf
```
Add: `host    all             all             127.0.0.1/32            md5`

Restart PostgreSQL:
```bash
sudo systemctl restart postgresql
```

---

## Step 4: Install Composer

```bash
cd ~
curl -sS https://getcomposer.org/installer -o composer-setup.php
sudo php composer-setup.php --install-dir=/usr/local/bin --filename=composer
composer --version
```

---

## Step 5: Install Nginx

### 5.1 Install Nginx
```bash
sudo apt install -y nginx
```

### 5.2 Start and Enable Nginx
```bash
sudo systemctl start nginx
sudo systemctl enable nginx
```

---

## Step 6: Setup Application Directory

### 6.1 Create Application Directory
```bash
sudo mkdir -p /var/www/hakimyar-fusion
sudo chown -R $USER:$USER /var/www/hakimyar-fusion
```

---

## Step 7: Deploy Laravel Application

### 7.1 Upload Your Code
From your local machine, use one of these methods:

**Option A: Using Git (Recommended)**
```bash
# On your local machine, push to a Git repository (GitHub, GitLab, etc.)
git init
git add .
git commit -m "Initial commit"
git remote add origin your-repo-url
git push -u origin main

# On the server
cd /var/www/hakimyar-fusion
git clone your-repo-url .
```

**Option B: Using SCP**
```bash
# On your local machine
cd /Users/sadeghmbp/Downloads/myDocuments/_develop/_fs_dev/hakimyarFusion/laraend
scp -r * user@your-vps-ip:/var/www/hakimyar-fusion/
```

**Option C: Using rsync (Recommended for updates)**
```bash
# On your local machine
rsync -avz --exclude 'vendor' --exclude 'node_modules' --exclude '.env' \
--exclude 'storage/logs/*' --exclude 'storage/framework/cache/*' \
--exclude 'storage/framework/sessions/*' --exclude 'storage/framework/views/*' \
/Users/sadeghmbp/Downloads/myDocuments/_develop/_fs_dev/hakimyarFusion/laraend/ \
user@your-vps-ip:/var/www/hakimyar-fusion/
```

### 7.2 Install Dependencies
```bash
cd /var/www/hakimyar-fusion
composer install --optimize-autoloader --no-dev
```

### 7.3 Setup Environment File
```bash
cp .env.example .env
nano .env
```

Update the following in `.env`:
```env
APP_NAME="Hakimyar Fusion"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=http://your-domain.com

LOG_CHANNEL=stack
LOG_LEVEL=error

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=hakimyar_fusion
DB_USERNAME=hakimyar_user
DB_PASSWORD=your_secure_password_here

BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120

# JWT Configuration
JWT_SECRET=
JWT_ALGO=HS256
JWT_TTL=60
JWT_REFRESH_TTL=20160

# SMS Configuration (if needed)
SMSIR_API_KEY=your_sms_api_key
```

### 7.4 Generate Application Key
```bash
php artisan key:generate
```

### 7.5 Generate JWT Secret
```bash
php artisan jwt:secret
```

### 7.6 Set Proper Permissions
```bash
sudo chown -R www-data:www-data /var/www/hakimyar-fusion
sudo chmod -R 755 /var/www/hakimyar-fusion
sudo chmod -R 775 /var/www/hakimyar-fusion/storage
sudo chmod -R 775 /var/www/hakimyar-fusion/bootstrap/cache
```

### 7.7 Run Migrations
```bash
php artisan migrate --force
```

### 7.8 Seed Database (if needed)
```bash
php artisan db:seed --force
```

### 7.9 Clear and Cache Configuration
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## Step 8: Configure Nginx

### 8.1 Create Nginx Configuration
```bash
sudo nano /etc/nginx/sites-available/hakimyar-fusion
```

Add the following configuration:
```nginx
server {
    listen 80;
    listen [::]:80;
    server_name your-domain.com www.your-domain.com;
    root /var/www/hakimyar-fusion/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    # Increase upload size if needed
    client_max_body_size 100M;
}
```

### 8.2 Enable Site and Test Configuration
```bash
sudo ln -s /etc/nginx/sites-available/hakimyar-fusion /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
```

---

## Step 9: Configure PHP-FPM

### 9.1 Optimize PHP-FPM Settings
```bash
sudo nano /etc/php/8.3/fpm/php.ini
```

Update these values:
```ini
upload_max_filesize = 100M
post_max_size = 100M
memory_limit = 512M
max_execution_time = 300
```

### 9.2 Restart PHP-FPM
```bash
sudo systemctl restart php8.3-fpm
```

---

## Step 10: Setup SSL with Let's Encrypt (Optional but Recommended)

### 10.1 Install Certbot
```bash
sudo apt install -y certbot python3-certbot-nginx
```

### 10.2 Obtain SSL Certificate
```bash
sudo certbot --nginx -d your-domain.com -d www.your-domain.com
```

### 10.3 Auto-renewal Test
```bash
sudo certbot renew --dry-run
```

---

## Step 11: Setup Supervisor for Queue Workers (Optional)

If you're using queues:

### 11.1 Install Supervisor
```bash
sudo apt install -y supervisor
```

### 11.2 Create Supervisor Configuration
```bash
sudo nano /etc/supervisor/conf.d/hakimyar-fusion-worker.conf
```

Add:
```ini
[program:hakimyar-fusion-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/hakimyar-fusion/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/hakimyar-fusion/storage/logs/worker.log
stopwaitsecs=3600
```

### 11.3 Start Supervisor
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start hakimyar-fusion-worker:*
```

---

## Step 12: Setup Firewall

```bash
sudo ufw allow OpenSSH
sudo ufw allow 'Nginx Full'
sudo ufw enable
sudo ufw status
```

---

## Step 13: Setup Automated Backups

### 13.1 Create Backup Script
```bash
sudo nano /usr/local/bin/backup-hakimyar.sh
```

Add:
```bash
#!/bin/bash
TIMESTAMP=$(date +"%Y%m%d_%H%M%S")
BACKUP_DIR="/var/backups/hakimyar-fusion"
DB_NAME="hakimyar_fusion"
DB_USER="hakimyar_user"

mkdir -p $BACKUP_DIR

# Backup Database
PGPASSWORD="your_secure_password_here" pg_dump -U $DB_USER -h localhost $DB_NAME > $BACKUP_DIR/db_$TIMESTAMP.sql

# Backup Files
tar -czf $BACKUP_DIR/files_$TIMESTAMP.tar.gz /var/www/hakimyar-fusion/storage

# Keep only last 7 days of backups
find $BACKUP_DIR -type f -mtime +7 -delete

echo "Backup completed: $TIMESTAMP"
```

### 13.2 Make Script Executable
```bash
sudo chmod +x /usr/local/bin/backup-hakimyar.sh
```

### 13.3 Setup Cron Job
```bash
sudo crontab -e
```

Add:
```cron
# Daily backup at 2 AM
0 2 * * * /usr/local/bin/backup-hakimyar.sh >> /var/log/hakimyar-backup.log 2>&1

# Laravel Scheduler (if using)
* * * * * cd /var/www/hakimyar-fusion && php artisan schedule:run >> /dev/null 2>&1
```

---

## Step 14: Monitoring and Logs

### 14.1 View Laravel Logs
```bash
tail -f /var/www/hakimyar-fusion/storage/logs/laravel.log
```

### 14.2 View Nginx Logs
```bash
# Access logs
tail -f /var/log/nginx/access.log

# Error logs
tail -f /var/log/nginx/error.log
```

### 14.3 View PHP-FPM Logs
```bash
tail -f /var/log/php8.3-fpm.log
```

---

## Step 15: Security Hardening

### 15.1 Disable Directory Listing
Already configured in Nginx config above.

### 15.2 Hide PHP Version
```bash
sudo nano /etc/php/8.3/fpm/php.ini
```
Set: `expose_php = Off`

### 15.3 Setup Fail2Ban (Optional)
```bash
sudo apt install -y fail2ban
sudo systemctl enable fail2ban
sudo systemctl start fail2ban
```

---

## Deployment Checklist

- [ ] Server updated and secured
- [ ] PHP 8.3 installed with all required extensions
- [ ] PostgreSQL installed and database created
- [ ] Composer installed
- [ ] Nginx installed and configured
- [ ] Application code deployed
- [ ] Dependencies installed
- [ ] Environment file configured
- [ ] Application key generated
- [ ] JWT secret generated
- [ ] File permissions set correctly
- [ ] Database migrated
- [ ] Nginx configuration created and enabled
- [ ] SSL certificate installed (if using domain)
- [ ] Firewall configured
- [ ] Backups configured
- [ ] Monitoring setup

---

## Common Issues and Solutions

### Issue: 500 Internal Server Error
**Solution**: Check Laravel logs and ensure proper permissions on storage and bootstrap/cache directories.

### Issue: Database Connection Failed
**Solution**: Verify PostgreSQL is running and credentials in .env are correct.

### Issue: 502 Bad Gateway
**Solution**: Ensure PHP-FPM is running: `sudo systemctl status php8.3-fpm`

### Issue: File Upload Issues
**Solution**: Check `upload_max_filesize` and `post_max_size` in php.ini and `client_max_body_size` in Nginx config.

---

## Updating the Application

```bash
# On your local machine
git push origin main

# On the server
cd /var/www/hakimyar-fusion
git pull origin main
composer install --optimize-autoloader --no-dev
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
sudo systemctl restart php8.3-fpm
```

---

## Useful Commands

```bash
# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Restart services
sudo systemctl restart nginx
sudo systemctl restart php8.3-fpm
sudo systemctl restart postgresql

# Check service status
sudo systemctl status nginx
sudo systemctl status php8.3-fpm
sudo systemctl status postgresql

# View real-time logs
tail -f storage/logs/laravel.log

# Run artisan commands
php artisan list
```

---

## Support and Contact

For issues or questions, please refer to:
- Laravel Documentation: https://laravel.com/docs
- PostgreSQL Documentation: https://www.postgresql.org/docs/
- Nginx Documentation: https://nginx.org/en/docs/

---

**Last Updated**: October 5, 2025


