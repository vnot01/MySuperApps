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
import matplotlib.pyplot as plt
import argparse
import shutil

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
        best_pt_path = "data/models/trained/active/best.pt"
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
    """Draw bounding boxes on image with readable labels inside the box."""
    result_image = image.copy()
    height, width = result_image.shape[:2]
    base_scale = max(width, height) / 800.0
    font_scale = max(0.5, min(1.2, 0.6 * base_scale))
    font_thickness = 2

    for detection in detections:
        x1, y1, x2, y2 = map(int, detection['bbox'])
        confidence = detection['confidence']
        class_name = detection['class_name']

        x1 = max(0, min(x1, width - 1))
        y1 = max(0, min(y1, height - 1))
        x2 = max(0, min(x2, width - 1))
        y2 = max(0, min(y2, height - 1))

        cv2.rectangle(result_image, (x1, y1), (x2, y2), color, thickness)

        label = f"{class_name}: {confidence:.2f}"
        (label_w, label_h), _ = cv2.getTextSize(label, cv2.FONT_HERSHEY_SIMPLEX, font_scale, font_thickness)
        box_x = x1 + 2
        box_y = y1 + 2
        rect_pt1, rect_pt2 = (box_x, box_y), (min(box_x + label_w + 6, width - 1), min(box_y + label_h + 6, height - 1))
        cv2.rectangle(result_image, rect_pt1, rect_pt2, color, -1)
        cv2.putText(result_image, label, (box_x + 3, box_y + label_h + 1), cv2.FONT_HERSHEY_SIMPLEX, font_scale, (255, 255, 255), font_thickness)

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

def create_visualization(image_path, yolo_detections, sam_masks, output_path):
    """Create comprehensive visualization"""
    # Load original image
    image = cv2.imread(image_path)
    image_rgb = cv2.cvtColor(image, cv2.COLOR_BGR2RGB)
    
    # Create subplots
    fig, axes = plt.subplots(2, 2, figsize=(15, 12))
    fig.suptitle(f'YOLO + SAM2 Results: {os.path.basename(image_path)}', fontsize=16)
    
    # Original image
    axes[0, 0].imshow(image_rgb)
    axes[0, 0].set_title('Original Image')
    axes[0, 0].axis('off')
    
    # YOLO detections
    yolo_image = draw_bounding_boxes(image_rgb, yolo_detections, color=(0, 255, 0))
    axes[0, 1].imshow(yolo_image)
    axes[0, 1].set_title(f'YOLO Detections ({len(yolo_detections)} objects)')
    axes[0, 1].axis('off')
    
    # SAM2 segmentation
    if sam_masks:
        sam_image = overlay_segmentation_masks(image_rgb, sam_masks)
        axes[1, 0].imshow(sam_image)
        axes[1, 0].set_title(f'SAM2 Segmentation ({len(sam_masks)} masks)')
    else:
        axes[1, 0].imshow(image_rgb)
        axes[1, 0].set_title('SAM2 Segmentation (No masks)')
    axes[1, 0].axis('off')
    
    # Combined result
    combined_image = yolo_image.copy()
    if sam_masks:
        combined_image = overlay_segmentation_masks(combined_image, sam_masks)
    axes[1, 1].imshow(combined_image)
    axes[1, 1].set_title('Combined Result')
    axes[1, 1].axis('off')
    
    plt.tight_layout()
    plt.savefig(output_path, dpi=150, bbox_inches='tight')
    plt.close()

def create_compare_visualization(image_path, yolo_detection_path, best_detection_path, segmentation_path, hybrid_path, output_path):
    """Create compare visualization combining all 4 results"""
    # Load original image
    image = cv2.imread(image_path)
    image_rgb = cv2.cvtColor(image, cv2.COLOR_BGR2RGB)
    
    # Load result images
    yolo_img = cv2.imread(yolo_detection_path) if os.path.exists(yolo_detection_path) else image
    best_img = cv2.imread(best_detection_path) if os.path.exists(best_detection_path) else image
    segmentation_img = cv2.imread(segmentation_path) if os.path.exists(segmentation_path) else image
    hybrid_img = cv2.imread(hybrid_path) if os.path.exists(hybrid_path) else image
    
    # Convert to RGB
    yolo_rgb = cv2.cvtColor(yolo_img, cv2.COLOR_BGR2RGB)
    best_rgb = cv2.cvtColor(best_img, cv2.COLOR_BGR2RGB)
    segmentation_rgb = cv2.cvtColor(segmentation_img, cv2.COLOR_BGR2RGB)
    hybrid_rgb = cv2.cvtColor(hybrid_img, cv2.COLOR_BGR2RGB)
    
    # Create subplots
    fig, axes = plt.subplots(2, 3, figsize=(18, 12))
    fig.suptitle(f'Compare Results: {os.path.basename(image_path)}', fontsize=16)
    
    # Original image
    axes[0, 0].imshow(image_rgb)
    axes[0, 0].set_title('Original Image')
    axes[0, 0].axis('off')
    
    # YOLO detection
    axes[0, 1].imshow(yolo_rgb)
    axes[0, 1].set_title('YOLO11m Detection')
    axes[0, 1].axis('off')
    
    # Best detection
    axes[0, 2].imshow(best_rgb)
    axes[0, 2].set_title('Best.pt Detection')
    axes[0, 2].axis('off')
    
    # Segmentation
    axes[1, 0].imshow(segmentation_rgb)
    axes[1, 0].set_title('SAM2 Segmentation')
    axes[1, 0].axis('off')
    
    # Hybrid
    axes[1, 1].imshow(hybrid_rgb)
    axes[1, 1].set_title('Hybrid (Best + SAM)')
    axes[1, 1].axis('off')
    
    # Hide the last subplot
    axes[1, 2].axis('off')
    
    plt.tight_layout()
    plt.savefig(output_path, dpi=150, bbox_inches='tight')
    plt.close()

def generate_visualizations(image_path, base_name, output_dir, yolo_dir, best_dir, segmentasi_dir, hybrid_dir):
    """Generate all visualizations for an image"""
    log_message(f"🎨 Generating visualizations for {base_name}...", 'info')
    
    # Load detection results
    yolo11m_json = os.path.join(output_dir, f"{base_name}-yolo11m-detection.json")
    best_pt_json = os.path.join(output_dir, f"{base_name}-best_pt-detection.json")
    
    # Process YOLO11m results
    if os.path.exists(yolo11m_json):
        with open(yolo11m_json, 'r') as f:
            yolo11m_detections = json.load(f)
        
        # Create comprehensive visualization in yolo directory
        output_path = os.path.join(yolo_dir, f"{base_name}-yolo11m-visualization.png")
        create_visualization(image_path, yolo11m_detections, [], output_path)
        log_message(f"✅ YOLO11m visualization saved: {output_path}", 'success')
    
    # Process best.pt results
    if os.path.exists(best_pt_json):
        with open(best_pt_json, 'r') as f:
            best_pt_detections = json.load(f)
        
        # Create comprehensive visualization in best directory
        output_path = os.path.join(best_dir, f"{base_name}-best_pt-visualization.png")
        create_visualization(image_path, best_pt_detections, [], output_path)
        log_message(f"✅ best.pt visualization saved: {output_path}", 'success')
    
    # Create compare visualization combining all results
    yolo_detection_path = os.path.join(yolo_dir, f"{base_name}-yolo11m-detection.png")
    best_detection_path = os.path.join(best_dir, f"{base_name}-best_pt-best.png")
    segmentation_path = os.path.join(segmentasi_dir, f"{base_name}-best_pt-segmentation.png")
    hybrid_path = os.path.join(hybrid_dir, f"{base_name}-best_pt-hybrid.png")
    
    # Check if all required files exist for compare
    missing_files = []
    if not os.path.exists(yolo_detection_path):
        missing_files.append("YOLO11m detection")
    if not os.path.exists(best_detection_path):
        missing_files.append("best.pt detection")
    if not os.path.exists(segmentation_path):
        missing_files.append("SAM2 segmentation")
    if not os.path.exists(hybrid_path):
        missing_files.append("hybrid result")
    
    if len(missing_files) == 0:
        compare_output_path = os.path.join(output_dir, f"{base_name}-best_pt-compare.png")
        create_compare_visualization(image_path, yolo_detection_path, best_detection_path, 
                                   segmentation_path, hybrid_path, compare_output_path)
        log_message(f"✅ Compare visualization saved: {compare_output_path}", 'success')
    else:
        log_message(f"⚠️  Compare visualization skipped for {base_name} - Missing: {', '.join(missing_files)}", 'warning')

def main():
    """Main function"""
    # Parse optional args for API session-aware URL writing
    parser = argparse.ArgumentParser(description='Hybrid integration test runner')
    parser.add_argument('--session_id', help='API session_id for download URLs', default=None)
    parser.add_argument('--timestamp', help='Specific timestamp for session', default=None)
    parser.add_argument('--user_id', help='Specific user_id for session', default=None)
    args = parser.parse_args()
    # Load project info from model_info.json
    try:
        with open('/data/models/model_info.json', 'r') as f:
            model_info = json.load(f)
        project_name = model_info['name']
        project_version = model_info['version']
    except:
        project_name = "MyHybrid-Detection"
        project_version = "1.0.0"
    
    log_message(f"🚀 {project_name} v{project_version}", 'info')
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
    
    # Find only the latest session images (most recent timestamp)
    timestamp_dirs = []
    for item in os.listdir(test_images_dir):
        item_path = os.path.join(test_images_dir, item)
        if os.path.isdir(item_path):
            timestamp_dirs.append(item)
    
    if not timestamp_dirs:
        log_message("❌ No timestamp directories found in remote directory", 'error')
        return
    
    # Sort timestamps and get the latest one
    latest_timestamp = sorted(timestamp_dirs)[-1]
    latest_session_dir = os.path.join(test_images_dir, latest_timestamp)
    
    log_message(f"📅 Processing latest session: {latest_timestamp}", 'info')
    
    # Find images only in the latest session
    for root, dirs, files in os.walk(latest_session_dir):
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
            log_message("⚠️  No YOLO11m detections, creating fallback detection image", 'warning')
            # Create fallback detection image (original image with "No detections" text)
            original_image = cv2.imread(image_path)
            original_image_rgb = cv2.cvtColor(original_image, cv2.COLOR_BGR2RGB)
            
            # Add "No detections" text
            cv2.putText(original_image, "No YOLO11m detections", (50, 50), 
                       cv2.FONT_HERSHEY_SIMPLEX, 1, (0, 0, 255), 2)
            
            # Save fallback detection image
            fallback_file = os.path.join(yolo_dir, f"{base_name}-yolo11m-detection.png")
            cv2.imwrite(fallback_file, original_image)
            log_message(f"✅ Fallback YOLO11m detection image saved: {fallback_file}", 'success')
        
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
        
        # Generate visualizations for this image
        generate_visualizations(image_path, base_name, output_dir, yolo_dir, best_dir, segmentasi_dir, hybrid_dir)
    
    # Create summary.json for each session
    create_session_summary(test_images, project_name, project_version, session_id=args.session_id)
    
    log_message(f"\n🎉 {project_name} completed successfully!", 'success')
    log_message("📊 Check 'data/output/remote' for results", 'info')

def create_session_summary(test_images, project_name, project_version, session_id=None):
    """Create summary.json for each session (timestamp/user_id)"""
    log_message("📋 Creating session summaries...", 'info')
    
    # Group images by session (timestamp/user_id)
    sessions = {}
    for image_path in test_images:
        path_parts = image_path.split(os.sep)
        timestamp = path_parts[-3] if len(path_parts) >= 3 else "unknown"
        user_id = path_parts[-2] if len(path_parts) >= 2 else "unknown"
        
        session_key = f"{timestamp}/{user_id}"
        if session_key not in sessions:
            sessions[session_key] = {
                'timestamp': timestamp,
                'user_id': user_id,
                'images': []
            }
        sessions[session_key]['images'].append(image_path)
    
    # Create summary for each session
    for session_key, session_data in sessions.items():
        timestamp = session_data['timestamp']
        user_id = session_data['user_id']
        
        log_message(f"📝 Creating summary for session: {session_key}", 'info')
        
        # Output directory for this session
        output_dir = f"data/output/remote/{timestamp}/{user_id}"
        
        # Collect all detection results for this session
        detection_summary = []
        
        # Find all JSON files in the session directory (only for current session)
        json_files = []
        if os.path.exists(output_dir):
            for root, dirs, files in os.walk(output_dir):
                for file in files:
                    if file.endswith('-detection.json'):
                        # Only include files from current session
                        json_files.append(os.path.join(root, file))
        
        # Process each JSON file
        for json_file in json_files:
            try:
                with open(json_file, 'r') as f:
                    detection_data = json.load(f)
                
                # Extract image name and model from filename
                filename = os.path.basename(json_file)
                # Format: image_name-model-detection.json
                parts = filename.replace('-detection.json', '').split('-')
                if len(parts) >= 2:
                    image_name = '-'.join(parts[:-1])  # Everything except last part (model)
                    model_name = parts[-1]  # Last part is model name
                else:
                    image_name = filename.replace('-detection.json', '')
                    model_name = 'unknown'
                
                # Create detection entry according to user's format
                detection_entry = {
                    "id": len(detection_summary),
                    "name": filename,
                    "datas": detection_data,
                    "detection_count": len(detection_data)
                }
                
                # Create images object with URLs for all model results
                images = {}
                
                # Helper to maybe copy file to root for API /api/download/<session_id>/<filename>
                def ensure_in_root(src_path: str, output_dir: str) -> str:
                    base = os.path.basename(src_path)
                    dst = os.path.join(output_dir, base)
                    try:
                        if os.path.exists(src_path) and not os.path.exists(dst):
                            shutil.copy2(src_path, dst)
                    except Exception as e:
                        log_message(f"⚠️  Failed to copy {src_path} -> {dst}: {e}", 'warning')
                    return base

                # Best image (best.pt result)
                best_dir = os.path.join(output_dir, "best")
                best_image_path = os.path.join(best_dir, f"{image_name}-{model_name}-best.png")
                if os.path.exists(best_image_path):
                    if session_id:
                        fname = ensure_in_root(best_image_path, output_dir)
                        images["best"] = f"http://100.98.142.94:5000/api/download/{session_id}/{fname}"
                    else:
                        images["best"] = f"http://100.98.142.94:5000/api/download/{timestamp}/{user_id}/best/{os.path.basename(best_image_path)}"
                
                # Summary image (compare result) - located in root output directory
                compare_image_path = os.path.join(output_dir, f"{image_name}-{model_name}-compare.png")
                if os.path.exists(compare_image_path):
                    if session_id:
                        fname = os.path.basename(compare_image_path)
                        detection_entry["summary_images_url"] = f"http://100.98.142.94:5000/api/download/{session_id}/{fname}"
                    else:
                        detection_entry["summary_images_url"] = f"http://100.98.142.94:5000/api/download/{timestamp}/{user_id}/{os.path.basename(compare_image_path)}"
                
                # YOLO image (yolo11m result)
                yolo_dir = os.path.join(output_dir, "yolo")
                yolo_image_path = os.path.join(yolo_dir, f"{image_name}-yolo11m-detection.png")
                if os.path.exists(yolo_image_path):
                    if session_id:
                        fname = ensure_in_root(yolo_image_path, output_dir)
                        images["yolo"] = f"http://100.98.142.94:5000/api/download/{session_id}/{fname}"
                    else:
                        images["yolo"] = f"http://100.98.142.94:5000/api/download/{timestamp}/{user_id}/yolo/{os.path.basename(yolo_image_path)}"
                
                # SAM image (segmentation result)
                segmentasi_dir = os.path.join(output_dir, "segmentasi")
                sam_image_path = os.path.join(segmentasi_dir, f"{image_name}-{model_name}-segmentation.png")
                if os.path.exists(sam_image_path):
                    if session_id:
                        fname = ensure_in_root(sam_image_path, output_dir)
                        images["sam"] = f"http://100.98.142.94:5000/api/download/{session_id}/{fname}"
                    else:
                        images["sam"] = f"http://100.98.142.94:5000/api/download/{timestamp}/{user_id}/segmentasi/{os.path.basename(sam_image_path)}"
                
                # Hybrid image (combined result)
                hybrid_dir = os.path.join(output_dir, "hybrid")
                hybrid_image_path = os.path.join(hybrid_dir, f"{image_name}-{model_name}-hybrid.png")
                if os.path.exists(hybrid_image_path):
                    if session_id:
                        fname = ensure_in_root(hybrid_image_path, output_dir)
                        images["hybrid"] = f"http://100.98.142.94:5000/api/download/{session_id}/{fname}"
                    else:
                        images["hybrid"] = f"http://100.98.142.94:5000/api/download/{timestamp}/{user_id}/hybrid/{os.path.basename(hybrid_image_path)}"
                
                # Add images object to detection entry
                if images:
                    detection_entry["images"] = images
                
                detection_summary.append(detection_entry)
                
            except Exception as e:
                log_message(f"⚠️  Error processing {json_file}: {e}", 'warning')
                continue
        
        # Create class summary by counting all class_name occurrences
        class_counts = {}
        for detection_entry in detection_summary:
            for detection_data in detection_entry["datas"]:
                class_name = detection_data["class_name"]
                class_counts[class_name] = class_counts.get(class_name, 0) + 1
        
        # Convert to class_summary array format
        class_summary = []
        for class_name, count in class_counts.items():
            class_summary.append({
                "class_name": class_name,
                "count": count
            })
        
        # Sort by count (descending) then by class_name (ascending)
        class_summary.sort(key=lambda x: (-x["count"], x["class_name"]))
        
        # Create summary data according to user's format
        summary_data = {
            "detection_summary": detection_summary,
            "class_summary": class_summary,
            "object_count": len(detection_summary)  # Total number of objects processed in this session
        }
        
        # Save summary.json
        summary_file = os.path.join(output_dir, "summary.json")
        try:
            with open(summary_file, 'w') as f:
                json.dump(summary_data, f, indent=2)
            log_message(f"✅ Summary saved: {summary_file}", 'success')
            log_message(f"   📊 {len(detection_summary)} detections from {len(session_data['images'])} images", 'info')
        except Exception as e:
            log_message(f"❌ Error saving summary: {e}", 'error')

if __name__ == "__main__":
    main()
