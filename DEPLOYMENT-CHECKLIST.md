# Deployment Checklist

Use this checklist to ensure a smooth deployment process.

---

## Pre-Deployment Checklist

### Local Machine (macOS)
- [ ] All code changes committed and tested
- [ ] `.env` file configured correctly for local development
- [ ] Database migrations tested locally
- [ ] All dependencies in `composer.json` are correct
- [ ] Application runs without errors locally
- [ ] PHP version compatibility checked (requires PHP 8.2+)

### VPS Information Gathered
- [ ] VPS IP address: `___________________`
- [ ] SSH access confirmed
- [ ] Root or sudo access available
- [ ] Domain name (if applicable): `___________________`

### Database Planning
- [ ] Database name decided: `___________________`
- [ ] Database username decided: `___________________`
- [ ] Strong database password generated: `___________________`

---

## Initial Server Setup Checklist

- [ ] SSH into VPS successful
- [ ] System packages updated (`apt update && apt upgrade`)
- [ ] PHP 8.3 installed with all required extensions
- [ ] PostgreSQL installed and running
- [ ] Database and user created
- [ ] Composer installed globally
- [ ] Nginx installed and running
- [ ] Application directory created (`/var/www/hakimyar-fusion`)
- [ ] Firewall configured (UFW)
- [ ] Nginx configuration created and enabled
- [ ] PHP-FPM configured and running

**Quick Command:**
```bash
./deploy-from-local.sh YOUR_VPS_IP setup
```

---

## Application Deployment Checklist

- [ ] Application files uploaded to VPS
- [ ] `.env` file created and configured on server
- [ ] Composer dependencies installed (`composer install --no-dev`)
- [ ] Application key generated (`php artisan key:generate`)
- [ ] JWT secret generated (`php artisan jwt:secret`)
- [ ] File permissions set correctly
  - [ ] `chown -R www-data:www-data /var/www/hakimyar-fusion`
  - [ ] `chmod -R 755 /var/www/hakimyar-fusion`
  - [ ] `chmod -R 775 storage bootstrap/cache`
- [ ] Database migrations run (`php artisan migrate --force`)
- [ ] Database seeded (if needed) (`php artisan db:seed --force`)
- [ ] Configuration cached
  - [ ] `php artisan config:cache`
  - [ ] `php artisan route:cache`
  - [ ] `php artisan view:cache`
- [ ] Services restarted (Nginx, PHP-FPM)
- [ ] Application accessible via browser

**Quick Command:**
```bash
./deploy-from-local.sh YOUR_VPS_IP deploy
```

---

## Post-Deployment Checklist

### Testing
- [ ] Homepage loads without errors
- [ ] API endpoints responding correctly
- [ ] Database connections working
- [ ] File uploads working (if applicable)
- [ ] Authentication working (login/register)
- [ ] JWT tokens generating correctly

### Security
- [ ] `.env` file permissions set to 600
- [ ] Debug mode disabled (`APP_DEBUG=false`)
- [ ] Strong passwords used for database
- [ ] SSH key authentication setup (recommended)
- [ ] Default SSH port changed (optional but recommended)
- [ ] Fail2Ban installed (optional)
- [ ] SSL certificate installed (if using domain)

### Monitoring & Maintenance
- [ ] Backup script created and tested
- [ ] Cron job for backups configured
- [ ] Laravel scheduler cron job added (if using)
- [ ] Log rotation configured
- [ ] Monitoring tools setup (optional)
- [ ] Error tracking configured (optional - Sentry, Bugsnag)

### Performance
- [ ] OPcache enabled
- [ ] Redis installed (optional)
- [ ] Queue workers setup (if using queues)
- [ ] CDN configured (optional)

---

## SSL Certificate Setup (Optional but Recommended)

- [ ] Domain DNS pointing to VPS IP
- [ ] Certbot installed
- [ ] SSL certificate obtained
- [ ] Auto-renewal tested
- [ ] HTTPS redirect configured
- [ ] Application URL updated in `.env` (https://)

**Commands:**
```bash
sudo apt install -y certbot python3-certbot-nginx
sudo certbot --nginx -d your-domain.com -d www.your-domain.com
sudo certbot renew --dry-run
```

---

## Queue Workers Setup (If Using Queues)

- [ ] Supervisor installed
- [ ] Worker configuration created
- [ ] Workers started and running
- [ ] Worker logs accessible

**Configuration File:** `/etc/supervisor/conf.d/hakimyar-fusion-worker.conf`

---

## Environment Variables Checklist

Ensure these are properly configured in `.env` on the server:

### Application
- [ ] `APP_NAME` - Set to your app name
- [ ] `APP_ENV` - Set to `production`
- [ ] `APP_KEY` - Generated (not empty)
- [ ] `APP_DEBUG` - Set to `false`
- [ ] `APP_URL` - Set to your domain or IP

### Database
- [ ] `DB_CONNECTION` - Set to `pgsql`
- [ ] `DB_HOST` - Set to `127.0.0.1`
- [ ] `DB_PORT` - Set to `5432`
- [ ] `DB_DATABASE` - Your database name
- [ ] `DB_USERNAME` - Your database user
- [ ] `DB_PASSWORD` - Your database password

### JWT
- [ ] `JWT_SECRET` - Generated (not empty)
- [ ] `JWT_TTL` - Set appropriately (default: 60)
- [ ] `JWT_REFRESH_TTL` - Set appropriately (default: 20160)

### SMS (If Using)
- [ ] `SMSIR_API_KEY` - Your SMS API key

### Cache & Session
- [ ] `CACHE_DRIVER` - Set appropriately
- [ ] `SESSION_DRIVER` - Set appropriately
- [ ] `QUEUE_CONNECTION` - Set appropriately

---

## Backup & Recovery Checklist

### Backup Setup
- [ ] Backup script created (`/usr/local/bin/backup-hakimyar.sh`)
- [ ] Backup directory created (`/var/backups/hakimyar-fusion`)
- [ ] Backup cron job configured
- [ ] Test backup created and verified
- [ ] Backup retention policy set (7 days default)

### Recovery Testing
- [ ] Database restore tested
- [ ] File restore tested
- [ ] Recovery procedure documented

---

## Update/Maintenance Checklist

Use this checklist when updating your application:

- [ ] Backup created before update
- [ ] Maintenance mode enabled (`php artisan down`)
- [ ] Latest code pulled/uploaded
- [ ] Dependencies updated (`composer install`)
- [ ] Migrations run (`php artisan migrate --force`)
- [ ] Caches cleared and rebuilt
- [ ] Services restarted
- [ ] Maintenance mode disabled (`php artisan up`)
- [ ] Application tested
- [ ] Logs checked for errors

**Quick Command:**
```bash
./deploy-from-local.sh YOUR_VPS_IP update
```

---

## Troubleshooting Checklist

If something goes wrong:

- [ ] Check Laravel logs: `tail -f storage/logs/laravel.log`
- [ ] Check Nginx error logs: `tail -f /var/log/nginx/error.log`
- [ ] Check PHP-FPM logs: `tail -f /var/log/php8.3-fpm.log`
- [ ] Verify services are running:
  - [ ] `systemctl status nginx`
  - [ ] `systemctl status php8.3-fpm`
  - [ ] `systemctl status postgresql`
- [ ] Check file permissions
- [ ] Verify database connection
- [ ] Clear all caches
- [ ] Check `.env` configuration

---

## Important Commands Reference

### Service Management
```bash
sudo systemctl restart nginx
sudo systemctl restart php8.3-fpm
sudo systemctl restart postgresql
```

### Cache Management
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### Logs
```bash
tail -f storage/logs/laravel.log
tail -f /var/log/nginx/error.log
```

### Permissions
```bash
sudo chown -R www-data:www-data /var/www/hakimyar-fusion
sudo chmod -R 755 /var/www/hakimyar-fusion
sudo chmod -R 775 /var/www/hakimyar-fusion/storage
sudo chmod -R 775 /var/www/hakimyar-fusion/bootstrap/cache
```

---

## Contact & Support

- **Laravel Documentation**: https://laravel.com/docs
- **PostgreSQL Documentation**: https://www.postgresql.org/docs/
- **Nginx Documentation**: https://nginx.org/en/docs/

---

## Notes

Use this space to document any custom configurations or important information:

```
_______________________________________________________________________________

_______________________________________________________________________________

_______________________________________________________________________________

_______________________________________________________________________________
```

---

**Deployment Date**: ___________________
**Deployed By**: ___________________
**VPS IP**: ___________________
**Domain**: ___________________


