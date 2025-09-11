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

def overlay_segmentation_masks(image, masks, alpha=0.5):
    """Overlay segmentation masks on image"""
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
        
        # Create colored mask
        colored_mask = np.zeros_like(image)
        colored_mask[mask > 0] = color
        
        # Overlay mask
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

def main():
    """Main function"""
    log_message("🎨 MyCV-Platform Results Visualization", 'info')
    log_message("=" * 50, 'info')
    
    # Get test images
    test_images_dir = "data/input/test_images"
    results_dir = "data/output/integration_results"
    output_dir = "data/output/visualizations"
    
    os.makedirs(output_dir, exist_ok=True)
    
    test_images = [f for f in os.listdir(test_images_dir) if f.endswith('.jpg')]
    
    for image_name in test_images:
        log_message(f"🖼️  Processing: {image_name}", 'info')
        
        image_path = os.path.join(test_images_dir, image_name)
        base_name = image_name.replace('.jpg', '')
        
        # Process YOLO11m results
        yolo11m_json = os.path.join(results_dir, f"{image_name}_yolo11m_detections.json")
        yolo11m_masks_dir = os.path.join(results_dir, f"{image_name}_yolo11m_masks")
        
        if os.path.exists(yolo11m_json):
            yolo11m_detections = load_detection_results(yolo11m_json)
            yolo11m_masks = load_segmentation_masks(yolo11m_masks_dir)
            
            output_path = os.path.join(output_dir, f"{base_name}_yolo11m_visualization.png")
            create_visualization(image_path, yolo11m_detections, yolo11m_masks, output_path)
            log_message(f"✅ YOLO11m visualization saved: {output_path}", 'success')
        
        # Process best.pt results
        best_pt_json = os.path.join(results_dir, f"{image_name}_best_pt_detections.json")
        best_pt_masks_dir = os.path.join(results_dir, f"{image_name}_best_pt_masks")
        
        if os.path.exists(best_pt_json):
            best_pt_detections = load_detection_results(best_pt_json)
            best_pt_masks = load_segmentation_masks(best_pt_masks_dir)
            
            output_path = os.path.join(output_dir, f"{base_name}_best_pt_visualization.png")
            create_visualization(image_path, best_pt_detections, best_pt_masks, output_path)
            log_message(f"✅ best.pt visualization saved: {output_path}", 'success')
    
    log_message("🎉 Visualization completed!", 'success')
    log_message(f"📊 Check '{output_dir}' for visualization images", 'info')

if __name__ == "__main__":
    main()
