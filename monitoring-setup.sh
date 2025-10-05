#!/bin/bash

# Monitoring and Logging Setup Script
# Run this script to set up monitoring for your Laravel application

echo "📊 Setting up monitoring and logging..."

# Install htop and other monitoring tools
echo "📦 Installing monitoring tools..."
apt install -y htop iotop nethogs

# Set up Laravel Telescope (for debugging and monitoring)
echo "🔭 Setting up Laravel Telescope..."
cd /var/www/laravel
sudo -u www-data composer require laravel/telescope --dev
sudo -u www-data php artisan telescope:install
sudo -u www-data php artisan migrate

# Create monitoring script
echo "📋 Creating monitoring script..."
cat > /usr/local/bin/laravel-monitor.sh << 'EOF'
#!/bin/bash

# Laravel Application Monitoring Script
APP_DIR="/var/www/laravel"
LOG_FILE="/var/log/laravel-monitor.log"

# Check if Laravel application is running
if ! pgrep -f "php.*artisan.*serve" > /dev/null; then
    echo "$(date): Laravel application not running, attempting restart..." >> $LOG_FILE
    cd $APP_DIR
    sudo -u www-data php artisan serve --host=0.0.0.0 --port=8000 &
fi

# Check disk space
DISK_USAGE=$(df / | awk 'NR==2 {print $5}' | sed 's/%//')
if [ $DISK_USAGE -gt 80 ]; then
    echo "$(date): WARNING - Disk usage is ${DISK_USAGE}%" >> $LOG_FILE
fi

# Check memory usage
MEMORY_USAGE=$(free | awk 'NR==2{printf "%.2f", $3*100/$2}')
if (( $(echo "$MEMORY_USAGE > 80" | bc -l) )); then
    echo "$(date): WARNING - Memory usage is ${MEMORY_USAGE}%" >> $LOG_FILE
fi

# Check Laravel logs for errors
if [ -f "$APP_DIR/storage/logs/laravel.log" ]; then
    ERROR_COUNT=$(grep -c "ERROR" $APP_DIR/storage/logs/laravel.log | tail -1)
    if [ $ERROR_COUNT -gt 0 ]; then
        echo "$(date): Found $ERROR_COUNT errors in Laravel logs" >> $LOG_FILE
    fi
fi
EOF

chmod +x /usr/local/bin/laravel-monitor.sh

# Add monitoring to crontab
echo "⏰ Adding monitoring to crontab..."
(crontab -l 2>/dev/null; echo "*/5 * * * * /usr/local/bin/laravel-monitor.sh") | crontab -

# Set up log rotation for Laravel logs
echo "📋 Setting up log rotation..."
cat > /etc/logrotate.d/laravel-monitor << EOF
/var/www/laravel/storage/logs/*.log {
    daily
    missingok
    rotate 30
    compress
    delaycompress
    notifempty
    create 644 www-data www-data
    postrotate
        systemctl reload php8.2-fpm
    endscript
}

/var/log/laravel-monitor.log {
    daily
    missingok
    rotate 7
    compress
    notifempty
    create 644 root root
}
EOF

# Create backup script
echo "💾 Creating backup script..."
cat > /usr/local/bin/laravel-backup.sh << 'EOF'
#!/bin/bash

# Laravel Application Backup Script
APP_DIR="/var/www/laravel"
BACKUP_DIR="/var/backups/laravel"
DATE=$(date +%Y%m%d_%H%M%S)

# Create backup directory
mkdir -p $BACKUP_DIR

# Backup database
mysqldump -u laravel_user -p hakimyar_fusion > $BACKUP_DIR/database_$DATE.sql

# Backup application files (excluding vendor and node_modules)
tar -czf $BACKUP_DIR/app_$DATE.tar.gz -C $APP_DIR --exclude=vendor --exclude=node_modules --exclude=.git .

# Keep only last 7 days of backups
find $BACKUP_DIR -name "*.sql" -mtime +7 -delete
find $BACKUP_DIR -name "*.tar.gz" -mtime +7 -delete

echo "$(date): Backup completed - database_$DATE.sql and app_$DATE.tar.gz"
EOF

chmod +x /usr/local/bin/laravel-backup.sh

# Add backup to crontab (daily at 2 AM)
echo "⏰ Adding backup to crontab..."
(crontab -l 2>/dev/null; echo "0 2 * * * /usr/local/bin/laravel-backup.sh") | crontab -

# Create system status check script
echo "📊 Creating system status script..."
cat > /usr/local/bin/system-status.sh << 'EOF'
#!/bin/bash

echo "=== Laravel Application Status ==="
echo "Date: $(date)"
echo ""

echo "=== System Resources ==="
echo "CPU Usage:"
top -bn1 | grep "Cpu(s)" | awk '{print $2}' | cut -d'%' -f1
echo ""

echo "Memory Usage:"
free -h
echo ""

echo "Disk Usage:"
df -h /
echo ""

echo "=== Services Status ==="
systemctl status nginx --no-pager -l
echo ""
systemctl status php8.2-fpm --no-pager -l
echo ""
systemctl status mysql --no-pager -l
echo ""

echo "=== Laravel Application ==="
cd /var/www/laravel
echo "Application Key: $(php artisan key:show 2>/dev/null || echo 'Not set')"
echo "Database Status: $(php artisan migrate:status 2>/dev/null | tail -1 || echo 'Error')"
echo "Queue Status: $(php artisan queue:work --once 2>/dev/null && echo 'Working' || echo 'Error')"
EOF

chmod +x /usr/local/bin/system-status.sh

echo "✅ Monitoring setup completed!"
echo "📋 Available commands:"
echo "- /usr/local/bin/system-status.sh - Check system status"
echo "- /usr/local/bin/laravel-backup.sh - Manual backup"
echo "- htop - Monitor system resources"
echo "- tail -f /var/www/laravel/storage/logs/laravel.log - View Laravel logs"

