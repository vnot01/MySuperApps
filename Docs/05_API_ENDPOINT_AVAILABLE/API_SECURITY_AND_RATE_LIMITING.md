# 🔒 API Security & Rate Limiting - MyRVM-Ecosystem v2.0

## 📍 Security Overview

### Network Security
- **Server**: `100.123.143.87:8001` (HTTPS recommended)
- **Jetson**: `100.117.234.2:5000` (Internal network)
- **VPN**: Tailscale for secure communication

---

## 🔐 Authentication & Authorization

### Server Authentication
- **Method**: JWT Bearer Token
- **Header**: `Authorization: Bearer {token}`
- **Expiration**: 24 hours (configurable)
- **Refresh**: Automatic on valid requests

### Jetson Authentication
- **Method**: RVM API Key
- **Header**: `X-RVM-API-Key: {api_key}`
- **Expiration**: 1 month (configurable)
- **Validation**: Server-side validation

### Master API Key
- **Purpose**: Server-to-server communication
- **Scope**: All RVM operations
- **Rotation**: Monthly (recommended)
- **Storage**: Environment variables

---

## 🚦 Rate Limiting

### Server API Rate Limits
| Endpoint Category | Rate Limit | Window | Burst |
|------------------|------------|--------|-------|
| **Authentication** | 10 req/min | 1 minute | 20 |
| **RVM Management** | 100 req/min | 1 minute | 200 |
| **Detection Results** | 200 req/min | 1 minute | 400 |
| **Economy System** | 50 req/min | 1 minute | 100 |
| **Analytics** | 30 req/min | 1 minute | 60 |
| **Status Check** | 20 req/min | 1 minute | 40 |

### Jetson API Rate Limits
| Endpoint Category | Rate Limit | Window | Burst |
|------------------|------------|--------|-------|
| **Health & Status** | 60 req/min | 1 minute | 120 |
| **Upload & Processing** | 10 req/min | 1 minute | 20 |
| **Download & History** | 100 req/min | 1 minute | 200 |
| **Monitoring** | 30 req/min | 1 minute | 60 |
| **RVM Integration** | 50 req/min | 1 minute | 100 |

### Rate Limit Headers
```http
X-RateLimit-Limit: 100
X-RateLimit-Remaining: 95
X-RateLimit-Reset: 1640995200
X-RateLimit-Window: 60
```

### Rate Limit Response
```json
{
    "success": false,
    "error": "Rate limit exceeded",
    "code": "RATE_LIMIT_EXCEEDED",
    "retry_after": 60,
    "limit": 100,
    "remaining": 0,
    "reset_time": "2025-01-02T11:00:00Z"
}
```

---

## 🛡️ Security Headers

### Required Headers
```http
X-Content-Type-Options: nosniff
X-Frame-Options: DENY
X-XSS-Protection: 1; mode=block
Strict-Transport-Security: max-age=31536000; includeSubDomains
Content-Security-Policy: default-src 'self'
Referrer-Policy: strict-origin-when-cross-origin
```

### CORS Configuration
```http
Access-Control-Allow-Origin: https://myrvm.com
Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS
Access-Control-Allow-Headers: Authorization, Content-Type, X-RVM-API-Key
Access-Control-Max-Age: 86400
```

---

## 🔒 Input Validation

### Request Validation
- **Content-Type**: Must match expected type
- **Content-Length**: Within allowed limits
- **Required Fields**: All required fields present
- **Data Types**: Correct data types
- **Format Validation**: Email, URL, date formats
- **Range Validation**: Numeric ranges, string lengths

### File Upload Security
- **File Types**: Only allowed extensions
- **File Size**: Maximum 16MB per file
- **File Count**: Maximum 10 files per request
- **Virus Scanning**: Optional virus scanning
- **Content Validation**: File content validation

### SQL Injection Prevention
- **Parameterized Queries**: All database queries
- **Input Sanitization**: All user inputs
- **Escape Sequences**: Special characters escaped
- **Query Validation**: Query structure validation

---

## 🔐 API Key Management

### RVM API Key Lifecycle
1. **Generation**: Automatic on RVM creation
2. **Distribution**: Secure transmission to Jetson
3. **Validation**: Server-side validation
4. **Rotation**: Monthly rotation (recommended)
5. **Revocation**: Immediate revocation capability
6. **Audit**: All key operations logged

### Key Storage
- **Server**: Encrypted in database
- **Jetson**: Environment variables
- **Transmission**: HTTPS only
- **Backup**: Encrypted backup storage

### Key Rotation Process
```bash
# 1. Generate new key
NEW_KEY=$(openssl rand -hex 32)

# 2. Update database
UPDATE reverse_vending_machines 
SET api_key = '$NEW_KEY', 
    api_key_expires_at = NOW() + INTERVAL 1 MONTH 
WHERE id = $RVM_ID;

# 3. Update Jetson config
echo "RVM_API_KEY=$NEW_KEY" > rvm_config.env

# 4. Restart Jetson API
systemctl restart jetson-api
```

---

## 🚨 Security Monitoring

### Authentication Monitoring
- **Failed Logins**: Track failed login attempts
- **Suspicious Activity**: Unusual access patterns
- **Token Abuse**: Invalid token usage
- **Brute Force**: Multiple failed attempts

### API Usage Monitoring
- **Rate Limit Violations**: Track rate limit breaches
- **Unusual Patterns**: Abnormal usage patterns
- **Error Rates**: High error rates
- **Response Times**: Slow response times

### Security Alerts
```json
{
    "alert_type": "SECURITY_VIOLATION",
    "severity": "HIGH",
    "message": "Multiple failed login attempts detected",
    "user_id": "admin@myrvm.com",
    "ip_address": "192.168.1.100",
    "timestamp": "2025-01-02T10:30:00Z",
    "details": {
        "failed_attempts": 5,
        "time_window": "5 minutes",
        "action_taken": "Account temporarily locked"
    }
}
```

---

## 🔍 Security Testing

### Authentication Testing
```bash
# Test invalid credentials
curl -X POST http://100.123.143.87:8001/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@myrvm.com","password":"wrongpassword"}'

# Test rate limiting
for i in {1..15}; do
    curl -X POST http://100.123.143.87:8001/api/auth/login \
      -H "Content-Type: application/json" \
      -d '{"email":"admin@myrvm.com","password":"password123"}' &
done
wait

# Test invalid token
curl -X GET http://100.123.143.87:8001/api/rvms \
  -H "Authorization: Bearer invalid_token"
```

### Input Validation Testing
```bash
# Test SQL injection
curl -X POST http://100.123.143.87:8001/api/rvms \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"name":"RVM\"; DROP TABLE users; --","location":"Test"}'

# Test XSS
curl -X POST http://100.123.143.87:8001/api/rvms \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"name":"<script>alert(\"XSS\")</script>","location":"Test"}'

# Test file upload
curl -X POST http://100.117.234.2:5000/api/upload \
  -H "X-RVM-API-Key: $RVM_API_KEY" \
  -F "files=@malicious.exe" \
  -F "user_id=test_user"
```

---

## 🛠️ Security Configuration

### Server Security Config
```php
// config/security.php
return [
    'rate_limiting' => [
        'enabled' => true,
        'default_limit' => 100,
        'default_window' => 60,
        'burst_limit' => 200,
    ],
    'authentication' => [
        'jwt_secret' => env('JWT_SECRET'),
        'jwt_expiry' => 86400, // 24 hours
        'refresh_threshold' => 3600, // 1 hour
    ],
    'cors' => [
        'allowed_origins' => ['https://myrvm.com'],
        'allowed_methods' => ['GET', 'POST', 'PUT', 'DELETE'],
        'allowed_headers' => ['Authorization', 'Content-Type', 'X-RVM-API-Key'],
    ],
    'file_upload' => [
        'max_size' => 16777216, // 16MB
        'allowed_types' => ['jpg', 'jpeg', 'png', 'gif', 'bmp'],
        'scan_viruses' => false,
    ],
];
```

### Jetson Security Config
```python
# config/security.py
SECURITY_CONFIG = {
    'rate_limiting': {
        'enabled': True,
        'default_limit': 50,
        'default_window': 60,
        'burst_limit': 100,
    },
    'file_upload': {
        'max_size': 16777216,  # 16MB
        'allowed_extensions': ['jpg', 'jpeg', 'png', 'gif', 'bmp'],
        'max_files': 10,
    },
    'api_key': {
        'validation_url': 'http://100.123.143.87:8001/api/rvm/validate-api-key',
        'cache_ttl': 300,  # 5 minutes
    },
    'cors': {
        'allowed_origins': ['http://100.123.143.87:8001'],
        'allowed_methods': ['GET', 'POST', 'PUT', 'DELETE'],
        'allowed_headers': ['X-RVM-API-Key', 'Content-Type'],
    }
}
```

---

## 📊 Security Metrics

### Key Performance Indicators
- **Authentication Success Rate**: > 99%
- **Rate Limit Violations**: < 1%
- **Security Incidents**: 0 per month
- **Response Time**: < 200ms
- **Uptime**: > 99.9%

### Monitoring Dashboard
```json
{
    "security_metrics": {
        "authentication": {
            "success_rate": 99.5,
            "failed_attempts": 12,
            "locked_accounts": 0
        },
        "rate_limiting": {
            "violations": 5,
            "blocked_requests": 23,
            "average_response_time": 150
        },
        "api_usage": {
            "total_requests": 15420,
            "error_rate": 0.2,
            "top_endpoints": [
                {"endpoint": "/api/health", "count": 5420},
                {"endpoint": "/api/upload", "count": 3200},
                {"endpoint": "/api/rvms", "count": 1800}
            ]
        }
    }
}
```

---

## 🚨 Incident Response

### Security Incident Types
1. **Authentication Breach**: Unauthorized access
2. **Rate Limit Abuse**: Excessive API usage
3. **Data Breach**: Unauthorized data access
4. **System Compromise**: Server/device compromise
5. **DDoS Attack**: Distributed denial of service

### Response Procedures
1. **Detection**: Automated monitoring alerts
2. **Assessment**: Severity and impact analysis
3. **Containment**: Immediate threat isolation
4. **Investigation**: Root cause analysis
5. **Recovery**: System restoration
6. **Documentation**: Incident report
7. **Prevention**: Security improvements

### Emergency Contacts
- **Security Team**: security@myrvm.com
- **System Admin**: admin@myrvm.com
- **Emergency Hotline**: +62-xxx-xxx-xxxx

---

## 📋 Security Checklist

### Pre-deployment
- [ ] All endpoints have authentication
- [ ] Rate limiting configured
- [ ] Input validation implemented
- [ ] Security headers set
- [ ] CORS configured
- [ ] File upload restrictions
- [ ] SQL injection prevention
- [ ] XSS protection enabled

### Post-deployment
- [ ] Security monitoring active
- [ ] Logs being collected
- [ ] Alerts configured
- [ ] Backup procedures tested
- [ ] Incident response plan ready
- [ ] Security team notified
- [ ] Documentation updated

### Regular Maintenance
- [ ] Security updates applied
- [ ] API keys rotated
- [ ] Access logs reviewed
- [ ] Rate limits adjusted
- [ ] Security tests run
- [ ] Incident response tested
- [ ] Documentation updated

---

**Last Updated**: 2025-01-02  
**Version**: 2.0.0  
**Status**: ✅ COMPLETE SECURITY & RATE LIMITING DOCUMENTATION
