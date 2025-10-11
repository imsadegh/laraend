# Performance Tuning Guide for Laraend

## Server Specifications

- **CPU**: 4 cores @ 2.5GHz (QEMU Virtual CPU)
- **RAM**: 4GB
- **OS**: Ubuntu 24.04 LTS
- **VPS IP**: 5.182.44.108

## Memory Allocation Strategy

### Total Available: 4GB (4096MB)

**Recommended allocation:**
```
System/OS:              ~1024MB (25%)   - Ubuntu, kernel, essential services
PostgreSQL:             ~512MB  (12.5%) - Database server
Redis:                  ~256MB  (6.25%) - Cache and queue
PHP-FPM:                ~1600MB (39%)   - 40 processes × 40MB
Nginx:                  ~100MB  (2.5%)  - Web server
PHP OPcache:            ~256MB  (6.25%) - Opcode cache
PHP JIT:                ~128MB  (3%)    - Just-in-time compilation
Buffer/Available:       ~220MB  (5.5%)  - System buffer
──────────────────────────────────────
Total:                  ~4096MB (100%)
```

## PHP-FPM Configuration

### Process Manager Settings

**File**: `/etc/php/8.4/fpm/pool.d/www.conf`

```ini
; Dynamic process management
pm = dynamic

; Maximum child processes (RAM limited)
; Formula: (Available RAM for PHP) / (Memory per process)
; (1600MB) / (40MB) = 40 processes
pm.max_children = 40

; Initial processes on startup
; 2 per CPU core: 4 cores × 2 = 8
pm.start_servers = 8

; Minimum idle processes
; 1 per CPU core: 4 cores × 1 = 4
pm.min_spare_servers = 4

; Maximum idle processes
; 3 per CPU core: 4 cores × 3 = 12
pm.max_spare_servers = 12

; Restart worker after N requests (prevents memory leaks)
pm.max_requests = 1000

; Kill idle processes after 10 seconds
pm.process_idle_timeout = 10s

; Request timeout (5 minutes max)
request_terminate_timeout = 300

; Slow request logging
slowlog = /var/log/php8.4-fpm-slow.log
request_slowlog_timeout = 5s

; File descriptor limits
rlimit_files = 4096
rlimit_core = 0
```

### Why These Numbers?

**pm.max_children = 40:**
- Each PHP process uses ~30-50MB RAM (average 40MB)
- 40 processes × 40MB = 1600MB
- Leaves room for PostgreSQL, Redis, and system overhead
- Supports ~40 concurrent requests

**pm.start_servers = 8:**
- Start with 2 processes per CPU core
- Provides immediate capacity without overhead
- Balances startup time vs resource usage

**pm.min_spare_servers = 4:**
- 1 spare process per CPU core
- Always have capacity ready for new requests
- Prevents "cold start" delays

**pm.max_spare_servers = 12:**
- 3 spare processes per CPU core
- Handles traffic spikes without spinning up new processes
- Prevents resource waste during low traffic

**pm.max_requests = 1000:**
- Recycles processes after 1000 requests
- Prevents memory leaks from accumulating
- Balances performance vs memory management

## PHP Configuration

### OPcache Settings

**File**: `/etc/php/8.4/fpm/php.ini`

```ini
; Enable OPcache
opcache.enable=1
opcache.enable_cli=0

; Memory allocation
; Using 256MB (6.4% of total RAM) for opcode cache
opcache.memory_consumption=256

; String interning buffer
; Stores common strings once in memory
opcache.interned_strings_buffer=16

; Maximum cached files
; Typical Laravel app has 5,000-10,000 PHP files
opcache.max_accelerated_files=20000

; Wasted memory threshold
; Clean cache when 5% is wasted
opcache.max_wasted_percentage=5

; Disable timestamp validation (production)
; Manually clear cache after deployments
opcache.validate_timestamps=0
opcache.revalidate_freq=0

; Performance optimizations
opcache.save_comments=1
opcache.fast_shutdown=1
opcache.enable_file_override=1
opcache.huge_code_pages=1

; JIT Compilation (PHP 8.4 feature)
; Provides 15-30% performance boost
opcache.jit_buffer_size=128M
opcache.jit=1255
```

### OPcache JIT Modes

**opcache.jit=1255** (Recommended for Laravel):
- **First digit (1)**: JIT enabled for traced functions
- **Second digit (2)**: Optimize traced functions
- **Third digit (5)**: Use tracing JIT
- **Fourth digit (5)**: Register allocation

This provides the best balance for web applications.

### Memory Settings

```ini
; Per-request memory limit
; 256MB is sufficient for most Laravel requests
memory_limit = 256M

; File upload limits
upload_max_filesize = 100M
post_max_size = 100M

; Execution time limits
max_execution_time = 300
max_input_time = 300

; Input variable limits
max_input_vars = 3000
max_file_uploads = 20

; Realpath cache (reduces filesystem lookups)
realpath_cache_size = 4M
realpath_cache_ttl = 7200
```

## PostgreSQL Configuration

### Memory Settings

**File**: `/etc/postgresql/16/main/postgresql.conf`

```ini
# Memory Configuration for 4GB RAM system

# Shared buffers (25% of RAM)
# Used for caching database pages
shared_buffers = 1GB

# Effective cache size (50% of RAM)
# Estimates available OS cache
effective_cache_size = 2GB

# Maintenance work memory
# Used for VACUUM, CREATE INDEX, etc.
maintenance_work_mem = 256MB

# Work memory (per operation)
# Used for sorts, hash tables, etc.
work_mem = 16MB

# WAL buffers
wal_buffers = 16MB

# Checkpoint settings
checkpoint_completion_target = 0.9
max_wal_size = 2GB
min_wal_size = 1GB

# Connection settings
max_connections = 100

# Query planner
random_page_cost = 1.1  # For SSD storage
effective_io_concurrency = 200  # For SSD storage

# Logging (adjust based on needs)
log_min_duration_statement = 1000  # Log slow queries (>1s)
```

**To apply changes:**
```bash
sudo nano /etc/postgresql/16/main/postgresql.conf
sudo systemctl restart postgresql
```

## Redis Configuration

### Memory Settings

**File**: `/etc/redis/redis.conf`

```ini
# Maximum memory for Redis (256MB)
maxmemory 256mb

# Eviction policy
# Remove least recently used keys when memory limit reached
maxmemory-policy allkeys-lru

# Save to disk periodically (optional)
save 900 1
save 300 10
save 60 10000

# Performance optimizations
tcp-backlog 511
tcp-keepalive 300
timeout 0

# Disable RDB compression (saves CPU)
rdbcompression no

# AOF persistence (disabled for cache-only usage)
appendonly no

# Binding
bind 127.0.0.1 ::1

# Password protection (recommended)
# requirepass YourStrongRedisPassword123!
```

**To apply changes:**
```bash
sudo nano /etc/redis/redis.conf
sudo systemctl restart redis-server
```

## Nginx Configuration

### Worker Processes

**File**: `/etc/nginx/nginx.conf`

```nginx
# One worker per CPU core
worker_processes 4;

# Maximum connections per worker
# Formula: ulimit -n / worker_processes
# Typically: 1024 / 4 = 256 per worker
events {
    worker_connections 768;
    use epoll;
    multi_accept on;
}

http {
    # Connection timeout
    keepalive_timeout 65;
    keepalive_requests 100;

    # Client settings
    client_body_buffer_size 128k;
    client_max_body_size 100M;
    client_header_buffer_size 1k;
    large_client_header_buffers 4 8k;

    # FastCGI settings
    fastcgi_buffer_size 128k;
    fastcgi_buffers 4 256k;
    fastcgi_busy_buffers_size 256k;
    fastcgi_temp_file_write_size 256k;
    fastcgi_connect_timeout 60s;
    fastcgi_send_timeout 60s;
    fastcgi_read_timeout 60s;

    # Gzip compression
    gzip on;
    gzip_vary on;
    gzip_proxied any;
    gzip_comp_level 6;
    gzip_types text/plain text/css text/xml text/javascript 
               application/json application/javascript application/xml+rss 
               application/rss+xml font/truetype font/opentype 
               application/vnd.ms-fontobject image/svg+xml;
    gzip_disable "msie6";

    # File cache
    open_file_cache max=10000 inactive=20s;
    open_file_cache_valid 30s;
    open_file_cache_min_uses 2;
    open_file_cache_errors on;

    # Logging
    access_log /var/log/nginx/access.log combined buffer=32k;
    error_log /var/log/nginx/error.log warn;
}
```

## Monitoring & Optimization

### Monitor PHP-FPM Status

**Enable status page** in `/etc/php/8.4/fpm/pool.d/www.conf`:
```ini
pm.status_path = /php-fpm-status
ping.path = /ping
```

**Add to Nginx site config:**
```nginx
location ~ ^/(php-fpm-status|ping)$ {
    access_log off;
    allow 127.0.0.1;
    deny all;
    fastcgi_pass unix:/var/run/php/php8.4-fpm.sock;
    include fastcgi_params;
    fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
}
```

**Check status:**
```bash
curl http://localhost/php-fpm-status?full
```

### Monitor OPcache

**Create monitoring script** `/var/www/laraend/opcache-status.php`:
```php
<?php
if (function_exists('opcache_get_status')) {
    $status = opcache_get_status();
    $config = opcache_get_configuration();
    
    echo "OPcache Status:\n";
    echo "Enabled: " . ($status['opcache_enabled'] ? 'Yes' : 'No') . "\n";
    echo "Memory Usage: " . round($status['memory_usage']['used_memory'] / 1024 / 1024, 2) . " MB\n";
    echo "Free Memory: " . round($status['memory_usage']['free_memory'] / 1024 / 1024, 2) . " MB\n";
    echo "Cached Scripts: " . $status['opcache_statistics']['num_cached_scripts'] . "\n";
    echo "Hit Rate: " . round($status['opcache_statistics']['opcache_hit_rate'], 2) . "%\n";
    echo "Misses: " . $status['opcache_statistics']['misses'] . "\n";
}
?>
```

**Run monitoring:**
```bash
php /var/www/laraend/opcache-status.php
```

### System Resource Monitoring

**Check memory usage:**
```bash
free -h
```

**Check CPU usage:**
```bash
top -bn1 | head -20
```

**Check disk I/O:**
```bash
iostat -x 1 5
```

**Check PHP-FPM processes:**
```bash
ps aux | grep php-fpm | wc -l
```

**Monitor in real-time:**
```bash
watch -n 2 'free -h && echo && ps aux | grep php-fpm | wc -l'
```

## Performance Benchmarking

### Before Optimization

Test your application before applying optimizations:

```bash
# Install Apache Bench
sudo apt install apache2-utils

# Run benchmark (100 requests, 10 concurrent)
ab -n 100 -c 10 https://api.ithdp.ir/api/courses
```

### After Optimization

Run the same test and compare:
- Requests per second
- Time per request
- Failed requests
- Memory usage

### Expected Performance

With these optimizations on 4-core/4GB VPS:
- **Throughput**: 50-100 requests/second
- **Response Time**: 50-200ms (average)
- **Concurrent Users**: 40+ simultaneous users
- **Memory Usage**: 70-80% under load
- **CPU Usage**: 60-80% under load

## Laravel Application Optimization

### 1. Cache Everything

```bash
# Cache configuration
php artisan config:cache

# Cache routes
php artisan route:cache

# Cache views
php artisan view:cache

# Cache events
php artisan event:cache
```

### 2. Optimize Composer Autoloader

```bash
composer dump-autoload --optimize --classmap-authoritative
```

### 3. Use Eager Loading

Prevent N+1 queries:
```php
// Bad
$courses = Course::all();
foreach ($courses as $course) {
    echo $course->instructor->name;
}

// Good
$courses = Course::with('instructor')->get();
foreach ($courses as $course) {
    echo $course->instructor->name;
}
```

### 4. Database Indexing

Add indexes to frequently queried columns:
```php
Schema::table('courses', function (Blueprint $table) {
    $table->index('category_id');
    $table->index('instructor_id');
    $table->index(['status', 'created_at']);
});
```

### 5. Use Queue for Heavy Tasks

Move slow operations to queues:
```php
// Dispatch jobs to queue
SendEmailJob::dispatch($user)->onQueue('emails');
ProcessVideoJob::dispatch($video)->onQueue('videos');
```

### 6. Enable HTTP/2

Already enabled in Nginx with Cloudflare SSL.

### 7. Use CDN for Static Assets

Consider using Cloudflare CDN for:
- Images
- CSS files
- JavaScript files
- Fonts

## Troubleshooting Performance Issues

### High Memory Usage

**Symptoms**: Server slowdown, OOM errors

**Solutions**:
```bash
# Check memory usage
free -m

# Find memory-hungry processes
ps aux --sort=-%mem | head

# Reduce PHP-FPM max_children
sudo nano /etc/php/8.4/fpm/pool.d/www.conf
# Reduce pm.max_children from 40 to 30

# Restart PHP-FPM
sudo systemctl restart php8.4-fpm
```

### High CPU Usage

**Symptoms**: Slow responses, high load average

**Solutions**:
```bash
# Check CPU usage
top

# Identify slow PHP requests
tail -f /var/log/php8.4-fpm-slow.log

# Optimize slow queries
# Check PostgreSQL slow query log
sudo tail -f /var/log/postgresql/postgresql-16-main.log
```

### Database Connection Issues

**Symptoms**: Connection pool exhausted

**Solutions**:
```bash
# Check PostgreSQL connections
sudo -u postgres psql -c "SELECT count(*) FROM pg_stat_activity;"

# Increase max_connections if needed
sudo nano /etc/postgresql/16/main/postgresql.conf
# max_connections = 100

# Restart PostgreSQL
sudo systemctl restart postgresql
```

### Slow Page Loads

**Check these in order:**
1. Laravel debug bar (in dev mode)
2. PHP-FPM slow log
3. PostgreSQL slow query log
4. Nginx access log response times
5. Network latency

## Performance Checklist

- [ ] PHP-FPM optimized for 4-core CPU
- [ ] OPcache enabled with 256MB
- [ ] JIT enabled (PHP 8.4)
- [ ] PostgreSQL shared_buffers set to 1GB
- [ ] Redis maxmemory set to 256MB
- [ ] Nginx gzip compression enabled
- [ ] Laravel config cached
- [ ] Laravel routes cached
- [ ] Composer autoloader optimized
- [ ] Database indexes created
- [ ] N+1 queries eliminated
- [ ] Queue workers running
- [ ] Monitoring tools configured
- [ ] Benchmarks recorded

## Expected Resource Usage

### Idle (No Traffic)
- **RAM Usage**: ~1.5GB (37%)
- **CPU Usage**: <5%
- **Processes**: ~15 PHP-FPM workers

### Normal Load (10-20 users)
- **RAM Usage**: ~2.5GB (62%)
- **CPU Usage**: 20-40%
- **Processes**: 20-25 PHP-FPM workers

### High Load (40+ users)
- **RAM Usage**: ~3.2GB (80%)
- **CPU Usage**: 60-80%
- **Processes**: 35-40 PHP-FPM workers

### Critical (Approaching Limits)
- **RAM Usage**: >3.5GB (87%)
- **CPU Usage**: >85%
- **Processes**: 40 PHP-FPM workers (maxed out)

**Action**: If consistently hitting critical levels, consider:
1. Upgrading VPS to 8GB RAM
2. Implementing caching strategies
3. Optimizing database queries
4. Using a CDN for static assets
5. Scaling horizontally (multiple servers)

---

**Last Updated**: October 11, 2025  
**VPS Specs**: 4-core CPU @ 2.5GHz, 4GB RAM  
**Optimized For**: Laravel 12.x on Ubuntu 24.04

