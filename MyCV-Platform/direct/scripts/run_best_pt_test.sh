#!/bin/bash

# MyCV-Platform best.pt Test Script
# Runs only best.pt detection and SAM2 segmentation

set -e

echo "🎯 MyCV-Platform best.pt Test"
echo "============================="

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Function to print colored output
print_status() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

print_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Check if we're in the right directory
if [ ! -f "run_yolo_sam_integration.py" ]; then
    print_error "Please run this script from the MyCV-Platform root directory"
    exit 1
fi

# Check virtual environment
if [ ! -d "venv" ]; then
    print_error "Virtual environment not found! Please run ./scripts/setup.sh first"
    exit 1
fi

print_status "Activating virtual environment..."
source venv/bin/activate

# Check best.pt model
if [ ! -f "data/models/trained/best.pt" ]; then
    print_warning "best.pt model not found, downloading from GitHub..."
    print_status "Downloading best.pt from https://github.com/vnot01/MySuperApps/releases/download/v1.0.0/best.pt"
    python3 -c "
import os
import requests
from tqdm import tqdm

def download_file(url, filename):
    print(f'Downloading {filename}...')
    response = requests.get(url, stream=True)
    total_size = int(response.headers.get('content-length', 0))
    
    with open(filename, 'wb') as file, tqdm(
        desc=filename,
        total=total_size,
        unit='iB',
        unit_scale=True,
        unit_divisor=1024,
    ) as progress_bar:
        for chunk in response.iter_content(chunk_size=8192):
            size = file.write(chunk)
            progress_bar.update(size)
    
    print(f'✅ {filename} downloaded successfully')

# Download best.pt
os.makedirs('data/models/trained', exist_ok=True)
download_file('https://github.com/vnot01/MySuperApps/releases/download/v1.0.0/best.pt', 'data/models/trained/best.pt')
"
    print_success "best.pt downloaded from GitHub"
fi

# Check SAM2_b model
if [ ! -f "data/models/sam/active/sam2_b.pt" ]; then
    print_warning "SAM2_b model not found, downloading..."
    python3 -c "
from ultralytics import SAM
import os
import shutil
print('Downloading SAM2_b...')
sam2_b = SAM('sam2_b.pt')
os.makedirs('data/models/sam/active', exist_ok=True)
if os.path.exists('sam2_b.pt'):
    shutil.copy('sam2_b.pt', 'data/models/sam/active/sam2_b.pt')
    print('SAM2_b downloaded and saved')
"
    # Clean up downloaded file from root directory
    rm -f sam2_b.pt
    print_success "✅ SAM2_b downloaded and cleaned up"
fi

print_success "Required models are available"

# Run best.pt test
print_status "Running best.pt + SAM2 test..."
python3 -c "
import os
import sys
import cv2
import numpy as np
import torch
from ultralytics import YOLO, SAM
from termcolor import colored
import json
from pathlib import Path

def log_message(message, level='info'):
    colors = {
        'info': 'blue',
        'success': 'green',
        'warning': 'yellow',
        'error': 'red'
    }
    color = colors.get(level, 'white')
    print(colored(f'{level.upper()}: {message}', color))

def check_environment():
    log_message('🔍 Checking environment...', 'info')
    
    if 'VIRTUAL_ENV' in os.environ:
        log_message(f'✅ Running in virtual environment: {os.environ[\"VIRTUAL_ENV\"]}', 'success')
    else:
        log_message('⚠️  Not running in virtual environment', 'warning')
    
    if torch.cuda.is_available():
        log_message(f'🚀 GPU MODE: Using CUDA device - {torch.cuda.get_device_name(0)}', 'success')
        log_message(f'   GPU Memory: {torch.cuda.get_device_properties(0).total_memory / 1024**3:.1f} GB', 'info')
        device = 'cuda'
    else:
        log_message('💻 CPU MODE: Using CPU for inference', 'warning')
        device = 'cpu'
    
    return device

def load_models(device):
    log_message('📦 Loading models...', 'info')
    
    models = {}
    
    # Load best.pt
    try:
        log_message('Loading best.pt model...', 'info')
        best_pt_path = 'data/models/trained/best.pt'
        if os.path.exists(best_pt_path):
            models['best_pt'] = YOLO(best_pt_path)
            log_message('✅ best.pt loaded successfully', 'success')
        else:
            log_message('❌ best.pt model not found', 'error')
            return None
    except Exception as e:
        log_message(f'❌ Failed to load best.pt: {e}', 'error')
        return None
    
    # Load SAM2_b
    try:
        log_message('Loading SAM2_b model...', 'info')
        sam2_path = 'data/models/sam/active/sam2_b.pt'
        if os.path.exists(sam2_path):
            models['sam2_b'] = SAM(sam2_path)
            log_message('✅ SAM2_b loaded successfully', 'success')
        else:
            log_message('❌ SAM2_b model not found', 'error')
            return None
    except Exception as e:
        log_message(f'❌ Failed to load SAM2_b: {e}', 'error')
        return None
    
    return models

def run_yolo_detection(model, image_path, model_name, device):
    log_message(f'🔍 Running {model_name} detection on {os.path.basename(image_path)}...', 'info')
    
    try:
        results = model(image_path, verbose=False)
        
        detections = []
        for result in results:
            if result.boxes is not None:
                boxes = result.boxes.xyxy.cpu().numpy()
                confidences = result.boxes.conf.cpu().numpy()
                classes = result.boxes.cls.cpu().numpy()
                
                for i, (box, conf, cls) in enumerate(zip(boxes, confidences, classes)):
                    detection = {
                        'bbox': box.tolist(),
                        'confidence': float(conf),
                        'class_id': int(cls),
                        'class_name': model.names[int(cls)] if hasattr(model, 'names') else f'class_{int(cls)}'
                    }
                    detections.append(detection)
        
        log_message(f'✅ {model_name} found {len(detections)} objects', 'success')
        for i, det in enumerate(detections):
            log_message(f'   Object {i+1}: {det[\"class_name\"]} (conf: {det[\"confidence\"]:.3f})', 'info')
        
        return detections
        
    except Exception as e:
        log_message(f'❌ {model_name} detection failed: {e}', 'error')
        return []

def run_sam_segmentation(sam_model, image_path, bounding_boxes, model_name, device):
    log_message(f'🎯 Running SAM2 segmentation with {len(bounding_boxes)} bounding boxes...', 'info')
    
    try:
        image = cv2.imread(image_path)
        image_rgb = cv2.cvtColor(image, cv2.COLOR_BGR2RGB)
        
        boxes = []
        for bbox in bounding_boxes:
            x1, y1, x2, y2 = bbox['bbox']
            boxes.append([x1, y1, x2, y2])
        
        if not boxes:
            log_message('⚠️  No bounding boxes provided for SAM2', 'warning')
            return []
        
        results = sam_model(image_rgb, bboxes=boxes, verbose=False)
        
        masks = []
        for i, result in enumerate(results):
            if hasattr(result, 'masks') and result.masks is not None:
                mask = result.masks.data.cpu().numpy()
                masks.append({
                    'mask': mask,
                    'bbox': bounding_boxes[i]['bbox'],
                    'confidence': bounding_boxes[i]['confidence'],
                    'class_name': bounding_boxes[i]['class_name']
                })
        
        log_message(f'✅ SAM2 generated {len(masks)} segmentation masks', 'success')
        return masks
        
    except Exception as e:
        log_message(f'❌ SAM2 segmentation failed: {e}', 'error')
        return []

def save_results(image_name, yolo_detections, sam_masks, output_dir):
    log_message(f'💾 Saving results for {image_name}...', 'info')
    
    os.makedirs(output_dir, exist_ok=True)
    
    detection_file = os.path.join(output_dir, f'{image_name}_best_pt_detections.json')
    with open(detection_file, 'w') as f:
        json.dump(yolo_detections, f, indent=2)
    
    if sam_masks:
        mask_dir = os.path.join(output_dir, f'{image_name}_best_pt_masks')
        os.makedirs(mask_dir, exist_ok=True)
        
        for i, mask_data in enumerate(sam_masks):
            mask_file = os.path.join(mask_dir, f'mask_{i+1}_{mask_data[\"class_name\"]}.png')
            mask = (mask_data['mask'][0] * 255).astype(np.uint8)
            cv2.imwrite(mask_file, mask)
    
    log_message(f'✅ Results saved to {output_dir}', 'success')

def main():
    log_message('🚀 MyCV-Platform best.pt Test', 'info')
    log_message('=' * 50, 'info')
    
    device = check_environment()
    models = load_models(device)
    if not models:
        log_message('❌ Failed to load models', 'error')
        return
    
    test_images_dir = 'data/input/test_images'
    test_images = [f for f in os.listdir(test_images_dir) if f.endswith('.jpg')]
    
    if not test_images:
        log_message('❌ No test images found', 'error')
        return
    
    log_message(f'📁 Found {len(test_images)} test images', 'info')
    
    for image_name in test_images:
        image_path = os.path.join(test_images_dir, image_name)
        log_message(f'🖼️  Processing: {image_name}', 'info')
        log_message('-' * 30, 'info')
        
        # Run best.pt detection
        log_message('1️⃣ best.pt Detection', 'info')
        best_pt_detections = run_yolo_detection(models['best_pt'], image_path, 'best.pt', device)
        
        # Run SAM2 with best.pt bounding boxes
        if best_pt_detections:
            log_message('2️⃣ SAM2 Segmentation (best.pt prompts)', 'info')
            sam_best_pt_masks = run_sam_segmentation(
                models['sam2_b'], image_path, best_pt_detections, 'SAM2_b', device
            )
            save_results(image_name, best_pt_detections, sam_best_pt_masks, 'data/output/integration_results')
        else:
            log_message('⚠️  No best.pt detections, skipping SAM2', 'warning')
    
    log_message('🎉 best.pt test completed successfully!', 'success')
    log_message('📊 Check data/output/integration_results for results', 'info')

if __name__ == '__main__':
    main()
"

if [ $? -eq 0 ]; then
    print_success "✅ best.pt test completed successfully"
else
    print_error "❌ best.pt test failed"
    exit 1
fi

print_status "Test Results Summary:"
echo "========================"
echo "📁 Generated files:"
find data/output/integration_results -name "*best_pt*" | wc -l | xargs echo "best.pt files:"
echo ""
echo "📊 Detection results:"
for file in data/output/integration_results/*_best_pt_detections.json; do
    if [ -f "$file" ]; then
        echo "📄 $(basename $file):"
        cat $file | python3 -c "
import json, sys
try:
    data = json.load(sys.stdin)
    print(f'  Objects: {len(data)}')
    for obj in data:
        print(f'  - {obj[\"class_name\"]}: {obj[\"confidence\"]:.3f}')
except:
    print('  Error reading file')
"
    fi
done

print_success "🎉 best.pt test completed!"
print_status "📊 Check 'data/output/integration_results/' for results"
