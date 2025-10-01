#!/usr/bin/env python3
"""
Run MyCV-Platform API with RVM Integration
This script loads environment variables and starts the API with RVM support
"""

import os
import sys
from pathlib import Path

def load_rvm_config():
    """Load RVM configuration from environment or config file"""
    config_file = Path("rvm_config.env")
    
    if config_file.exists():
        print("📝 Loading RVM configuration from rvm_config.env...")
        with open(config_file, 'r') as f:
            for line in f:
                line = line.strip()
                if line and not line.startswith('#') and '=' in line:
                    key, value = line.split('=', 1)
                    os.environ[key.strip()] = value.strip()
        print("✅ Configuration loaded successfully")
    else:
        print("⚠️  rvm_config.env not found, using default values")
        print("   Create rvm_config.env from rvm_config.example for custom configuration")

def check_requirements():
    """Check if all requirements are met"""
    print("🔍 Checking requirements...")
    
    # Check if we're in the right directory
    if not Path("app.py").exists():
        print("❌ app.py not found. Please run this script from the API directory.")
        return False
    
    # Check if data directories exist
    data_dir = Path("../../data-jetson")
    if not data_dir.exists():
        print("❌ data-jetson directory not found. Run setup_rvm_directories.py first.")
        return False
    
    # Check if models exist
    models_dir = data_dir / "models"
    required_models = [
        "yolo/active/yolo11m.pt",
        "trained/active/best.pt", 
        "sam/active/sam2_b.pt"
    ]
    
    missing_models = []
    for model_path in required_models:
        if not (models_dir / model_path).exists():
            missing_models.append(model_path)
    
    if missing_models:
        print(f"⚠️  Missing model files: {missing_models}")
        print("   Some features may not work properly")
    else:
        print("✅ All required models found")
    
    print("✅ Requirements check completed")
    return True

def print_startup_info():
    """Print startup information"""
    print("\n" + "="*60)
    print("🚀 MyCV-Platform API with RVM Integration")
    print("="*60)
    
    # Get configuration values
    rvm_api_url = os.getenv('RVM_API_BASE_URL', 'http://localhost:8000/api')
    rvm_api_key = os.getenv('RVM_API_KEY', 'Not set')
    api_host = os.getenv('API_HOST', '0.0.0.0')
    api_port = os.getenv('API_PORT', '5000')
    
    print(f"📡 API URL: http://{api_host}:{api_port}")
    print(f"🔗 RVM Platform: {rvm_api_url}")
    print(f"🔑 RVM API Key: {'Set' if rvm_api_key != 'Not set' else 'Not set'}")
    print(f"📁 Data Directory: {os.getenv('BASE_DATA_DIR', '../../data-jetson')}")
    
    print("\n📋 Available Endpoints:")
    print("   GET  /api/health - Health check")
    print("   GET  /api/status - API status")
    print("   POST /api/upload - Upload images (with RVM support)")
    print("   GET  /api/process/<session_id> - Get processing status")
    print("   GET  /api/results/<session_id> - Get detection results")
    print("   GET  /api/detections - Get detections (with RVM filtering)")
    print("   POST /api/detections/search - Search detections (with RVM filtering)")
    print("   POST /api/rvm/validate - Validate RVM API key")
    print("   GET  /api/rvm/<rvm_id>/stats - Get RVM statistics")
    
    print("\n🔐 RVM Integration:")
    print("   - Use X-RVM-API-Key header for authentication")
    print("   - Include rvm_id parameter for RVM-specific operations")
    print("   - Data stored in rvm_{id}/ structure")
    
    print("\n🧪 Testing:")
    print("   python3 test_rvm_integration.py - Quick integration test")
    print("   python3 test_full_integration.py - Full integration test")
    
    print("="*60)

def main():
    """Main function"""
    print("🚀 Starting MyCV-Platform API with RVM Integration...")
    
    # Load configuration
    load_rvm_config()
    
    # Check requirements
    if not check_requirements():
        print("❌ Requirements check failed. Please fix the issues above.")
        return 1
    
    # Print startup information
    print_startup_info()
    
    # Import and run the Flask app
    try:
        print("\n🔄 Starting Flask application...")
        
        # Import the app
        sys.path.append(os.path.dirname(__file__))
        from app import app
        
        # Get configuration
        host = os.getenv('API_HOST', '0.0.0.0')
        port = int(os.getenv('API_PORT', '5000'))
        debug = os.getenv('API_DEBUG', 'false').lower() == 'true'
        
        print(f"🌐 Starting server on {host}:{port}")
        print(f"🐛 Debug mode: {'ON' if debug else 'OFF'}")
        print("\nPress Ctrl+C to stop the server")
        print("-" * 60)
        
        # Run the app
        app.run(host=host, port=port, debug=debug)
        
    except KeyboardInterrupt:
        print("\n\n👋 Server stopped by user")
        return 0
    except Exception as e:
        print(f"\n❌ Error starting server: {e}")
        return 1

if __name__ == "__main__":
    exit(main())
