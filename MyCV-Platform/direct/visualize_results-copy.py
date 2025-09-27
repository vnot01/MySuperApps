#!/usr/bin/env python3
"""
MyCV-Platform Results Visualization Script
Visualizes YOLO detections and SAM2 segmentation results
"""

import os
import cv2
import numpy as np
import json
import matplotlib.pyplot as plt
from pathlib import Path
from termcolor import colored

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

def load_detection_results(json_file):
    """Load detection results from JSON file"""
    with open(json_file, 'r') as f:
        return json.load(f)

def load_segmentation_masks(mask_dir):
    """Load segmentation masks from directory"""
    masks = []
    if os.path.exists(mask_dir):
        for mask_file in os.listdir(mask_dir):
            if mask_file.endswith('.png'):
                mask_path = os.path.join(mask_dir, mask_file)
                mask = cv2.imread(mask_path, cv2.IMREAD_GRAYSCALE)
                masks.append({
                    'mask': mask,
                    'filename': mask_file
                })
    return masks

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
        mask = mask_data['mask']
        color = colors[i % len(colors)]
        
        # Create colored mask with better blending
        colored_mask = np.zeros_like(image)
        colored_mask[mask > 0] = color
        
        # Use lighter alpha for better brightness preservation
        result_image = cv2.addWeighted(result_image, 1 - alpha, colored_mask, alpha, 0)
    
    return result_image

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

def main():
    """Main function"""
    log_message("🎨 MyCV-Platform Results Visualization (Copy Version)", 'info')
    log_message("=" * 60, 'info')
    
    # Get test images from remote directory
    test_images_dir = "data/input/remote"
    results_dir = "data/output/remote"
    
    # Find all images in remote subdirectories
    test_images = []
    for root, dirs, files in os.walk(test_images_dir):
        for file in files:
            if file.endswith('.jpg'):
                test_images.append(os.path.join(root, file))
    
    if not test_images:
        log_message("❌ No test images found in remote directory", 'error')
        return
    
    log_message(f"📁 Found {len(test_images)} test images in remote directory", 'info')
    
    for image_path in test_images:
        image_name = os.path.basename(image_path)
        log_message(f"🖼️  Processing: {image_name}", 'info')
        
        # Extract timestamp and user_id from path
        path_parts = image_path.split(os.sep)
        timestamp = path_parts[-3] if len(path_parts) >= 3 else "unknown"
        user_id = path_parts[-2] if len(path_parts) >= 2 else "unknown"
        
        # Set output directories
        output_dir = f"{results_dir}/{timestamp}/{user_id}"
        yolo_dir = f"{output_dir}/yolo"
        best_dir = f"{output_dir}/best"
        segmentasi_dir = f"{output_dir}/segmentasi"
        hybrid_dir = f"{output_dir}/hybrid"
        
        base_name = os.path.splitext(image_name)[0]
        
        # Process YOLO11m results
        yolo11m_json = os.path.join(output_dir, f"{base_name}-yolo11m-detection.json")
        
        if os.path.exists(yolo11m_json):
            yolo11m_detections = load_detection_results(yolo11m_json)
            
            # Create comprehensive visualization in yolo directory
            output_path = os.path.join(yolo_dir, f"{base_name}-yolo11m-visualization.png")
            create_visualization(image_path, yolo11m_detections, [], output_path)
            log_message(f"✅ YOLO11m visualization saved: {output_path}", 'success')
        
        # Process best.pt results
        best_pt_json = os.path.join(output_dir, f"{base_name}-best_pt-detection.json")
        
        if os.path.exists(best_pt_json):
            best_pt_detections = load_detection_results(best_pt_json)
            
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
        if (os.path.exists(yolo_detection_path) and os.path.exists(best_detection_path) and 
            os.path.exists(segmentation_path) and os.path.exists(hybrid_path)):
            
            compare_output_path = os.path.join(output_dir, f"{base_name}-best_pt-compare.png")
            create_compare_visualization(image_path, yolo_detection_path, best_detection_path, 
                                       segmentation_path, hybrid_path, compare_output_path)
            log_message(f"✅ Compare visualization saved: {compare_output_path}", 'success')
        else:
            log_message(f"⚠️  Some files missing for compare visualization of {base_name}", 'warning')
    
    log_message("🎉 Visualization completed!", 'success')
    log_message(f"📊 Check '{results_dir}' for visualization images", 'info')

if __name__ == "__main__":
    main()
