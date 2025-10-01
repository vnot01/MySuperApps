# MyCV-Platform RVM Setup

This directory contains the RVM-integrated version of MyCV-Platform.

## Quick Start

1. **Configure RVM Platform**
   ```bash
   # Update rvm_config.env with your RVM Platform details
   cp rvm_config.env .env
   nano .env
   ```

2. **Start the API**
   ```bash
   python3 app.py
   ```

3. **Test Integration**
   ```bash
   python3 test_rvm_integration.py
   ```

## Directory Structure

- `rvm_{id}/` - RVM-specific data directories
- `legacy/` - Backward compatibility directory
- `models/` - AI model files

## API Endpoints

- `POST /api/upload` - Upload with RVM authentication
- `GET /api/detections` - Get detections with RVM filtering
- `POST /api/detections/search` - Search with RVM filtering
- `POST /api/rvm/validate` - Validate RVM API key
- `GET /api/rvm/{id}/stats` - Get RVM statistics

## Authentication

Use `X-RVM-API-Key` header for RVM-specific operations:
```bash
curl -H "X-RVM-API-Key: your_key" http://localhost:5000/api/detections?rvm_id=1
```

## Configuration

See `rvm_config.env` for all configuration options.

## Documentation

See `RVM_INTEGRATION.md` for detailed integration documentation.
