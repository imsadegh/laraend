# Laraend Deployment Checklist

Use this checklist to ensure all deployment steps are completed successfully.

## Pre-Deployment

- [ ] VPS is accessible via SSH (5.182.44.108)
- [ ] Domain DNS is configured (api.ithdp.ir → 5.182.44.108)
- [ ] DNS propagation verified (`dig api.ithdp.ir`)
- [ ] All local changes committed and tested
- [ ] Database credentials decided
- [ ] SMS-IR API credentials ready

## Server Setup

- [ ] System updated (`apt update && apt upgrade`)
- [ ] Timezone set to Asia/Tehran
- [ ] Deploy user created and configured
- [ ] PHP 8.4 installed with all extensions
- [ ] PostgreSQL 16 installed and running
- [ ] Redis installed and running
- [ ] Nginx installed and running
- [ ] Composer installed globally
- [ ] Supervisor installed
- [ ] Self-signed SSL certificate generated

## Database Configuration

- [ ] PostgreSQL database created (`laraend_db`)
- [ ] PostgreSQL user created (`laraend_user`)
- [ ] Database privileges granted
- [ ] Database connection tested

## Application Setup

- [ ] Application directory created (`/var/www/laraend`)
- [ ] Files uploaded to server
- [ ] Composer dependencies installed
- [ ] `.env` file created and configured
- [ ] Application key generated
- [ ] JWT secret generated
- [ ] File permissions set correctly
- [ ] Storage directories writable
- [ ] Migrations run successfully
- [ ] Seeders run (if needed)

## Web Server Configuration

- [ ] Nginx site configuration created
- [ ] Nginx configuration tested (`nginx -t`)
- [ ] Nginx restarted
- [ ] PHP-FPM pool configured
- [ ] PHP-FPM settings optimized
- [ ] PHP-FPM restarted

## SSL Configuration

- [ ] DNS verified pointing to VPS (Cloudflare)
- [ ] Self-signed SSL certificate generated
- [ ] Cloudflare SSL/TLS mode set to "Full"
- [ ] HTTPS working correctly
- [ ] HTTP redirects to HTTPS
- [ ] Cloudflare proxy enabled (orange cloud)

## Queue & Scheduler

- [ ] Supervisor configuration created
- [ ] Queue workers started
- [ ] Queue workers status verified
- [ ] Cron job for scheduler added
- [ ] Cron job tested

## Security

- [ ] UFW firewall enabled
- [ ] Firewall rules configured (22, 80, 443)
- [ ] Redis password set (if needed)
- [ ] PostgreSQL secured (localhost only)
- [ ] `.env` file permissions secured (600)
- [ ] Debug mode disabled in production

## Monitoring & Backups

- [ ] Database backup script created
- [ ] Application backup script created
- [ ] Backup cron jobs scheduled
- [ ] Backup scripts tested
- [ ] Log rotation configured

## Testing

- [ ] API health check endpoint tested
- [ ] Authentication endpoints tested
- [ ] CORS configuration verified
- [ ] All services status checked
- [ ] Logs reviewed for errors
- [ ] Queue jobs processing verified
- [ ] SSL working via Cloudflare
- [ ] API responds with {"Laravel":"12.33.0"}

## Post-Deployment

- [ ] Application caches cleared and rebuilt
- [ ] All services restarted
- [ ] Frontend configured with API URL
- [ ] Mobile app configured with API URL
- [ ] Performance monitoring set up
- [ ] Documentation updated
- [ ] Team notified of deployment

## Rollback Plan

- [ ] Database backup taken before deployment
- [ ] Application backup available
- [ ] Rollback procedure documented
- [ ] Rollback tested (in staging if available)

## Notes

**VPS IP**: 5.182.44.108  
**Domain**: api.ithdp.ir  
**Database**: laraend_db  
**DB User**: laraend_user  
**Deploy User**: deploy  
**App Path**: /var/www/laraend  

**Important Commands**:
```bash
# SSH into server
ssh deploy@5.182.44.108

# Navigate to project
cd /var/www/laraend

# Restart all services
sudo systemctl restart nginx php8.4-fpm postgresql redis-server
sudo supervisorctl restart laraend-worker:*

# View logs
tail -f storage/logs/laravel.log
tail -f /var/log/nginx/laraend-error.log

# Clear caches
php artisan optimize:clear
php artisan optimize
```

---

**Deployment Date**: _____________  
**Deployed By**: _____________  
**Version**: _____________  

