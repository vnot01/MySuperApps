# 🔧 API Troubleshooting & FAQ - MyRVM-Ecosystem v2.0

## 📍 Troubleshooting Overview

### Common Issues
- **Authentication Problems**: API key issues, expired keys
- **Connection Issues**: Network connectivity, timeouts
- **Validation Errors**: Input validation failures
- **Performance Issues**: Slow responses, high memory usage
- **Integration Problems**: Jetson connectivity, data sync

---

## 🔐 Authentication Issues

### Q: I'm getting "UNAUTHORIZED" error. What should I do?

**A: Check your API key configuration:**

```bash
# Check if API key is included in request
curl -H "Authorization: Bearer YOUR_API_KEY" \
     http://100.123.143.87:8001/api/v2/rvms

# Verify API key format
echo "API Key: 38bbe1d2ecf75df21546b05340f5878a24e74ed0d8b88d75db1ddeff198380c1"
```

**Common causes:**
- Missing `Authorization` header
- Incorrect `Bearer` prefix
- Invalid or expired API key
- API key not generated for your user

**Solution:**
1. Generate a new API key from the dashboard
2. Include it in the `Authorization` header
3. Ensure the key hasn't expired

### Q: My API key expired. How do I get a new one?

**A: Generate a new API key:**

```bash
# Option 1: Via API (if you have admin access)
curl -X POST http://100.123.143.87:8001/api/v2/users/regenerate-api-key \
     -H "Authorization: Bearer ADMIN_API_KEY" \
     -H "Content-Type: application/json" \
     -d '{"user_id": 1}'

# Option 2: Via dashboard
# 1. Login to dashboard
# 2. Go to Profile Settings
# 3. Click "Regenerate API Key"
```

### Q: I'm getting "INVALID_API_KEY" error even with a valid key.

**A: Check the following:**

```bash
# 1. Verify API key format
echo "Your API key: $API_KEY"
echo "Expected format: 64-character hex string"

# 2. Check if key is properly encoded
curl -v -H "Authorization: Bearer $API_KEY" \
     http://100.123.143.87:8001/api/v2/rvms

# 3. Verify key exists in database
docker-compose exec app php artisan tinker
>>> App\Models\User::where('api_key', 'your-key-here')->first();
```

---

## 🌐 Connection Issues

### Q: I can't connect to the API server. What should I check?

**A: Follow this troubleshooting checklist:**

```bash
# 1. Check server status
curl -I http://100.123.143.87:8001/api/v2/health

# 2. Check network connectivity
ping 100.123.143.87

# 3. Check if port is open
telnet 100.123.143.87 8001

# 4. Check DNS resolution
nslookup 100.123.143.87

# 5. Check firewall rules
sudo ufw status
```

**Common solutions:**
- Restart Docker containers: `docker-compose restart`
- Check server logs: `docker-compose logs app`
- Verify network configuration
- Check firewall settings

### Q: I'm getting connection timeouts. How do I fix this?

**A: Check timeout settings and server load:**

```bash
# 1. Check server response time
curl -w "@curl-format.txt" -o /dev/null -s http://100.123.143.87:8001/api/v2/health

# 2. Check server resources
docker stats

# 3. Check database performance
docker-compose exec postgres psql -U myrvm_user -d myrvm_ecosystem -c "
SELECT query, mean_time, calls 
FROM pg_stat_statements 
ORDER BY mean_time DESC 
LIMIT 5;"

# 4. Check Redis performance
docker-compose exec redis redis-cli info stats
```

**Solutions:**
- Increase timeout values in your client
- Optimize database queries
- Scale server resources
- Implement connection pooling

---

## 📝 Validation Errors

### Q: I'm getting "VALIDATION_ERROR" when creating an RVM. What's wrong?

**A: Check the validation requirements:**

```bash
# Example of valid RVM creation
curl -X POST http://100.123.143.87:8001/api/v2/rvms \
     -H "Authorization: Bearer YOUR_API_KEY" \
     -H "Content-Type: application/json" \
     -d '{
       "name": "Test RVM",
       "location": "Test Location",
       "ip_address": "192.168.1.100",
       "capacity": 100
     }'
```

**Common validation errors:**
- `name`: Required, string, 3-255 characters
- `location`: Required, string, 5-500 characters
- `ip_address`: Required, valid IP address, unique
- `capacity`: Required, integer, 1-1000
- `latitude`: Optional, numeric, -90 to 90
- `longitude`: Optional, numeric, -180 to 180

**Solution:**
1. Check the error details in the response
2. Fix the validation errors
3. Ensure all required fields are provided
4. Verify data types and formats

### Q: I'm getting "UNIQUE constraint failed" error. What does this mean?

**A: This means you're trying to create a resource that already exists:**

```bash
# Check if RVM with same IP already exists
curl -H "Authorization: Bearer YOUR_API_KEY" \
     "http://100.123.143.87:8001/api/v2/rvms?ip_address=192.168.1.100"

# Check if user with same email already exists
curl -H "Authorization: Bearer YOUR_API_KEY" \
     "http://100.123.143.87:8001/api/v2/users?email=test@example.com"
```

**Solutions:**
- Use a different IP address for the RVM
- Update the existing resource instead of creating a new one
- Delete the existing resource first

---

## ⚡ Performance Issues

### Q: The API is responding slowly. How can I improve performance?

**A: Check and optimize the following:**

```bash
# 1. Check server response times
curl -w "@curl-format.txt" -o /dev/null -s http://100.123.143.87:8001/api/v2/rvms

# 2. Check database performance
docker-compose exec app php artisan tinker
>>> DB::select('EXPLAIN ANALYZE SELECT * FROM reverse_vending_machines');

# 3. Check cache performance
docker-compose exec redis redis-cli info stats

# 4. Check memory usage
docker stats
```

**Optimization strategies:**
- Use pagination for large datasets
- Implement caching for frequently accessed data
- Optimize database queries
- Use connection pooling
- Scale server resources

### Q: I'm getting "RATE_LIMIT_EXCEEDED" error. How do I handle this?

**A: Implement exponential backoff:**

```javascript
// Example with exponential backoff
async function makeRequestWithRetry(url, options, maxRetries = 3) {
    for (let i = 0; i < maxRetries; i++) {
        try {
            const response = await fetch(url, options);
            
            if (response.status === 429) {
                const retryAfter = response.headers.get('Retry-After') || Math.pow(2, i);
                await new Promise(resolve => setTimeout(resolve, retryAfter * 1000));
                continue;
            }
            
            return response;
        } catch (error) {
            if (i === maxRetries - 1) throw error;
            await new Promise(resolve => setTimeout(resolve, Math.pow(2, i) * 1000));
        }
    }
}
```

**Rate limit information:**
- Default: 60 requests per minute per API key
- Burst: 100 requests per minute
- Reset: Every minute

---

## 🤖 Jetson Integration Issues

### Q: My Jetson can't connect to the server. What should I check?

**A: Check the Jetson configuration:**

```bash
# 1. Check Jetson configuration
cat /home/my/MySuperApps/MyCV-Platform/direct/app/api-hybrid-detection-jetson/rvm_config.env

# 2. Test server connectivity from Jetson
curl -I http://100.123.143.87:8001/api/v2/health

# 3. Test API key validation
curl -X POST http://100.123.143.87:8001/api/v2/rvm/validate \
     -H "Authorization: Bearer YOUR_API_KEY" \
     -H "Content-Type: application/json" \
     -d '{"api_key": "YOUR_API_KEY"}'

# 4. Check Jetson API status
curl http://100.117.234.2:5000/api/health
```

**Common issues:**
- Incorrect server URL in `rvm_config.env`
- Invalid API key
- Network connectivity problems
- Firewall blocking connections

### Q: Detection results are not being stored on the server. What's wrong?

**A: Check the data flow:**

```bash
# 1. Check Jetson API logs
ssh my@orin1 "cd /home/my/MySuperApps/MyCV-Platform/direct/app/api-hybrid-detection-jetson && tail -f app.log"

# 2. Check server API logs
docker-compose logs app | grep "detection"

# 3. Test detection endpoint manually
curl -X POST http://100.117.234.2:5000/api/detection \
     -H "Authorization: Bearer YOUR_API_KEY" \
     -H "Content-Type: application/json" \
     -d '{
       "rvm_id": 1,
       "image_path": "/path/to/test/image.jpg",
       "detection_results": [{"class": "bottle", "confidence": 0.95}]
     }'

# 4. Check if detection was stored
curl -H "Authorization: Bearer YOUR_API_KEY" \
     http://100.123.143.87:8001/api/v2/detection-results
```

**Solutions:**
- Verify API key is valid and not expired
- Check network connectivity between Jetson and server
- Ensure detection data format is correct
- Check server logs for errors

---

## 🔄 Data Synchronization Issues

### Q: Data is not syncing between Jetson and server. How do I fix this?

**A: Check the synchronization process:**

```bash
# 1. Check Jetson status
curl http://100.117.234.2:5000/api/status

# 2. Check server RVM status
curl -H "Authorization: Bearer YOUR_API_KEY" \
     http://100.123.143.87:8001/api/v2/rvms/1

# 3. Force synchronization
curl -X POST http://100.117.234.2:5000/api/sync \
     -H "Authorization: Bearer YOUR_API_KEY"

# 4. Check sync logs
docker-compose logs app | grep "sync"
```

**Common issues:**
- API key expiration
- Network connectivity problems
- Data format mismatches
- Server overload

### Q: I'm seeing stale data in the API responses. How do I refresh it?

**A: Clear caches and force refresh:**

```bash
# 1. Clear application cache
docker-compose exec app php artisan cache:clear

# 2. Clear configuration cache
docker-compose exec app php artisan config:clear

# 3. Clear route cache
docker-compose exec app php artisan route:clear

# 4. Clear view cache
docker-compose exec app php artisan view:clear

# 5. Restart services
docker-compose restart
```

---

## 🐛 Debugging Tools

### Q: How can I debug API issues effectively?

**A: Use these debugging tools:**

```bash
# 1. Enable debug mode
echo "APP_DEBUG=true" >> .env
docker-compose restart app

# 2. Check detailed logs
docker-compose logs -f app

# 3. Use API testing tools
curl -v -H "Authorization: Bearer YOUR_API_KEY" \
     http://100.123.143.87:8001/api/v2/rvms

# 4. Check database queries
docker-compose exec app php artisan tinker
>>> DB::enableQueryLog();
>>> App\Models\Rvm::all();
>>> DB::getQueryLog();

# 5. Check Redis operations
docker-compose exec redis redis-cli monitor
```

### Q: How can I monitor API performance in real-time?

**A: Use these monitoring tools:**

```bash
# 1. Check system metrics
curl http://100.123.143.87:8001/api/v2/monitoring/metrics

# 2. Check health status
curl http://100.123.143.87:8001/api/v2/monitoring/health

# 3. Monitor server resources
docker stats

# 4. Check database performance
docker-compose exec postgres psql -U myrvm_user -d myrvm_ecosystem -c "
SELECT query, mean_time, calls 
FROM pg_stat_statements 
ORDER BY mean_time DESC 
LIMIT 10;"

# 5. Check Redis performance
docker-compose exec redis redis-cli info stats
```

---

## 📊 Common Error Codes

### HTTP Status Codes
| Code | Meaning | Common Causes | Solutions |
|------|---------|---------------|-----------|
| 200 | OK | Success | - |
| 201 | Created | Resource created successfully | - |
| 400 | Bad Request | Invalid request format | Check request body format |
| 401 | Unauthorized | Missing or invalid API key | Provide valid API key |
| 403 | Forbidden | Insufficient permissions | Check user permissions |
| 404 | Not Found | Resource not found | Check resource ID |
| 422 | Unprocessable Entity | Validation failed | Check validation errors |
| 429 | Too Many Requests | Rate limit exceeded | Implement backoff strategy |
| 500 | Internal Server Error | Server error | Check server logs |
| 502 | Bad Gateway | Upstream server error | Check service dependencies |
| 503 | Service Unavailable | Service temporarily unavailable | Retry after delay |

### Custom Error Codes
| Code | Meaning | Common Causes | Solutions |
|------|---------|---------------|-----------|
| `API_ERROR` | General API error | Unexpected error | Check server logs |
| `VALIDATION_ERROR` | Input validation failed | Invalid input data | Fix validation errors |
| `UNAUTHORIZED` | Authentication required | Missing API key | Provide API key |
| `INVALID_API_KEY` | API key is invalid | Wrong or expired key | Generate new key |
| `INSUFFICIENT_PERMISSIONS` | User lacks permissions | Wrong user role | Check user permissions |
| `RVM_NOT_FOUND` | RVM not found | Invalid RVM ID | Check RVM ID |
| `RATE_LIMIT_EXCEEDED` | Too many requests | Exceeded rate limit | Implement backoff |
| `DATABASE_ERROR` | Database operation failed | Database issue | Check database logs |
| `CACHE_ERROR` | Cache operation failed | Cache issue | Check Redis logs |

---

## 🔧 Troubleshooting Scripts

### Server Health Check Script
```bash
#!/bin/bash
# server_health_check.sh

echo "🏥 Server Health Check"
echo "====================="

# Check server status
echo "🖥️ Server status..."
if curl -f http://100.123.143.87:8001/api/v2/health > /dev/null 2>&1; then
    echo "✅ Server: Healthy"
else
    echo "❌ Server: Unhealthy"
    exit 1
fi

# Check database
echo "🗄️ Database status..."
if docker-compose exec postgres psql -U myrvm_user -d myrvm_ecosystem -c "SELECT 1;" > /dev/null 2>&1; then
    echo "✅ Database: Healthy"
else
    echo "❌ Database: Unhealthy"
fi

# Check Redis
echo "🔴 Redis status..."
if docker-compose exec redis redis-cli ping | grep -q "PONG"; then
    echo "✅ Redis: Healthy"
else
    echo "❌ Redis: Unhealthy"
fi

# Check disk space
echo "💾 Disk space..."
df -h | grep -E "(Filesystem|/dev/)"

# Check memory usage
echo "🧠 Memory usage..."
free -h

echo "✅ Health check completed!"
```

### Jetson Health Check Script
```bash
#!/bin/bash
# jetson_health_check.sh

echo "🤖 Jetson Health Check"
echo "====================="

# Check Jetson status
echo "🤖 Jetson status..."
if curl -f http://100.117.234.2:5000/api/health > /dev/null 2>&1; then
    echo "✅ Jetson: Healthy"
else
    echo "❌ Jetson: Unhealthy"
    exit 1
fi

# Check GPU status
echo "🎮 GPU status..."
ssh my@orin1 "nvidia-smi --query-gpu=utilization.gpu,memory.used,memory.total --format=csv,noheader,nounits"

# Check Python processes
echo "🐍 Python processes..."
ssh my@orin1 "ps aux | grep python"

# Check API logs
echo "📝 API logs..."
ssh my@orin1 "cd /home/my/MySuperApps/MyCV-Platform/direct/app/api-hybrid-detection-jetson && tail -5 app.log"

echo "✅ Jetson health check completed!"
```

---

## 📞 Support and Resources

### Getting Help
- **Documentation**: Check the API documentation first
- **Logs**: Check server and application logs
- **Community**: Join our Discord server
- **Issues**: Report bugs on GitHub
- **Email**: support@myrvm.com

### Useful Resources
- **API Documentation**: `/docs/api`
- **Postman Collection**: `/docs/postman`
- **OpenAPI Spec**: `/docs/openapi`
- **GitHub Repository**: `https://github.com/myrvm/ecosystem`
- **Status Page**: `https://status.myrvm.com`

---

**Last Updated**: 2025-01-02  
**Version**: 2.0.0  
**Status**: ✅ COMPLETE TROUBLESHOOTING & FAQ DOCUMENTATION
