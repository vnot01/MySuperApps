# Server Deployment Guide
**Date:** 2025-01-21  
**Version:** 2.1  
**Target:** MyRVM-Platform Backend System  
**Status:** ✅ **PRODUCTION READY**

## 📋 Overview

This guide provides comprehensive instructions for deploying the MyRVM-Platform backend system in both development and production environments. The system is built with Laravel 12, PHP 8.2+, and PostgreSQL.

## 🏗️ Architecture Overview

### System Components
```
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   Nginx         │    │   Laravel App   │    │   PostgreSQL    │
│   (Web Server)  │◄──►│   (PHP-FPM)     │◄──►│   (Database)    │
│   Port: 80/443  │    │   Port: 9000    │    │   Port: 5432    │
└─────────────────┘    └─────────────────┘    └─────────────────┘
```

## 🚀 Quick Start

### Prerequisites
- Docker & Docker Compose
- Git
- 4GB RAM minimum
- 20GB disk space

### 1. Clone Repository
```bash
git clone https://github.com/vnot01/MySuperApps.git
cd MySuperApps/MyRVM-Platform
```

### 2. Environment Setup
```bash
# Copy environment file
cp .env.example .env

# Edit configuration
nano .env
```

### 3. Start Services
```bash
# Start all services
docker compose up -d

# Check status
docker compose ps
```

### 4. Initialize Database
```bash
# Run migrations
docker compose exec --user 1000:1000 app php artisan migrate

# Seed sample data
docker compose exec --user 1000:1000 app php artisan db:seed
```

### 5. Verify Installation
```bash
# Test health check
curl -X GET "http://localhost:8001/api/health-check"

# Expected response
{
    "success": true,
    "message": "MyRVM Platform is healthy",
    "data": {
        "status": "healthy",
        "database": {
            "status": "connected"
        }
    }
}
```

## 🔧 Development Environment

### Local Development Setup

#### 1. Environment Configuration
```bash
# .env for development
APP_NAME=MyRVM-Platform
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8001

DB_CONNECTION=pgsql
DB_HOST=postgres
DB_PORT=5432
DB_DATABASE=myrvm_platform
DB_USERNAME=myrvm_user
DB_PASSWORD=secure_password

SANCTUM_STATEFUL_DOMAINS=localhost:8001,172.28.233.83,10.3.52.161
```

#### 2. Docker Compose Configuration
```yaml
# docker-compose.yml
version: '3.8'

services:
  app:
    build:
      context: .
      dockerfile: Dockerfile
    container_name: myrvm-app
    restart: unless-stopped
    working_dir: /var/www/html
    volumes:
      - ./:/var/www/html
      - ./docker/php/local.ini:/usr/local/etc/php/conf.d/local.ini
    networks:
      - myrvm-network
    depends_on:
      - postgres
      - redis

  postgres:
    image: postgres:15-alpine
    container_name: myrvm-postgres
    restart: unless-stopped
    environment:
      POSTGRES_DB: myrvm_platform
      POSTGRES_USER: myrvm_user
      POSTGRES_PASSWORD: secure_password
    volumes:
      - postgres_data:/var/lib/postgresql/data
    networks:
      - myrvm-network

  redis:
    image: redis:7-alpine
    container_name: myrvm-redis
    restart: unless-stopped
    networks:
      - myrvm-network

  nginx:
    image: nginx:alpine
    container_name: myrvm-nginx
    restart: unless-stopped
    ports:
      - "8001:80"
    volumes:
      - ./:/var/www/html
      - ./docker/nginx/default.conf:/etc/nginx/conf.d/default.conf
    networks:
      - myrvm-network
    depends_on:
      - app

networks:
  myrvm-network:
    driver: bridge

volumes:
  postgres_data:
    driver: local
```

#### 3. Development Commands
```bash
# Start development environment
docker compose up -d

# View logs
docker compose logs -f app

# Run artisan commands
docker compose exec --user 1000:1000 app php artisan migrate
docker compose exec --user 1000:1000 app php artisan cache:clear
docker compose exec --user 1000:1000 app php artisan route:clear

# Access container shell
docker compose exec --user 1000:1000 app bash

# Stop services
docker compose down
```

## 🏭 Production Environment

### Production Deployment

#### 1. Server Requirements
- **CPU:** 2+ cores
- **RAM:** 4GB minimum, 8GB recommended
- **Storage:** 50GB SSD
- **OS:** Ubuntu 20.04+ or CentOS 8+
- **Network:** Static IP with domain name

#### 2. Production Environment Configuration
```bash
# .env for production
APP_NAME=MyRVM-Platform
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

DB_CONNECTION=pgsql
DB_HOST=postgres
DB_PORT=5432
DB_DATABASE=myrvm_platform
DB_USERNAME=myrvm_user
DB_PASSWORD=very_secure_password

SANCTUM_STATEFUL_DOMAINS=yourdomain.com,api.yourdomain.com

# SSL Configuration
FORCE_HTTPS=true
```

#### 3. Production Docker Compose
```yaml
# docker-compose.prod.yml
version: '3.8'

services:
  app:
    build:
      context: .
      dockerfile: Dockerfile.prod
    container_name: myrvm-app-prod
    restart: always
    working_dir: /var/www/html
    volumes:
      - ./:/var/www/html
      - ./docker/php/production.ini:/usr/local/etc/php/conf.d/production.ini
    networks:
      - myrvm-network
    depends_on:
      - postgres
      - redis

  postgres:
    image: postgres:15-alpine
    container_name: myrvm-postgres-prod
    restart: always
    environment:
      POSTGRES_DB: myrvm_platform
      POSTGRES_USER: myrvm_user
      POSTGRES_PASSWORD: very_secure_password
    volumes:
      - postgres_data:/var/lib/postgresql/data
      - ./backups:/backups
    networks:
      - myrvm-network

  redis:
    image: redis:7-alpine
    container_name: myrvm-redis-prod
    restart: always
    volumes:
      - redis_data:/data
    networks:
      - myrvm-network

  nginx:
    image: nginx:alpine
    container_name: myrvm-nginx-prod
    restart: always
    ports:
      - "80:80"
      - "443:443"
    volumes:
      - ./:/var/www/html
      - ./docker/nginx/production.conf:/etc/nginx/conf.d/default.conf
      - ./ssl:/etc/nginx/ssl
    networks:
      - myrvm-network
    depends_on:
      - app

networks:
  myrvm-network:
    driver: bridge

volumes:
  postgres_data:
    driver: local
  redis_data:
    driver: local
```

#### 4. SSL Configuration
```bash
# Install Certbot
sudo apt update
sudo apt install certbot python3-certbot-nginx

# Generate SSL certificate
sudo certbot --nginx -d yourdomain.com -d api.yourdomain.com

# Auto-renewal
sudo crontab -e
# Add: 0 12 * * * /usr/bin/certbot renew --quiet
```

#### 5. Production Deployment Steps
```bash
# 1. Clone repository
git clone https://github.com/vnot01/MySuperApps.git
cd MySuperApps/MyRVM-Platform

# 2. Configure environment
cp .env.example .env
nano .env  # Configure production settings

# 3. Build and start services
docker compose -f docker-compose.prod.yml up -d --build

# 4. Initialize database
docker compose -f docker-compose.prod.yml exec --user 1000:1000 app php artisan migrate --force

# 5. Optimize for production
docker compose -f docker-compose.prod.yml exec --user 1000:1000 app php artisan config:cache
docker compose -f docker-compose.prod.yml exec --user 1000:1000 app php artisan route:cache
docker compose -f docker-compose.prod.yml exec --user 1000:1000 app php artisan view:cache

# 6. Set proper permissions
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

## 🔧 Configuration Files

### Nginx Configuration
```nginx
# docker/nginx/production.conf
server {
    listen 80;
    server_name yourdomain.com api.yourdomain.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name yourdomain.com api.yourdomain.com;
    
    root /var/www/html/public;
    index index.php index.html;
    
    ssl_certificate /etc/nginx/ssl/cert.pem;
    ssl_certificate_key /etc/nginx/ssl/key.pem;
    
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers ECDHE-RSA-AES256-GCM-SHA512:DHE-RSA-AES256-GCM-SHA512;
    ssl_prefer_server_ciphers off;
    
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    location ~ \.php$ {
        fastcgi_pass app:9000;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
    
    location ~ /\.ht {
        deny all;
    }
    
    # Security headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header Referrer-Policy "no-referrer-when-downgrade" always;
    add_header Content-Security-Policy "default-src 'self' http: https: data: blob: 'unsafe-inline'" always;
}
```

### PHP Configuration
```ini
# docker/php/production.ini
[PHP]
engine = On
short_open_tag = Off
precision = 14
output_buffering = 4096
zlib.output_compression = Off
implicit_flush = Off
unserialize_callback_func =
serialize_precision = -1
disable_functions =
disable_classes =
zend.enable_gc = On
zend.exception_ignore_args = On

expose_php = Off

max_execution_time = 300
max_input_time = 300
memory_limit = 512M

error_reporting = E_ALL & ~E_DEPRECATED & ~E_STRICT
display_errors = Off
display_startup_errors = Off
log_errors = On
log_errors_max_len = 1024
ignore_repeated_errors = Off
ignore_repeated_source = Off
report_memleaks = On

post_max_size = 100M
upload_max_filesize = 100M
max_file_uploads = 20

date.timezone = Asia/Jakarta

[opcache]
opcache.enable=1
opcache.enable_cli=1
opcache.memory_consumption=128
opcache.interned_strings_buffer=8
opcache.max_accelerated_files=4000
opcache.revalidate_freq=2
opcache.fast_shutdown=1
```

## 📊 Monitoring & Maintenance

### Health Monitoring
```bash
# Check service status
docker compose ps

# View logs
docker compose logs -f app
docker compose logs -f postgres
docker compose logs -f nginx

# Check resource usage
docker stats

# Test API endpoints
curl -X GET "https://yourdomain.com/api/health-check"
```

### Database Maintenance
```bash
# Backup database
docker compose exec postgres pg_dump -U myrvm_user myrvm_platform > backup_$(date +%Y%m%d_%H%M%S).sql

# Restore database
docker compose exec -T postgres psql -U myrvm_user myrvm_platform < backup_file.sql

# Optimize database
docker compose exec --user 1000:1000 app php artisan migrate:status
docker compose exec --user 1000:1000 app php artisan db:show
```

### Performance Optimization
```bash
# Clear caches
docker compose exec --user 1000:1000 app php artisan cache:clear
docker compose exec --user 1000:1000 app php artisan config:clear
docker compose exec --user 1000:1000 app php artisan route:clear
docker compose exec --user 1000:1000 app php artisan view:clear

# Optimize for production
docker compose exec --user 1000:1000 app php artisan config:cache
docker compose exec --user 1000:1000 app php artisan route:cache
docker compose exec --user 1000:1000 app php artisan view:cache

# Queue processing
docker compose exec --user 1000:1000 app php artisan queue:work --daemon
```

## 🔒 Security Configuration

### Firewall Setup
```bash
# UFW configuration
sudo ufw enable
sudo ufw allow 22/tcp
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw deny 5432/tcp
```

### Docker Security
```bash
# Run containers as non-root user
docker compose exec --user 1000:1000 app php artisan migrate

# Set proper file permissions
sudo chown -R 1000:1000 storage bootstrap/cache
sudo chmod -R 755 storage bootstrap/cache
```

### Environment Security
```bash
# Generate application key
docker compose exec --user 1000:1000 app php artisan key:generate

# Generate JWT secret
docker compose exec --user 1000:1000 app php artisan jwt:secret

# Set secure database password
# Use strong passwords with special characters
```

## 🚨 Troubleshooting

### Common Issues

#### 1. Database Connection Issues
```bash
# Check database status
docker compose exec postgres pg_isready -U myrvm_user

# Test connection
docker compose exec --user 1000:1000 app php artisan tinker
>>> DB::connection()->getPdo();
```

#### 2. Permission Issues
```bash
# Fix file permissions
sudo chown -R 1000:1000 storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

#### 3. Memory Issues
```bash
# Increase PHP memory limit
# Edit docker/php/production.ini
memory_limit = 1024M

# Restart services
docker compose restart app
```

#### 4. SSL Certificate Issues
```bash
# Check certificate status
sudo certbot certificates

# Renew certificate
sudo certbot renew --dry-run
```

## 📈 Performance Tuning

### Database Optimization
```sql
-- Analyze query performance
EXPLAIN ANALYZE SELECT * FROM deposits WHERE user_id = 1;

-- Update table statistics
ANALYZE deposits;

-- Reindex tables
REINDEX TABLE deposits;
```

### Application Optimization
```bash
# Enable OPcache
# Already configured in production.ini

# Use Redis for caching
# Configure in .env
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis
```

### Nginx Optimization
```nginx
# Enable gzip compression
gzip on;
gzip_vary on;
gzip_min_length 1024;
gzip_types text/plain text/css application/json application/javascript text/xml application/xml application/xml+rss text/javascript;

# Enable HTTP/2
listen 443 ssl http2;
```

## 📞 Support

### Log Locations
- **Application Logs:** `storage/logs/laravel.log`
- **Nginx Logs:** `var/log/nginx/`
- **Docker Logs:** `docker compose logs`

### Monitoring Commands
```bash
# System resources
htop
df -h
free -h

# Docker resources
docker stats
docker system df

# Application health
curl -X GET "https://yourdomain.com/api/health-check"
```

---

**Deployment Guide Generated:** 2025-01-21  
**Version:** 2.1  
**Status:** ✅ Production Ready
