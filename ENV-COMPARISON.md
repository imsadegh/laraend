# Environment Configuration Comparison

This document shows the differences between your development and production `.env` configurations.

## Development Environment (Your Mac)

```env
APP_NAME=Laravel
APP_ENV=local                          # ← Development
APP_KEY=base64:...
APP_DEBUG=true                         # ← Debug enabled
APP_TIMEZONE=Asia/Tehran
APP_URL=http://localhost:8000          # ← Local URL
FRONTEND_URL=http://localhost:5173     # ← Local frontend

SMSIR_API_KEY=

APP_LOCALE=en
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=en_US

APP_MAINTENANCE_DRIVER=file
# APP_MAINTENANCE_STORE=database

PHP_CLI_SERVER_WORKERS=4

BCRYPT_ROUNDS=12

LOG_CHANNEL=stack
LOG_STACK=single
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug                        # ← Debug logging

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=hakimyar_db                # ← Dev database
DB_USERNAME=hakimyar_user              # ← Dev user
DB_PASSWORD=hakimyar_password          # ← Dev password

SESSION_DRIVER=database                # ← Database sessions
SESSION_LIFETIME=120
SESSION_ENCRYPT=false                  # ← No encryption
SESSION_PATH=/
SESSION_DOMAIN=null                    # ← No domain

BROADCAST_CONNECTION=log
FILESYSTEM_DISK=local
QUEUE_CONNECTION=database              # ← Database queue

CACHE_STORE=database                   # ← Database cache
CACHE_PREFIX=                          # ← No prefix

MEMCACHED_HOST=127.0.0.1

REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=log
MAIL_SCHEME=null
MAIL_HOST=127.0.0.1
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_FROM_ADDRESS="hello@example.com" # ← Generic email
MAIL_FROM_NAME="${APP_NAME}"

AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=
AWS_USE_PATH_STYLE_ENDPOINT=false

VITE_APP_NAME="${APP_NAME}"

JWT_SECRET=base64:...
```

## Production Environment (VPS)

```env
APP_NAME=Laravel
APP_ENV=production                     # ← Production
APP_KEY=                               # ← Generate on server
APP_DEBUG=false                        # ← Debug disabled
APP_TIMEZONE=Asia/Tehran
APP_URL=https://api.ithdp.ir           # ← Production URL
FRONTEND_URL=https://ithdp.ir          # ← Production frontend

SMSIR_API_KEY=your_smsir_api_key_here  # ← Add your key

APP_LOCALE=en
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=en_US

APP_MAINTENANCE_DRIVER=file
# APP_MAINTENANCE_STORE=database

PHP_CLI_SERVER_WORKERS=4

BCRYPT_ROUNDS=12

LOG_CHANNEL=stack
LOG_STACK=single
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=error                        # ← Error logging only

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=laraend_db                 # ← Production database
DB_USERNAME=laraend_user               # ← Production user
DB_PASSWORD=LaraEnd2025!SecurePass     # ← Strong password

SESSION_DRIVER=redis                   # ← Redis sessions
SESSION_LIFETIME=120
SESSION_ENCRYPT=true                   # ← Encryption enabled
SESSION_PATH=/
SESSION_DOMAIN=.ithdp.ir               # ← Domain for CORS

BROADCAST_CONNECTION=log
FILESYSTEM_DISK=local
QUEUE_CONNECTION=redis                 # ← Redis queue

CACHE_STORE=redis                      # ← Redis cache
CACHE_PREFIX=laraend_cache             # ← Cache prefix

MEMCACHED_HOST=127.0.0.1

REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null                    # ← Consider adding password
REDIS_PORT=6379

MAIL_MAILER=log
MAIL_SCHEME=null
MAIL_HOST=127.0.0.1
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_FROM_ADDRESS="noreply@ithdp.ir"   # ← Production email
MAIL_FROM_NAME="${APP_NAME}"

AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=
AWS_USE_PATH_STYLE_ENDPOINT=false

VITE_APP_NAME="${APP_NAME}"

JWT_SECRET=                            # ← Generate on server
```

## Key Differences Summary

| Setting | Development | Production | Reason |
|---------|-------------|------------|--------|
| `APP_ENV` | `local` | `production` | Environment identifier |
| `APP_DEBUG` | `true` | `false` | Hide errors from users |
| `APP_URL` | `http://localhost:8000` | `https://api.ithdp.ir` | Production domain |
| `FRONTEND_URL` | `http://localhost:5173` | `https://ithdp.ir` | Production frontend |
| `LOG_LEVEL` | `debug` | `error` | Reduce log noise |
| `DB_DATABASE` | `hakimyar_db` | `laraend_db` | Production database |
| `DB_USERNAME` | `hakimyar_user` | `laraend_user` | Production user |
| `DB_PASSWORD` | `hakimyar_password` | `LaraEnd2025!SecurePass` | Strong password |
| `SESSION_DRIVER` | `database` | `redis` | Better performance |
| `SESSION_ENCRYPT` | `false` | `true` | Security |
| `SESSION_DOMAIN` | `null` | `.ithdp.ir` | CORS support |
| `QUEUE_CONNECTION` | `database` | `redis` | Better performance |
| `CACHE_STORE` | `database` | `redis` | Better performance |
| `CACHE_PREFIX` | (empty) | `laraend_cache` | Avoid conflicts |
| `MAIL_FROM_ADDRESS` | `hello@example.com` | `noreply@ithdp.ir` | Professional email |

## Why Redis in Production?

### Performance Benefits

**Database-driven (Development):**
- ✅ Simple setup - no additional services
- ✅ Good for development
- ❌ Slower - requires database queries
- ❌ More database load
- ❌ No atomic operations

**Redis-driven (Production):**
- ✅ Much faster - in-memory storage
- ✅ Reduces database load
- ✅ Atomic operations for queues
- ✅ Better for concurrent users
- ✅ Industry standard for production
- ❌ Requires Redis installation

### Real-World Impact

With **100 concurrent users**:
- **Database sessions**: ~100 extra DB queries per second
- **Redis sessions**: ~100 cache hits (microseconds)

With **1000 queued jobs**:
- **Database queue**: Slower processing, table locks
- **Redis queue**: Fast, atomic, no locks

## Migration Notes

### No Code Changes Required!

The beauty of Laravel is that switching from database to Redis requires **zero code changes**. Just:

1. Install Redis on VPS ✓ (covered in deployment guide)
2. Update `.env` configuration ✓ (covered in deployment guide)
3. Restart services ✓ (covered in deployment guide)

Your application code works exactly the same!

### Session Data Migration

**Important**: When you switch from database to Redis sessions, existing user sessions will be lost. Users will need to log in again. This is normal and expected during deployment.

To minimize impact:
1. Deploy during low-traffic hours
2. Notify users of maintenance window
3. Use `php artisan down` during deployment

### Cache Migration

Cache data doesn't need migration - it's temporary by nature. Old cache in database will simply expire naturally.

### Queue Migration

For queue jobs:
1. Process all pending database queue jobs before deployment
2. Or migrate them manually if critical

```bash
# Before switching to Redis, ensure database queue is empty
php artisan queue:work database --stop-when-empty
```

## Testing Locally with Redis (Optional)

If you want to test Redis locally before production:

### Install Redis on Mac

```bash
# Using Homebrew
brew install redis

# Start Redis
brew services start redis

# Verify Redis is running
redis-cli ping
# Should return: PONG
```

### Update Local .env

```env
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis
CACHE_STORE=redis
CACHE_PREFIX=laraend_dev_cache
```

### Test Your Application

```bash
php artisan cache:clear
php artisan config:clear
php artisan serve
```

Everything should work exactly the same, but faster!

## Security Considerations

### Production Security Enhancements

1. **Session Encryption** (`SESSION_ENCRYPT=true`)
   - Encrypts session data
   - Prevents session tampering
   - Recommended for production

2. **Session Domain** (`SESSION_DOMAIN=.ithdp.ir`)
   - Allows cookies across subdomains
   - Required for CORS with frontend
   - Prevents cookie conflicts

3. **Strong Database Password**
   - Use strong, unique password
   - Never reuse development passwords
   - Store securely (password manager)

4. **Redis Password** (Optional but Recommended)
   ```bash
   # On VPS, edit Redis config
   sudo nano /etc/redis/redis.conf
   
   # Add:
   requirepass YourStrongRedisPassword123!
   
   # Update .env:
   REDIS_PASSWORD=YourStrongRedisPassword123!
   ```

5. **Debug Mode Disabled** (`APP_DEBUG=false`)
   - Never show stack traces to users
   - Critical security requirement
   - Logs errors instead

## Troubleshooting

### Redis Connection Issues

**Error**: `Connection refused [tcp://127.0.0.1:6379]`

**Solution**:
```bash
# Check if Redis is running
sudo systemctl status redis-server

# Start Redis if not running
sudo systemctl start redis-server

# Check Redis connectivity
redis-cli ping
```

### Session Issues After Deployment

**Problem**: Users can't stay logged in

**Solution**:
```bash
# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear

# Restart PHP-FPM
sudo systemctl restart php8.4-fpm
```

### Queue Jobs Not Processing

**Problem**: Jobs stuck in queue

**Solution**:
```bash
# Restart queue workers
sudo supervisorctl restart laraend-worker:*

# Check worker logs
tail -f /var/www/laraend/storage/logs/worker.log
```

## Quick Setup Checklist

When deploying to production:

- [ ] Redis installed and running on VPS
- [ ] `.env` updated with Redis configuration
- [ ] `APP_DEBUG=false` set
- [ ] `APP_ENV=production` set
- [ ] Strong database password set
- [ ] Production URLs configured
- [ ] SMS-IR API key added
- [ ] `php artisan key:generate` run
- [ ] `php artisan jwt:secret` run
- [ ] `php artisan config:cache` run
- [ ] Services restarted
- [ ] Application tested

---

**Note**: The deployment guide (`laraend.md`) covers all these steps in detail. This document is just for understanding the differences between development and production configurations.

