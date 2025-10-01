# MyCV-Platform RVM Integration

## Overview
This document describes the integration between MyCV-Platform and MyRVM-Platform for multi-RVM support with authentication and data isolation.

## Features
- **Multi-RVM Support**: Each RVM has isolated data storage
- **API Key Authentication**: Secure access using RVM-specific API keys
- **Data Isolation**: RVM data stored in separate directories
- **Backward Compatibility**: Legacy endpoints still work without RVM authentication
- **Caching**: RVM data cached for performance

## Directory Structure

### New RVM Structure
```
data-jetson/
├── input/
│   └── rvm_{rvm_id}/
│       └── {timestamp}/
│           └── {user_id}/
│               └── *.jpg
├── output/
│   └── rvm_{rvm_id}/
│       └── {timestamp}/
│           └── {user_id}/
│               ├── yolo/
│               ├── best/
│               ├── segmentasi/
│               ├── hybrid/
│               └── summary.json
└── models/
    ├── yolo/active/
    ├── trained/active/
    └── sam/active/
```

### Legacy Structure (Backward Compatibility)
```
data-jetson/
├── input/
│   └── legacy/
│       └── {timestamp}/
│           └── {user_id}/
│               └── *.jpg
└── output/
    └── legacy/
        └── {timestamp}/
            └── {user_id}/
                ├── yolo/
                ├── best/
                ├── segmentasi/
                ├── hybrid/
                └── summary.json
```

## API Endpoints

### Authentication
All RVM-specific endpoints require authentication using the `X-RVM-API-Key` header.

### 1. Upload Images (with RVM support)
```http
POST /api/upload
Content-Type: multipart/form-data
X-RVM-API-Key: your_rvm_api_key

Form Data:
- files: image files
- rvm_id: RVM ID (required for RVM mode)
- user_id: User ID (optional)
- api_key: Alternative API key (if not in header)
```

**Response:**
```json
{
  "success": true,
  "session_id": "session_abc123",
  "timestamp": "20250101_120000",
  "user_id": "user123",
  "rvm": {
    "id": 1,
    "name": "RVM-001",
    "location": "Mall Central"
  },
  "uploaded_files": [...],
  "message": "Files uploaded successfully. Processing started.",
  "status_url": "/api/process/session_abc123",
  "results_url": "/api/results/session_abc123"
}
```

### 2. Get Detections (with RVM filtering)
```http
GET /api/detections?rvm_id=1&user_id=user123&page=1&limit=20
X-RVM-API-Key: your_rvm_api_key
```

**Response:**
```json
{
  "pagination": {
    "current_page": 1,
    "total_pages": 5,
    "total_items": 100,
    "items_per_page": 20,
    "has_next": true,
    "has_prev": false
  },
  "filters": {
    "user_id": "user123",
    "rvm_id": 1
  },
  "recent_detections": [
    {
      "timestamp": "20250101_120000",
      "user_id": "user123",
      "rvm_id": 1,
      "image_name": "image001",
      "detections": [...],
      "detection_count": 3
    }
  ]
}
```

### 3. Search Detections (with RVM filtering)
```http
POST /api/detections/search
Content-Type: application/json
X-RVM-API-Key: your_rvm_api_key

{
  "page": 1,
  "limit": 20,
  "user_id": "user123",
  "rvm_id": 1,
  "when": "20250101",
  "class_name": "bottle"
}
```

### 4. Validate RVM API Key
```http
POST /api/rvm/validate
Content-Type: application/json

{
  "api_key": "your_rvm_api_key"
}
```

**Response:**
```json
{
  "valid": true,
  "rvm": {
    "id": 1,
    "name": "RVM-001",
    "location": "Mall Central",
    "status": "active"
  }
}
```

### 5. Get RVM Statistics
```http
GET /api/rvm/1/stats
X-RVM-API-Key: your_rvm_api_key
```

**Response:**
```json
{
  "rvm_id": 1,
  "total_sessions": 150,
  "total_detections": 450,
  "recent_activity": [...]
}
```

## Configuration

### Environment Variables
```bash
# RVM Platform Integration
RVM_API_BASE_URL=http://localhost:8000/api
RVM_API_KEY=your_master_api_key_here

# API Configuration
API_HOST=0.0.0.0
API_PORT=5000

# Cache Settings
RVM_CACHE_TTL=300  # 5 minutes
```

### RVM Platform Setup
1. Ensure MyRVM-Platform is running
2. Create RVM records in the database
3. Generate API keys for each RVM
4. Configure the RVM_API_BASE_URL and RVM_API_KEY

## Security

### API Key Validation
- RVM API keys are validated against the RVM Platform
- Keys are cached for performance (5-minute TTL)
- Invalid keys result in 401 Unauthorized

### Data Isolation
- Each RVM's data is stored in separate directories
- RVM-specific queries require valid API key
- Cross-RVM data access is prevented

### Error Handling
- Invalid API keys: 401 Unauthorized
- Access denied: 403 Forbidden
- Missing parameters: 400 Bad Request
- Server errors: 500 Internal Server Error

## Migration from Legacy

### Backward Compatibility
- Legacy endpoints work without RVM authentication
- Data stored in `legacy/` directory structure
- No breaking changes to existing integrations

### Migration Steps
1. Update client code to include RVM authentication
2. Add `rvm_id` parameter to requests
3. Update data processing to handle RVM-specific responses
4. Test with both legacy and RVM modes

## Monitoring and Logging

### RVM Activity Tracking
- Each RVM's activity is tracked separately
- Statistics available via `/api/rvm/{id}/stats`
- Session and detection counts per RVM

### Error Logging
- RVM authentication failures logged
- API communication errors logged
- Processing errors include RVM context

## Performance Considerations

### Caching
- RVM data cached for 5 minutes
- Reduces API calls to RVM Platform
- Cache invalidation on errors

### Directory Structure
- Efficient file system access
- RVM data isolated for better performance
- Legacy structure maintained for compatibility

## Troubleshooting

### Common Issues

1. **401 Unauthorized**
   - Check API key validity
   - Verify RVM Platform connectivity
   - Check cache expiration

2. **403 Forbidden**
   - Verify RVM ID matches API key
   - Check RVM status in platform

3. **Data Not Found**
   - Verify RVM ID in request
   - Check directory structure
   - Validate timestamp format

### Debug Mode
Enable debug logging by setting `API_DEBUG=true` in configuration.

## Future Enhancements

- Real-time RVM status updates
- Advanced RVM analytics
- Multi-tenant data isolation
- RVM-specific model configurations
- Automated RVM health monitoring
