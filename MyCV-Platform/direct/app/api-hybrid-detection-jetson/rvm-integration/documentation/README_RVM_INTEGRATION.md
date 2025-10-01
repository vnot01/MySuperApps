# MyCV-Platform RVM Integration

## Overview
This document provides a comprehensive guide for integrating MyCV-Platform with MyRVM-Platform to support multiple Reverse Vending Machines (RVMs) with secure authentication and data isolation.

## 🏗️ Architecture

### Multi-RVM Support
- **Isolated Data Storage**: Each RVM has its own directory structure
- **API Key Authentication**: Secure access using RVM-specific API keys
- **Backward Compatibility**: Legacy endpoints work without RVM authentication
- **Caching**: RVM data cached for performance (5-minute TTL)

### Directory Structure
```
data-jetson/
├── input/
│   ├── rvm_{id}/          # RVM-specific input
│   └── legacy/            # Backward compatibility
├── output/
│   ├── rvm_{id}/          # RVM-specific output
│   │   ├── yolo/
│   │   ├── best/
│   │   ├── segmentasi/
│   │   └── hybrid/
│   └── legacy/            # Backward compatibility
└── models/                # Shared AI models
    ├── yolo/active/
    ├── trained/active/
    └── sam/active/
```

## 🚀 Quick Start

### 1. Setup Directory Structure
```bash
# Create RVM directories
python3 setup_rvm_directories.py 1,2,3

# Or create custom RVM IDs
python3 setup_rvm_directories.py 10,20,30
```

### 2. Configure RVM Platform
```bash
# Copy and edit configuration
cp rvm_config.example rvm_config.env
nano rvm_config.env
```

Update the following values:
```bash
RVM_API_BASE_URL=http://your-rvm-platform.com/api
RVM_API_KEY=your_master_api_key_here
```

### 3. Start the API
```bash
# Run with RVM integration
python3 run_rvm_api.py

# Or run directly
python3 app.py
```

### 4. Test Integration
```bash
# Quick test
python3 test_rvm_integration.py

# Full integration test
python3 test_full_integration.py
```

## 📡 API Endpoints

### Authentication
All RVM-specific endpoints require the `X-RVM-API-Key` header.

### Core Endpoints

#### 1. Upload Images (with RVM support)
```http
POST /api/upload
Content-Type: multipart/form-data
X-RVM-API-Key: your_rvm_api_key

Form Data:
- files: image files
- rvm_id: RVM ID (required for RVM mode)
- user_id: User ID (optional)
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
  "message": "Files uploaded successfully. Processing started."
}
```

#### 2. Get Detections (with RVM filtering)
```http
GET /api/detections?rvm_id=1&user_id=user123&page=1&limit=20
X-RVM-API-Key: your_rvm_api_key
```

#### 3. Search Detections (with RVM filtering)
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

#### 4. Validate RVM API Key
```http
POST /api/rvm/validate
Content-Type: application/json

{
  "api_key": "your_rvm_api_key"
}
```

#### 5. Get RVM Statistics
```http
GET /api/rvm/1/stats
X-RVM-API-Key: your_rvm_api_key
```

## 🔧 Configuration

### Environment Variables
```bash
# RVM Platform Integration
RVM_API_BASE_URL=http://localhost:8000/api
RVM_API_KEY=your_master_api_key_here

# API Configuration
API_HOST=0.0.0.0
API_PORT=5000
API_DEBUG=false

# Data Directories
BASE_DATA_DIR=../../data-jetson
UPLOAD_FOLDER=../../data-jetson/input
OUTPUT_FOLDER=../../data-jetson/output

# Cache Settings
RVM_CACHE_TTL=300  # 5 minutes
```

### RVM Platform Setup
1. **Database Migration**: Run the detection_results migration
2. **API Endpoints**: Implement the RVM integration endpoints
3. **Authentication**: Set up API key validation
4. **Data Storage**: Configure detection result storage

## 🔐 Security

### API Key Validation
- RVM API keys validated against RVM Platform
- Keys cached for performance (5-minute TTL)
- Invalid keys return 401 Unauthorized

### Data Isolation
- Each RVM's data stored in separate directories
- RVM-specific queries require valid API key
- Cross-RVM data access prevented

### Error Handling
- **401 Unauthorized**: Invalid API key
- **403 Forbidden**: Access denied
- **400 Bad Request**: Missing parameters
- **500 Internal Server Error**: Server errors

## 📊 Monitoring and Analytics

### RVM Activity Tracking
- Session counts per RVM
- Detection counts per RVM
- Processing time metrics
- Error rate monitoring

### Statistics Endpoints
- `/api/rvm/{id}/stats` - RVM-specific statistics
- Real-time activity monitoring
- Historical data analysis

## 🧪 Testing

### Integration Tests
```bash
# Quick integration test
python3 test_rvm_integration.py

# Full integration test
python3 test_full_integration.py
```

### Test Scenarios
1. **Authentication Tests**
   - Valid API key validation
   - Invalid API key rejection
   - RVM ID verification

2. **Upload Tests**
   - File upload with RVM authentication
   - Processing status tracking
   - Result retrieval

3. **Data Retrieval Tests**
   - RVM-specific detections
   - Search with RVM filtering
   - Statistics retrieval

4. **Error Handling Tests**
   - Invalid parameters
   - Network errors
   - Authentication failures

## 🔄 Migration from Legacy

### Backward Compatibility
- Legacy endpoints work without RVM authentication
- Data stored in `legacy/` directory structure
- No breaking changes to existing integrations

### Migration Steps
1. **Update Client Code**
   ```python
   # Add RVM authentication
   headers = {"X-RVM-API-Key": "your_api_key"}
   data = {"rvm_id": 1, "user_id": "user123"}
   ```

2. **Update Data Processing**
   ```python
   # Handle RVM-specific responses
   if 'rvm' in response:
       rvm_info = response['rvm']
       print(f"Processing for RVM {rvm_info['id']}")
   ```

3. **Test Integration**
   ```bash
   python3 test_full_integration.py
   ```

## 🛠️ Troubleshooting

### Common Issues

#### 1. 401 Unauthorized
```bash
# Check API key validity
curl -X POST http://localhost:5000/api/rvm/validate \
  -H "Content-Type: application/json" \
  -d '{"api_key": "your_key"}'
```

#### 2. 403 Forbidden
```bash
# Verify RVM ID matches API key
curl -H "X-RVM-API-Key: your_key" \
  http://localhost:5000/api/rvm/1/stats
```

#### 3. Data Not Found
```bash
# Check directory structure
ls -la ../../data-jetson/output/rvm_1/
```

#### 4. Connection Errors
```bash
# Check RVM Platform connectivity
curl http://your-rvm-platform.com/api/health
```

### Debug Mode
```bash
# Enable debug logging
export API_DEBUG=true
python3 run_rvm_api.py
```

### Logs
- API logs: Console output
- Error logs: Check console for error messages
- RVM Platform logs: Check RVM Platform logs

## 📈 Performance Optimization

### Caching
- RVM data cached for 5 minutes
- Reduces API calls to RVM Platform
- Cache invalidation on errors

### Directory Structure
- Efficient file system access
- RVM data isolated for better performance
- Legacy structure maintained for compatibility

### Database Optimization
- Indexed queries for RVM data
- Pagination for large datasets
- Efficient data retrieval

## 🔮 Future Enhancements

### Planned Features
- **Real-time Updates**: WebSocket integration for live updates
- **Advanced Analytics**: Machine learning insights
- **Multi-tenant Support**: Enhanced data isolation
- **RVM Health Monitoring**: Automated health checks
- **Model Management**: RVM-specific model configurations

### API Extensions
- **Batch Processing**: Multiple image processing
- **Async Processing**: Non-blocking operations
- **Webhook Support**: Real-time notifications
- **Data Export**: CSV/JSON export functionality

## 📚 Additional Resources

### Documentation
- `RVM_INTEGRATION.md` - Detailed integration guide
- `API_REFERENCE.md` - Complete API reference
- `DEPLOYMENT.md` - Deployment guide

### Examples
- `examples/` - Code examples and samples
- `test_cases/` - Test case examples
- `scripts/` - Utility scripts

### Support
- **Issues**: GitHub Issues
- **Discussions**: GitHub Discussions
- **Documentation**: Wiki pages

## 🤝 Contributing

### Development Setup
1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests
5. Submit a pull request

### Code Standards
- Follow PEP 8 for Python code
- Add type hints
- Write comprehensive tests
- Update documentation

### Testing
```bash
# Run all tests
python3 -m pytest tests/

# Run specific test
python3 test_rvm_integration.py
```

---

## 📞 Support

For questions, issues, or contributions:
- **GitHub**: [Repository Issues](https://github.com/your-repo/issues)
- **Email**: support@yourcompany.com
- **Documentation**: [Wiki](https://github.com/your-repo/wiki)

---

**Last Updated**: January 2025  
**Version**: 1.0.0  
**License**: MIT
