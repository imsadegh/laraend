# Manual Deployment Fix

## Issue
The deployment script is trying to run Laravel commands from the local Mac directory instead of the VPS directory.

## Quick Fix

### Step 1: SSH to VPS
```bash
ssh deploy@5.182.44.108 -p 3031
```

### Step 2: Navigate to Laravel Directory
```bash
cd /var/www/laraend
pwd
# Should show: /var/www/laraend
```

### Step 3: Set Correct Permissions
```bash
# Fix storage permissions
sudo chown -R deploy:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache

# Fix ownership of entire app
sudo chown -R deploy:www-data /var/www/laraend
```

### Step 4: Run Laravel Commands
```bash
# Generate application key (if not set)
php artisan key:generate

# Generate JWT secret (if not set)
php artisan jwt:secret

# Clear and cache configuration
php artisan config:clear
php artisan config:cache
php artisan route:cache

# Run migrations
php artisan migrate --force

# Clear caches
php artisan cache:clear
php artisan view:clear
```

### Step 5: Restart Services
```bash
# Restart PHP-FPM
sudo systemctl restart php8.4-fpm

# Restart queue workers
sudo supervisorctl restart laraend-worker:*

# Check services
sudo systemctl status php8.4-fpm
sudo supervisorctl status
```

### Step 6: Test Application
```bash
# Test Laravel
php artisan --version

# Test API endpoint
curl -I https://api.ithdp.ir
```

## Alternative: Fix the Deploy Script

If you want to fix the deploy script, update it to ensure it's in the correct directory:

```bash
# On VPS, edit the deploy script
nano /var/www/laraend/deploy.sh

# Add this line after line 50 (after the directory check):
cd "$APP_DIR"
echo "Current directory: $(pwd)"
```

## Expected Output

After running the manual steps, you should see:
- Laravel commands working without permission errors
- Services running properly
- API accessible at https://api.ithdp.ir
