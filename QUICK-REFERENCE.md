# Laraend Quick Reference

## Connection

```bash
# SSH to VPS
ssh deploy@5.182.44.108

# Navigate to app
cd /var/www/laraend
```

## Deployment

```bash
# From your Mac - Upload and deploy
./upload-to-server.sh

# On VPS - Deploy changes
cd /var/www/laraend
./deploy.sh
```

## Service Management

```bash
# Restart all services
sudo systemctl restart nginx php8.3-fpm postgresql redis-server
sudo supervisorctl restart laraend-worker:*

# Check service status
sudo systemctl status nginx
sudo systemctl status php8.3-fpm
sudo systemctl status postgresql
sudo systemctl status redis-server
sudo supervisorctl status

# Reload Nginx (after config changes)
sudo nginx -t && sudo systemctl reload nginx
```

## Laravel Artisan

```bash
cd /var/www/laraend

# Maintenance mode
php artisan down
php artisan up

# Clear caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Rebuild caches
php artisan config:cache
php artisan route:cache

# Database
php artisan migrate --force
php artisan migrate:rollback --force
php artisan db:seed --force

# Queue
php artisan queue:work --once
php artisan queue:failed
php artisan queue:retry all
```

## Logs

```bash
# Laravel application logs
tail -f /var/www/laraend/storage/logs/laravel.log

# Nginx access logs
tail -f /var/log/nginx/laraend-access.log

# Nginx error logs
tail -f /var/log/nginx/laraend-error.log

# Queue worker logs
tail -f /var/www/laraend/storage/logs/worker.log

# PHP-FPM logs
tail -f /var/log/php8.3-fpm.log

# System logs
journalctl -u nginx -f
journalctl -u php8.3-fpm -f
```

## Database

```bash
# Connect to PostgreSQL
sudo -u postgres psql -U laraend_user -d laraend_db

# Backup database
export PGPASSWORD='LaraEnd2025!SecurePass'
pg_dump -U laraend_user -h localhost laraend_db > backup.sql

# Restore database
sudo -u postgres psql laraend_db < backup.sql

# PostgreSQL commands (in psql)
\l              # List databases
\c laraend_db   # Connect to database
\dt             # List tables
\d users        # Describe table
\q              # Quit
```

## Permissions

```bash
cd /var/www/laraend

# Fix ownership
sudo chown -R deploy:www-data .

# Fix permissions
sudo find . -type d -exec chmod 755 {} \;
sudo find . -type f -exec chmod 644 {} \;
sudo chmod -R 775 storage bootstrap/cache
```

## SSL Certificate

```bash
# Check certificate status
sudo certbot certificates

# Renew certificate
sudo certbot renew

# Test renewal
sudo certbot renew --dry-run
```

## Monitoring

```bash
# Disk space
df -h

# Memory usage
free -m

# CPU usage
top
htop  # if installed

# Active connections
ss -tuln | grep :80
ss -tuln | grep :443

# PHP processes
ps aux | grep php

# Nginx processes
ps aux | grep nginx
```

## Firewall

```bash
# Check firewall status
sudo ufw status

# Allow port
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp

# Deny port
sudo ufw deny 8080/tcp

# Enable/disable firewall
sudo ufw enable
sudo ufw disable
```

## Backup

```bash
# Run database backup
/home/deploy/backups/backup-db.sh

# Run application backup
/home/deploy/backups/backup-app.sh

# List backups
ls -lh /home/deploy/backups/database/
ls -lh /home/deploy/backups/application/
```

## Testing

```bash
# Test API health
curl https://api.ithdp.ir

# Test with headers
curl -I https://api.ithdp.ir

# Test login endpoint
curl -X POST https://api.ithdp.ir/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com","password":"password"}'

# Test CORS
curl -H "Origin: https://ithdp.ir" \
     -H "Access-Control-Request-Method: POST" \
     -X OPTIONS https://api.ithdp.ir/api/auth/login -v
```

## Composer

```bash
cd /var/www/laraend

# Install dependencies
composer install --no-dev --optimize-autoloader

# Update dependencies
composer update --no-dev

# Dump autoload
composer dump-autoload --optimize
```

## Git (if using)

```bash
cd /var/www/laraend

# Pull latest changes
git pull origin main

# Check status
git status

# View recent commits
git log --oneline -10
```

## Supervisor (Queue Workers)

```bash
# Check status
sudo supervisorctl status

# Start workers
sudo supervisorctl start laraend-worker:*

# Stop workers
sudo supervisorctl stop laraend-worker:*

# Restart workers
sudo supervisorctl restart laraend-worker:*

# Reload configuration
sudo supervisorctl reread
sudo supervisorctl update

# View logs
tail -f /var/www/laraend/storage/logs/worker.log
```

## Cron Jobs

```bash
# Edit crontab
crontab -e

# List cron jobs
crontab -l

# View cron logs
grep CRON /var/log/syslog
```

## Emergency Procedures

### Application Down
```bash
cd /var/www/laraend
php artisan down
# Fix issue
php artisan up
```

### Rollback Deployment
```bash
# Restore from backup
cd /home/deploy/backups/application
ls -lt | head -5  # Find recent backup
tar -xzf laraend_YYYYMMDD_HHMMSS.tar.gz -C /var/www

# Restore database
cd /home/deploy/backups/database
ls -lt | head -5  # Find recent backup
gunzip laraend_db_YYYYMMDD_HHMMSS.sql.gz
sudo -u postgres psql laraend_db < laraend_db_YYYYMMDD_HHMMSS.sql

# Restart services
sudo systemctl restart php8.3-fpm
sudo supervisorctl restart laraend-worker:*
```

### Clear All Caches (Nuclear Option)
```bash
cd /var/www/laraend
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan optimize:clear
composer dump-autoload --optimize
sudo systemctl restart php8.3-fpm
sudo supervisorctl restart laraend-worker:*
```

## Configuration Files

```bash
# Nginx site config
sudo nano /etc/nginx/sites-available/laraend

# PHP-FPM pool config
sudo nano /etc/php/8.3/fpm/pool.d/www.conf

# PHP configuration
sudo nano /etc/php/8.3/fpm/php.ini

# Supervisor worker config
sudo nano /etc/supervisor/conf.d/laraend-worker.conf

# Laravel environment
nano /var/www/laraend/.env

# PostgreSQL config
sudo nano /etc/postgresql/16/main/postgresql.conf
sudo nano /etc/postgresql/16/main/pg_hba.conf

# Redis config
sudo nano /etc/redis/redis.conf
```

## URLs

- **API**: https://api.ithdp.ir
- **Frontend**: https://ithdp.ir
- **VPS IP**: 5.182.44.108

## Credentials

- **VPS User**: deploy
- **Database**: laraend_db
- **DB User**: laraend_user
- **DB Password**: LaraEnd2025!SecurePass

---

**Tip**: Bookmark this file for quick access to common commands!

