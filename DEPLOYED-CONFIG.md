# Laraend - Deployed Configuration

## ✅ Successfully Deployed!

Your Laravel backend is now live and responding at: **https://api.ithdp.ir**

```bash
curl https://api.ithdp.ir
# Response: {"Laravel":"12.33.0"}
```

---

## Actual Deployment Configuration

### Server Details
- **VPS IP**: 5.182.44.108
- **Domain**: api.ithdp.ir
- **OS**: Ubuntu 24.04 LTS
- **Deploy User**: deploy
- **App Path**: /var/www/laraend

### Software Versions
- **PHP**: 8.4
- **PostgreSQL**: 16
- **Nginx**: Latest
- **Redis**: Latest
- **Composer**: 2.8.12
- **Laravel**: 12.33.0

### SSL Configuration
**Method**: Cloudflare + Self-signed Certificate

**How it works**:
1. **Client → Cloudflare**: Full SSL/TLS (Cloudflare's certificate)
2. **Cloudflare → Server**: Encrypted with self-signed certificate
3. **Cloudflare Settings**: SSL/TLS mode set to "Full"

**Self-signed Certificate Commands** (Already done):
```bash
sudo openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
  -keyout /etc/ssl/private/nginx-selfsigned.key \
  -out /etc/ssl/certs/nginx-selfsigned.crt \
  -subj "/C=IR/ST=Tehran/L=Tehran/O=Laraend/CN=api.ithdp.ir"

sudo chmod 600 /etc/ssl/private/nginx-selfsigned.key
sudo chmod 644 /etc/ssl/certs/nginx-selfsigned.crt
```

### Nginx Configuration
**File**: `/etc/nginx/sites-available/laraend`

**Key Settings**:
- HTTP (port 80) → Redirects to HTTPS
- HTTPS (port 443) → Self-signed SSL cert
- PHP-FPM socket: `/var/run/php/php8.4-fpm.sock`
- Max upload size: 100MB
- Gzip compression: Enabled
- Security headers: Enabled

### Database Configuration
- **Database Name**: laraend_db
- **Username**: laraend_user
- **Password**: LaraEnd2025!SecurePass
- **Host**: localhost (127.0.0.1)
- **Port**: 5432

### Performance Configuration
- **Sessions**: Redis
- **Cache**: Redis
- **Queue**: Redis
- **Queue Workers**: 2 (managed by Supervisor)

---

## Working Nginx Configuration

```nginx
# HTTP - Redirect to HTTPS
server {
    listen 80;
    listen [::]:80;
    server_name api.ithdp.ir;

    return 301 https://$server_name$request_uri;
}

# HTTPS (Cloudflare handles SSL)
server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name api.ithdp.ir;

    root /var/www/laraend/public;
    index index.php index.html;

    # Self-signed SSL certificate (Cloudflare handles the real SSL)
    ssl_certificate /etc/ssl/certs/nginx-selfsigned.crt;
    ssl_certificate_key /etc/ssl/private/nginx-selfsigned.key;
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

---

## Deployment Process Used

### Initial Setup
1. ✅ Installed PHP 8.4 with extensions
2. ✅ Installed PostgreSQL 16
3. ✅ Installed Redis
4. ✅ Installed Nginx
5. ✅ Generated self-signed SSL certificate
6. ✅ Configured Nginx with custom configuration
7. ✅ Set up database and user
8. ✅ Deployed Laravel application
9. ✅ Configured Cloudflare DNS and SSL

### Cloudflare Configuration
**DNS Settings**:
- Record Type: A
- Name: api
- Content: 5.182.44.108
- Proxy status: Proxied (Orange cloud enabled)

**SSL/TLS Settings**:
- Encryption mode: Full
- Always Use HTTPS: Enabled
- Automatic HTTPS Rewrites: Enabled
- Minimum TLS Version: 1.2

---

## Daily Operations

### SSH Access
```bash
ssh deploy@5.182.44.108
cd /var/www/laraend
```

### Restart Services
```bash
sudo systemctl restart nginx php8.4-fpm postgresql redis-server
sudo supervisorctl restart laraend-worker:*
```

### View Logs
```bash
# Laravel application logs
tail -f /var/www/laraend/storage/logs/laravel.log

# Nginx error logs
tail -f /var/log/nginx/laraend-error.log

# PHP-FPM logs
tail -f /var/log/php8.4-fpm.log

# Queue worker logs
tail -f /var/www/laraend/storage/logs/worker.log
```

### Check Service Status
```bash
sudo systemctl status nginx
sudo systemctl status php8.4-fpm
sudo systemctl status postgresql
sudo systemctl status redis-server
sudo supervisorctl status
```

### Laravel Maintenance
```bash
cd /var/www/laraend

# Put in maintenance mode
php artisan down

# Clear caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear

# Rebuild caches
php artisan config:cache
php artisan route:cache

# Bring back online
php artisan up
```

---

## Deployment Workflow

### For Future Updates

**From your Mac**:
```bash
# Method 1: Using helper script
./upload-to-server.sh

# Method 2: Manual rsync
rsync -avz --exclude='vendor' --exclude='node_modules' \
    --exclude='.env' \
    ./ deploy@5.182.44.108:/var/www/laraend/
```

**On the server**:
```bash
ssh deploy@5.182.44.108
cd /var/www/laraend
./deploy.sh
```

The `deploy.sh` script will:
1. Create a backup
2. Enable maintenance mode
3. Install dependencies
4. Run migrations
5. Clear and rebuild caches
6. Restart services
7. Disable maintenance mode
8. Run health checks

---

## Testing Endpoints

### Health Check
```bash
curl https://api.ithdp.ir
# Response: {"Laravel":"12.33.0"}
```

### Test with Headers
```bash
curl -I https://api.ithdp.ir
# Should return HTTP/2 200
# With Cloudflare headers: cf-ray, cf-cache-status
```

### Test Authentication
```bash
curl -X POST https://api.ithdp.ir/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com","password":"password"}'
```

### Test CORS
```bash
curl -H "Origin: https://ithdp.ir" \
     -H "Access-Control-Request-Method: POST" \
     -X OPTIONS https://api.ithdp.ir/api/auth/login -v
```

---

## Environment Variables

### Production .env Highlights
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://api.ithdp.ir
FRONTEND_URL=https://ithdp.ir

DB_CONNECTION=pgsql
DB_DATABASE=laraend_db
DB_USERNAME=laraend_user

SESSION_DRIVER=redis
QUEUE_CONNECTION=redis
CACHE_STORE=redis
```

---

## File Locations

### Application Files
- **Laravel Root**: `/var/www/laraend`
- **Public Directory**: `/var/www/laraend/public`
- **Environment File**: `/var/www/laraend/.env`
- **Storage**: `/var/www/laraend/storage`

### Configuration Files
- **Nginx**: `/etc/nginx/sites-available/laraend`
- **PHP-FPM Pool**: `/etc/php/8.4/fpm/pool.d/www.conf`
- **PHP Config**: `/etc/php/8.4/fpm/php.ini`
- **Supervisor**: `/etc/supervisor/conf.d/laraend-worker.conf`
- **SSL Certificate**: `/etc/ssl/certs/nginx-selfsigned.crt`
- **SSL Private Key**: `/etc/ssl/private/nginx-selfsigned.key`

### Log Files
- **Laravel**: `/var/www/laraend/storage/logs/laravel.log`
- **Nginx Access**: `/var/log/nginx/laraend-access.log`
- **Nginx Error**: `/var/log/nginx/laraend-error.log`
- **PHP-FPM**: `/var/log/php8.4-fpm.log`
- **Queue Workers**: `/var/www/laraend/storage/logs/worker.log`

### Backup Locations
- **Database Backups**: `/home/deploy/backups/database`
- **Application Backups**: `/home/deploy/backups/application`
- **Deployment Backups**: `/home/deploy/backups/deployments`

---

## Security Checklist

- ✅ APP_DEBUG=false in production
- ✅ Strong database password
- ✅ Firewall (UFW) enabled with proper rules
- ✅ HTTPS enforced via Cloudflare
- ✅ Self-signed certificate for Cloudflare-to-server encryption
- ✅ Security headers enabled in Nginx
- ✅ Hidden files access denied
- ✅ PHP version header hidden
- ✅ Session encryption enabled
- ✅ PostgreSQL localhost-only access
- ✅ Redis localhost-only access

### Recommended Next Steps
- [ ] Set Redis password (optional but recommended)
- [ ] Set up monitoring/alerting
- [ ] Configure backups to external storage
- [ ] Set up log rotation
- [ ] Configure fail2ban for SSH protection

---

## Performance Metrics

### Expected Performance
- **API Response Time**: < 100ms (cached)
- **Database Queries**: Optimized with indexes
- **Queue Processing**: Near real-time with Redis
- **Session Management**: Fast with Redis
- **Static Assets**: Cached for 1 year

### Optimization Applied
- ✅ OPcache enabled for PHP
- ✅ Redis for sessions, cache, and queue
- ✅ Gzip compression enabled
- ✅ Static asset caching (1 year)
- ✅ Composer autoloader optimized
- ✅ Laravel config/route caching

---

## Monitoring Commands

### Quick Health Check
```bash
# Check all services at once
sudo systemctl status nginx php8.4-fpm postgresql redis-server | grep Active

# Check supervisor workers
sudo supervisorctl status

# Check disk space
df -h

# Check memory
free -m

# Check recent errors
tail -20 /var/www/laraend/storage/logs/laravel.log
```

### Performance Monitoring
```bash
# Monitor PHP-FPM processes
ps aux | grep php-fpm

# Monitor Nginx connections
ss -tuln | grep :443

# Monitor database connections
sudo -u postgres psql -c "SELECT count(*) FROM pg_stat_activity;"

# Monitor Redis
redis-cli INFO stats
```

---

## Troubleshooting

### Common Issues

**502 Bad Gateway**
```bash
sudo systemctl restart php8.4-fpm
tail -f /var/log/php8.4-fpm.log
```

**500 Internal Server Error**
```bash
tail -f /var/www/laraend/storage/logs/laravel.log
php artisan config:clear
```

**Database Connection Error**
```bash
sudo systemctl status postgresql
sudo -u postgres psql -U laraend_user -d laraend_db
```

**Queue Jobs Not Processing**
```bash
sudo supervisorctl restart laraend-worker:*
tail -f /var/www/laraend/storage/logs/worker.log
```

---

## Success Indicators

- ✅ API responding at https://api.ithdp.ir
- ✅ Returns: `{"Laravel":"12.33.0"}`
- ✅ HTTP redirects to HTTPS
- ✅ Cloudflare proxy active
- ✅ All services running
- ✅ Queue workers processing
- ✅ No errors in logs

---

## Contact & Support

For issues or questions related to deployment:
- Refer to `laraend.md` for detailed documentation
- Check `QUICK-REFERENCE.md` for common commands
- Review `TROUBLESHOOTING.md` for solutions

**Your deployment is successful and production-ready!** 🎉

---

**Document Created**: October 11, 2025  
**Deployment Status**: ✅ Live and Running  
**API URL**: https://api.ithdp.ir  
**Laravel Version**: 12.33.0  
**PHP Version**: 8.4

