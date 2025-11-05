---
description: Prepare and guide deployment of a TALL Stack application
---

You are helping deploy a TALL Stack application. Follow this comprehensive checklist:

## 1. Pre-Deployment Checklist

### Environment Configuration
- [ ] Copy `.env.example` to `.env` on production
- [ ] Generate application key: `php artisan key:generate`
- [ ] Set `APP_ENV=production`
- [ ] Set `APP_DEBUG=false`
- [ ] Configure database credentials
- [ ] Set proper `APP_URL`
- [ ] Configure mail settings
- [ ] Set up queue driver (Redis recommended)
- [ ] Configure cache driver (Redis recommended)
- [ ] Configure session driver (Redis/database recommended)

### Security
- [ ] Review and update `.env` with secure values
- [ ] Ensure HTTPS is enabled
- [ ] Set secure session cookie settings
- [ ] Configure CORS if needed
- [ ] Set up rate limiting
- [ ] Review and set proper file permissions
- [ ] Enable CSRF protection (enabled by default)
- [ ] Configure trusted proxies if behind load balancer

### Database
- [ ] Run migrations: `php artisan migrate --force`
- [ ] Seed initial data if needed: `php artisan db:seed --force`
- [ ] Backup database before deployment
- [ ] Set up automatic backups
- [ ] Configure database connection pooling

### Optimization
- [ ] Clear and cache config: `php artisan config:cache`
- [ ] Clear and cache routes: `php artisan route:cache`
- [ ] Clear and cache views: `php artisan view:cache`
- [ ] Clear and cache events: `php artisan event:cache`
- [ ] Optimize autoloader: `composer install --optimize-autoloader --no-dev`
- [ ] Build assets: `npm run build`
- [ ] Enable OPcache in PHP

## 2. Server Requirements

### PHP Requirements (Laravel 10+)
- PHP >= 8.1
- Extensions:
  - BCMath
  - Ctype
  - Fileinfo
  - JSON
  - Mbstring
  - OpenSSL
  - PDO
  - Tokenizer
  - XML
  - cURL
  - GD or Imagick (for image processing)

### Additional Requirements
- Composer
- Node.js & NPM (for building assets)
- Redis (recommended for cache/queue/sessions)
- Supervisor (for queue workers)

## 3. Deployment Process

### Option 1: Manual Deployment

1. **Upload Files**
   ```bash
   # On local
   rsync -avz --exclude 'node_modules' --exclude '.env' \
     ./ user@server:/path/to/app/
   ```

2. **On Server**
   ```bash
   cd /path/to/app

   # Install dependencies
   composer install --optimize-autoloader --no-dev

   # Run migrations
   php artisan migrate --force

   # Clear and cache
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache

   # Build assets
   npm ci
   npm run build

   # Set permissions
   chmod -R 755 storage bootstrap/cache
   chown -R www-data:www-data storage bootstrap/cache
   ```

### Option 2: Laravel Forge
- Connect GitHub/GitLab repository
- Configure deployment script
- Set up automatic deployments
- Configure SSL certificate
- Set up queue workers
- Configure scheduled jobs

### Option 3: Laravel Vapor (Serverless)
- Install Vapor CLI
- Configure `vapor.yml`
- Deploy with `vapor deploy production`

### Option 4: Docker
```dockerfile
FROM php:8.2-fpm

# Install dependencies
RUN apt-get update && apt-get install -y \
    nginx \
    supervisor \
    redis-server

# Configure nginx, supervisor, etc.
```

## 4. Queue Workers Setup

### Using Supervisor

Create `/etc/supervisor/conf.d/laravel-worker.conf`:

```ini
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/app/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=4
redirect_stderr=true
stdout_logfile=/path/to/app/storage/logs/worker.log
stopwaitsecs=3600
```

Then:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start laravel-worker:*
```

## 5. Scheduled Tasks

Add to crontab:
```bash
* * * * * cd /path/to/app && php artisan schedule:run >> /dev/null 2>&1
```

## 6. Web Server Configuration

### Nginx
```nginx
server {
    listen 80;
    server_name example.com;
    root /path/to/app/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

### Apache
Ensure mod_rewrite is enabled and `.htaccess` is in `public/` directory.

## 7. SSL Certificate

### Using Certbot (Let's Encrypt)
```bash
sudo apt install certbot python3-certbot-nginx
sudo certbot --nginx -d example.com -d www.example.com
```

## 8. Monitoring & Logging

### Error Logging
- Configure log channel in `config/logging.php`
- Consider using services like:
  - Sentry
  - Bugsnag
  - Flare

### Application Monitoring
- Laravel Telescope (development only)
- Laravel Pulse
- New Relic
- Datadog

### Server Monitoring
- Monitor disk space
- Monitor memory usage
- Monitor CPU usage
- Set up alerts

## 9. Post-Deployment

### Verify Everything Works
- [ ] Website loads correctly
- [ ] Database connections work
- [ ] Queue workers are running
- [ ] Scheduled tasks are running
- [ ] Email sending works
- [ ] File uploads work
- [ ] All Livewire components function
- [ ] Forms submit correctly
- [ ] Authentication works

### Performance Check
- [ ] Run Lighthouse audit
- [ ] Check page load times
- [ ] Verify asset loading
- [ ] Test on multiple devices
- [ ] Check database query performance

### Set Up Backups
- [ ] Database backups (daily)
- [ ] File backups (weekly)
- [ ] Test restore process

## 10. Rollback Plan

Create a rollback script:
```bash
#!/bin/bash
cd /path/to/app
git checkout previous-commit
composer install --no-dev
php artisan migrate:rollback
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## 11. Deployment Script Example

Create `deploy.sh`:
```bash
#!/bin/bash

echo "🚀 Deploying application..."

# Pull latest code
git pull origin main

# Install dependencies
composer install --optimize-autoloader --no-dev

# Run migrations
php artisan migrate --force

# Clear caches
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Build assets
npm ci
npm run build

# Restart queue workers
php artisan queue:restart

# Restart PHP-FPM
sudo systemctl reload php8.2-fpm

echo "✅ Deployment complete!"
```

## 12. Livewire-Specific Considerations

- [ ] Verify `@livewireStyles` and `@livewireScripts` are in layout
- [ ] Check Livewire asset URLs are correct
- [ ] Verify WebSocket connections work (if using Livewire 3 polling)
- [ ] Test file uploads with proper storage configuration
- [ ] Check that Livewire component auto-discovery works

Ask the user:
1. What deployment method they prefer?
2. What is their hosting environment?
3. Do they need help with any specific deployment step?
