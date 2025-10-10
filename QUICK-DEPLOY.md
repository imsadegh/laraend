# Quick Deployment Guide

This is a condensed version of the deployment process. For detailed instructions, see `DEPLOYMENT.md`.

## Prerequisites
- Ubuntu 20.04 VPS with SSH access
- Your VPS IP address
- Domain name (optional)

---

## Quick Start (3 Methods)

### Method 1: Automated Setup (Recommended)

**On your VPS:**
```bash
# Upload the setup script
scp deploy-server-setup.sh root@YOUR_VPS_IP:/root/

# SSH into your VPS
ssh root@YOUR_VPS_IP

# Run the setup script
chmod +x /root/deploy-server-setup.sh
/root/deploy-server-setup.sh
```

**Then upload your application:**
```bash
# From your local machine
cd /Users/sadeghmbp/Downloads/myDocuments/_develop/_fs_dev/hakimyarFusion/laraend
rsync -avz --exclude 'vendor' --exclude 'node_modules' --exclude '.env' \
--exclude 'storage/logs/*' --exclude 'storage/framework/cache/*' \
--exclude 'storage/framework/sessions/*' --exclude 'storage/framework/views/*' \
--exclude '.git' . root@YOUR_VPS_IP:/var/www/hakimyar-fusion/
```

**Finally, on your VPS:**
```bash
cd /var/www/hakimyar-fusion
cp .env.template .env
composer install --optimize-autoloader --no-dev
php artisan key:generate
php artisan jwt:secret
chown -R www-data:www-data .
chmod -R 755 .
chmod -R 775 storage bootstrap/cache
php artisan migrate --force
php artisan config:cache
php artisan route:cache
```

---

### Method 2: Using Git (Best for ongoing updates)

**On your local machine:**
```bash
# Initialize git and push to your repository
cd /Users/sadeghmbp/Downloads/myDocuments/_develop/_fs_dev/hakimyarFusion/laraend
git init
git add .
git commit -m "Initial commit"
git remote add origin YOUR_GIT_REPO_URL
git push -u origin main
```

**On your VPS (after running deploy-server-setup.sh):**
```bash
cd /var/www/hakimyar-fusion
git clone YOUR_GIT_REPO_URL .
cp .env.template .env
nano .env  # Update any necessary values
composer install --optimize-autoloader --no-dev
php artisan key:generate
php artisan jwt:secret
chown -R www-data:www-data .
chmod -R 755 .
chmod -R 775 storage bootstrap/cache
php artisan migrate --force
php artisan config:cache
php artisan route:cache
```

---

### Method 3: Manual Setup

Follow the detailed steps in `DEPLOYMENT.md`.

---

## Essential Commands

### Deploy Updates
```bash
cd /var/www/hakimyar-fusion
./deploy-app.sh
```

### Manual Update Process
```bash
cd /var/www/hakimyar-fusion
php artisan down
git pull origin main  # or upload files via rsync
composer install --optimize-autoloader --no-dev
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
sudo systemctl restart php8.3-fpm
php artisan up
```

### View Logs
```bash
# Laravel logs
tail -f /var/www/hakimyar-fusion/storage/logs/laravel.log

# Nginx error logs
tail -f /var/log/nginx/error.log

# PHP-FPM logs
tail -f /var/log/php8.3-fpm.log
```

### Restart Services
```bash
sudo systemctl restart nginx
sudo systemctl restart php8.3-fpm
sudo systemctl restart postgresql
```

### Clear Caches
```bash
cd /var/www/hakimyar-fusion
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### Run Migrations
```bash
cd /var/www/hakimyar-fusion
php artisan migrate --force
```

### Seed Database
```bash
cd /var/www/hakimyar-fusion
php artisan db:seed --force
```

---

## Troubleshooting

### 500 Internal Server Error
```bash
# Check Laravel logs
tail -f /var/www/hakimyar-fusion/storage/logs/laravel.log

# Fix permissions
cd /var/www/hakimyar-fusion
sudo chown -R www-data:www-data .
sudo chmod -R 755 .
sudo chmod -R 775 storage bootstrap/cache
```

### 502 Bad Gateway
```bash
# Check if PHP-FPM is running
sudo systemctl status php8.3-fpm

# Restart PHP-FPM
sudo systemctl restart php8.3-fpm
```

### Database Connection Error
```bash
# Check PostgreSQL is running
sudo systemctl status postgresql

# Test database connection
cd /var/www/hakimyar-fusion
php artisan tinker
>>> DB::connection()->getPdo();
```

### Permission Denied Errors
```bash
cd /var/www/hakimyar-fusion
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

---

## SSL Setup (Let's Encrypt)

```bash
# Install Certbot
sudo apt install -y certbot python3-certbot-nginx

# Obtain certificate
sudo certbot --nginx -d your-domain.com -d www.your-domain.com

# Test auto-renewal
sudo certbot renew --dry-run
```

---

## Backup and Restore

### Create Backup
```bash
/usr/local/bin/backup-hakimyar.sh
```

### Restore Database
```bash
PGPASSWORD="your_password" psql -U hakimyar_user -h localhost hakimyar_fusion < /var/backups/hakimyar-fusion/db_TIMESTAMP.sql
```

### Restore Files
```bash
tar -xzf /var/backups/hakimyar-fusion/files_TIMESTAMP.tar.gz -C /
```

---

## Performance Optimization

### Enable OPcache
```bash
sudo nano /etc/php/8.3/fpm/php.ini
```
Add/Update:
```ini
opcache.enable=1
opcache.memory_consumption=256
opcache.interned_strings_buffer=16
opcache.max_accelerated_files=10000
opcache.revalidate_freq=2
```

### Enable Redis (Optional)
```bash
sudo apt install -y redis-server
sudo systemctl enable redis-server
sudo systemctl start redis-server
```

Update `.env`:
```env
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis
```

---

## Security Checklist

- [ ] Change default SSH port
- [ ] Disable root SSH login
- [ ] Setup SSH key authentication
- [ ] Configure firewall (UFW)
- [ ] Install Fail2Ban
- [ ] Setup SSL certificate
- [ ] Use strong database passwords
- [ ] Keep system updated
- [ ] Setup automated backups
- [ ] Enable Laravel's security features
- [ ] Configure rate limiting

---

## Monitoring

### Check Service Status
```bash
sudo systemctl status nginx
sudo systemctl status php8.3-fpm
sudo systemctl status postgresql
```

### Check Disk Space
```bash
df -h
```

### Check Memory Usage
```bash
free -h
```

### Check CPU Usage
```bash
top
```

### Check Open Connections
```bash
netstat -tuln
```

---

## Important File Locations

- **Application**: `/var/www/hakimyar-fusion`
- **Nginx Config**: `/etc/nginx/sites-available/hakimyar-fusion`
- **PHP Config**: `/etc/php/8.3/fpm/php.ini`
- **Laravel Logs**: `/var/www/hakimyar-fusion/storage/logs/laravel.log`
- **Nginx Logs**: `/var/log/nginx/`
- **Backups**: `/var/backups/hakimyar-fusion/`

---

## Need Help?

- Check `DEPLOYMENT.md` for detailed instructions
- Review Laravel logs: `storage/logs/laravel.log`
- Check Nginx logs: `/var/log/nginx/error.log`
- Laravel Documentation: https://laravel.com/docs
- PostgreSQL Documentation: https://www.postgresql.org/docs/


