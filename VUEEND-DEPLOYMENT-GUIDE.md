# Vueend Frontend Deployment Guide

## Overview

This guide covers deploying your Vue.js frontend (Vueend) to the same VPS as your Laravel backend, connecting it to the API, and configuring SSL.

## Prerequisites

- VPS with Ubuntu 24.04 LTS
- VPS IP: 5.182.44.108
- SSH Port: 2222
- Backend API already deployed at: https://api.ithdp.ir
- Domain: ithdp.ir (for frontend)
- Node.js LTS installed on VPS

---

## Step 1: VPS Preparation

### 1.1 Connect to VPS
```bash
ssh deploy@5.182.44.108 -p 2222
```

### 1.2 Install Node.js LTS
```bash
# Install Node Version Manager (fnm - faster alternative to nvm)
curl -fsSL https://fnm.vercel.app/install | bash

# Reload shell or source the profile
source ~/.bashrc

# Install and use latest LTS Node.js
fnm install --lts
fnm use lts

# Verify installation
node --version
npm --version
```

**Alternative using nvm:**
```bash
# Install nvm
curl -o- https://raw.githubusercontent.com/nvm-sh/nvm/v0.39.0/install.sh | bash
source ~/.bashrc

# Install and use latest LTS
nvm install --lts
nvm use --lts
```

### 1.3 Create Frontend Directory
```bash
# Create application directory
sudo mkdir -p /var/www/vueend
sudo chown -R deploy:www-data /var/www/vueend
cd /var/www/vueend
```

---

## Step 2: DNS Configuration

### 2.1 Configure Root Domain
**In your DNS provider or Cloudflare:**

Add A record:
- **Type**: A
- **Name**: @ (or leave blank for root domain)
- **Value**: 5.182.44.108
- **TTL**: 3600 (or default)

**Also add www subdomain:**
- **Type**: A
- **Name**: www
- **Value**: 5.182.44.108
- **TTL**: 3600

### 2.2 Verify DNS Propagation
```bash
# Check DNS resolution
dig ithdp.ir
nslookup ithdp.ir

# Should return: 5.182.44.108
```

---

## Step 3: Frontend Configuration

### 3.1 Environment Configuration
**In your Vueend project, create `.env.production`:**

```env
# Production environment variables
VITE_API_BASE_URL=https://api.ithdp.ir
VITE_APP_NAME=ITHDP
VITE_APP_ENV=production
```

**Update your `vite.config.js` if needed:**
```javascript
import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'

export default defineConfig({
  plugins: [vue()],
  build: {
    outDir: 'dist',
    assetsDir: 'assets',
    sourcemap: false, // Disable sourcemaps in production
    rollupOptions: {
      output: {
        manualChunks: {
          vendor: ['vue', 'vue-router'],
          // Add other large dependencies here
        }
      }
    }
  },
  server: {
    host: true,
    port: 5173
  }
})
```

### 3.2 Upload Frontend Code
**From your local machine:**

```bash
# Navigate to your Vueend project directory
cd /path/to/your/vueend

# Upload files to VPS (exclude node_modules, .git, etc.)
rsync -avz --progress \
    --exclude='node_modules/' \
    --exclude='.git/' \
    --exclude='dist/' \
    --exclude='.env.local' \
    --exclude='.env.development' \
    ./ deploy@5.182.44.108:/var/www/vueend/
```

### 3.3 Install Dependencies and Build
**On the VPS:**

```bash
cd /var/www/vueend

# Install dependencies
npm ci --production=false

# Build for production
npm run build

# Verify build output
ls -la dist/
```

---

## Step 4: Nginx Configuration

### 4.1 Create Nginx Site Configuration
```bash
sudo nano /etc/nginx/sites-available/vueend
```

**Nginx configuration for Vue SPA:**
```nginx
# HTTP - Redirect to HTTPS
server {
    listen 80;
    listen [::]:80;
    server_name ithdp.ir www.ithdp.ir;

    # Redirect all HTTP to HTTPS
    return 301 https://$server_name$request_uri;
}

# HTTPS
server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name ithdp.ir www.ithdp.ir;

    root /var/www/vueend/dist;
    index index.html;

    # SSL Configuration (will be configured by Certbot)
    ssl_certificate /etc/letsencrypt/live/ithdp.ir/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/ithdp.ir/privkey.pem;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;
    ssl_prefer_server_ciphers on;

    # Security Headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "strict-origin-when-cross-origin" always;

    # Logging
    access_log /var/log/nginx/vueend-access.log;
    error_log /var/log/nginx/vueend-error.log;

    # Client body size (for file uploads if needed)
    client_max_body_size 20M;

    # Gzip Compression
    gzip on;
    gzip_vary on;
    gzip_min_length 1024;
    gzip_types text/plain text/css text/xml text/javascript 
               application/x-javascript application/xml+rss 
               application/json application/javascript 
               application/rss+xml application/atom+xml 
               image/svg+xml;

    # Cache static assets (hashed files from Vite)
    location ~* \.(js|css|png|jpg|jpeg|gif|svg|woff|woff2|ttf|eot|ico)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
        try_files $uri =404;
    }

    # Main SPA routing - serve index.html for all routes
    location / {
        try_files $uri $uri/ /index.html;
        
        # Don't cache index.html (for updates)
        location = /index.html {
            add_header Cache-Control "no-cache, no-store, must-revalidate";
            add_header Pragma "no-cache";
            add_header Expires "0";
        }
    }

    # Deny access to hidden files
    location ~ /\. {
        deny all;
    }

    # Deny access to source files
    location ~ \.(vue|ts|js)$ {
        deny all;
    }
}
```

### 4.2 Enable Site and Test Configuration
```bash
# Create symbolic link to enable site
sudo ln -s /etc/nginx/sites-available/vueend /etc/nginx/sites-enabled/

# Test Nginx configuration
sudo nginx -t

# If test passes, reload Nginx
sudo systemctl reload nginx
```

---

## Step 5: SSL Configuration

### 5.1 Option A: Using Let's Encrypt (Certbot)
```bash
# Install Certbot if not already installed
sudo apt install certbot python3-certbot-nginx

# Obtain SSL certificate
sudo certbot --nginx -d ithdp.ir -d www.ithdp.ir

# Follow prompts:
# - Enter email address
# - Agree to terms
# - Choose whether to redirect HTTP to HTTPS (choose Yes)

# Test auto-renewal
sudo certbot renew --dry-run
```

### 5.2 Option B: Using Cloudflare (Recommended)
**If using Cloudflare:**

1. **In Cloudflare Dashboard:**
   - Go to SSL/TLS → Overview
   - Set encryption mode to **Full (strict)**
   - Go to SSL/TLS → Edge Certificates
   - Enable:
     - ✅ Always Use HTTPS
     - ✅ Automatic HTTPS Rewrites
     - ✅ Minimum TLS Version: TLS 1.2

2. **Create Origin Certificate (Optional but recommended):**
   - Go to SSL/TLS → Origin Server
   - Create Certificate
   - Download certificate and private key
   - Upload to server and update Nginx config

---

## Step 6: Backend CORS Configuration

### 6.1 Verify Backend CORS Settings
**On the VPS, check your Laravel backend .env:**

```bash
cd /var/www/laraend
cat .env | grep FRONTEND_URL
```

**Should show:**
```env
FRONTEND_URL=https://ithdp.ir
```

### 6.2 Update CORS if Needed
```bash
cd /var/www/laraend

# Update .env if needed
nano .env

# Clear and rebuild config cache
php artisan config:clear
php artisan config:cache

# Restart PHP-FPM
sudo systemctl restart php8.4-fpm
```

---

## Step 7: Testing Deployment

### 7.1 Test Frontend
```bash
# Test HTTPS connection
curl -I https://ithdp.ir

# Should return: HTTP/2 200

# Test in browser
# Open: https://ithdp.ir
```

### 7.2 Test API Connection
**In browser console or using curl:**
```bash
# Test API endpoint from frontend domain
curl -H "Origin: https://ithdp.ir" \
     -H "Access-Control-Request-Method: GET" \
     -X OPTIONS \
     https://api.ithdp.ir/api/courses -v
```

### 7.3 Verify CORS Headers
**Check that API returns proper CORS headers:**
```bash
curl -H "Origin: https://ithdp.ir" \
     -I https://api.ithdp.ir/api/courses
```

**Should include:**
```
Access-Control-Allow-Origin: https://ithdp.ir
Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS
Access-Control-Allow-Headers: Content-Type, Authorization
```

---

## Step 8: Deployment Automation

### 8.1 Create Frontend Deployment Script
**On VPS, create deployment script:**

```bash
nano /var/www/vueend/deploy-frontend.sh
```

**Script content:**
```bash
#!/bin/bash

set -e

APP_DIR="/var/www/vueend"
BACKUP_DIR="/home/deploy/backups/frontend"
DATE=$(date +"%Y%m%d_%H%M%S")

echo "Starting frontend deployment..."

# Create backup
mkdir -p "$BACKUP_DIR"
if [ -d "$APP_DIR/dist" ]; then
    tar -czf "$BACKUP_DIR/frontend-${DATE}.tar.gz" -C "$APP_DIR" dist/
    echo "Backup created: $BACKUP_DIR/frontend-${DATE}.tar.gz"
fi

# Navigate to app directory
cd "$APP_DIR"

# Install dependencies
npm ci

# Build for production
npm run build

# Set permissions
sudo chown -R deploy:www-data dist/
sudo chmod -R 755 dist/

# Test Nginx configuration
sudo nginx -t

# Reload Nginx
sudo systemctl reload nginx

echo "Frontend deployment completed successfully!"
echo "Backup location: $BACKUP_DIR/frontend-${DATE}.tar.gz"
```

**Make script executable:**
```bash
chmod +x /var/www/vueend/deploy-frontend.sh
```

### 8.2 Create Local Upload Script
**On your local machine, create upload script:**

```bash
nano upload-frontend.sh
```

**Script content:**
```bash
#!/bin/bash

set -e

LOCAL_DIR="/path/to/your/vueend"  # Update this path
REMOTE_USER="deploy"
REMOTE_HOST="5.182.44.108"
REMOTE_PORT="2222"
REMOTE_DIR="/var/www/vueend"

echo "Uploading frontend files..."

# Upload files (exclude node_modules, .git, dist)
rsync -avz --progress \
    --exclude='node_modules/' \
    --exclude='.git/' \
    --exclude='dist/' \
    --exclude='.env.local' \
    --exclude='.env.development' \
    -e "ssh -p $REMOTE_PORT" \
    "$LOCAL_DIR/" "$REMOTE_USER@$REMOTE_HOST:$REMOTE_DIR/"

echo "Files uploaded. Running deployment on server..."

# Run deployment script on server
ssh -p "$REMOTE_PORT" "$REMOTE_USER@$REMOTE_HOST" "cd $REMOTE_DIR && ./deploy-frontend.sh"

echo "Frontend deployment completed!"
```

**Make script executable:**
```bash
chmod +x upload-frontend.sh
```

---

## Step 9: Update Workflow

### 9.1 Development to Production Workflow

**When you make changes to your frontend:**

1. **Test locally:**
   ```bash
   cd /path/to/your/vueend
   npm run dev
   # Test at http://localhost:5173
   ```

2. **Upload and deploy:**
   ```bash
   ./upload-frontend.sh
   ```

3. **Or manual deployment:**
   ```bash
   # Upload files
   rsync -avz --exclude='node_modules/' --exclude='.git/' --exclude='dist/' \
       ./ deploy@5.182.44.108:/var/www/vueend/
   
   # SSH and deploy
   ssh deploy@5.182.44.108 -p 2222
   cd /var/www/vueend
   ./deploy-frontend.sh
   ```

### 9.2 Environment Management

**Development (.env.development):**
```env
VITE_API_BASE_URL=http://localhost:8000
VITE_APP_NAME=ITHDP (Dev)
```

**Production (.env.production):**
```env
VITE_API_BASE_URL=https://api.ithdp.ir
VITE_APP_NAME=ITHDP
```

---

## Step 10: Monitoring and Maintenance

### 10.1 Log Monitoring
```bash
# Frontend Nginx logs
tail -f /var/log/nginx/vueend-access.log
tail -f /var/log/nginx/vueend-error.log

# System logs
journalctl -u nginx -f
```

### 10.2 Performance Monitoring
```bash
# Check disk space
df -h

# Check memory usage
free -m

# Check Nginx status
sudo systemctl status nginx
```

### 10.3 Backup Strategy
```bash
# Manual backup
cd /var/www/vueend
tar -czf ~/backups/vueend-$(date +%Y%m%d).tar.gz dist/

# Automated backup (add to crontab)
# Daily backup at 2 AM
0 2 * * * tar -czf /home/deploy/backups/vueend-$(date +\%Y\%m\%d).tar.gz -C /var/www/vueend dist/
```

---

## Troubleshooting

### Issue: 404 on Page Refresh
**Problem**: Vue Router routes return 404 when accessed directly

**Solution**: Ensure Nginx configuration includes:
```nginx
location / {
    try_files $uri $uri/ /index.html;
}
```

### Issue: API Calls Failing
**Problem**: CORS errors or API connection issues

**Solutions**:
```bash
# Check backend CORS configuration
cd /var/www/laraend
cat .env | grep FRONTEND_URL

# Should be: FRONTEND_URL=https://ithdp.ir

# Clear Laravel config cache
php artisan config:clear
php artisan config:cache
sudo systemctl restart php8.4-fpm
```

### Issue: SSL Certificate Problems
**Problem**: SSL errors or mixed content warnings

**Solutions**:
```bash
# Check certificate status
sudo certbot certificates

# Renew certificate
sudo certbot renew

# Check Nginx SSL configuration
sudo nginx -t
```

### Issue: Build Failures
**Problem**: npm run build fails

**Solutions**:
```bash
# Clear npm cache
npm cache clean --force

# Delete node_modules and reinstall
rm -rf node_modules package-lock.json
npm install

# Check Node.js version
node --version
# Should be LTS version
```

### Issue: Slow Loading
**Problem**: Frontend loads slowly

**Solutions**:
```bash
# Check if gzip is working
curl -H "Accept-Encoding: gzip" -I https://ithdp.ir

# Check file sizes
ls -lh /var/www/vueend/dist/assets/

# Consider enabling Brotli compression
sudo apt install nginx-module-brotli
```

---

## Security Considerations

### 1. File Permissions
```bash
# Set correct permissions
sudo chown -R deploy:www-data /var/www/vueend
sudo chmod -R 755 /var/www/vueend
sudo chmod -R 644 /var/www/vueend/dist/*
```

### 2. Hide Source Files
**Nginx configuration already includes:**
```nginx
# Deny access to source files
location ~ \.(vue|ts|js)$ {
    deny all;
}
```

### 3. Security Headers
**Already configured in Nginx:**
```nginx
add_header X-Frame-Options "SAMEORIGIN" always;
add_header X-Content-Type-Options "nosniff" always;
add_header X-XSS-Protection "1; mode=block" always;
add_header Referrer-Policy "strict-origin-when-cross-origin" always;
```

### 4. Environment Variables
**Never commit sensitive data to version control:**
- Use `.env.production` for production variables
- Add `.env*` to `.gitignore`
- Use build-time environment variables with Vite

---

## Performance Optimization

### 1. Asset Optimization
**Vite automatically handles:**
- Code splitting
- Tree shaking
- Asset hashing for cache busting
- Minification

### 2. Caching Strategy
**Nginx configuration provides:**
- Long-term caching for hashed assets (1 year)
- No caching for index.html (immediate updates)
- Gzip compression

### 3. CDN Integration
**Consider using Cloudflare for:**
- Global CDN
- Additional caching
- DDoS protection
- SSL termination

---

## Quick Reference

### URLs
- **Frontend**: https://ithdp.ir
- **Backend API**: https://api.ithdp.ir
- **VPS IP**: 5.182.44.108
- **SSH Port**: 2222

### Key Directories
- **Frontend App**: /var/www/vueend
- **Frontend Build**: /var/www/vueend/dist
- **Nginx Config**: /etc/nginx/sites-available/vueend
- **SSL Certs**: /etc/letsencrypt/live/ithdp.ir/

### Common Commands
```bash
# SSH to server
ssh deploy@5.182.44.108 -p 2222

# Deploy frontend
cd /var/www/vueend && ./deploy-frontend.sh

# Check Nginx status
sudo systemctl status nginx

# View logs
tail -f /var/log/nginx/vueend-error.log

# Test SSL
curl -I https://ithdp.ir
```

---

## Environment Variables Reference

| Variable | Development | Production | Description |
|----------|-------------|------------|-------------|
| `VITE_API_BASE_URL` | `http://localhost:8000` | `https://api.ithdp.ir` | Backend API URL |
| `VITE_APP_NAME` | `ITHDP (Dev)` | `ITHDP` | Application name |
| `VITE_APP_ENV` | `development` | `production` | Environment identifier |

---

## Deployment Checklist

- [ ] Node.js LTS installed on VPS
- [ ] Frontend directory created with correct permissions
- [ ] DNS configured (ithdp.ir → 5.182.44.108)
- [ ] Frontend code uploaded to VPS
- [ ] Dependencies installed and built
- [ ] Nginx configuration created and enabled
- [ ] SSL certificate obtained (Certbot or Cloudflare)
- [ ] Backend CORS configured for ithdp.ir
- [ ] Frontend accessible at https://ithdp.ir
- [ ] API calls working from frontend
- [ ] Deployment scripts created
- [ ] Backup strategy implemented
- [ ] Monitoring configured

---

**Last Updated**: October 11, 2025  
**Frontend**: Vue.js SPA  
**Backend**: Laravel API (https://api.ithdp.ir)  
**VPS**: Ubuntu 24.04 LTS (5.182.44.108)
