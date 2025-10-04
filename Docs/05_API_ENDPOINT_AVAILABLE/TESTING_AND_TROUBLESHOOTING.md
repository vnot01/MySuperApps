# 🧪 Testing & Troubleshooting Guide - MyRVM-Ecosystem v2.0

## 📍 Network Configuration
- **Server**: `100.123.143.87:8001` (MyRVM-Ecosystem-v2)
- **Jetson**: `100.117.234.2:5000` (MyCV-Platform)
- **Multi-RVM**: `100.117.234.X:5000` (Scalable RVM Network)

---

## 🧪 Testing Scripts

### 1. Complete System Health Check
```bash
#!/bin/bash
# complete_health_check.sh

echo "🏥 Complete System Health Check"
echo "================================"

# Server Health Check
echo "1. Checking Server Health..."
SERVER_HEALTH=$(curl -s -o /dev/null -w "%{http_code}" http://100.123.143.87:8001/api/health)
if [ $SERVER_HEALTH -eq 200 ]; then
    echo "✅ Server: Healthy"
else
    echo "❌ Server: Unhealthy (HTTP $SERVER_HEALTH)"
fi

# Jetson Health Check
echo "2. Checking Jetson Health..."
JETSON_HEALTH=$(curl -s -o /dev/null -w "%{http_code}" http://100.117.234.2:5000/api/health)
if [ $JETSON_HEALTH -eq 200 ]; then
    echo "✅ Jetson: Healthy"
else
    echo "❌ Jetson: Unhealthy (HTTP $JETSON_HEALTH)"
fi

# Server Status Check
echo "3. Checking Server Status..."
curl -s http://100.123.143.87:8001/api/health | jq '.'

# Jetson Status Check
echo "4. Checking Jetson Status..."
curl -s http://100.117.234.2:5000/api/status | jq '.'

# Jetson Hardware Check
echo "5. Checking Jetson Hardware..."
curl -s http://100.117.234.2:5000/api/hardware | jq '.'

echo "🏥 Health check completed!"
```

### 2. Authentication Test
```bash
#!/bin/bash
# authentication_test.sh

echo "🔐 Authentication Test"
echo "====================="

# Test User Login
echo "1. Testing User Login..."
LOGIN_RESPONSE=$(curl -s -X POST http://100.123.143.87:8001/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "admin@myrvm.com",
    "password": "password123"
  }')

echo "Login Response:"
echo $LOGIN_RESPONSE | jq '.'

# Extract token
TOKEN=$(echo $LOGIN_RESPONSE | jq -r '.token')

if [ "$TOKEN" != "null" ] && [ "$TOKEN" != "" ]; then
    echo "✅ Login successful, token: ${TOKEN:0:20}..."
    
    # Test authenticated request
    echo "2. Testing Authenticated Request..."
    curl -s -X GET http://100.123.143.87:8001/api/rvms \
      -H "Authorization: Bearer $TOKEN" | jq '.'
else
    echo "❌ Login failed"
fi

# Test RVM API Key Validation
echo "3. Testing RVM API Key Validation..."
curl -s -X POST http://100.117.234.2:5000/api/rvm/validate \
  -H "Content-Type: application/json" \
  -d '{
    "api_key": "38bbe1d2ecf75df21546b05340f5878a24e74ed0d8b88d75db1ddeff198380c1"
  }' | jq '.'

echo "🔐 Authentication test completed!"
```

### 3. RVM Management Test
```bash
#!/bin/bash
# rvm_management_test.sh

echo "🏪 RVM Management Test"
echo "====================="

# Get authentication token
TOKEN=$(curl -s -X POST http://100.123.143.87:8001/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@myrvm.com","password":"password123"}' | jq -r '.token')

if [ "$TOKEN" = "null" ] || [ "$TOKEN" = "" ]; then
    echo "❌ Authentication failed"
    exit 1
fi

# Test Get All RVMs
echo "1. Testing Get All RVMs..."
curl -s -X GET http://100.123.143.87:8001/api/rvms \
  -H "Authorization: Bearer $TOKEN" | jq '.'

# Test Create RVM
echo "2. Testing Create RVM..."
CREATE_RESPONSE=$(curl -s -X POST http://100.123.143.87:8001/api/rvms \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Test-RVM-001",
    "location": "Test Location",
    "ip_address": "100.117.234.5",
    "address": "Test Address",
    "latitude": -6.200000,
    "longitude": 106.816666
  }')

echo "Create Response:"
echo $CREATE_RESPONSE | jq '.'

# Extract RVM ID
RVM_ID=$(echo $CREATE_RESPONSE | jq -r '.data.id')

if [ "$RVM_ID" != "null" ] && [ "$RVM_ID" != "" ]; then
    echo "✅ RVM created with ID: $RVM_ID"
    
    # Test Get RVM Details
    echo "3. Testing Get RVM Details..."
    curl -s -X GET http://100.123.143.87:8001/api/rvms/$RVM_ID \
      -H "Authorization: Bearer $TOKEN" | jq '.'
    
    # Test Update RVM
    echo "4. Testing Update RVM..."
    curl -s -X PUT http://100.123.143.87:8001/api/rvms/$RVM_ID \
      -H "Authorization: Bearer $TOKEN" \
      -H "Content-Type: application/json" \
      -d '{
        "name": "Test-RVM-001-Updated",
        "location": "Test Location Updated"
      }' | jq '.'
    
    # Test Delete RVM
    echo "5. Testing Delete RVM..."
    curl -s -X DELETE http://100.123.143.87:8001/api/rvms/$RVM_ID \
      -H "Authorization: Bearer $TOKEN" | jq '.'
    
    echo "✅ RVM management test completed!"
else
    echo "❌ RVM creation failed"
fi
```

### 4. Detection Flow Test
```bash
#!/bin/bash
# detection_flow_test.sh

echo "🔍 Detection Flow Test"
echo "====================="

# Test Upload to Jetson
echo "1. Testing Upload to Jetson..."
UPLOAD_RESPONSE=$(curl -s -X POST http://100.117.234.2:5000/api/upload \
  -H "X-RVM-API-Key: 38bbe1d2ecf75df21546b05340f5878a24e74ed0d8b88d75db1ddeff198380c1" \
  -F "files=@test_image.jpg" \
  -F "user_id=test_user" \
  -F "rvm_id=1")

echo "Upload Response:"
echo $UPLOAD_RESPONSE | jq '.'

# Extract session ID
SESSION_ID=$(echo $UPLOAD_RESPONSE | jq -r '.session_id')

if [ "$SESSION_ID" != "null" ] && [ "$SESSION_ID" != "" ]; then
    echo "✅ Upload successful, session: $SESSION_ID"
    
    # Test Processing Status
    echo "2. Testing Processing Status..."
    for i in {1..5}; do
        echo "Check $i/5..."
        STATUS_RESPONSE=$(curl -s -X GET http://100.117.234.2:5000/api/process/$SESSION_ID)
        echo $STATUS_RESPONSE | jq '.'
        
        STATUS=$(echo $STATUS_RESPONSE | jq -r '.status')
        if [ "$STATUS" = "completed" ]; then
            echo "✅ Processing completed!"
            break
        elif [ "$STATUS" = "failed" ]; then
            echo "❌ Processing failed!"
            break
        else
            echo "⏳ Processing in progress..."
            sleep 10
        fi
    done
    
    # Test Get Results
    echo "3. Testing Get Results..."
    curl -s -X GET http://100.117.234.2:5000/api/results/$SESSION_ID | jq '.'
    
    echo "✅ Detection flow test completed!"
else
    echo "❌ Upload failed"
fi
```

### 5. Economy System Test
```bash
#!/bin/bash
# economy_system_test.sh

echo "💰 Economy System Test"
echo "====================="

# Get authentication token
TOKEN=$(curl -s -X POST http://100.123.143.87:8001/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@myrvm.com","password":"password123"}' | jq -r '.token')

if [ "$TOKEN" = "null" ] || [ "$TOKEN" = "" ]; then
    echo "❌ Authentication failed"
    exit 1
fi

# Test Get Balance
echo "1. Testing Get Balance..."
curl -s -X GET http://100.123.143.87:8001/api/economy/balance \
  -H "Authorization: Bearer $TOKEN" | jq '.'

# Test Calculate Reward
echo "2. Testing Calculate Reward..."
curl -s -X POST http://100.123.143.87:8001/api/economy/calculate-reward \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "waste_type": "plastic_bottle",
    "weight": 0.5,
    "quality_grade": "A",
    "confidence": 0.95
  }' | jq '.'

# Test Add Balance
echo "3. Testing Add Balance..."
curl -s -X POST http://100.123.143.87:8001/api/economy/balance/add \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "amount": 25.0,
    "description": "Test reward for plastic bottle"
  }' | jq '.'

# Test Get Transactions
echo "4. Testing Get Transactions..."
curl -s -X GET "http://100.123.143.87:8001/api/economy/transactions?page=1&limit=10" \
  -H "Authorization: Bearer $TOKEN" | jq '.'

# Test Get Vouchers
echo "5. Testing Get Vouchers..."
curl -s -X GET http://100.123.143.87:8001/api/economy/vouchers \
  -H "Authorization: Bearer $TOKEN" | jq '.'

echo "✅ Economy system test completed!"
```

### 6. Monitoring Test
```bash
#!/bin/bash
# monitoring_test.sh

echo "📊 Monitoring Test"
echo "================="

# Test Jetson Monitoring Status
echo "1. Testing Jetson Monitoring Status..."
curl -s -X GET http://100.117.234.2:5000/api/monitoring/status | jq '.'

# Test Performance Summary
echo "2. Testing Performance Summary..."
curl -s -X GET "http://100.117.234.2:5000/api/monitoring/summary?hours=1" | jq '.'

# Test Recent Alerts
echo "3. Testing Recent Alerts..."
curl -s -X GET http://100.117.234.2:5000/api/monitoring/alerts | jq '.'

# Test Server Analytics
echo "4. Testing Server Analytics..."
TOKEN=$(curl -s -X POST http://100.123.143.87:8001/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@myrvm.com","password":"password123"}' | jq -r '.token')

curl -s -X GET "http://100.123.143.87:8001/api/analytics/dashboard?period=1d" \
  -H "Authorization: Bearer $TOKEN" | jq '.'

echo "✅ Monitoring test completed!"
```

---

## 🔧 Troubleshooting Guide

### Common Issues and Solutions

#### 1. Server Connection Issues

**Problem**: Cannot connect to server
```bash
curl: (7) Failed to connect to 100.123.143.87 port 8001: Connection refused
```

**Solutions**:
```bash
# Check if server is running
docker ps | grep myrvm

# Check server logs
docker logs myrvm-app

# Restart server
docker-compose restart app

# Check port availability
netstat -tlnp | grep 8001
```

#### 2. Jetson Connection Issues

**Problem**: Cannot connect to Jetson
```bash
curl: (7) Failed to connect to 100.117.234.2 port 5000: Connection refused
```

**Solutions**:
```bash
# Check if Jetson API is running
ssh my@orin1 "ps aux | grep python"

# Check Jetson logs
ssh my@orin1 "tail -f /path/to/jetson/logs"

# Restart Jetson API
ssh my@orin1 "cd /path/to/api && python3 app.py"

# Check network connectivity
ping 100.117.234.2
```

#### 3. Authentication Issues

**Problem**: 401 Unauthorized
```json
{
    "success": false,
    "error": "Unauthorized",
    "code": "UNAUTHORIZED"
}
```

**Solutions**:
```bash
# Check token validity
echo "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..." | base64 -d

# Re-authenticate
curl -X POST http://100.123.143.87:8001/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@myrvm.com","password":"password123"}'

# Check RVM API key
curl -X POST http://100.117.234.2:5000/api/rvm/validate \
  -H "Content-Type: application/json" \
  -d '{"api_key":"your_api_key_here"}'
```

#### 4. Database Connection Issues

**Problem**: Database connection failed
```json
{
    "success": false,
    "error": "Database connection failed",
    "code": "DATABASE_ERROR"
}
```

**Solutions**:
```bash
# Check database status
docker ps | grep postgres

# Check database logs
docker logs myrvm-postgres

# Test database connection
docker exec -it myrvm-app php artisan tinker
>>> DB::connection()->getPdo();

# Restart database
docker-compose restart postgres
```

#### 5. File Upload Issues

**Problem**: File upload failed
```json
{
    "error": "Upload failed: File too large"
}
```

**Solutions**:
```bash
# Check file size limits
curl -X GET http://100.117.234.2:5000/api/status | jq '.max_file_size'

# Check disk space
df -h

# Check upload directory permissions
ls -la /path/to/upload/directory

# Test with smaller file
curl -X POST http://100.117.234.2:5000/api/upload \
  -F "files=@small_test_image.jpg" \
  -F "user_id=test_user"
```

#### 6. Processing Issues

**Problem**: Detection processing failed
```json
{
    "status": "failed",
    "message": "Detection failed: Model not found"
}
```

**Solutions**:
```bash
# Check model files
ls -la /path/to/models/

# Check GPU availability
curl -X GET http://100.117.234.2:5000/api/hardware | jq '.cuda_info'

# Check processing logs
tail -f /path/to/processing/logs

# Test with different image
curl -X POST http://100.117.234.2:5000/api/upload \
  -F "files=@test_image.jpg" \
  -F "user_id=test_user"
```

---

## 📊 Performance Testing

### Load Testing Script
```bash
#!/bin/bash
# load_test.sh

echo "🚀 Load Testing"
echo "==============="

# Test concurrent uploads
echo "1. Testing Concurrent Uploads..."
for i in {1..10}; do
    (
        curl -s -X POST http://100.117.234.2:5000/api/upload \
          -F "files=@test_image.jpg" \
          -F "user_id=test_user_$i" \
          -F "rvm_id=1" &
    )
done
wait

# Test concurrent API calls
echo "2. Testing Concurrent API Calls..."
for i in {1..20}; do
    (
        curl -s -X GET http://100.123.143.87:8001/api/health &
        curl -s -X GET http://100.117.234.2:5000/api/health &
    )
done
wait

# Test memory usage
echo "3. Testing Memory Usage..."
curl -s -X GET http://100.117.234.2:5000/api/monitoring/status | jq '.monitoring.current_metrics.memory_percent'

echo "✅ Load testing completed!"
```

### Stress Testing Script
```bash
#!/bin/bash
# stress_test.sh

echo "💪 Stress Testing"
echo "================="

# Test with large files
echo "1. Testing Large File Uploads..."
curl -X POST http://100.117.234.2:5000/api/upload \
  -F "files=@large_image.jpg" \
  -F "user_id=stress_test" \
  -F "rvm_id=1"

# Test rapid API calls
echo "2. Testing Rapid API Calls..."
for i in {1..100}; do
    curl -s -X GET http://100.117.234.2:5000/api/health > /dev/null &
done
wait

# Test system under load
echo "3. Testing System Under Load..."
while true; do
    curl -s -X GET http://100.117.234.2:5000/api/monitoring/status | jq '.monitoring.current_metrics'
    sleep 1
done
```

---

## 🔍 Debugging Tools

### Network Debugging
```bash
# Check network connectivity
ping 100.123.143.87
ping 100.117.234.2

# Check port availability
nmap -p 8001 100.123.143.87
nmap -p 5000 100.117.234.2

# Check DNS resolution
nslookup 100.123.143.87
nslookup 100.117.234.2

# Check routing
traceroute 100.123.143.87
traceroute 100.117.234.2
```

### API Debugging
```bash
# Enable verbose curl
curl -v -X GET http://100.123.143.87:8001/api/health

# Check response headers
curl -I -X GET http://100.123.143.87:8001/api/health

# Test with different user agents
curl -X GET http://100.123.143.87:8001/api/health \
  -H "User-Agent: MyRVM-Test-Client/1.0"

# Check SSL/TLS (if applicable)
openssl s_client -connect 100.123.143.87:8001
```

### Database Debugging
```bash
# Check database connections
docker exec -it myrvm-app php artisan tinker
>>> DB::connection()->getPdo();

# Check database queries
docker exec -it myrvm-app php artisan tinker
>>> DB::enableQueryLog();
>>> // Run some operations
>>> DB::getQueryLog();

# Check database performance
docker exec -it myrvm-postgres psql -U myrvm_user -d myrvm_ecosystem -c "SELECT * FROM pg_stat_activity;"
```

---

## 📝 Log Analysis

### Server Logs
```bash
# Check application logs
docker logs myrvm-app

# Check web server logs
docker logs myrvm-nginx

# Check database logs
docker logs myrvm-postgres

# Follow logs in real-time
docker logs -f myrvm-app
```

### Jetson Logs
```bash
# Check API logs
ssh my@orin1 "tail -f /path/to/api/logs/app.log"

# Check system logs
ssh my@orin1 "journalctl -u jetson-api -f"

# Check GPU logs
ssh my@orin1 "nvidia-smi -l 1"

# Check processing logs
ssh my@orin1 "tail -f /path/to/processing/logs"
```

---

## 🚨 Emergency Procedures

### Server Recovery
```bash
# Stop all services
docker-compose down

# Backup database
docker exec myrvm-postgres pg_dump -U myrvm_user myrvm_ecosystem > backup.sql

# Restart services
docker-compose up -d

# Check service status
docker-compose ps
```

### Jetson Recovery
```bash
# Restart Jetson API
ssh my@orin1 "pkill -f python3"
ssh my@orin1 "cd /path/to/api && nohup python3 app.py > app.log 2>&1 &"

# Check API status
ssh my@orin1 "ps aux | grep python3"
ssh my@orin1 "curl -s http://localhost:5000/api/health"
```

---

**Last Updated**: 2025-01-02  
**Version**: 2.0.0  
**Status**: ✅ COMPLETE TESTING & TROUBLESHOOTING DOCUMENTATION
