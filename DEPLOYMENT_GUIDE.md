# Laravel Backend Deployment Guide for Ubuntu 20.04 VPS

This guide will help you deploy your Laravel backend application to an Ubuntu 20.04 VPS.

## Prerequisites

- Ubuntu 20.04 VPS with root access
- Domain name pointing to your VPS IP address
- SSH access to your VPS

## Step 1: Initial VPS Setup

1. **Connect to your VPS:**
   ```bash
   ssh root@your-vps-ip
   ```

2. **Upload and run the setup script:**
   ```bash
   # Upload the deploy-setup.sh file to your VPS
   chmod +x deploy-setup.sh
   ./deploy-setup.sh
   ```

## Step 2: Prepare Your Laravel Project

1. **Create a production environment file:**
   - Copy `env.production.example` to `.env.production`
   - Update the database credentials, domain name, and other settings

2. **Upload your project to the VPS:**
   ```bash
   # Option 1: Using SCP
   scp -r /path/to/your/laravel/project root@your-vps-ip:/var/www/laravel
   
   # Option 2: Using Git (recommended)
   git clone https://github.com/your-username/your-laravel-project.git /var/www/laravel
   ```

## Step 3: Deploy Laravel Application

1. **Run the deployment script:**
   ```bash
   chmod +x deploy-laravel.sh
   ./deploy-laravel.sh
   ```

2. **Update the script variables:**
   - Edit `deploy-laravel.sh` and update:
     - `DOMAIN`: Your actual domain name
     - `DB_PASS`: Secure database password
     - Other configuration as needed

## Step 4: Configure Domain and SSL

1. **Point your domain to the VPS:**
   - Update your domain's DNS A record to point to your VPS IP
   - Wait for DNS propagation (can take up to 24 hours)

2. **Install SSL certificate:**
   ```bash
   chmod +x ssl-setup.sh
   ./ssl-setup.sh
   ```

## Step 5: Set Up Monitoring (Optional)

1. **Run the monitoring setup:**
   ```bash
   chmod +x monitoring-setup.sh
   ./monitoring-setup.sh
   ```

## Step 6: Final Configuration

1. **Update your environment file:**
   ```bash
   nano /var/www/laravel/.env
   ```
   
   Update the following:
   - `APP_URL`: Your domain URL
   - `DB_*`: Database credentials
   - `MAIL_*`: Email configuration
   - `JWT_SECRET`: Generated JWT secret

2. **Test your application:**
   ```bash
   # Check if services are running
   systemctl status nginx
   systemctl status php8.2-fpm
   systemctl status mysql
   
   # Test Laravel application
   cd /var/www/laravel
   php artisan route:list
   ```

## Security Considerations

1. **Firewall Configuration:**
   ```bash
   ufw allow 22/tcp
   ufw allow 80/tcp
   ufw allow 443/tcp
   ufw enable
   ```

2. **Database Security:**
   - Use strong passwords
   - Limit database user privileges
   - Regular backups

3. **Application Security:**
   - Keep Laravel and dependencies updated
   - Use HTTPS only
   - Regular security audits

## Maintenance Commands

```bash
# Check system status
/usr/local/bin/system-status.sh

# Manual backup
/usr/local/bin/laravel-backup.sh

# View logs
tail -f /var/www/laravel/storage/logs/laravel.log

# Update application
cd /var/www/laravel
git pull origin main
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## Troubleshooting

### Common Issues:

1. **Permission Errors:**
   ```bash
   chown -R www-data:www-data /var/www/laravel
   chmod -R 755 /var/www/laravel
   chmod -R 775 /var/www/laravel/storage
   chmod -R 775 /var/www/laravel/bootstrap/cache
   ```

2. **Database Connection Issues:**
   - Check database credentials in `.env`
   - Ensure MySQL is running: `systemctl status mysql`
   - Test connection: `mysql -u laravel_user -p hakimyar_fusion`

3. **Nginx Configuration Issues:**
   - Test configuration: `nginx -t`
   - Check error logs: `tail -f /var/log/nginx/error.log`

4. **PHP-FPM Issues:**
   - Check status: `systemctl status php8.2-fpm`
   - Restart service: `systemctl restart php8.2-fpm`

## API Endpoints

Your Laravel application provides the following API endpoints:

- **Authentication:**
  - `POST /api/signup` - User registration
  - `POST /api/auth/login` - User login
  - `POST /api/otp/send` - Send OTP
  - `POST /api/otp/verify` - Verify OTP

- **Course Management:**
  - `GET /api/courses` - List courses
  - `POST /api/courses` - Create course
  - `GET /api/courses/{id}` - Get course details
  - `PUT /api/courses/{id}` - Update course

- **Assignments:**
  - `GET /api/courses/{course}/assignments` - Get course assignments
  - `POST /api/courses/{course}/assignments` - Create assignment
  - `POST /api/assignments/{id}/submissions` - Submit assignment

- **Exams:**
  - `GET /api/exams` - List exams
  - `POST /api/exams` - Create exam
  - `POST /api/exams/{exam}/attempts` - Start exam attempt
  - `POST /api/attempts/{attempt}/answers` - Submit answer

## Support

If you encounter any issues during deployment, check:

1. Laravel logs: `/var/www/laravel/storage/logs/laravel.log`
2. Nginx logs: `/var/log/nginx/error.log`
3. System logs: `/var/log/syslog`
4. Application status: `/usr/local/bin/system-status.sh`

