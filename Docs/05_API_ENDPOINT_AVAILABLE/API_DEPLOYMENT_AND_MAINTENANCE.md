# 🚀 API Deployment & Maintenance - MyRVM-Ecosystem v2.0

## 📍 Deployment Overview

### Production Environment
- **Server**: `100.123.143.87:8001` (MyRVM-Ecosystem-v2)
- **Jetson**: `100.117.234.2:5000` (MyCV-Platform)
- **Database**: PostgreSQL on Docker
- **Cache**: Redis on Docker
- **Web Server**: Nginx on Docker

---

## 🐳 Docker Deployment

### Server Deployment (MyRVM-Ecosystem-v2)
```yaml
# docker-compose.yml
version: '3.8'

services:
  app:
    build: .
    ports:
      - "8001:8000"
    environment:
      - APP_ENV=production
      - APP_DEBUG=false
      - DB_HOST=postgres
      - DB_PORT=5432
      - DB_DATABASE=myrvm_ecosystem
      - DB_USERNAME=myrvm_user
      - DB_PASSWORD=myrvm_password
      - REDIS_HOST=redis
      - REDIS_PORT=6379
    depends_on:
      - postgres
      - redis
    volumes:
      - ./storage:/var/www/html/storage
      - ./bootstrap/cache:/var/www/html/bootstrap/cache
    networks:
      - myrvm-network

  postgres:
    image: postgres:15
    environment:
      - POSTGRES_DB=myrvm_ecosystem
      - POSTGRES_USER=myrvm_user
      - POSTGRES_PASSWORD=myrvm_password
    volumes:
      - postgres_data:/var/lib/postgresql/data
    networks:
      - myrvm-network

  redis:
    image: redis:7-alpine
    volumes:
      - redis_data:/data
    networks:
      - myrvm-network

  nginx:
    image: nginx:alpine
    ports:
      - "80:80"
      - "443:443"
    volumes:
      - ./nginx.conf:/etc/nginx/nginx.conf
      - ./ssl:/etc/nginx/ssl
    depends_on:
      - app
    networks:
      - myrvm-network

volumes:
  postgres_data:
  redis_data:

networks:
  myrvm-network:
    driver: bridge
```

### Jetson Deployment (MyCV-Platform)
```yaml
# docker-compose.yml
version: '3.8'

services:
  jetson-api:
    build: .
    ports:
      - "5000:5000"
    environment:
      - RVM_API_BASE_URL=http://100.123.143.87:8001/api
      - RVM_API_KEY=38bbe1d2ecf75df21546b05340f5878a24e74ed0d8b88d75db1ddeff198380c1
      - API_HOST=0.0.0.0
      - API_PORT=5000
      - API_DEBUG=false
    volumes:
      - ./data-jetson:/app/data-jetson
      - ./models:/app/models
    devices:
      - /dev/nvidia0:/dev/nvidia0
    networks:
      - jetson-network

networks:
  jetson-network:
    driver: bridge
```

---

## 🔧 Deployment Scripts

### Server Deployment Script
```bash
#!/bin/bash
# deploy_server.sh

echo "🚀 Deploying MyRVM-Ecosystem-v2 Server"
echo "======================================"

# Set variables
SERVER_IP="100.123.143.87"
SERVER_PORT="8001"
APP_DIR="/home/my/MySuperApps/MyRVM-Ecosystem-v2"

# Check if server is accessible
if ! ping -c 1 $SERVER_IP > /dev/null 2>&1; then
    echo "❌ Server $SERVER_IP is not accessible"
    exit 1
fi

# Backup current deployment
echo "📦 Creating backup..."
ssh root@$SERVER_IP "cd $APP_DIR && tar -czf backup-$(date +%Y%m%d-%H%M%S).tar.gz ."

# Stop services
echo "🛑 Stopping services..."
ssh root@$SERVER_IP "cd $APP_DIR && docker-compose down"

# Update code
echo "📥 Updating code..."
rsync -avz --delete $APP_DIR/ root@$SERVER_IP:$APP_DIR/

# Install dependencies
echo "📦 Installing dependencies..."
ssh root@$SERVER_IP "cd $APP_DIR && docker-compose run --rm app composer install --no-dev --optimize-autoloader"

# Run migrations
echo "🗄️ Running migrations..."
ssh root@$SERVER_IP "cd $APP_DIR && docker-compose run --rm app php artisan migrate --force"

# Clear caches
echo "🧹 Clearing caches..."
ssh root@$SERVER_IP "cd $APP_DIR && docker-compose run --rm app php artisan config:cache"
ssh root@$SERVER_IP "cd $APP_DIR && docker-compose run --rm app php artisan route:cache"
ssh root@$SERVER_IP "cd $APP_DIR && docker-compose run --rm app php artisan view:cache"

# Start services
echo "▶️ Starting services..."
ssh root@$SERVER_IP "cd $APP_DIR && docker-compose up -d"

# Wait for services to be ready
echo "⏳ Waiting for services to be ready..."
sleep 30

# Health check
echo "🏥 Performing health check..."
if curl -f http://$SERVER_IP:$SERVER_PORT/api/health > /dev/null 2>&1; then
    echo "✅ Deployment successful!"
else
    echo "❌ Health check failed!"
    exit 1
fi

echo "🎉 Server deployment completed!"
```

### Jetson Deployment Script
```bash
#!/bin/bash
# deploy_jetson.sh

echo "🤖 Deploying MyCV-Platform Jetson API"
echo "====================================="

# Set variables
JETSON_IP="100.117.234.2"
JETSON_PORT="5000"
APP_DIR="/home/my/MySuperApps/MyCV-Platform/direct/app/api-hybrid-detection-jetson"

# Check if Jetson is accessible
if ! ping -c 1 $JETSON_IP > /dev/null 2>&1; then
    echo "❌ Jetson $JETSON_IP is not accessible"
    exit 1
fi

# Backup current deployment
echo "📦 Creating backup..."
ssh my@orin1 "cd $APP_DIR && tar -czf backup-$(date +%Y%m%d-%H%M%S).tar.gz ."

# Stop services
echo "🛑 Stopping services..."
ssh my@orin1 "cd $APP_DIR && pkill -f python3"

# Update code
echo "📥 Updating code..."
rsync -avz --delete $APP_DIR/ my@orin1:$APP_DIR/

# Install dependencies
echo "📦 Installing dependencies..."
ssh my@orin1 "cd $APP_DIR && pip install -r ../../requirements.txt"

# Start services
echo "▶️ Starting services..."
ssh my@orin1 "cd $APP_DIR && nohup python3 app.py > app.log 2>&1 &"

# Wait for services to be ready
echo "⏳ Waiting for services to be ready..."
sleep 30

# Health check
echo "🏥 Performing health check..."
if curl -f http://$JETSON_IP:$JETSON_PORT/api/health > /dev/null 2>&1; then
    echo "✅ Deployment successful!"
else
    echo "❌ Health check failed!"
    exit 1
fi

echo "🎉 Jetson deployment completed!"
```

---

## 🔄 Maintenance Procedures

### Daily Maintenance
```bash
#!/bin/bash
# daily_maintenance.sh

echo "🔧 Daily Maintenance"
echo "==================="

# Server maintenance
echo "🖥️ Server maintenance..."
ssh root@100.123.143.87 "cd /home/my/MySuperApps/MyRVM-Ecosystem-v2 && docker-compose exec app php artisan cache:clear"
ssh root@100.123.143.87 "cd /home/my/MySuperApps/MyRVM-Ecosystem-v2 && docker-compose exec app php artisan queue:work --once"

# Jetson maintenance
echo "🤖 Jetson maintenance..."
ssh my@orin1 "cd /home/my/MySuperApps/MyCV-Platform/direct/app/api-hybrid-detection-jetson && python3 -c 'import gc; gc.collect()'"

# Database maintenance
echo "🗄️ Database maintenance..."
ssh root@100.123.143.87 "cd /home/my/MySuperApps/MyRVM-Ecosystem-v2 && docker-compose exec postgres psql -U myrvm_user -d myrvm_ecosystem -c 'VACUUM ANALYZE;'"

# Log rotation
echo "📝 Log rotation..."
ssh root@100.123.143.87 "find /var/log/myrvm -name '*.log' -mtime +7 -delete"

echo "✅ Daily maintenance completed!"
```

### Weekly Maintenance
```bash
#!/bin/bash
# weekly_maintenance.sh

echo "🔧 Weekly Maintenance"
echo "===================="

# Server maintenance
echo "🖥️ Server maintenance..."
ssh root@100.123.143.87 "cd /home/my/MySuperApps/MyRVM-Ecosystem-v2 && docker-compose exec app php artisan optimize"
ssh root@100.123.143.87 "cd /home/my/MySuperApps/MyRVM-Ecosystem-v2 && docker-compose exec app php artisan config:cache"
ssh root@100.123.143.87 "cd /home/my/MySuperApps/MyRVM-Ecosystem-v2 && docker-compose exec app php artisan route:cache"
ssh root@100.123.143.87 "cd /home/my/MySuperApps/MyRVM-Ecosystem-v2 && docker-compose exec app php artisan view:cache"

# Database maintenance
echo "🗄️ Database maintenance..."
ssh root@100.123.143.87 "cd /home/my/MySuperApps/MyRVM-Ecosystem-v2 && docker-compose exec postgres psql -U myrvm_user -d myrvm_ecosystem -c 'REINDEX DATABASE myrvm_ecosystem;'"

# Cleanup old data
echo "🧹 Cleanup old data..."
ssh root@100.123.143.87 "cd /home/my/MySuperApps/MyRVM-Ecosystem-v2 && docker-compose exec app php artisan tinker --execute='App\\Models\\DetectionResult::where(\"created_at\", \"<\", now()->subDays(30))->delete();'"

# Update system packages
echo "📦 Update system packages..."
ssh root@100.123.143.87 "apt update && apt upgrade -y"
ssh my@orin1 "sudo apt update && sudo apt upgrade -y"

echo "✅ Weekly maintenance completed!"
```

### Monthly Maintenance
```bash
#!/bin/bash
# monthly_maintenance.sh

echo "🔧 Monthly Maintenance"
echo "====================="

# Security updates
echo "🔒 Security updates..."
ssh root@100.123.143.87 "apt update && apt upgrade -y"
ssh my@orin1 "sudo apt update && sudo apt upgrade -y"

# Database backup
echo "💾 Database backup..."
BACKUP_FILE="backup-$(date +%Y%m%d).sql"
ssh root@100.123.143.87 "cd /home/my/MySuperApps/MyRVM-Ecosystem-v2 && docker-compose exec postgres pg_dump -U myrvm_user -d myrvm_ecosystem > $BACKUP_FILE"
scp root@100.123.143.87:/home/my/MySuperApps/MyRVM-Ecosystem-v2/$BACKUP_FILE ./backups/

# Log analysis
echo "📊 Log analysis..."
ssh root@100.123.143.87 "cd /var/log/myrvm && grep -c 'ERROR' *.log | sort -nr | head -10"

# Performance analysis
echo "📈 Performance analysis..."
curl -s http://100.123.143.87:8001/api/analytics/dashboard | jq '.data.overview'

# API key rotation
echo "🔑 API key rotation..."
ssh root@100.123.143.87 "cd /home/my/MySuperApps/MyRVM-Ecosystem-v2 && docker-compose exec app php artisan rvm:rotate-api-keys"

echo "✅ Monthly maintenance completed!"
```

---

## 📊 Monitoring & Alerting

### Health Check Script
```bash
#!/bin/bash
# health_check.sh

echo "🏥 System Health Check"
echo "====================="

# Server health
echo "🖥️ Server health..."
if curl -f http://100.123.143.87:8001/api/health > /dev/null 2>&1; then
    echo "✅ Server: Healthy"
else
    echo "❌ Server: Unhealthy"
    exit 1
fi

# Jetson health
echo "🤖 Jetson health..."
if curl -f http://100.117.234.2:5000/api/health > /dev/null 2>&1; then
    echo "✅ Jetson: Healthy"
else
    echo "❌ Jetson: Unhealthy"
    exit 1
fi

# Database health
echo "🗄️ Database health..."
if ssh root@100.123.143.87 "cd /home/my/MySuperApps/MyRVM-Ecosystem-v2 && docker-compose exec postgres psql -U myrvm_user -d myrvm_ecosystem -c 'SELECT 1;'" > /dev/null 2>&1; then
    echo "✅ Database: Healthy"
else
    echo "❌ Database: Unhealthy"
    exit 1
fi

# Redis health
echo "🔴 Redis health..."
if ssh root@100.123.143.87 "cd /home/my/MySuperApps/MyRVM-Ecosystem-v2 && docker-compose exec redis redis-cli ping" | grep -q "PONG"; then
    echo "✅ Redis: Healthy"
else
    echo "❌ Redis: Unhealthy"
    exit 1
fi

echo "🎉 All systems healthy!"
```

### Alerting Script
```bash
#!/bin/bash
# alerting.sh

echo "🚨 Alerting System"
echo "=================="

# Check response times
SERVER_RESPONSE=$(curl -s -w "%{time_total}" -o /dev/null http://100.123.143.87:8001/api/health)
JETSON_RESPONSE=$(curl -s -w "%{time_total}" -o /dev/null http://100.117.234.2:5000/api/health)

if (( $(echo "$SERVER_RESPONSE > 1.0" | bc -l) )); then
    echo "⚠️ Server response time: ${SERVER_RESPONSE}s"
    # Send alert
fi

if (( $(echo "$JETSON_RESPONSE > 1.0" | bc -l) )); then
    echo "⚠️ Jetson response time: ${JETSON_RESPONSE}s"
    # Send alert
fi

# Check error rates
SERVER_ERRORS=$(curl -s http://100.123.143.87:8001/api/health | jq -r '.error_rate // 0')
JETSON_ERRORS=$(curl -s http://100.117.234.2:5000/api/status | jq -r '.error_rate // 0')

if (( $(echo "$SERVER_ERRORS > 0.1" | bc -l) )); then
    echo "⚠️ Server error rate: ${SERVER_ERRORS}%"
    # Send alert
fi

if (( $(echo "$JETSON_ERRORS > 0.1" | bc -l) )); then
    echo "⚠️ Jetson error rate: ${JETSON_ERRORS}%"
    # Send alert
fi

echo "✅ Alerting check completed!"
```

---

## 🔄 Backup & Recovery

### Backup Script
```bash
#!/bin/bash
# backup.sh

echo "💾 System Backup"
echo "==============="

BACKUP_DIR="/home/my/backups/$(date +%Y%m%d)"
mkdir -p $BACKUP_DIR

# Server backup
echo "🖥️ Server backup..."
ssh root@100.123.143.87 "cd /home/my/MySuperApps/MyRVM-Ecosystem-v2 && tar -czf server-backup-$(date +%Y%m%d).tar.gz ."
scp root@100.123.143.87:/home/my/MySuperApps/MyRVM-Ecosystem-v2/server-backup-$(date +%Y%m%d).tar.gz $BACKUP_DIR/

# Database backup
echo "🗄️ Database backup..."
ssh root@100.123.143.87 "cd /home/my/MySuperApps/MyRVM-Ecosystem-v2 && docker-compose exec postgres pg_dump -U myrvm_user -d myrvm_ecosystem > db-backup-$(date +%Y%m%d).sql"
scp root@100.123.143.87:/home/my/MySuperApps/MyRVM-Ecosystem-v2/db-backup-$(date +%Y%m%d).sql $BACKUP_DIR/

# Jetson backup
echo "🤖 Jetson backup..."
ssh my@orin1 "cd /home/my/MySuperApps/MyCV-Platform/direct/app/api-hybrid-detection-jetson && tar -czf jetson-backup-$(date +%Y%m%d).tar.gz ."
scp my@orin1:/home/my/MySuperApps/MyCV-Platform/direct/app/api-hybrid-detection-jetson/jetson-backup-$(date +%Y%m%d).tar.gz $BACKUP_DIR/

# Configuration backup
echo "⚙️ Configuration backup..."
cp -r /home/my/MySuperApps/Docs $BACKUP_DIR/
cp -r /home/my/MySuperApps/MyRVM-Ecosystem-v2/.env $BACKUP_DIR/server.env
cp /home/my/MySuperApps/MyCV-Platform/direct/app/api-hybrid-detection-jetson/rvm_config.env $BACKUP_DIR/jetson.env

echo "✅ Backup completed: $BACKUP_DIR"
```

### Recovery Script
```bash
#!/bin/bash
# recovery.sh

echo "🔄 System Recovery"
echo "================="

BACKUP_DATE=$1
if [ -z "$BACKUP_DATE" ]; then
    echo "Usage: $0 <backup_date> (YYYYMMDD)"
    exit 1
fi

BACKUP_DIR="/home/my/backups/$BACKUP_DATE"
if [ ! -d "$BACKUP_DIR" ]; then
    echo "❌ Backup directory not found: $BACKUP_DIR"
    exit 1
fi

# Server recovery
echo "🖥️ Server recovery..."
scp $BACKUP_DIR/server-backup-$BACKUP_DATE.tar.gz root@100.123.143.87:/tmp/
ssh root@100.123.143.87 "cd /home/my/MySuperApps/MyRVM-Ecosystem-v2 && tar -xzf /tmp/server-backup-$BACKUP_DATE.tar.gz"

# Database recovery
echo "🗄️ Database recovery..."
scp $BACKUP_DIR/db-backup-$BACKUP_DATE.sql root@100.123.143.87:/tmp/
ssh root@100.123.143.87 "cd /home/my/MySuperApps/MyRVM-Ecosystem-v2 && docker-compose exec postgres psql -U myrvm_user -d myrvm_ecosystem -f /tmp/db-backup-$BACKUP_DATE.sql"

# Jetson recovery
echo "🤖 Jetson recovery..."
scp $BACKUP_DIR/jetson-backup-$BACKUP_DATE.tar.gz my@orin1:/tmp/
ssh my@orin1 "cd /home/my/MySuperApps/MyCV-Platform/direct/app/api-hybrid-detection-jetson && tar -xzf /tmp/jetson-backup-$BACKUP_DATE.tar.gz"

# Restart services
echo "▶️ Restarting services..."
ssh root@100.123.143.87 "cd /home/my/MySuperApps/MyRVM-Ecosystem-v2 && docker-compose restart"
ssh my@orin1 "cd /home/my/MySuperApps/MyCV-Platform/direct/app/api-hybrid-detection-jetson && pkill -f python3 && nohup python3 app.py > app.log 2>&1 &"

echo "✅ Recovery completed!"
```

---

## 📊 Deployment Monitoring

### Deployment Status Dashboard
```html
<!DOCTYPE html>
<html>
<head>
    <title>MyRVM Deployment Status</title>
    <style>
        .status-card { border: 1px solid #ddd; margin: 10px; padding: 20px; border-radius: 5px; }
        .healthy { background-color: #d4edda; border-color: #c3e6cb; }
        .unhealthy { background-color: #f8d7da; border-color: #f5c6cb; }
        .warning { background-color: #fff3cd; border-color: #ffeaa7; }
    </style>
</head>
<body>
    <h1>MyRVM Deployment Status</h1>
    
    <div class="status-card" id="server-status">
        <h3>Server (MyRVM-Ecosystem-v2)</h3>
        <p>Status: <span id="server-health">Checking...</span></p>
        <p>Response Time: <span id="server-response">-</span></p>
        <p>Uptime: <span id="server-uptime">-</span></p>
    </div>
    
    <div class="status-card" id="jetson-status">
        <h3>Jetson (MyCV-Platform)</h3>
        <p>Status: <span id="jetson-health">Checking...</span></p>
        <p>Response Time: <span id="jetson-response">-</span></p>
        <p>GPU Usage: <span id="jetson-gpu">-</span></p>
    </div>
    
    <div class="status-card" id="database-status">
        <h3>Database (PostgreSQL)</h3>
        <p>Status: <span id="database-health">Checking...</span></p>
        <p>Connections: <span id="database-connections">-</span></p>
        <p>Size: <span id="database-size">-</span></p>
    </div>
    
    <script>
        function updateStatus() {
            // Update server status
            fetch('http://100.123.143.87:8001/api/health')
                .then(response => response.json())
                .then(data => {
                    document.getElementById('server-health').textContent = data.status;
                    document.getElementById('server-status').className = 'status-card healthy';
                })
                .catch(error => {
                    document.getElementById('server-health').textContent = 'Unhealthy';
                    document.getElementById('server-status').className = 'status-card unhealthy';
                });
            
            // Update Jetson status
            fetch('http://100.117.234.2:5000/api/health')
                .then(response => response.json())
                .then(data => {
                    document.getElementById('jetson-health').textContent = data.status;
                    document.getElementById('jetson-status').className = 'status-card healthy';
                })
                .catch(error => {
                    document.getElementById('jetson-health').textContent = 'Unhealthy';
                    document.getElementById('jetson-status').className = 'status-card unhealthy';
                });
        }
        
        // Update every 30 seconds
        setInterval(updateStatus, 30000);
        updateStatus();
    </script>
</body>
</html>
```

---

## 🔧 Troubleshooting

### Common Issues

#### 1. Service Not Starting
```bash
# Check Docker logs
docker-compose logs app
docker-compose logs postgres
docker-compose logs redis

# Check system resources
docker stats
df -h
free -h
```

#### 2. Database Connection Issues
```bash
# Check database status
docker-compose exec postgres psql -U myrvm_user -d myrvm_ecosystem -c "SELECT 1;"

# Check connection pool
docker-compose exec postgres psql -U myrvm_user -d myrvm_ecosystem -c "SELECT count(*) FROM pg_stat_activity;"
```

#### 3. API Not Responding
```bash
# Check API health
curl -v http://100.123.143.87:8001/api/health
curl -v http://100.117.234.2:5000/api/health

# Check network connectivity
ping 100.123.143.87
ping 100.117.234.2
```

#### 4. Performance Issues
```bash
# Check system resources
htop
nvidia-smi
iostat 1

# Check API response times
curl -w "@curl-format.txt" -o /dev/null -s http://100.123.143.87:8001/api/health
```

---

**Last Updated**: 2025-01-02  
**Version**: 2.0.0  
**Status**: ✅ COMPLETE DEPLOYMENT & MAINTENANCE DOCUMENTATION
