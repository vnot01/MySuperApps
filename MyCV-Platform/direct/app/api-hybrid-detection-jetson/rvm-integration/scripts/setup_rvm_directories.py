#!/usr/bin/env python3
"""
Setup script for RVM directory structure
Creates the necessary directory structure for multi-RVM support
"""

import os
import sys
from pathlib import Path

def create_rvm_directory_structure(base_dir="../../data-jetson", rvm_ids=None):
    """Create directory structure for RVM support"""
    
    if rvm_ids is None:
        rvm_ids = [1, 2, 3]  # Default RVM IDs for testing
    
    print("🏗️  Creating RVM directory structure...")
    print(f"Base directory: {base_dir}")
    print(f"RVM IDs: {rvm_ids}")
    
    # Base directories
    input_dir = os.path.join(base_dir, "input")
    output_dir = os.path.join(base_dir, "output")
    models_dir = os.path.join(base_dir, "models")
    
    # Create base directories
    os.makedirs(input_dir, exist_ok=True)
    os.makedirs(output_dir, exist_ok=True)
    os.makedirs(models_dir, exist_ok=True)
    
    # Create RVM-specific directories
    for rvm_id in rvm_ids:
        print(f"\n📁 Creating directories for RVM {rvm_id}...")
        
        # Input directory structure
        rvm_input_dir = os.path.join(input_dir, f"rvm_{rvm_id}")
        os.makedirs(rvm_input_dir, exist_ok=True)
        
        # Output directory structure
        rvm_output_dir = os.path.join(output_dir, f"rvm_{rvm_id}")
        os.makedirs(rvm_output_dir, exist_ok=True)
        
        # Create subdirectories for each RVM
        subdirs = ['yolo', 'best', 'segmentasi', 'hybrid']
        for subdir in subdirs:
            os.makedirs(os.path.join(rvm_output_dir, subdir), exist_ok=True)
        
        print(f"   ✅ RVM {rvm_id} directories created")
    
    # Create legacy directory structure for backward compatibility
    print(f"\n📁 Creating legacy directory structure...")
    legacy_input_dir = os.path.join(input_dir, "legacy")
    legacy_output_dir = os.path.join(output_dir, "legacy")
    
    os.makedirs(legacy_input_dir, exist_ok=True)
    os.makedirs(legacy_output_dir, exist_ok=True)
    
    # Create model directories
    print(f"\n📁 Creating model directories...")
    model_subdirs = [
        "yolo/active",
        "trained/active", 
        "sam/active"
    ]
    
    for subdir in model_subdirs:
        model_path = os.path.join(models_dir, subdir)
        os.makedirs(model_path, exist_ok=True)
        print(f"   ✅ {subdir} directory created")
    
    print(f"\n🎉 Directory structure created successfully!")
    print_directory_tree(base_dir)

def print_directory_tree(base_dir, max_depth=3):
    """Print directory tree structure"""
    print(f"\n📂 Directory Structure:")
    print("=" * 50)
    
    def print_tree(directory, prefix="", depth=0):
        if depth > max_depth:
            return
        
        try:
            items = sorted(os.listdir(directory))
            for i, item in enumerate(items):
                item_path = os.path.join(directory, item)
                is_last = i == len(items) - 1
                
                if os.path.isdir(item_path):
                    print(f"{prefix}{'└── ' if is_last else '├── '}{item}/")
                    if depth < max_depth:
                        new_prefix = prefix + ("    " if is_last else "│   ")
                        print_tree(item_path, new_prefix, depth + 1)
                else:
                    print(f"{prefix}{'└── ' if is_last else '├── '}{item}")
        except PermissionError:
            print(f"{prefix}└── [Permission Denied]")
    
    print_tree(base_dir)

def create_sample_rvm_config():
    """Create sample RVM configuration file"""
    print(f"\n📝 Creating sample RVM configuration...")
    
    config_content = """# MyCV-Platform RVM Configuration
# Update these values according to your setup

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

# File Upload Settings
MAX_CONTENT_LENGTH=16777216  # 16MB
ALLOWED_EXTENSIONS=png,jpg,jpeg,gif,bmp

# Cache Settings
RVM_CACHE_TTL=300  # 5 minutes

# GPU Settings
CUDA_VISIBLE_DEVICES=0

# RVM IDs (comma-separated)
RVM_IDS=1,2,3
"""
    
    config_file = "rvm_config.env"
    try:
        with open(config_file, 'w') as f:
            f.write(config_content)
        print(f"   ✅ Configuration file created: {config_file}")
    except Exception as e:
        print(f"   ❌ Failed to create config file: {e}")

def create_readme():
    """Create README for RVM setup"""
    print(f"\n📖 Creating README...")
    
    readme_content = """# MyCV-Platform RVM Setup

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
"""
    
    readme_file = "README_RVM.md"
    try:
        with open(readme_file, 'w') as f:
            f.write(readme_content)
        print(f"   ✅ README created: {readme_file}")
    except Exception as e:
        print(f"   ❌ Failed to create README: {e}")

def main():
    """Main setup function"""
    print("🚀 MyCV-Platform RVM Setup")
    print("=" * 40)
    
    # Get RVM IDs from command line or use defaults
    rvm_ids = None
    if len(sys.argv) > 1:
        try:
            rvm_ids = [int(x) for x in sys.argv[1].split(',')]
        except ValueError:
            print("❌ Invalid RVM IDs format. Use comma-separated integers.")
            return
    
    # Create directory structure
    create_rvm_directory_structure(rvm_ids=rvm_ids)
    
    # Create configuration files
    create_sample_rvm_config()
    create_readme()
    
    print(f"\n🎉 Setup completed successfully!")
    print(f"\nNext steps:")
    print(f"1. Update rvm_config.env with your RVM Platform details")
    print(f"2. Start the API: python3 app.py")
    print(f"3. Test integration: python3 test_rvm_integration.py")

if __name__ == "__main__":
    main()
