# Laraend Deployment Summary

## What Has Been Created

I've prepared comprehensive deployment documentation and helper scripts for deploying your Laravel backend to Ubuntu 24.04 VPS.

### Documentation Files

1. **laraend.md** - Complete deployment guide (15 steps)
   - Server setup and configuration
   - Software installation (PHP 8.4, PostgreSQL 16, Redis, Nginx)
   - SSL configuration with Let's Encrypt
   - Queue workers with Supervisor
   - Security hardening
   - Backup strategies
   - Troubleshooting guide
   - Database schema documentation
   - API endpoints overview

2. **README.md** - Updated project README
   - Project overview
   - Quick start guide
   - Links to full documentation

3. **DEPLOYMENT-CHECKLIST.md** - Step-by-step checklist
   - Pre-deployment checks
   - Server setup verification
   - Post-deployment testing
   - Rollback plan

4. **.env.example** - Production environment template
   - PostgreSQL configuration
   - Redis settings
   - JWT configuration
   - CORS settings for frontend

### Helper Scripts

1. **upload-to-server.sh** (Run on your Mac)
   - Uploads code from local to VPS
   - Excludes unnecessary files (vendor, node_modules, .env)
   - Interactive deployment option
   - Usage: `./upload-to-server.sh`

2. **deploy.sh** (Run on VPS)
   - Automates deployment process
   - Creates backups before deployment
   - Runs migrations
   - Clears and rebuilds caches
   - Restarts services
   - Health check verification
   - Usage: `./deploy.sh`

## Deployment Configuration

### Server Details
- **VPS IP**: 5.182.44.108
- **OS**: Ubuntu 24.04 LTS
- **Domain**: api.ithdp.ir (subdomain for API)
- **User**: deploy
- **App Path**: /var/www/laraend

### Software Stack
- **Web Server**: Nginx
- **PHP**: 8.3 with FPM
- **Database**: PostgreSQL 16
- **Cache/Queue**: Redis (production) / Database (development)
- **Process Manager**: Supervisor
- **SSL**: Let's Encrypt (Certbot)

### Database Configuration
- **Database Name**: laraend_db
- **Username**: laraend_user
- **Password**: LaraEnd2025!SecurePass (change if needed)

### Development vs Production Differences
Your development environment uses:
- Database-driven sessions, cache, and queue
- Debug mode enabled
- Local URLs (localhost:8000 for API, localhost:5173 for frontend)

Production environment uses:
- Redis for sessions, cache, and queue (better performance)
- Debug mode disabled
- Production URLs (api.ithdp.ir for API, ithdp.ir for frontend)
- Enhanced security settings (session encryption, secure cookies)

## Deployment Steps Overview

### Initial Deployment (First Time)

1. **Prepare VPS** (Steps 1-2 in laraend.md)
   - Update system
   - Create deploy user
   - Install all required software

2. **Configure Services** (Steps 3-7)
   - Setup PostgreSQL database
   - Configure Nginx
   - Configure PHP-FPM
   - Setup SSL certificate
   - Configure queue workers

3. **Deploy Application** (Step 4)
   - Upload files to server
   - Install dependencies
   - Configure .env
   - Run migrations
   - Set permissions

4. **Test & Monitor** (Steps 14-15)
   - Test API endpoints
   - Verify services
   - Monitor logs

### Subsequent Deployments

**Option 1: Using Helper Scripts (Recommended)**
```bash
# On your Mac
./upload-to-server.sh
# This will upload files and optionally run deployment
```

**Option 2: Manual Process**
```bash
# On your Mac - Upload files
rsync -avz --exclude='vendor' --exclude='node_modules' \
    --exclude='.env' ./ deploy@5.182.44.108:/var/www/laraend/

# SSH to server
ssh deploy@5.182.44.108

# Run deployment script
cd /var/www/laraend
./deploy.sh
```

## Important DNS Configuration

Before SSL setup, you MUST configure DNS:

1. **Access your DirectAdmin panel** (or DNS provider)
2. **Add A Record**:
   - Type: A
   - Name: api
   - Value: 5.182.44.108
   - TTL: 3600 (or default)

3. **Verify DNS propagation**:
   ```bash
   dig api.ithdp.ir
   # or
   nslookup api.ithdp.ir
   ```

4. **Wait for propagation** (can take 5 minutes to 48 hours)

5. **Then proceed with SSL certificate** (Step 7 in laraend.md)

## Frontend & Mobile App Configuration

### Frontend (Vueend)
Configure API base URL in your Vue.js app:
```javascript
// In your axios or API configuration
const API_BASE_URL = 'https://api.ithdp.ir'
```

### Mobile App (HekmatSara)
Configure API endpoint in your Flutter app:
```dart
// In your API configuration
const String apiBaseUrl = 'https://api.ithdp.ir';
```

### CORS Configuration
Already configured in Laravel to accept requests from:
- `https://ithdp.ir` (Frontend)
- Configured via `FRONTEND_URL` in .env

## Security Checklist

- [ ] Strong database password set
- [ ] `.env` file secured (600 permissions)
- [ ] `APP_DEBUG=false` in production
- [ ] Firewall (UFW) enabled
- [ ] SSL certificate installed
- [ ] Redis password set (optional but recommended)
- [ ] Regular backups scheduled
- [ ] Log monitoring set up

## Backup Strategy

### Automated Backups
- **Database**: Daily at 2 AM (7-day retention)
- **Application**: Weekly on Sunday at 3 AM (7-day retention)
- **Location**: `/home/deploy/backups/`

### Manual Backup
```bash
# Database
/home/deploy/backups/backup-db.sh

# Application
/home/deploy/backups/backup-app.sh
```

## Monitoring

### Log Locations
- **Laravel**: `/var/www/laraend/storage/logs/laravel.log`
- **Nginx Access**: `/var/log/nginx/laraend-access.log`
- **Nginx Error**: `/var/log/nginx/laraend-error.log`
- **Queue Workers**: `/var/www/laraend/storage/logs/worker.log`

### Quick Commands
```bash
# View Laravel logs
tail -f /var/www/laraend/storage/logs/laravel.log

# Check all services
sudo systemctl status nginx php8.4-fpm postgresql redis-server
sudo supervisorctl status

# Restart all services
sudo systemctl restart nginx php8.4-fpm
sudo supervisorctl restart laraend-worker:*
```

## Troubleshooting

### Common Issues

**502 Bad Gateway**
- Check PHP-FPM: `sudo systemctl status php8.4-fpm`
- Restart: `sudo systemctl restart php8.4-fpm`

**Database Connection Failed**
- Verify PostgreSQL running: `sudo systemctl status postgresql`
- Check credentials in `.env`
- Test connection: `sudo -u postgres psql -U laraend_user -d laraend_db`

**Permission Denied**
```bash
cd /var/www/laraend
sudo chown -R deploy:www-data .
sudo chmod -R 755 .
sudo chmod -R 775 storage bootstrap/cache
```

**Queue Jobs Not Processing**
```bash
sudo supervisorctl restart laraend-worker:*
tail -f /var/www/laraend/storage/logs/worker.log
```

## Next Steps

1. **Configure DNS** for api.ithdp.ir → 5.182.44.108
2. **Wait for DNS propagation**
3. **Follow laraend.md** step by step for initial deployment
4. **Test API endpoints** after deployment
5. **Configure frontend and mobile app** with API URL
6. **Set up monitoring** and alerts
7. **Document any custom configurations**

## Support Resources

- **Full Documentation**: See `laraend.md`
- **Deployment Checklist**: See `DEPLOYMENT-CHECKLIST.md`
- **Laravel Documentation**: https://laravel.com/docs
- **Nginx Documentation**: https://nginx.org/en/docs/
- **PostgreSQL Documentation**: https://www.postgresql.org/docs/

## Questions to Consider

Before deployment, ensure you have:
- [ ] SMS-IR API credentials (for OTP functionality)
- [ ] Mail server configuration (if sending emails)
- [ ] Any external API keys your app uses
- [ ] SSL certificate email for Let's Encrypt notifications
- [ ] Backup storage plan (local vs cloud)

---

**Created**: October 10, 2025  
**For**: HakimyarFusion LMS - Laraend Backend  
**Deployment Target**: Ubuntu 24.04 VPS (5.182.44.108)

