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

def save_results_remote(base_name, yolo_detections, sam_masks, output_dir, original_image_path):
    """Save detection and segmentation results with new naming convention"""
    log_message(f"💾 Saving remote results for {base_name}...", 'info')
    
    # Create output directory
    os.makedirs(output_dir, exist_ok=True)
    
    # Load original image for visualization
    original_image = cv2.imread(original_image_path)
    original_image_rgb = cv2.cvtColor(original_image, cv2.COLOR_BGR2RGB)
    
    # Save detection results as JSON with new naming
    json_file = os.path.join(output_dir, f"{base_name}-detection.json")
    with open(json_file, 'w') as f:
        json.dump(yolo_detections, f, indent=2)
    
    # Create detection visualization (bounding boxes)
    detection_image = draw_bounding_boxes(original_image_rgb, yolo_detections)
    detection_file = os.path.join(output_dir, f"{base_name}-detection.png")
    cv2.imwrite(detection_file, cv2.cvtColor(detection_image, cv2.COLOR_RGB2BGR))
    
    # Create segmentation visualization if masks exist
    if sam_masks:
        segmentation_image = overlay_segmentation_masks(original_image_rgb, sam_masks)
        segmentation_file = os.path.join(output_dir, f"{base_name}-segmentation.png")
        cv2.imwrite(segmentation_file, cv2.cvtColor(segmentation_image, cv2.COLOR_RGB2BGR))
        
        # Create hybrid visualization (detection + segmentation)
        hybrid_image = detection_image.copy()
        hybrid_image = overlay_segmentation_masks(hybrid_image, sam_masks)
        hybrid_file = os.path.join(output_dir, f"{base_name}-hybrid.png")
        cv2.imwrite(hybrid_file, cv2.cvtColor(hybrid_image, cv2.COLOR_RGB2BGR))
        
        log_message(f"✅ Remote results saved: detection, segmentation, hybrid", 'success')
    else:
        log_message(f"✅ Remote results saved: detection only", 'success')
    
    log_message(f"📁 Files saved to {output_dir}", 'info')

def draw_bounding_boxes(image, detections, color=(0, 255, 0), thickness=2):
    """Draw bounding boxes on image"""
    result_image = image.copy()
    
    for i, detection in enumerate(detections):
        x1, y1, x2, y2 = map(int, detection['bbox'])
        confidence = detection['confidence']
        class_name = detection['class_name']
        
        # Draw bounding box
        cv2.rectangle(result_image, (x1, y1), (x2, y2), color, thickness)
        
        # Draw label
        label = f"{class_name}: {confidence:.3f}"
        label_size = cv2.getTextSize(label, cv2.FONT_HERSHEY_SIMPLEX, 0.5, 2)[0]
        cv2.rectangle(result_image, (x1, y1 - label_size[1] - 10), 
                     (x1 + label_size[0], y1), color, -1)
        cv2.putText(result_image, label, (x1, y1 - 5), 
                   cv2.FONT_HERSHEY_SIMPLEX, 0.5, (255, 255, 255), 2)
    
    return result_image

def overlay_segmentation_masks(image, masks, alpha=0.3):
    """Overlay segmentation masks on image with better brightness preservation"""
    result_image = image.copy()
    
    colors = [
        (255, 0, 0),    # Red
        (0, 255, 0),    # Green
        (0, 0, 255),    # Blue
        (255, 255, 0),  # Yellow
        (255, 0, 255),  # Magenta
        (0, 255, 255),  # Cyan
    ]
    
    for i, mask_data in enumerate(masks):
        mask = mask_data['mask'][0] if len(mask_data['mask'].shape) == 3 else mask_data['mask']
        color = colors[i % len(colors)]
        
        # Create colored mask with better blending
        colored_mask = np.zeros_like(image)
        colored_mask[mask > 0] = color
        
        # Use lighter alpha for better brightness preservation
        result_image = cv2.addWeighted(result_image, 1 - alpha, colored_mask, alpha, 0)
    
    return result_image

def save_results_remote_structured(base_name, yolo_detections, sam_masks, model_dir, segmentasi_dir, hybrid_dir, main_output_dir, original_image_path, model_type):
    """Save detection and segmentation results with structured directory organization"""
    log_message(f"💾 Saving structured remote results for {base_name}...", 'info')
    
    # Load original image for visualization
    original_image = cv2.imread(original_image_path)
    original_image_rgb = cv2.cvtColor(original_image, cv2.COLOR_BGR2RGB)
    
    # Save detection results as JSON in main output directory (only for best.pt)
    if model_type == "best_pt":
        json_file = os.path.join(main_output_dir, f"{base_name}-detection.json")
        with open(json_file, 'w') as f:
            json.dump(yolo_detections, f, indent=2)
        log_message(f"📄 JSON saved for best.pt model", 'info')
    
    # Create detection visualization (bounding boxes) in model directory
    detection_image = draw_bounding_boxes(original_image_rgb, yolo_detections)
    
    # Determine file naming based on model type
    if model_type == "best_pt":
        detection_file = os.path.join(model_dir, f"{base_name}-best.png")
    else:  # yolo11m
        detection_file = os.path.join(model_dir, f"{base_name}-detection.png")
    
    cv2.imwrite(detection_file, cv2.cvtColor(detection_image, cv2.COLOR_RGB2BGR))
    
    # Create segmentation visualization if masks exist
    if sam_masks:
        segmentation_image = overlay_segmentation_masks(original_image_rgb, sam_masks)
        segmentation_file = os.path.join(segmentasi_dir, f"{base_name}-segmentation.png")
        cv2.imwrite(segmentation_file, cv2.cvtColor(segmentation_image, cv2.COLOR_RGB2BGR))
        
        # Create hybrid visualization (detection + segmentation) in hybrid directory
        hybrid_image = detection_image.copy()
        hybrid_image = overlay_segmentation_masks(hybrid_image, sam_masks)
        hybrid_file = os.path.join(hybrid_dir, f"{base_name}-hybrid.png")
        cv2.imwrite(hybrid_file, cv2.cvtColor(hybrid_image, cv2.COLOR_RGB2BGR))
        
        log_message(f"✅ Structured results saved: detection/best, segmentation, hybrid", 'success')
    else:
        log_message(f"✅ Structured results saved: detection/best only", 'success')
    
    if model_type == "best_pt":
        log_message(f"📁 Files saved to: {model_dir}, {segmentasi_dir}, {hybrid_dir}, {main_output_dir}", 'info')
    else:
        log_message(f"📁 Files saved to: {model_dir}, {segmentasi_dir}, {hybrid_dir}", 'info')

def main():
    """Main function"""
    log_message("🚀 MyCV-Platform YOLO + SAM Integration (Copy Version)", 'info')
    log_message("=" * 60, 'info')
    
    # Check environment
    device = check_environment()
    
    # Load models
    models = load_models(device)
    if not models:
        log_message("❌ Failed to load models", 'error')
        return
    
    # Get test images from remote directory
    test_images_dir = "data/input/remote"
    test_images = []
    
    # Find all images in remote subdirectories
    for root, dirs, files in os.walk(test_images_dir):
        for file in files:
            if file.endswith('.jpg'):
                test_images.append(os.path.join(root, file))
    
    if not test_images:
        log_message("❌ No test images found in remote directory", 'error')
        return
    
    log_message(f"📁 Found {len(test_images)} test images in remote directory", 'info')
    
    # Process each image
    for image_path in test_images:
        image_name = os.path.basename(image_path)
        log_message(f"\n🖼️  Processing: {image_name}", 'info')
        log_message("-" * 30, 'info')
        
        # Extract timestamp and user_id from path
        path_parts = image_path.split(os.sep)
        timestamp = path_parts[-3] if len(path_parts) >= 3 else "unknown"
        user_id = path_parts[-2] if len(path_parts) >= 2 else "unknown"
        
        # Create output directories for this user session
        output_dir = f"data/output/remote/{timestamp}/{user_id}"
        yolo_dir = f"{output_dir}/yolo"
        best_dir = f"{output_dir}/best"
        segmentasi_dir = f"{output_dir}/segmentasi"
        hybrid_dir = f"{output_dir}/hybrid"
        
        os.makedirs(output_dir, exist_ok=True)
        os.makedirs(yolo_dir, exist_ok=True)
        os.makedirs(best_dir, exist_ok=True)
        os.makedirs(segmentasi_dir, exist_ok=True)
        os.makedirs(hybrid_dir, exist_ok=True)
        
        # Get base name without extension
        base_name = os.path.splitext(image_name)[0]
        
        # 1. Run YOLO11m detection
        log_message("1️⃣ YOLO11m Detection", 'info')
        yolo11m_detections = run_yolo_detection(models['yolo11m'], image_path, 'YOLO11m', device)
        
        # 2. Run SAM2 with YOLO11m bounding boxes
        if yolo11m_detections:
            log_message("2️⃣ SAM2 Segmentation (YOLO11m prompts)", 'info')
            sam_yolo11m_masks = run_sam_segmentation(
                models['sam2_b'], image_path, yolo11m_detections, 'SAM2_b', device
            )
            save_results_remote_structured(f"{base_name}-yolo11m", yolo11m_detections, sam_yolo11m_masks, 
                                         yolo_dir, segmentasi_dir, hybrid_dir, output_dir, image_path, "yolo11m")
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
            save_results_remote_structured(f"{base_name}-best_pt", best_pt_detections, sam_best_pt_masks, 
                                         best_dir, segmentasi_dir, hybrid_dir, output_dir, image_path, "best_pt")
        else:
            log_message("⚠️  No best.pt detections, skipping SAM2", 'warning')
    
    log_message("\n🎉 Integration completed successfully!", 'success')
    log_message("📊 Check 'data/output/remote' for results", 'info')

if __name__ == "__main__":
    main()
