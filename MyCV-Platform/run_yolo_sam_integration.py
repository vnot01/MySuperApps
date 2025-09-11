#!/usr/bin/env python3
"""
MyCV-Platform YOLO + SAM Integration Script
Runs YOLO11m and best.pt models, then uses bounding boxes as prompts for SAM2_b
"""

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
    """Print colored log message"""
    colors = {
        'info': 'blue',
        'success': 'green',
        'warning': 'yellow',
        'error': 'red'
    }
    color = colors.get(level, 'white')
    print(colored(f"{level.upper()}: {message}", color))

def check_environment():
    """Check virtual environment and GPU availability"""
    log_message("🔍 Checking environment...", 'info')
    
    # Check virtual environment
    if 'VIRTUAL_ENV' in os.environ:
        log_message(f"✅ Running in virtual environment: {os.environ['VIRTUAL_ENV']}", 'success')
    else:
        log_message("⚠️  Not running in virtual environment", 'warning')
    
    # Check GPU
    if torch.cuda.is_available():
        log_message(f"🚀 GPU MODE: Using CUDA device - {torch.cuda.get_device_name(0)}", 'success')
        log_message(f"   GPU Memory: {torch.cuda.get_device_properties(0).total_memory / 1024**3:.1f} GB", 'info')
        device = 'cuda'
    else:
        log_message("💻 CPU MODE: Using CPU for inference", 'warning')
        device = 'cpu'
    
    return device

def load_models(device):
    """Load YOLO and SAM models"""
    log_message("📦 Loading models...", 'info')
    
    models = {}
    
    # Load YOLO11m
    try:
        log_message("Loading YOLO11m model...", 'info')
        yolo11m_path = "data/models/yolo/active/yolo11m.pt"
        if os.path.exists(yolo11m_path):
            models['yolo11m'] = YOLO(yolo11m_path)
            log_message("✅ YOLO11m loaded successfully", 'success')
        else:
            log_message("❌ YOLO11m model not found", 'error')
            return None
    except Exception as e:
        log_message(f"❌ Failed to load YOLO11m: {e}", 'error')
        return None
    
    # Load best.pt
    try:
        log_message("Loading best.pt model...", 'info')
        best_pt_path = "data/models/trained/best.pt"
        if os.path.exists(best_pt_path):
            models['best_pt'] = YOLO(best_pt_path)
            log_message("✅ best.pt loaded successfully", 'success')
        else:
            log_message("❌ best.pt model not found", 'error')
            return None
    except Exception as e:
        log_message(f"❌ Failed to load best.pt: {e}", 'error')
        return None
    
    # Load SAM2_b
    try:
        log_message("Loading SAM2_b model...", 'info')
        sam2_path = "data/models/sam/active/sam2_b.pt"
        if os.path.exists(sam2_path):
            models['sam2_b'] = SAM(sam2_path)
            log_message("✅ SAM2_b loaded successfully", 'success')
        else:
            log_message("❌ SAM2_b model not found", 'error')
            return None
    except Exception as e:
        log_message(f"❌ Failed to load SAM2_b: {e}", 'error')
        return None
    
    return models

def run_yolo_detection(model, image_path, model_name, device):
    """Run YOLO detection on image"""
    log_message(f"🔍 Running {model_name} detection on {os.path.basename(image_path)}...", 'info')
    
    try:
        # Run detection
        results = model(image_path, verbose=False)
        
        # Extract bounding boxes
        detections = []
        for result in results:
            if result.boxes is not None:
                boxes = result.boxes.xyxy.cpu().numpy()  # x1, y1, x2, y2
                confidences = result.boxes.conf.cpu().numpy()
                classes = result.boxes.cls.cpu().numpy()
                
                for i, (box, conf, cls) in enumerate(zip(boxes, confidences, classes)):
                    detection = {
                        'bbox': box.tolist(),  # [x1, y1, x2, y2]
                        'confidence': float(conf),
                        'class_id': int(cls),
                        'class_name': model.names[int(cls)] if hasattr(model, 'names') else f'class_{int(cls)}'
                    }
                    detections.append(detection)
        
        log_message(f"✅ {model_name} found {len(detections)} objects", 'success')
        for i, det in enumerate(detections):
            log_message(f"   Object {i+1}: {det['class_name']} (conf: {det['confidence']:.3f})", 'info')
        
        return detections
        
    except Exception as e:
        log_message(f"❌ {model_name} detection failed: {e}", 'error')
        return []

def run_sam_segmentation(sam_model, image_path, bounding_boxes, model_name, device):
    """Run SAM2 segmentation using bounding boxes as prompts"""
    log_message(f"🎯 Running SAM2 segmentation with {len(bounding_boxes)} bounding boxes...", 'info')
    
    try:
        # Load image
        image = cv2.imread(image_path)
        image_rgb = cv2.cvtColor(image, cv2.COLOR_BGR2RGB)
        
        # Convert bounding boxes to SAM format (x1, y1, x2, y2)
        boxes = []
        for bbox in bounding_boxes:
            x1, y1, x2, y2 = bbox['bbox']
            boxes.append([x1, y1, x2, y2])
        
        if not boxes:
            log_message("⚠️  No bounding boxes provided for SAM2", 'warning')
            return []
        
        # Run SAM2 segmentation
        results = sam_model(image_rgb, bboxes=boxes, verbose=False)
        
        # Extract masks
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
        
        log_message(f"✅ SAM2 generated {len(masks)} segmentation masks", 'success')
        return masks
        
    except Exception as e:
        log_message(f"❌ SAM2 segmentation failed: {e}", 'error')
        return []

def save_results(image_name, yolo_detections, sam_masks, output_dir):
    """Save detection and segmentation results"""
    log_message(f"💾 Saving results for {image_name}...", 'info')
    
    # Create output directory
    os.makedirs(output_dir, exist_ok=True)
    
    # Save detection results as JSON
    detection_file = os.path.join(output_dir, f"{image_name}_detections.json")
    with open(detection_file, 'w') as f:
        json.dump(yolo_detections, f, indent=2)
    
    # Save segmentation masks
    if sam_masks:
        mask_dir = os.path.join(output_dir, f"{image_name}_masks")
        os.makedirs(mask_dir, exist_ok=True)
        
        for i, mask_data in enumerate(sam_masks):
            mask_file = os.path.join(mask_dir, f"mask_{i+1}_{mask_data['class_name']}.png")
            mask = (mask_data['mask'][0] * 255).astype(np.uint8)
            cv2.imwrite(mask_file, mask)
    
    log_message(f"✅ Results saved to {output_dir}", 'success')

def main():
    """Main function"""
    log_message("🚀 MyCV-Platform YOLO + SAM Integration", 'info')
    log_message("=" * 50, 'info')
    
    # Check environment
    device = check_environment()
    
    # Load models
    models = load_models(device)
    if not models:
        log_message("❌ Failed to load models", 'error')
        return
    
    # Get test images
    test_images_dir = "data/input/test_images"
    test_images = [f for f in os.listdir(test_images_dir) if f.endswith('.jpg')]
    
    if not test_images:
        log_message("❌ No test images found", 'error')
        return
    
    log_message(f"📁 Found {len(test_images)} test images", 'info')
    
    # Process each image
    for image_name in test_images:
        image_path = os.path.join(test_images_dir, image_name)
        log_message(f"\n🖼️  Processing: {image_name}", 'info')
        log_message("-" * 30, 'info')
        
        # 1. Run YOLO11m detection
        log_message("1️⃣ YOLO11m Detection", 'info')
        yolo11m_detections = run_yolo_detection(models['yolo11m'], image_path, 'YOLO11m', device)
        
        # 2. Run SAM2 with YOLO11m bounding boxes
        if yolo11m_detections:
            log_message("2️⃣ SAM2 Segmentation (YOLO11m prompts)", 'info')
            sam_yolo11m_masks = run_sam_segmentation(
                models['sam2_b'], image_path, yolo11m_detections, 'SAM2_b', device
            )
            save_results(f"{image_name}_yolo11m", yolo11m_detections, sam_yolo11m_masks, "data/output/integration_results")
        else:
            log_message("⚠️  No YOLO11m detections, skipping SAM2", 'warning')
        
        # 3. Run best.pt detection
        log_message("3️⃣ best.pt Detection", 'info')
        best_pt_detections = run_yolo_detection(models['best_pt'], image_path, 'best.pt', device)
        
        # 4. Run SAM2 with best.pt bounding boxes
        if best_pt_detections:
            log_message("4️⃣ SAM2 Segmentation (best.pt prompts)", 'info')
            sam_best_pt_masks = run_sam_segmentation(
                models['sam2_b'], image_path, best_pt_detections, 'SAM2_b', device
            )
            save_results(f"{image_name}_best_pt", best_pt_detections, sam_best_pt_masks, "data/output/integration_results")
        else:
            log_message("⚠️  No best.pt detections, skipping SAM2", 'warning')
    
    log_message("\n🎉 Integration completed successfully!", 'success')
    log_message("📊 Check 'data/output/integration_results' for results", 'info')

if __name__ == "__main__":
    main()
