# MyCV-Platform Hybrid Detection API | 🖥️ GPU Server

RESTful API untuk deteksi objek dan segmentasi menggunakan YOLO + SAM2.

## 🚀 Quick Start

### 1. Jalankan API Server
```bash
cd /home/my/MySuperApps/MyCV-Platform/direct/app/api-hybrid-detection
./run_api.sh
```

### 2. Akses API
- **URL**: http://100.98.142.94:5000
- **Health Check**: http://100.98.142.94:5000/api/health

## 📋 API Endpoints

### Health & Status
- `GET /api/health` - Health check
- `GET /api/hardware` - Comprehensive hardware information
#### Response: `GET /api/hardware`
```json
{
  "status": "success",
  "service": "MyCV-GPU-Server",
  "hardware_info": {
    "system_info": {
      "architecture": "x86_64",
      "hostname": "cv-host",
      "kernel_version": "5.15.0-156-generic"
    },
    "gpu_info": {
      "status": "success",
      "cuda_available": true,
      "cudnn_enabled": true,
      "pytorch_cuda_version": "2.8.0+cu128",
      "available_gpus": 1,
      "gpus": [
        {
          "id": 0,
          "name": "NVIDIA GeForce RTX 3060",
          "memory_gb": 11.63
        }
      ],
      "total_memory_all_gpus_gb": 11.63
    },
    "memory_info": {
      "total_gb": 12.75
    },
    "disk_info": {
      "available": "74G",
      "size": "153G",
      "used": "72G",
      "use_percent": "50%"
    },
    "network_info": {
      "local_ip": "10.3.52.184",
      "public_ip": "202.152.145.34"
    },
    "updated_at": "2025-10-01T09:07:45.807590"
  }
}
```

#### Response: `GET /api/health`
```json
{
    "service": "MyCV-Platform Hybrid Detection API",
    "status": "healthy",
    "timestamp": "2025-09-28T13:52:36.623043",
    "uptime": 1759067556.6230543,
    "version": "1.0.0"
}
```

- `GET /api/status` - API status dan informasi lengkap dengan GPU details
#### Response: `GET /api/status`
```json
{
    "api_status": "online",
    "endpoints": [
        "/api/health",
        "/api/status",
        "/api/hardware",
        "/api/upload",
        "/api/process/<session_id>",
        "/api/results/<session_id>",
        "/api/download/<session_id>/<filename>",
        "/api/detections",
        "/api/backup/<session_id>"
    ],
    "gpu_info": {
        "available_gpus": 1,
        "cuda_available": true,
        "cudnn_enabled": true,
        "gpus": [
            {
                "id": 0,
                "name": "NVIDIA GeForce RTX 3060",
                "memory_gb": 11.63
            }
        ],
        "pytorch_cuda_version": "2.8.0+cu128",
        "status": "success",
        "total_memory_all_gpus_gb": 11.63
    },
    "service": "MyCV-Platform Hybrid Detection API",
    "timestamp": "2025-09-28T15:09:48.393352",
    "total_sessions_processed": 7,
    "version": "1.0.0"
}
```

### Upload & Processing
- `POST /api/upload` - Upload gambar untuk deteksi
#### Response: `POST /api/upload`
```json
{
    "message": "Files uploaded successfully. Processing started.",
    "results_url": "/api/results/session_6ad61814",
    "session_id": "session_6ad61814",
    "status_url": "/api/process/session_6ad61814",
    "success": true,
    "timestamp": "20251001_093224",
    "uploaded_files": [
        {
            "original_name": "77.milk.jpg",
            "path": "../../data/input/remote/20251001_093224/user-milk1/77.milk.jpg",
            "saved_name": "77.milk.jpg"
        }
    ],
    "user_id": "user-milk1"
}
```
- `GET /api/process/<session_id>` - Status pemrosesan
#### Response: `GET /api/process/session_9eebbca5`
```json
{
    "end_time": "2025-10-01T09:32:33.841991",
    "message": "Detection completed successfully",
    "start_time": "2025-10-01T09:32:24.497569",
    "status": "completed",
    "timestamp": "20251001_093224",
    "user_id": "user-milk1"
}
```
- `GET /api/results/<session_id>` - Hasil deteksi dari summary.json
#### Response: `GET /api/results/session_a88aa433`
```json
{
    "results": {
        "detection_summary": [
            {
                "id": 0,
                "name": "1-botol_mineral-best_pt-detection.json",
                "datas": [
                    {
                        "bbox": [
                            57.36895751953125,
                            62.235107421875,
                            344.09259033203125,
                            577.8096923828125
                        ],
                        "confidence": 0.8787883520126343,
                        "class_id": 2,
                        "class_name": "mineral"
                    }
                ],
                "detection_count": 1,
                "summary_images_url": "https://100.98.142.94:5000/api/download/20250929_023747/test_user_multi/1-botol_mineral-best_pt-compare.png",
                "images": {
                    "best": "https://100.98.142.94:5000/api/download/20250929_023747/test_user_multi/best/1-botol_mineral-best_pt-best.png",
                    "yolo": "https://100.98.142.94:5000/api/download/20250929_023747/test_user_multi/yolo/1-botol_mineral-yolo11m-detection.png",
                    "sam": "https://100.98.142.94:5000/api/download/20250929_023747/test_user_multi/segmentasi/1-botol_mineral-best_pt-segmentation.png",
                    "hybrid": "https://100.98.142.94:5000/api/download/20250929_023747/test_user_multi/hybrid/1-botol_mineral-best_pt-hybrid.png"
                }
            },
            {
                "id": 1,
                "name": "24_mineral-best_pt-detection.json",
                "datas": [
                    {
                        "bbox": [
                            103.0361328125,
                            117.67948150634766,
                            713.5864868164062,
                            283.5408630371094
                        ],
                        "confidence": 0.8873822689056396,
                        "class_id": 2,
                        "class_name": "mineral"
                    },
                    {
                        "bbox": [
                            549.1739501953125,
                            188.59642028808594,
                            646.7528686523438,
                            271.01678466796875
                        ],
                        "confidence": 0.6350509524345398,
                        "class_id": 4,
                        "class_name": "not_empty"
                    }
                ],
                "detection_count": 2,
                "summary_images_url": "https://100.98.142.94:5000/api/download/20250929_023747/test_user_multi/24_mineral-best_pt-compare.png",
                "images": {
                    "best": "https://100.98.142.94:5000/api/download/20250929_023747/test_user_multi/best/24_mineral-best_pt-best.png",
                    "yolo": "https://100.98.142.94:5000/api/download/20250929_023747/test_user_multi/yolo/24_mineral-yolo11m-detection.png",
                    "sam": "https://100.98.142.94:5000/api/download/20250929_023747/test_user_multi/segmentasi/24_mineral-best_pt-segmentation.png",
                    "hybrid": "https://100.98.142.94:5000/api/download/20250929_023747/test_user_multi/hybrid/24_mineral-best_pt-hybrid.png"
                }
            }
        ],
        "class_summary": [
            {
                "class_name": "mineral",
                "count": 3
            },
            {
                "class_name": "not_empty",
                "count": 1
            }
        ],
        "object_count": 3
    },
    "session_id": "session_a88aa433",
    "status": "completed",
    "timestamp": "20250929_023747",
    "user_id": "test_user_multi"
}
```

### Download, Backup & History
- `GET /api/download/<session_id>/<filename>` - Download file hasil
- `GET /api/backup/<session_id>` - Buat dan unduh TAR.GZ backup satu sesi
- `GET /api/detections` - Semua deteksi terbaru
#### Response: `GET /api/detections`
```json
{
    "recent_detections": [
        {
            "detection_count": 1,
            "detections": [
                {
                    "bbox": [
                        198.26275634765625,
                        101.52664947509766,
                        638.0875854492188,
                        364.34716796875
                    ],
                    "class_id": 1,
                    "class_name": "milk",
                    "confidence": 0.9603502154350281
                }
            ],
            "image_name": "77.milk",
            "timestamp": "20251001_093224",
            "user_id": "user-milk1"
        },
        {
            "detection_count": 3,
            "detections": {
                "class_summary": [
                    {
                        "class_name": "milk",
                        "count": 1
                    }
                ],
                "detection_summary": [
                    {
                        "datas": [
                            {
                                "bbox": [
                                    198.26275634765625,
                                    101.52664947509766,
                                    638.0875854492188,
                                    364.34716796875
                                ],
                                "class_id": 1,
                                "class_name": "milk",
                                "confidence": 0.9603502154350281
                            }
                        ],
                        "detection_count": 1,
                        "id": 0,
                        "images": {
                            "best": "http://100.98.142.94:5000/api/download/session_6ad61814/77.milk-best_pt-best.png",
                            "hybrid": "http://100.98.142.94:5000/api/download/session_6ad61814/77.milk-best_pt-hybrid.png",
                            "sam": "http://100.98.142.94:5000/api/download/session_6ad61814/77.milk-best_pt-segmentation.png",
                            "yolo": "http://100.98.142.94:5000/api/download/session_6ad61814/77.milk-yolo11m-detection.png"
                        },
                        "name": "77.milk-best_pt-detection.json",
                        "summary_images_url": "http://100.98.142.94:5000/api/download/session_6ad61814/77.milk-best_pt-compare.png"
                    }
                ],
                "object_count": 1
            },
            "image_name": "summary.json",
            "timestamp": "20251001_093224",
            "user_id": "user-milk1"
        },
        {
            "detection_count": 1,
            "detections": [
                {
                    "bbox": [
                        0.04709620401263237,
                        51.37726974487305,
                        166.78123474121094,
                        566.7282104492188
                    ],
                    "class_id": 2,
                    "class_name": "mineral",
                    "confidence": 0.26000073552131653
                }
            ],
            "image_name": "dogs",
            "timestamp": "20251001_091332",
            "user_id": "test_api"
        },
        {
            "detection_count": 3,
            "detections": {
                "class_summary": [
                    {
                        "class_name": "mineral",
                        "count": 1
                    }
                ],
                "detection_summary": [
                    {
                        "datas": [
                            {
                                "bbox": [
                                    0.04709620401263237,
                                    51.37726974487305,
                                    166.78123474121094,
                                    566.7282104492188
                                ],
                                "class_id": 2,
                                "class_name": "mineral",
                                "confidence": 0.26000073552131653
                            }
                        ],
                        "detection_count": 1,
                        "id": 0,
                        "images": {
                            "best": "http://100.98.142.94:5000/api/download/session_3de9cfac/dogs-best_pt-best.png",
                            "hybrid": "http://100.98.142.94:5000/api/download/session_3de9cfac/dogs-best_pt-hybrid.png",
                            "sam": "http://100.98.142.94:5000/api/download/session_3de9cfac/dogs-best_pt-segmentation.png",
                            "yolo": "http://100.98.142.94:5000/api/download/session_3de9cfac/dogs-yolo11m-detection.png"
                        },
                        "name": "dogs-best_pt-detection.json",
                        "summary_images_url": "http://100.98.142.94:5000/api/download/session_3de9cfac/dogs-best_pt-compare.png"
                    }
                ],
                "object_count": 1
            },
            "image_name": "summary.json",
            "timestamp": "20251001_091332",
            "user_id": "test_api"
        },
        {
            "detection_count": 1,
            "detections": [
                {
                    "bbox": [
                        298.2039794921875,
                        1.9976806640625,
                        509.9661865234375,
                        512.7738037109375
                    ],
                    "class_id": 2,
                    "class_name": "mineral",
                    "confidence": 0.8263616561889648
                }
            ],
            "image_name": "45",
            "timestamp": "20250929_172107",
            "user_id": "web_user"
        },
        {
            "detection_count": 1,
            "detections": [
                {
                    "bbox": [
                        144.16207885742188,
                        0.5894851684570312,
                        371.603759765625,
                        578.9254150390625
                    ],
                    "class_id": 2,
                    "class_name": "mineral",
                    "confidence": 0.8801478147506714
                }
            ],
            "image_name": "62",
            "timestamp": "20250929_172107",
            "user_id": "web_user"
        },
        {
            "detection_count": 1,
            "detections": [
                {
                    "bbox": [
                        38.06759262084961,
                        78.14295959472656,
                        186.31874084472656,
                        149.91268920898438
                    ],
                    "class_id": 3,
                    "class_name": "non_mineral",
                    "confidence": 0.8600907921791077
                }
            ],
            "image_name": "43",
            "timestamp": "20250929_172107",
            "user_id": "web_user"
        },
        {
            "detection_count": 1,
            "detections": [
                {
                    "bbox": [
                        30.171833038330078,
                        79.58673095703125,
                        184.52322387695312,
                        149.0042266845703
                    ],
                    "class_id": 3,
                    "class_name": "non_mineral",
                    "confidence": 0.8676203489303589
                }
            ],
            "image_name": "47",
            "timestamp": "20250929_172107",
            "user_id": "web_user"
        },
        {
            "detection_count": 1,
            "detections": [
                {
                    "bbox": [
                        12.346000671386719,
                        73.65291595458984,
                        180.5181884765625,
                        132.32968139648438
                    ],
                    "class_id": 2,
                    "class_name": "mineral",
                    "confidence": 0.8531134128570557
                }
            ],
            "image_name": "68",
            "timestamp": "20250929_172107",
            "user_id": "web_user"
        },
        {
            "detection_count": 1,
            "detections": [
                {
                    "bbox": [
                        27.81463623046875,
                        83.37459564208984,
                        180.17550659179688,
                        142.06809997558594
                    ],
                    "class_id": 3,
                    "class_name": "non_mineral",
                    "confidence": 0.8540395498275757
                }
            ],
            "image_name": "41",
            "timestamp": "20250929_172107",
            "user_id": "web_user"
        },
        {
            "detection_count": 3,
            "detections": [
                {
                    "bbox": [
                        311.40557861328125,
                        14.549903869628906,
                        525.6377563476562,
                        600.0
                    ],
                    "class_id": 2,
                    "class_name": "mineral",
                    "confidence": 0.9183132648468018
                },
                {
                    "bbox": [
                        363.94683837890625,
                        233.01409912109375,
                        474.237060546875,
                        598.4949951171875
                    ],
                    "class_id": 4,
                    "class_name": "not_empty",
                    "confidence": 0.5841004252433777
                },
                {
                    "bbox": [
                        331.4144287109375,
                        0.0,
                        464.59991455078125,
                        517.9365234375
                    ],
                    "class_id": 4,
                    "class_name": "not_empty",
                    "confidence": 0.3303992748260498
                }
            ],
            "image_name": "56",
            "timestamp": "20250929_172107",
            "user_id": "web_user"
        },
        {
            "detection_count": 1,
            "detections": [
                {
                    "bbox": [
                        14.848899841308594,
                        72.94762420654297,
                        178.99942016601562,
                        138.27264404296875
                    ],
                    "class_id": 2,
                    "class_name": "mineral",
                    "confidence": 0.8416655659675598
                }
            ],
            "image_name": "48",
            "timestamp": "20250929_172107",
            "user_id": "web_user"
        },
        {
            "detection_count": 3,
            "detections": {
                "class_summary": [
                    {
                        "class_name": "mineral",
                        "count": 10
                    },
                    {
                        "class_name": "not_empty",
                        "count": 5
                    },
                    {
                        "class_name": "non_mineral",
                        "count": 4
                    }
                ],
                "detection_summary": [
                    {
                        "datas": [
                            {
                                "bbox": [
                                    298.2039794921875,
                                    1.9976806640625,
                                    509.9661865234375,
                                    512.7738037109375
                                ],
                                "class_id": 2,
                                "class_name": "mineral",
                                "confidence": 0.8263616561889648
                            }
                        ],
                        "detection_count": 1,
                        "id": 0,
                        "images": {
                            "best": "http://100.98.142.94:5000/api/download/session_a72818e4/45-best_pt-best.png",
                            "hybrid": "http://100.98.142.94:5000/api/download/session_a72818e4/45-best_pt-hybrid.png",
                            "sam": "http://100.98.142.94:5000/api/download/session_a72818e4/45-best_pt-segmentation.png",
                            "yolo": "http://100.98.142.94:5000/api/download/session_a72818e4/45-yolo11m-detection.png"
                        },
                        "name": "45-best_pt-detection.json",
                        "summary_images_url": "http://100.98.142.94:5000/api/download/session_a72818e4/45-best_pt-compare.png"
                    },
                    {
                        "datas": [
                            {
                                "bbox": [
                                    144.16207885742188,
                                    0.5894851684570312,
                                    371.603759765625,
                                    578.9254150390625
                                ],
                                "class_id": 2,
                                "class_name": "mineral",
                                "confidence": 0.8801478147506714
                            }
                        ],
                        "detection_count": 1,
                        "id": 1,
                        "images": {
                            "best": "http://100.98.142.94:5000/api/download/session_a72818e4/62-best_pt-best.png",
                            "hybrid": "http://100.98.142.94:5000/api/download/session_a72818e4/62-best_pt-hybrid.png",
                            "sam": "http://100.98.142.94:5000/api/download/session_a72818e4/62-best_pt-segmentation.png",
                            "yolo": "http://100.98.142.94:5000/api/download/session_a72818e4/62-yolo11m-detection.png"
                        },
                        "name": "62-best_pt-detection.json",
                        "summary_images_url": "http://100.98.142.94:5000/api/download/session_a72818e4/62-best_pt-compare.png"
                    },
                    {
                        "datas": [
                            {
                                "bbox": [
                                    38.06759262084961,
                                    78.14295959472656,
                                    186.31874084472656,
                                    149.91268920898438
                                ],
                                "class_id": 3,
                                "class_name": "non_mineral",
                                "confidence": 0.8600907921791077
                            }
                        ],
                        "detection_count": 1,
                        "id": 2,
                        "images": {
                            "best": "http://100.98.142.94:5000/api/download/session_a72818e4/43-best_pt-best.png",
                            "hybrid": "http://100.98.142.94:5000/api/download/session_a72818e4/43-best_pt-hybrid.png",
                            "sam": "http://100.98.142.94:5000/api/download/session_a72818e4/43-best_pt-segmentation.png",
                            "yolo": "http://100.98.142.94:5000/api/download/session_a72818e4/43-yolo11m-detection.png"
                        },
                        "name": "43-best_pt-detection.json",
                        "summary_images_url": "http://100.98.142.94:5000/api/download/session_a72818e4/43-best_pt-compare.png"
                    },
                    {
                        "datas": [
                            {
                                "bbox": [
                                    30.171833038330078,
                                    79.58673095703125,
                                    184.52322387695312,
                                    149.0042266845703
                                ],
                                "class_id": 3,
                                "class_name": "non_mineral",
                                "confidence": 0.8676203489303589
                            }
                        ],
                        "detection_count": 1,
                        "id": 3,
                        "images": {
                            "best": "http://100.98.142.94:5000/api/download/session_a72818e4/47-best_pt-best.png",
                            "hybrid": "http://100.98.142.94:5000/api/download/session_a72818e4/47-best_pt-hybrid.png",
                            "sam": "http://100.98.142.94:5000/api/download/session_a72818e4/47-best_pt-segmentation.png",
                            "yolo": "http://100.98.142.94:5000/api/download/session_a72818e4/47-yolo11m-detection.png"
                        },
                        "name": "47-best_pt-detection.json",
                        "summary_images_url": "http://100.98.142.94:5000/api/download/session_a72818e4/47-best_pt-compare.png"
                    },
                    {
                        "datas": [
                            {
                                "bbox": [
                                    12.346000671386719,
                                    73.65291595458984,
                                    180.5181884765625,
                                    132.32968139648438
                                ],
                                "class_id": 2,
                                "class_name": "mineral",
                                "confidence": 0.8531134128570557
                            }
                        ],
                        "detection_count": 1,
                        "id": 4,
                        "images": {
                            "best": "http://100.98.142.94:5000/api/download/session_a72818e4/68-best_pt-best.png",
                            "hybrid": "http://100.98.142.94:5000/api/download/session_a72818e4/68-best_pt-hybrid.png",
                            "sam": "http://100.98.142.94:5000/api/download/session_a72818e4/68-best_pt-segmentation.png",
                            "yolo": "http://100.98.142.94:5000/api/download/session_a72818e4/68-yolo11m-detection.png"
                        },
                        "name": "68-best_pt-detection.json",
                        "summary_images_url": "http://100.98.142.94:5000/api/download/session_a72818e4/68-best_pt-compare.png"
                    },
                    {
                        "datas": [
                            {
                                "bbox": [
                                    27.81463623046875,
                                    83.37459564208984,
                                    180.17550659179688,
                                    142.06809997558594
                                ],
                                "class_id": 3,
                                "class_name": "non_mineral",
                                "confidence": 0.8540395498275757
                            }
                        ],
                        "detection_count": 1,
                        "id": 5,
                        "images": {
                            "best": "http://100.98.142.94:5000/api/download/session_a72818e4/41-best_pt-best.png",
                            "hybrid": "http://100.98.142.94:5000/api/download/session_a72818e4/41-best_pt-hybrid.png",
                            "sam": "http://100.98.142.94:5000/api/download/session_a72818e4/41-best_pt-segmentation.png",
                            "yolo": "http://100.98.142.94:5000/api/download/session_a72818e4/41-yolo11m-detection.png"
                        },
                        "name": "41-best_pt-detection.json",
                        "summary_images_url": "http://100.98.142.94:5000/api/download/session_a72818e4/41-best_pt-compare.png"
                    },
                    {
                        "datas": [
                            {
                                "bbox": [
                                    311.40557861328125,
                                    14.549903869628906,
                                    525.6377563476562,
                                    600.0
                                ],
                                "class_id": 2,
                                "class_name": "mineral",
                                "confidence": 0.9183132648468018
                            },
                            {
                                "bbox": [
                                    363.94683837890625,
                                    233.01409912109375,
                                    474.237060546875,
                                    598.4949951171875
                                ],
                                "class_id": 4,
                                "class_name": "not_empty",
                                "confidence": 0.5841004252433777
                            },
                            {
                                "bbox": [
                                    331.4144287109375,
                                    0.0,
                                    464.59991455078125,
                                    517.9365234375
                                ],
                                "class_id": 4,
                                "class_name": "not_empty",
                                "confidence": 0.3303992748260498
                            }
                        ],
                        "detection_count": 3,
                        "id": 6,
                        "images": {
                            "best": "http://100.98.142.94:5000/api/download/session_a72818e4/56-best_pt-best.png",
                            "hybrid": "http://100.98.142.94:5000/api/download/session_a72818e4/56-best_pt-hybrid.png",
                            "sam": "http://100.98.142.94:5000/api/download/session_a72818e4/56-best_pt-segmentation.png",
                            "yolo": "http://100.98.142.94:5000/api/download/session_a72818e4/56-yolo11m-detection.png"
                        },
                        "name": "56-best_pt-detection.json",
                        "summary_images_url": "http://100.98.142.94:5000/api/download/session_a72818e4/56-best_pt-compare.png"
                    },
                    {
                        "datas": [
                            {
                                "bbox": [
                                    14.848899841308594,
                                    72.94762420654297,
                                    178.99942016601562,
                                    138.27264404296875
                                ],
                                "class_id": 2,
                                "class_name": "mineral",
                                "confidence": 0.8416655659675598
                            }
                        ],
                        "detection_count": 1,
                        "id": 7,
                        "images": {
                            "best": "http://100.98.142.94:5000/api/download/session_a72818e4/48-best_pt-best.png",
                            "hybrid": "http://100.98.142.94:5000/api/download/session_a72818e4/48-best_pt-hybrid.png",
                            "sam": "http://100.98.142.94:5000/api/download/session_a72818e4/48-best_pt-segmentation.png",
                            "yolo": "http://100.98.142.94:5000/api/download/session_a72818e4/48-yolo11m-detection.png"
                        },
                        "name": "48-best_pt-detection.json",
                        "summary_images_url": "http://100.98.142.94:5000/api/download/session_a72818e4/48-best_pt-compare.png"
                    },
                    {
                        "datas": [
                            {
                                "bbox": [
                                    21.98314666748047,
                                    72.17144775390625,
                                    182.4072265625,
                                    140.4998321533203
                                ],
                                "class_id": 2,
                                "class_name": "mineral",
                                "confidence": 0.8402790427207947
                            }
                        ],
                        "detection_count": 1,
                        "id": 8,
                        "images": {
                            "best": "http://100.98.142.94:5000/api/download/session_a72818e4/67-best_pt-best.png",
                            "hybrid": "http://100.98.142.94:5000/api/download/session_a72818e4/67-best_pt-hybrid.png",
                            "sam": "http://100.98.142.94:5000/api/download/session_a72818e4/67-best_pt-segmentation.png",
                            "yolo": "http://100.98.142.94:5000/api/download/session_a72818e4/67-yolo11m-detection.png"
                        },
                        "name": "67-best_pt-detection.json",
                        "summary_images_url": "http://100.98.142.94:5000/api/download/session_a72818e4/67-best_pt-compare.png"
                    },
                    {
                        "datas": [
                            {
                                "bbox": [
                                    23.66887664794922,
                                    69.21644592285156,
                                    182.6279296875,
                                    149.929931640625
                                ],
                                "class_id": 3,
                                "class_name": "non_mineral",
                                "confidence": 0.8623256087303162
                            }
                        ],
                        "detection_count": 1,
                        "id": 9,
                        "images": {
                            "best": "http://100.98.142.94:5000/api/download/session_a72818e4/66-best_pt-best.png",
                            "hybrid": "http://100.98.142.94:5000/api/download/session_a72818e4/66-best_pt-hybrid.png",
                            "sam": "http://100.98.142.94:5000/api/download/session_a72818e4/66-best_pt-segmentation.png",
                            "yolo": "http://100.98.142.94:5000/api/download/session_a72818e4/66-yolo11m-detection.png"
                        },
                        "name": "66-best_pt-detection.json",
                        "summary_images_url": "http://100.98.142.94:5000/api/download/session_a72818e4/66-best_pt-compare.png"
                    },
                    {
                        "datas": [
                            {
                                "bbox": [
                                    73.11982727050781,
                                    142.9651336669922,
                                    575.3226928710938,
                                    628.5154418945312
                                ],
                                "class_id": 2,
                                "class_name": "mineral",
                                "confidence": 0.9209938049316406
                            }
                        ],
                        "detection_count": 1,
                        "id": 10,
                        "images": {
                            "best": "http://100.98.142.94:5000/api/download/session_a72818e4/46-best_pt-best.png",
                            "hybrid": "http://100.98.142.94:5000/api/download/session_a72818e4/46-best_pt-hybrid.png",
                            "sam": "http://100.98.142.94:5000/api/download/session_a72818e4/46-best_pt-segmentation.png",
                            "yolo": "http://100.98.142.94:5000/api/download/session_a72818e4/46-yolo11m-detection.png"
                        },
                        "name": "46-best_pt-detection.json",
                        "summary_images_url": "http://100.98.142.94:5000/api/download/session_a72818e4/46-best_pt-compare.png"
                    },
                    {
                        "datas": [
                            {
                                "bbox": [
                                    277.94927978515625,
                                    0.0,
                                    504.4984436035156,
                                    599.8125
                                ],
                                "class_id": 2,
                                "class_name": "mineral",
                                "confidence": 0.8820997476577759
                            },
                            {
                                "bbox": [
                                    354.6028137207031,
                                    477.6714782714844,
                                    426.41632080078125,
                                    508.4154968261719
                                ],
                                "class_id": 4,
                                "class_name": "not_empty",
                                "confidence": 0.5251861810684204
                            },
                            {
                                "bbox": [
                                    345.4852294921875,
                                    3.9904212951660156,
                                    418.4837341308594,
                                    302.1035461425781
                                ],
                                "class_id": 4,
                                "class_name": "not_empty",
                                "confidence": 0.3441903591156006
                            },
                            {
                                "bbox": [
                                    328.01861572265625,
                                    72.40955352783203,
                                    438.3974914550781,
                                    534.0603637695312
                                ],
                                "class_id": 4,
                                "class_name": "not_empty",
                                "confidence": 0.3214245140552521
                            }
                        ],
                        "detection_count": 4,
                        "id": 11,
                        "images": {
                            "best": "http://100.98.142.94:5000/api/download/session_a72818e4/65-best_pt-best.png",
                            "hybrid": "http://100.98.142.94:5000/api/download/session_a72818e4/65-best_pt-hybrid.png",
                            "sam": "http://100.98.142.94:5000/api/download/session_a72818e4/65-best_pt-segmentation.png",
                            "yolo": "http://100.98.142.94:5000/api/download/session_a72818e4/65-yolo11m-detection.png"
                        },
                        "name": "65-best_pt-detection.json",
                        "summary_images_url": "http://100.98.142.94:5000/api/download/session_a72818e4/65-best_pt-compare.png"
                    },
                    {
                        "datas": [
                            {
                                "bbox": [
                                    22.055301666259766,
                                    78.5847396850586,
                                    184.1155242919922,
                                    147.85702514648438
                                ],
                                "class_id": 2,
                                "class_name": "mineral",
                                "confidence": 0.8496163487434387
                            }
                        ],
                        "detection_count": 1,
                        "id": 12,
                        "images": {
                            "best": "http://100.98.142.94:5000/api/download/session_a72818e4/44-best_pt-best.png",
                            "hybrid": "http://100.98.142.94:5000/api/download/session_a72818e4/44-best_pt-hybrid.png",
                            "sam": "http://100.98.142.94:5000/api/download/session_a72818e4/44-best_pt-segmentation.png",
                            "yolo": "http://100.98.142.94:5000/api/download/session_a72818e4/44-yolo11m-detection.png"
                        },
                        "name": "44-best_pt-detection.json",
                        "summary_images_url": "http://100.98.142.94:5000/api/download/session_a72818e4/44-best_pt-compare.png"
                    },
                    {
                        "datas": [
                            {
                                "bbox": [
                                    65.28730773925781,
                                    202.1991424560547,
                                    583.2535400390625,
                                    636.6233520507812
                                ],
                                "class_id": 2,
                                "class_name": "mineral",
                                "confidence": 0.9288041591644287
                            }
                        ],
                        "detection_count": 1,
                        "id": 13,
                        "images": {
                            "best": "http://100.98.142.94:5000/api/download/session_a72818e4/42-best_pt-best.png",
                            "hybrid": "http://100.98.142.94:5000/api/download/session_a72818e4/42-best_pt-hybrid.png",
                            "sam": "http://100.98.142.94:5000/api/download/session_a72818e4/42-best_pt-segmentation.png",
                            "yolo": "http://100.98.142.94:5000/api/download/session_a72818e4/42-yolo11m-detection.png"
                        },
                        "name": "42-best_pt-detection.json",
                        "summary_images_url": "http://100.98.142.94:5000/api/download/session_a72818e4/42-best_pt-compare.png"
                    }
                ],
                "object_count": 14
            },
            "image_name": "summary.json",
            "timestamp": "20250929_172107",
            "user_id": "web_user"
        },
        {
            "detection_count": 1,
            "detections": [
                {
                    "bbox": [
                        21.98314666748047,
                        72.17144775390625,
                        182.4072265625,
                        140.4998321533203
                    ],
                    "class_id": 2,
                    "class_name": "mineral",
                    "confidence": 0.8402790427207947
                }
            ],
            "image_name": "67",
            "timestamp": "20250929_172107",
            "user_id": "web_user"
        },
        {
            "detection_count": 1,
            "detections": [
                {
                    "bbox": [
                        23.66887664794922,
                        69.21644592285156,
                        182.6279296875,
                        149.929931640625
                    ],
                    "class_id": 3,
                    "class_name": "non_mineral",
                    "confidence": 0.8623256087303162
                }
            ],
            "image_name": "66",
            "timestamp": "20250929_172107",
            "user_id": "web_user"
        },
        {
            "detection_count": 1,
            "detections": [
                {
                    "bbox": [
                        73.11982727050781,
                        142.9651336669922,
                        575.3226928710938,
                        628.5154418945312
                    ],
                    "class_id": 2,
                    "class_name": "mineral",
                    "confidence": 0.9209938049316406
                }
            ],
            "image_name": "46",
            "timestamp": "20250929_172107",
            "user_id": "web_user"
        },
        {
            "detection_count": 4,
            "detections": [
                {
                    "bbox": [
                        277.94927978515625,
                        0.0,
                        504.4984436035156,
                        599.8125
                    ],
                    "class_id": 2,
                    "class_name": "mineral",
                    "confidence": 0.8820997476577759
                },
                {
                    "bbox": [
                        354.6028137207031,
                        477.6714782714844,
                        426.41632080078125,
                        508.4154968261719
                    ],
                    "class_id": 4,
                    "class_name": "not_empty",
                    "confidence": 0.5251861810684204
                },
                {
                    "bbox": [
                        345.4852294921875,
                        3.9904212951660156,
                        418.4837341308594,
                        302.1035461425781
                    ],
                    "class_id": 4,
                    "class_name": "not_empty",
                    "confidence": 0.3441903591156006
                },
                {
                    "bbox": [
                        328.01861572265625,
                        72.40955352783203,
                        438.3974914550781,
                        534.0603637695312
                    ],
                    "class_id": 4,
                    "class_name": "not_empty",
                    "confidence": 0.3214245140552521
                }
            ],
            "image_name": "65",
            "timestamp": "20250929_172107",
            "user_id": "web_user"
        },
        {
            "detection_count": 1,
            "detections": [
                {
                    "bbox": [
                        22.055301666259766,
                        78.5847396850586,
                        184.1155242919922,
                        147.85702514648438
                    ],
                    "class_id": 2,
                    "class_name": "mineral",
                    "confidence": 0.8496163487434387
                }
            ],
            "image_name": "44",
            "timestamp": "20250929_172107",
            "user_id": "web_user"
        },
        {
            "detection_count": 1,
            "detections": [
                {
                    "bbox": [
                        65.28730773925781,
                        202.1991424560547,
                        583.2535400390625,
                        636.6233520507812
                    ],
                    "class_id": 2,
                    "class_name": "mineral",
                    "confidence": 0.9288041591644287
                }
            ],
            "image_name": "42",
            "timestamp": "20250929_172107",
            "user_id": "web_user"
        },
        {
            "detection_count": 1,
            "detections": [
                {
                    "bbox": [
                        19.61334228515625,
                        95.25968933105469,
                        180.70230102539062,
                        149.99090576171875
                    ],
                    "class_id": 3,
                    "class_name": "non_mineral",
                    "confidence": 0.865796685218811
                }
            ],
            "image_name": "2",
            "timestamp": "20250929_171237",
            "user_id": "web_user"
        },
        {
            "detection_count": 1,
            "detections": [
                {
                    "bbox": [
                        23.886775970458984,
                        74.8812484741211,
                        184.21766662597656,
                        146.26339721679688
                    ],
                    "class_id": 3,
                    "class_name": "non_mineral",
                    "confidence": 0.8686655163764954
                }
            ],
            "image_name": "5",
            "timestamp": "20250929_171237",
            "user_id": "web_user"
        },
        {
            "detection_count": 1,
            "detections": [
                {
                    "bbox": [
                        71.99132537841797,
                        214.1638641357422,
                        372.3984375,
                        493.9196472167969
                    ],
                    "class_id": 2,
                    "class_name": "mineral",
                    "confidence": 0.8471338152885437
                }
            ],
            "image_name": "6",
            "timestamp": "20250929_171237",
            "user_id": "web_user"
        },
        {
            "detection_count": 1,
            "detections": [
                {
                    "bbox": [
                        318.94964599609375,
                        0.0,
                        564.5763549804688,
                        600.0
                    ],
                    "class_id": 2,
                    "class_name": "mineral",
                    "confidence": 0.8856334090232849
                }
            ],
            "image_name": "4",
            "timestamp": "20250929_171237",
            "user_id": "web_user"
        },
        {
            "detection_count": 1,
            "detections": [
                {
                    "bbox": [
                        86.64531707763672,
                        176.4845733642578,
                        374.3604431152344,
                        629.4055786132812
                    ],
                    "class_id": 2,
                    "class_name": "mineral",
                    "confidence": 0.8809738159179688
                }
            ],
            "image_name": "3",
            "timestamp": "20250929_171237",
            "user_id": "web_user"
        },
        {
            "detection_count": 1,
            "detections": [
                {
                    "bbox": [
                        276.8039245605469,
                        3.0908203125,
                        535.4398803710938,
                        591.9407958984375
                    ],
                    "class_id": 2,
                    "class_name": "mineral",
                    "confidence": 0.8865288496017456
                }
            ],
            "image_name": "1",
            "timestamp": "20250929_171237",
            "user_id": "web_user"
        },
        {
            "detection_count": 3,
            "detections": {
                "class_summary": [
                    {
                        "class_name": "mineral",
                        "count": 4
                    },
                    {
                        "class_name": "non_mineral",
                        "count": 2
                    }
                ],
                "detection_summary": [
                    {
                        "datas": [
                            {
                                "bbox": [
                                    19.61334228515625,
                                    95.25968933105469,
                                    180.70230102539062,
                                    149.99090576171875
                                ],
                                "class_id": 3,
                                "class_name": "non_mineral",
                                "confidence": 0.865796685218811
                            }
                        ],
                        "detection_count": 1,
                        "id": 0,
                        "images": {
                            "best": "http://100.98.142.94:5000/api/download/session_a326b858/2-best_pt-best.png",
                            "hybrid": "http://100.98.142.94:5000/api/download/session_a326b858/2-best_pt-hybrid.png",
                            "sam": "http://100.98.142.94:5000/api/download/session_a326b858/2-best_pt-segmentation.png",
                            "yolo": "http://100.98.142.94:5000/api/download/session_a326b858/2-yolo11m-detection.png"
                        },
                        "name": "2-best_pt-detection.json",
                        "summary_images_url": "http://100.98.142.94:5000/api/download/session_a326b858/2-best_pt-compare.png"
                    },
                    {
                        "datas": [
                            {
                                "bbox": [
                                    23.886775970458984,
                                    74.8812484741211,
                                    184.21766662597656,
                                    146.26339721679688
                                ],
                                "class_id": 3,
                                "class_name": "non_mineral",
                                "confidence": 0.8686655163764954
                            }
                        ],
                        "detection_count": 1,
                        "id": 1,
                        "images": {
                            "best": "http://100.98.142.94:5000/api/download/session_a326b858/5-best_pt-best.png",
                            "hybrid": "http://100.98.142.94:5000/api/download/session_a326b858/5-best_pt-hybrid.png",
                            "sam": "http://100.98.142.94:5000/api/download/session_a326b858/5-best_pt-segmentation.png",
                            "yolo": "http://100.98.142.94:5000/api/download/session_a326b858/5-yolo11m-detection.png"
                        },
                        "name": "5-best_pt-detection.json",
                        "summary_images_url": "http://100.98.142.94:5000/api/download/session_a326b858/5-best_pt-compare.png"
                    },
                    {
                        "datas": [
                            {
                                "bbox": [
                                    71.99132537841797,
                                    214.1638641357422,
                                    372.3984375,
                                    493.9196472167969
                                ],
                                "class_id": 2,
                                "class_name": "mineral",
                                "confidence": 0.8471338152885437
                            }
                        ],
                        "detection_count": 1,
                        "id": 2,
                        "images": {
                            "best": "http://100.98.142.94:5000/api/download/session_a326b858/6-best_pt-best.png",
                            "hybrid": "http://100.98.142.94:5000/api/download/session_a326b858/6-best_pt-hybrid.png",
                            "sam": "http://100.98.142.94:5000/api/download/session_a326b858/6-best_pt-segmentation.png",
                            "yolo": "http://100.98.142.94:5000/api/download/session_a326b858/6-yolo11m-detection.png"
                        },
                        "name": "6-best_pt-detection.json",
                        "summary_images_url": "http://100.98.142.94:5000/api/download/session_a326b858/6-best_pt-compare.png"
                    },
                    {
                        "datas": [
                            {
                                "bbox": [
                                    318.94964599609375,
                                    0.0,
                                    564.5763549804688,
                                    600.0
                                ],
                                "class_id": 2,
                                "class_name": "mineral",
                                "confidence": 0.8856334090232849
                            }
                        ],
                        "detection_count": 1,
                        "id": 3,
                        "images": {
                            "best": "http://100.98.142.94:5000/api/download/session_a326b858/4-best_pt-best.png",
                            "hybrid": "http://100.98.142.94:5000/api/download/session_a326b858/4-best_pt-hybrid.png",
                            "sam": "http://100.98.142.94:5000/api/download/session_a326b858/4-best_pt-segmentation.png",
                            "yolo": "http://100.98.142.94:5000/api/download/session_a326b858/4-yolo11m-detection.png"
                        },
                        "name": "4-best_pt-detection.json",
                        "summary_images_url": "http://100.98.142.94:5000/api/download/session_a326b858/4-best_pt-compare.png"
                    },
                    {
                        "datas": [
                            {
                                "bbox": [
                                    86.64531707763672,
                                    176.4845733642578,
                                    374.3604431152344,
                                    629.4055786132812
                                ],
                                "class_id": 2,
                                "class_name": "mineral",
                                "confidence": 0.8809738159179688
                            }
                        ],
                        "detection_count": 1,
                        "id": 4,
                        "images": {
                            "best": "http://100.98.142.94:5000/api/download/session_a326b858/3-best_pt-best.png",
                            "hybrid": "http://100.98.142.94:5000/api/download/session_a326b858/3-best_pt-hybrid.png",
                            "sam": "http://100.98.142.94:5000/api/download/session_a326b858/3-best_pt-segmentation.png",
                            "yolo": "http://100.98.142.94:5000/api/download/session_a326b858/3-yolo11m-detection.png"
                        },
                        "name": "3-best_pt-detection.json",
                        "summary_images_url": "http://100.98.142.94:5000/api/download/session_a326b858/3-best_pt-compare.png"
                    },
                    {
                        "datas": [
                            {
                                "bbox": [
                                    276.8039245605469,
                                    3.0908203125,
                                    535.4398803710938,
                                    591.9407958984375
                                ],
                                "class_id": 2,
                                "class_name": "mineral",
                                "confidence": 0.8865288496017456
                            }
                        ],
                        "detection_count": 1,
                        "id": 5,
                        "images": {
                            "best": "http://100.98.142.94:5000/api/download/session_a326b858/1-best_pt-best.png",
                            "hybrid": "http://100.98.142.94:5000/api/download/session_a326b858/1-best_pt-hybrid.png",
                            "sam": "http://100.98.142.94:5000/api/download/session_a326b858/1-best_pt-segmentation.png",
                            "yolo": "http://100.98.142.94:5000/api/download/session_a326b858/1-yolo11m-detection.png"
                        },
                        "name": "1-best_pt-detection.json",
                        "summary_images_url": "http://100.98.142.94:5000/api/download/session_a326b858/1-best_pt-compare.png"
                    }
                ],
                "object_count": 6
            },
            "image_name": "summary.json",
            "timestamp": "20250929_171237",
            "user_id": "web_user"
        },
        {
            "detection_count": 1,
            "detections": [
                {
                    "bbox": [
                        19.61334228515625,
                        95.25968933105469,
                        180.70230102539062,
                        149.99090576171875
                    ],
                    "class_id": 3,
                    "class_name": "non_mineral",
                    "confidence": 0.865796685218811
                }
            ],
            "image_name": "2",
            "timestamp": "20250929_170306",
            "user_id": "web_user"
        },
        {
            "detection_count": 1,
            "detections": [
                {
                    "bbox": [
                        23.886775970458984,
                        74.8812484741211,
                        184.21766662597656,
                        146.26339721679688
                    ],
                    "class_id": 3,
                    "class_name": "non_mineral",
                    "confidence": 0.8686655163764954
                }
            ],
            "image_name": "5",
            "timestamp": "20250929_170306",
            "user_id": "web_user"
        },
        {
            "detection_count": 1,
            "detections": [
                {
                    "bbox": [
                        71.99132537841797,
                        214.1638641357422,
                        372.3984375,
                        493.9196472167969
                    ],
                    "class_id": 2,
                    "class_name": "mineral",
                    "confidence": 0.8471338152885437
                }
            ],
            "image_name": "6",
            "timestamp": "20250929_170306",
            "user_id": "web_user"
        },
        {
            "detection_count": 1,
            "detections": [
                {
                    "bbox": [
                        318.94964599609375,
                        0.0,
                        564.5763549804688,
                        600.0
                    ],
                    "class_id": 2,
                    "class_name": "mineral",
                    "confidence": 0.8856334090232849
                }
            ],
            "image_name": "4",
            "timestamp": "20250929_170306",
            "user_id": "web_user"
        },
        {
            "detection_count": 1,
            "detections": [
                {
                    "bbox": [
                        86.64531707763672,
                        176.4845733642578,
                        374.3604431152344,
                        629.4055786132812
                    ],
                    "class_id": 2,
                    "class_name": "mineral",
                    "confidence": 0.8809738159179688
                }
            ],
            "image_name": "3",
            "timestamp": "20250929_170306",
            "user_id": "web_user"
        },
        {
            "detection_count": 1,
            "detections": [
                {
                    "bbox": [
                        276.8039245605469,
                        3.0908203125,
                        535.4398803710938,
                        591.9407958984375
                    ],
                    "class_id": 2,
                    "class_name": "mineral",
                    "confidence": 0.8865288496017456
                }
            ],
            "image_name": "1",
            "timestamp": "20250929_170306",
            "user_id": "web_user"
        },
        {
            "detection_count": 3,
            "detections": {
                "class_summary": [
                    {
                        "class_name": "mineral",
                        "count": 4
                    },
                    {
                        "class_name": "non_mineral",
                        "count": 2
                    }
                ],
                "detection_summary": [
                    {
                        "datas": [
                            {
                                "bbox": [
                                    19.61334228515625,
                                    95.25968933105469,
                                    180.70230102539062,
                                    149.99090576171875
                                ],
                                "class_id": 3,
                                "class_name": "non_mineral",
                                "confidence": 0.865796685218811
                            }
                        ],
                        "detection_count": 1,
                        "id": 0,
                        "images": {
                            "best": "http://100.98.142.94:5000/api/download/session_ee379942/2-best_pt-best.png",
                            "hybrid": "http://100.98.142.94:5000/api/download/session_ee379942/2-best_pt-hybrid.png",
                            "sam": "http://100.98.142.94:5000/api/download/session_ee379942/2-best_pt-segmentation.png",
                            "yolo": "http://100.98.142.94:5000/api/download/session_ee379942/2-yolo11m-detection.png"
                        },
                        "name": "2-best_pt-detection.json",
                        "summary_images_url": "http://100.98.142.94:5000/api/download/session_ee379942/2-best_pt-compare.png"
                    },
                    {
                        "datas": [
                            {
                                "bbox": [
                                    23.886775970458984,
                                    74.8812484741211,
                                    184.21766662597656,
                                    146.26339721679688
                                ],
                                "class_id": 3,
                                "class_name": "non_mineral",
                                "confidence": 0.8686655163764954
                            }
                        ],
                        "detection_count": 1,
                        "id": 1,
                        "images": {
                            "best": "http://100.98.142.94:5000/api/download/session_ee379942/5-best_pt-best.png",
                            "hybrid": "http://100.98.142.94:5000/api/download/session_ee379942/5-best_pt-hybrid.png",
                            "sam": "http://100.98.142.94:5000/api/download/session_ee379942/5-best_pt-segmentation.png",
                            "yolo": "http://100.98.142.94:5000/api/download/session_ee379942/5-yolo11m-detection.png"
                        },
                        "name": "5-best_pt-detection.json",
                        "summary_images_url": "http://100.98.142.94:5000/api/download/session_ee379942/5-best_pt-compare.png"
                    },
                    {
                        "datas": [
                            {
                                "bbox": [
                                    71.99132537841797,
                                    214.1638641357422,
                                    372.3984375,
                                    493.9196472167969
                                ],
                                "class_id": 2,
                                "class_name": "mineral",
                                "confidence": 0.8471338152885437
                            }
                        ],
                        "detection_count": 1,
                        "id": 2,
                        "images": {
                            "best": "http://100.98.142.94:5000/api/download/session_ee379942/6-best_pt-best.png",
                            "hybrid": "http://100.98.142.94:5000/api/download/session_ee379942/6-best_pt-hybrid.png",
                            "sam": "http://100.98.142.94:5000/api/download/session_ee379942/6-best_pt-segmentation.png",
                            "yolo": "http://100.98.142.94:5000/api/download/session_ee379942/6-yolo11m-detection.png"
                        },
                        "name": "6-best_pt-detection.json",
                        "summary_images_url": "http://100.98.142.94:5000/api/download/session_ee379942/6-best_pt-compare.png"
                    },
                    {
                        "datas": [
                            {
                                "bbox": [
                                    318.94964599609375,
                                    0.0,
                                    564.5763549804688,
                                    600.0
                                ],
                                "class_id": 2,
                                "class_name": "mineral",
                                "confidence": 0.8856334090232849
                            }
                        ],
                        "detection_count": 1,
                        "id": 3,
                        "images": {
                            "best": "http://100.98.142.94:5000/api/download/session_ee379942/4-best_pt-best.png",
                            "hybrid": "http://100.98.142.94:5000/api/download/session_ee379942/4-best_pt-hybrid.png",
                            "sam": "http://100.98.142.94:5000/api/download/session_ee379942/4-best_pt-segmentation.png",
                            "yolo": "http://100.98.142.94:5000/api/download/session_ee379942/4-yolo11m-detection.png"
                        },
                        "name": "4-best_pt-detection.json",
                        "summary_images_url": "http://100.98.142.94:5000/api/download/session_ee379942/4-best_pt-compare.png"
                    },
                    {
                        "datas": [
                            {
                                "bbox": [
                                    86.64531707763672,
                                    176.4845733642578,
                                    374.3604431152344,
                                    629.4055786132812
                                ],
                                "class_id": 2,
                                "class_name": "mineral",
                                "confidence": 0.8809738159179688
                            }
                        ],
                        "detection_count": 1,
                        "id": 4,
                        "images": {
                            "best": "http://100.98.142.94:5000/api/download/session_ee379942/3-best_pt-best.png",
                            "hybrid": "http://100.98.142.94:5000/api/download/session_ee379942/3-best_pt-hybrid.png",
                            "sam": "http://100.98.142.94:5000/api/download/session_ee379942/3-best_pt-segmentation.png",
                            "yolo": "http://100.98.142.94:5000/api/download/session_ee379942/3-yolo11m-detection.png"
                        },
                        "name": "3-best_pt-detection.json",
                        "summary_images_url": "http://100.98.142.94:5000/api/download/session_ee379942/3-best_pt-compare.png"
                    },
                    {
                        "datas": [
                            {
                                "bbox": [
                                    276.8039245605469,
                                    3.0908203125,
                                    535.4398803710938,
                                    591.9407958984375
                                ],
                                "class_id": 2,
                                "class_name": "mineral",
                                "confidence": 0.8865288496017456
                            }
                        ],
                        "detection_count": 1,
                        "id": 5,
                        "images": {
                            "best": "http://100.98.142.94:5000/api/download/session_ee379942/1-best_pt-best.png",
                            "hybrid": "http://100.98.142.94:5000/api/download/session_ee379942/1-best_pt-hybrid.png",
                            "sam": "http://100.98.142.94:5000/api/download/session_ee379942/1-best_pt-segmentation.png",
                            "yolo": "http://100.98.142.94:5000/api/download/session_ee379942/1-yolo11m-detection.png"
                        },
                        "name": "1-best_pt-detection.json",
                        "summary_images_url": "http://100.98.142.94:5000/api/download/session_ee379942/1-best_pt-compare.png"
                    }
                ],
                "object_count": 6
            },
            "image_name": "summary.json",
            "timestamp": "20250929_170306",
            "user_id": "web_user"
        },
        {
            "detection_count": 1,
            "detections": [
                {
                    "bbox": [
                        58.03447341918945,
                        134.4967803955078,
                        592.0010375976562,
                        623.7411499023438
                    ],
                    "class_id": 2,
                    "class_name": "mineral",
                    "confidence": 0.894088089466095
                }
            ],
            "image_name": "378",
            "timestamp": "20250929_164315",
            "user_id": "web_user"
        },
        {
            "detection_count": 1,
            "detections": [
                {
                    "bbox": [
                        65.6668472290039,
                        207.7731475830078,
                        546.5119018554688,
                        635.6267700195312
                    ],
                    "class_id": 2,
                    "class_name": "mineral",
                    "confidence": 0.9120920300483704
                }
            ],
            "image_name": "341",
            "timestamp": "20250929_164315",
            "user_id": "web_user"
        },
        {
            "detection_count": 1,
            "detections": [
                {
                    "bbox": [
                        229.16587829589844,
                        1.0831069946289062,
                        566.10595703125,
                        599.0540161132812
                    ],
                    "class_id": 2,
                    "class_name": "mineral",
                    "confidence": 0.8829227089881897
                }
            ],
            "image_name": "384",
            "timestamp": "20250929_164315",
            "user_id": "web_user"
        },
        {
            "detection_count": 1,
            "detections": [
                {
                    "bbox": [
                        71.2474136352539,
                        136.7961883544922,
                        615.6281127929688,
                        566.58837890625
                    ],
                    "class_id": 2,
                    "class_name": "mineral",
                    "confidence": 0.9240455031394958
                }
            ],
            "image_name": "338",
            "timestamp": "20250929_164315",
            "user_id": "web_user"
        },
        {
            "detection_count": 1,
            "detections": [
                {
                    "bbox": [
                        75.037353515625,
                        237.60044860839844,
                        643.7103881835938,
                        511.0901794433594
                    ],
                    "class_id": 2,
                    "class_name": "mineral",
                    "confidence": 0.8888660669326782
                }
            ],
            "image_name": "321",
            "timestamp": "20250929_164315",
            "user_id": "web_user"
        },
        {
            "detection_count": 1,
            "detections": [
                {
                    "bbox": [
                        57.02041244506836,
                        164.49624633789062,
                        670.578125,
                        390.94927978515625
                    ],
                    "class_id": 3,
                    "class_name": "non_mineral",
                    "confidence": 0.599696934223175
                }
            ],
            "image_name": "313",
            "timestamp": "20250929_164315",
            "user_id": "web_user"
        },
        {
            "detection_count": 3,
            "detections": {
                "class_summary": [
                    {
                        "class_name": "mineral",
                        "count": 6
                    },
                    {
                        "class_name": "not_empty",
                        "count": 3
                    },
                    {
                        "class_name": "non_mineral",
                        "count": 1
                    }
                ],
                "detection_summary": [
                    {
                        "datas": [
                            {
                                "bbox": [
                                    58.03447341918945,
                                    134.4967803955078,
                                    592.0010375976562,
                                    623.7411499023438
                                ],
                                "class_id": 2,
                                "class_name": "mineral",
                                "confidence": 0.894088089466095
                            }
                        ],
                        "detection_count": 1,
                        "id": 0,
                        "images": {
                            "best": "http://100.98.142.94:5000/api/download/session_914d0780/378-best_pt-best.png",
                            "hybrid": "http://100.98.142.94:5000/api/download/session_914d0780/378-best_pt-hybrid.png",
                            "sam": "http://100.98.142.94:5000/api/download/session_914d0780/378-best_pt-segmentation.png",
                            "yolo": "http://100.98.142.94:5000/api/download/session_914d0780/378-yolo11m-detection.png"
                        },
                        "name": "378-best_pt-detection.json",
                        "summary_images_url": "http://100.98.142.94:5000/api/download/session_914d0780/378-best_pt-compare.png"
                    },
                    {
                        "datas": [
                            {
                                "bbox": [
                                    65.6668472290039,
                                    207.7731475830078,
                                    546.5119018554688,
                                    635.6267700195312
                                ],
                                "class_id": 2,
                                "class_name": "mineral",
                                "confidence": 0.9120920300483704
                            }
                        ],
                        "detection_count": 1,
                        "id": 1,
                        "images": {
                            "best": "http://100.98.142.94:5000/api/download/session_914d0780/341-best_pt-best.png",
                            "hybrid": "http://100.98.142.94:5000/api/download/session_914d0780/341-best_pt-hybrid.png",
                            "sam": "http://100.98.142.94:5000/api/download/session_914d0780/341-best_pt-segmentation.png",
                            "yolo": "http://100.98.142.94:5000/api/download/session_914d0780/341-yolo11m-detection.png"
                        },
                        "name": "341-best_pt-detection.json",
                        "summary_images_url": "http://100.98.142.94:5000/api/download/session_914d0780/341-best_pt-compare.png"
                    },
                    {
                        "datas": [
                            {
                                "bbox": [
                                    229.16587829589844,
                                    1.0831069946289062,
                                    566.10595703125,
                                    599.0540161132812
                                ],
                                "class_id": 2,
                                "class_name": "mineral",
                                "confidence": 0.8829227089881897
                            }
                        ],
                        "detection_count": 1,
                        "id": 2,
                        "images": {
                            "best": "http://100.98.142.94:5000/api/download/session_914d0780/384-best_pt-best.png",
                            "hybrid": "http://100.98.142.94:5000/api/download/session_914d0780/384-best_pt-hybrid.png",
                            "sam": "http://100.98.142.94:5000/api/download/session_914d0780/384-best_pt-segmentation.png",
                            "yolo": "http://100.98.142.94:5000/api/download/session_914d0780/384-yolo11m-detection.png"
                        },
                        "name": "384-best_pt-detection.json",
                        "summary_images_url": "http://100.98.142.94:5000/api/download/session_914d0780/384-best_pt-compare.png"
                    },
                    {
                        "datas": [
                            {
                                "bbox": [
                                    71.2474136352539,
                                    136.7961883544922,
                                    615.6281127929688,
                                    566.58837890625
                                ],
                                "class_id": 2,
                                "class_name": "mineral",
                                "confidence": 0.9240455031394958
                            }
                        ],
                        "detection_count": 1,
                        "id": 3,
                        "images": {
                            "best": "http://100.98.142.94:5000/api/download/session_914d0780/338-best_pt-best.png",
                            "hybrid": "http://100.98.142.94:5000/api/download/session_914d0780/338-best_pt-hybrid.png",
                            "sam": "http://100.98.142.94:5000/api/download/session_914d0780/338-best_pt-segmentation.png",
                            "yolo": "http://100.98.142.94:5000/api/download/session_914d0780/338-yolo11m-detection.png"
                        },
                        "name": "338-best_pt-detection.json",
                        "summary_images_url": "http://100.98.142.94:5000/api/download/session_914d0780/338-best_pt-compare.png"
                    },
                    {
                        "datas": [
                            {
                                "bbox": [
                                    75.037353515625,
                                    237.60044860839844,
                                    643.7103881835938,
                                    511.0901794433594
                                ],
                                "class_id": 2,
                                "class_name": "mineral",
                                "confidence": 0.8888660669326782
                            }
                        ],
                        "detection_count": 1,
                        "id": 4,
                        "images": {
                            "best": "http://100.98.142.94:5000/api/download/session_914d0780/321-best_pt-best.png",
                            "hybrid": "http://100.98.142.94:5000/api/download/session_914d0780/321-best_pt-hybrid.png",
                            "sam": "http://100.98.142.94:5000/api/download/session_914d0780/321-best_pt-segmentation.png",
                            "yolo": "http://100.98.142.94:5000/api/download/session_914d0780/321-yolo11m-detection.png"
                        },
                        "name": "321-best_pt-detection.json",
                        "summary_images_url": "http://100.98.142.94:5000/api/download/session_914d0780/321-best_pt-compare.png"
                    },
                    {
                        "datas": [
                            {
                                "bbox": [
                                    57.02041244506836,
                                    164.49624633789062,
                                    670.578125,
                                    390.94927978515625
                                ],
                                "class_id": 3,
                                "class_name": "non_mineral",
                                "confidence": 0.599696934223175
                            }
                        ],
                        "detection_count": 1,
                        "id": 5,
                        "images": {
                            "best": "http://100.98.142.94:5000/api/download/session_914d0780/313-best_pt-best.png",
                            "hybrid": "http://100.98.142.94:5000/api/download/session_914d0780/313-best_pt-hybrid.png",
                            "sam": "http://100.98.142.94:5000/api/download/session_914d0780/313-best_pt-segmentation.png",
                            "yolo": "http://100.98.142.94:5000/api/download/session_914d0780/313-yolo11m-detection.png"
                        },
                        "name": "313-best_pt-detection.json",
                        "summary_images_url": "http://100.98.142.94:5000/api/download/session_914d0780/313-best_pt-compare.png"
                    },
                    {
                        "datas": [
                            {
                                "bbox": [
                                    258.7248229980469,
                                    0.0,
                                    489.5411682128906,
                                    599.09033203125
                                ],
                                "class_id": 2,
                                "class_name": "mineral",
                                "confidence": 0.8533073663711548
                            },
                            {
                                "bbox": [
                                    336.07513427734375,
                                    488.0848693847656,
                                    417.63458251953125,
                                    519.3463134765625
                                ],
                                "class_id": 4,
                                "class_name": "not_empty",
                                "confidence": 0.3200879991054535
                            },
                            {
                                "bbox": [
                                    312.18585205078125,
                                    79.28901672363281,
                                    419.75146484375,
                                    565.7007446289062
                                ],
                                "class_id": 4,
                                "class_name": "not_empty",
                                "confidence": 0.3165200352668762
                            },
                            {
                                "bbox": [
                                    319.8309020996094,
                                    11.757278442382812,
                                    400.3699035644531,
                                    391.1419677734375
                                ],
                                "class_id": 4,
                                "class_name": "not_empty",
                                "confidence": 0.27894076704978943
                            }
                        ],
                        "detection_count": 4,
                        "id": 6,
                        "images": {
                            "best": "http://100.98.142.94:5000/api/download/session_914d0780/354-best_pt-best.png",
                            "hybrid": "http://100.98.142.94:5000/api/download/session_914d0780/354-best_pt-hybrid.png",
                            "sam": "http://100.98.142.94:5000/api/download/session_914d0780/354-best_pt-segmentation.png",
                            "yolo": "http://100.98.142.94:5000/api/download/session_914d0780/354-yolo11m-detection.png"
                        },
                        "name": "354-best_pt-detection.json",
                        "summary_images_url": "http://100.98.142.94:5000/api/download/session_914d0780/354-best_pt-compare.png"
                    }
                ],
                "object_count": 7
            },
            "image_name": "summary.json",
            "timestamp": "20250929_164315",
            "user_id": "web_user"
        },
        {
            "detection_count": 4,
            "detections": [
                {
                    "bbox": [
                        258.7248229980469,
                        0.0,
                        489.5411682128906,
                        599.09033203125
                    ],
                    "class_id": 2,
                    "class_name": "mineral",
                    "confidence": 0.8533073663711548
                },
                {
                    "bbox": [
                        336.07513427734375,
                        488.0848693847656,
                        417.63458251953125,
                        519.3463134765625
                    ],
                    "class_id": 4,
                    "class_name": "not_empty",
                    "confidence": 0.3200879991054535
                },
                {
                    "bbox": [
                        312.18585205078125,
                        79.28901672363281,
                        419.75146484375,
                        565.7007446289062
                    ],
                    "class_id": 4,
                    "class_name": "not_empty",
                    "confidence": 0.3165200352668762
                },
                {
                    "bbox": [
                        319.8309020996094,
                        11.757278442382812,
                        400.3699035644531,
                        391.1419677734375
                    ],
                    "class_id": 4,
                    "class_name": "not_empty",
                    "confidence": 0.27894076704978943
                }
            ],
            "image_name": "354",
            "timestamp": "20250929_164315",
            "user_id": "web_user"
        },
        {
            "detection_count": 4,
            "detections": [
                {
                    "bbox": [
                        242.61474609375,
                        12.343025207519531,
                        532.5955810546875,
                        600.0
                    ],
                    "class_id": 2,
                    "class_name": "mineral",
                    "confidence": 0.9195183515548706
                },
                {
                    "bbox": [
                        266.65155029296875,
                        98.52569580078125,
                        378.8997802734375,
                        164.14520263671875
                    ],
                    "class_id": 4,
                    "class_name": "not_empty",
                    "confidence": 0.8401011228561401
                },
                {
                    "bbox": [
                        285.4236755371094,
                        265.30230712890625,
                        521.5062866210938,
                        600.0
                    ],
                    "class_id": 4,
                    "class_name": "not_empty",
                    "confidence": 0.6164628863334656
                },
                {
                    "bbox": [
                        269.50390625,
                        230.6625213623047,
                        505.3045959472656,
                        557.5062255859375
                    ],
                    "class_id": 4,
                    "class_name": "not_empty",
                    "confidence": 0.42666763067245483
                }
            ],
            "image_name": "193",
            "timestamp": "20250929_163736",
            "user_id": "web_user"
        },
        {
            "detection_count": 1,
            "detections": [
                {
                    "bbox": [
                        179.23629760742188,
                        0.0,
                        429.785400390625,
                        539.3291625976562
                    ],
                    "class_id": 2,
                    "class_name": "mineral",
                    "confidence": 0.8473432064056396
                }
            ],
            "image_name": "181",
            "timestamp": "20250929_163736",
            "user_id": "web_user"
        },
        {
            "detection_count": 1,
            "detections": [
                {
                    "bbox": [
                        276.7310791015625,
                        0.0,
                        531.376220703125,
                        571.3033447265625
                    ],
                    "class_id": 2,
                    "class_name": "mineral",
                    "confidence": 0.8509712219238281
                }
            ],
            "image_name": "118",
            "timestamp": "20250929_163736",
            "user_id": "web_user"
        },
        {
            "detection_count": 3,
            "detections": [
                {
                    "bbox": [
                        275.3157653808594,
                        0.895538330078125,
                        489.90179443359375,
                        600.0
                    ],
                    "class_id": 2,
                    "class_name": "mineral",
                    "confidence": 0.8866058588027954
                },
                {
                    "bbox": [
                        328.6308288574219,
                        71.87113952636719,
                        434.80194091796875,
                        382.4837951660156
                    ],
                    "class_id": 4,
                    "class_name": "not_empty",
                    "confidence": 0.6486598253250122
                },
                {
                    "bbox": [
                        344.604248046875,
                        479.9710388183594,
                        423.48516845703125,
                        517.8389282226562
                    ],
                    "class_id": 4,
                    "class_name": "not_empty",
                    "confidence": 0.5457576513290405
                }
            ],
            "image_name": "107",
            "timestamp": "20250929_163736",
            "user_id": "web_user"
        },
        {
            "detection_count": 2,
            "detections": [
                {
                    "bbox": [
                        162.58079528808594,
                        0.0,
                        443.83935546875,
                        600.0
                    ],
                    "class_id": 2,
                    "class_name": "mineral",
                    "confidence": 0.6811609864234924
                },
                {
                    "bbox": [
                        175.410888671875,
                        0.0,
                        439.87176513671875,
                        600.0
                    ],
                    "class_id": 3,
                    "class_name": "non_mineral",
                    "confidence": 0.5245780944824219
                }
            ],
            "image_name": "163",
            "timestamp": "20250929_163736",
            "user_id": "web_user"
        },
        {
            "detection_count": 3,
            "detections": {
                "class_summary": [
                    {
                        "class_name": "mineral",
                        "count": 6
                    },
                    {
                        "class_name": "not_empty",
                        "count": 5
                    },
                    {
                        "class_name": "non_mineral",
                        "count": 1
                    }
                ],
                "detection_summary": [
                    {
                        "datas": [
                            {
                                "bbox": [
                                    242.61474609375,
                                    12.343025207519531,
                                    532.5955810546875,
                                    600.0
                                ],
                                "class_id": 2,
                                "class_name": "mineral",
                                "confidence": 0.9195183515548706
                            },
                            {
                                "bbox": [
                                    266.65155029296875,
                                    98.52569580078125,
                                    378.8997802734375,
                                    164.14520263671875
                                ],
                                "class_id": 4,
                                "class_name": "not_empty",
                                "confidence": 0.8401011228561401
                            },
                            {
                                "bbox": [
                                    285.4236755371094,
                                    265.30230712890625,
                                    521.5062866210938,
                                    600.0
                                ],
                                "class_id": 4,
                                "class_name": "not_empty",
                                "confidence": 0.6164628863334656
                            },
                            {
                                "bbox": [
                                    269.50390625,
                                    230.6625213623047,
                                    505.3045959472656,
                                    557.5062255859375
                                ],
                                "class_id": 4,
                                "class_name": "not_empty",
                                "confidence": 0.42666763067245483
                            }
                        ],
                        "detection_count": 4,
                        "id": 0,
                        "images": {
                            "best": "http://100.98.142.94:5000/api/download/session_e3ffd252/193-best_pt-best.png",
                            "hybrid": "http://100.98.142.94:5000/api/download/session_e3ffd252/193-best_pt-hybrid.png",
                            "sam": "http://100.98.142.94:5000/api/download/session_e3ffd252/193-best_pt-segmentation.png",
                            "yolo": "http://100.98.142.94:5000/api/download/session_e3ffd252/193-yolo11m-detection.png"
                        },
                        "name": "193-best_pt-detection.json",
                        "summary_images_url": "http://100.98.142.94:5000/api/download/session_e3ffd252/193-best_pt-compare.png"
                    },
                    {
                        "datas": [
                            {
                                "bbox": [
                                    179.23629760742188,
                                    0.0,
                                    429.785400390625,
                                    539.3291625976562
                                ],
                                "class_id": 2,
                                "class_name": "mineral",
                                "confidence": 0.8473432064056396
                            }
                        ],
                        "detection_count": 1,
                        "id": 1,
                        "images": {
                            "best": "http://100.98.142.94:5000/api/download/session_e3ffd252/181-best_pt-best.png",
                            "hybrid": "http://100.98.142.94:5000/api/download/session_e3ffd252/181-best_pt-hybrid.png",
                            "sam": "http://100.98.142.94:5000/api/download/session_e3ffd252/181-best_pt-segmentation.png",
                            "yolo": "http://100.98.142.94:5000/api/download/session_e3ffd252/181-yolo11m-detection.png"
                        },
                        "name": "181-best_pt-detection.json",
                        "summary_images_url": "http://100.98.142.94:5000/api/download/session_e3ffd252/181-best_pt-compare.png"
                    },
                    {
                        "datas": [
                            {
                                "bbox": [
                                    276.7310791015625,
                                    0.0,
                                    531.376220703125,
                                    571.3033447265625
                                ],
                                "class_id": 2,
                                "class_name": "mineral",
                                "confidence": 0.8509712219238281
                            }
                        ],
                        "detection_count": 1,
                        "id": 2,
                        "images": {
                            "best": "http://100.98.142.94:5000/api/download/session_e3ffd252/118-best_pt-best.png",
                            "hybrid": "http://100.98.142.94:5000/api/download/session_e3ffd252/118-best_pt-hybrid.png",
                            "sam": "http://100.98.142.94:5000/api/download/session_e3ffd252/118-best_pt-segmentation.png",
                            "yolo": "http://100.98.142.94:5000/api/download/session_e3ffd252/118-yolo11m-detection.png"
                        },
                        "name": "118-best_pt-detection.json",
                        "summary_images_url": "http://100.98.142.94:5000/api/download/session_e3ffd252/118-best_pt-compare.png"
                    },
                    {
                        "datas": [
                            {
                                "bbox": [
                                    275.3157653808594,
                                    0.895538330078125,
                                    489.90179443359375,
                                    600.0
                                ],
                                "class_id": 2,
                                "class_name": "mineral",
                                "confidence": 0.8866058588027954
                            },
                            {
                                "bbox": [
                                    328.6308288574219,
                                    71.87113952636719,
                                    434.80194091796875,
                                    382.4837951660156
                                ],
                                "class_id": 4,
                                "class_name": "not_empty",
                                "confidence": 0.6486598253250122
                            },
                            {
                                "bbox": [
                                    344.604248046875,
                                    479.9710388183594,
                                    423.48516845703125,
                                    517.8389282226562
                                ],
                                "class_id": 4,
                                "class_name": "not_empty",
                                "confidence": 0.5457576513290405
                            }
                        ],
                        "detection_count": 3,
                        "id": 3,
                        "images": {
                            "best": "http://100.98.142.94:5000/api/download/session_e3ffd252/107-best_pt-best.png",
                            "hybrid": "http://100.98.142.94:5000/api/download/session_e3ffd252/107-best_pt-hybrid.png",
                            "sam": "http://100.98.142.94:5000/api/download/session_e3ffd252/107-best_pt-segmentation.png",
                            "yolo": "http://100.98.142.94:5000/api/download/session_e3ffd252/107-yolo11m-detection.png"
                        },
                        "name": "107-best_pt-detection.json",
                        "summary_images_url": "http://100.98.142.94:5000/api/download/session_e3ffd252/107-best_pt-compare.png"
                    },
                    {
                        "datas": [
                            {
                                "bbox": [
                                    162.58079528808594,
                                    0.0,
                                    443.83935546875,
                                    600.0
                                ],
                                "class_id": 2,
                                "class_name": "mineral",
                                "confidence": 0.6811609864234924
                            },
                            {
                                "bbox": [
                                    175.410888671875,
                                    0.0,
                                    439.87176513671875,
                                    600.0
                                ],
                                "class_id": 3,
                                "class_name": "non_mineral",
                                "confidence": 0.5245780944824219
                            }
                        ],
                        "detection_count": 2,
                        "id": 4,
                        "images": {
                            "best": "http://100.98.142.94:5000/api/download/session_e3ffd252/163-best_pt-best.png",
                            "hybrid": "http://100.98.142.94:5000/api/download/session_e3ffd252/163-best_pt-hybrid.png",
                            "sam": "http://100.98.142.94:5000/api/download/session_e3ffd252/163-best_pt-segmentation.png",
                            "yolo": "http://100.98.142.94:5000/api/download/session_e3ffd252/163-yolo11m-detection.png"
                        },
                        "name": "163-best_pt-detection.json",
                        "summary_images_url": "http://100.98.142.94:5000/api/download/session_e3ffd252/163-best_pt-compare.png"
                    },
                    {
                        "datas": [
                            {
                                "bbox": [
                                    302.72998046875,
                                    0.4238128662109375,
                                    563.0634765625,
                                    599.367431640625
                                ],
                                "class_id": 2,
                                "class_name": "mineral",
                                "confidence": 0.8695794939994812
                            }
                        ],
                        "detection_count": 1,
                        "id": 5,
                        "images": {
                            "best": "http://100.98.142.94:5000/api/download/session_e3ffd252/197-best_pt-best.png",
                            "hybrid": "http://100.98.142.94:5000/api/download/session_e3ffd252/197-best_pt-hybrid.png",
                            "sam": "http://100.98.142.94:5000/api/download/session_e3ffd252/197-best_pt-segmentation.png",
                            "yolo": "http://100.98.142.94:5000/api/download/session_e3ffd252/197-yolo11m-detection.png"
                        },
                        "name": "197-best_pt-detection.json",
                        "summary_images_url": "http://100.98.142.94:5000/api/download/session_e3ffd252/197-best_pt-compare.png"
                    }
                ],
                "object_count": 6
            },
            "image_name": "summary.json",
            "timestamp": "20250929_163736",
            "user_id": "web_user"
        },
        {
            "detection_count": 1,
            "detections": [
                {
                    "bbox": [
                        302.72998046875,
                        0.4238128662109375,
                        563.0634765625,
                        599.367431640625
                    ],
                    "class_id": 2,
                    "class_name": "mineral",
                    "confidence": 0.8695794939994812
                }
            ],
            "image_name": "197",
            "timestamp": "20250929_163736",
            "user_id": "web_user"
        },
        {
            "detection_count": 1,
            "detections": [
                {
                    "bbox": [
                        81.505615234375,
                        148.776123046875,
                        570.615234375,
                        632.4052124023438
                    ],
                    "class_id": 2,
                    "class_name": "mineral",
                    "confidence": 0.9154394268989563
                }
            ],
            "image_name": "108",
            "timestamp": "20250929_153909",
            "user_id": "web_user"
        },
        {
            "detection_count": 3,
            "detections": [
                {
                    "bbox": [
                        275.3157653808594,
                        0.895538330078125,
                        489.90179443359375,
                        600.0
                    ],
                    "class_id": 2,
                    "class_name": "mineral",
                    "confidence": 0.8866058588027954
                },
                {
                    "bbox": [
                        328.6308288574219,
                        71.87113952636719,
                        434.80194091796875,
                        382.4837951660156
                    ],
                    "class_id": 4,
                    "class_name": "not_empty",
                    "confidence": 0.6486598253250122
                },
                {
                    "bbox": [
                        344.604248046875,
                        479.9710388183594,
                        423.48516845703125,
                        517.8389282226562
                    ],
                    "class_id": 4,
                    "class_name": "not_empty",
                    "confidence": 0.5457576513290405
                }
            ],
            "image_name": "107",
            "timestamp": "20250929_153909",
            "user_id": "web_user"
        }
    ],
    "total_sessions": 54
}
```

## 📤 Upload Images

### Menggunakan curl:
```bash
curl -X POST \
  -F 'files=@image1.jpg' \
  -F 'files=@image2.jpg' \
  -F 'user_id=my_user' \
  http://100.98.142.94:5000/api/upload
```

### Response:
```json
{
  "success": true,
  "session_id": "session_abc123",
  "timestamp": "20250928_143022",
  "user_id": "my_user",
  "uploaded_files": [...],
  "status_url": "/api/process/session_abc123",
  "results_url": "/api/results/session_abc123"
}
```

## 📊 Check Processing Status

```bash
curl http://100.98.142.94:5000/api/process/session_abc123
```

### Response:
```json
{
  "status": "completed",
  "message": "Detection completed successfully",
  "timestamp": "20250928_143022",
  "user_id": "my_user",
  "start_time": "2025-09-28T14:30:22",
  "end_time": "2025-09-28T14:30:45"
}
```

## 📈 Get Detection Results

```bash
curl http://100.98.142.94:5000/api/results/session_abc123
```

### Response:
```json
{
  "session_id": "session_abc123",
  "status": "completed",
  "results": {
    "detection_summary": [
      {
        "id": 0,
        "name": "image1-best_pt-detection.json",
        "datas": [
          {
            "bbox": [100, 200, 300, 400],
            "confidence": 0.85,
            "class_id": 2,
            "class_name": "mineral"
          }
        ],
        "detection_count": 1,
        "summary_images_url": "https://100.98.142.94:5000/api/download/20250929_023747/test_user_multi/image1-best_pt-compare.png",
        "images": {
          "best": "https://100.98.142.94:5000/api/download/20250929_023747/test_user_multi/best/image1-best_pt-best.png",
          "yolo": "https://100.98.142.94:5000/api/download/20250929_023747/test_user_multi/yolo/image1-yolo11m-detection.png",
          "sam": "https://100.98.142.94:5000/api/download/20250929_023747/test_user_multi/segmentasi/image1-best_pt-segmentation.png",
          "hybrid": "https://100.98.142.94:5000/api/download/20250929_023747/test_user_multi/hybrid/image1-best_pt-hybrid.png"
        }
      }
    ],
    "class_summary": [
      {
        "class_name": "mineral",
        "count": 1
      }
    ],
    "object_count": 1
  }
}
```

## 📁 File Structure

```
./direct/app/api-hybrid-detection/
├── app.py                 # Flask API server
├── requirements.txt       # Python dependencies
├── run_api.sh            # API launcher script
└── README.md             # Documentation

./direct/data/
├── input/remote/         # Uploaded images
│   └── <timestamp>/<user_id>/
└── output/remote/        # Detection results
    └── <timestamp>/<user_id>/
        ├── yolo/         # YOLO11m results
        ├── best/         # best.pt results
        ├── segmentasi/   # SAM2 segmentation
        ├── hybrid/       # Combined results
        └── *.json        # Detection data
```

## 🔧 Configuration

### Environment Variables
- `UPLOAD_FOLDER`: Directory untuk upload (default: `../../data/input/remote`)
- `OUTPUT_FOLDER`: Directory untuk output (default: `../../data/output/remote`)
- `MAX_CONTENT_LENGTH`: Max file size (default: 16MB)

### Supported File Types
- PNG, JPG, JPEG, GIF, BMP

## 🎯 Features

- ✅ **Multi-file Upload**: Upload multiple images sekaligus
- ✅ **Background Processing**: Processing berjalan di background
- ✅ **Session Management**: Setiap upload mendapat session ID unik
- ✅ **Real-time Status**: Check status processing real-time
- ✅ **File Download**: Download hasil visualisasi
- ✅ **Detection History**: Lihat semua deteksi terbaru
- ✅ **CORS Support**: Support untuk web applications
- ✅ **Error Handling**: Comprehensive error handling
- ✅ **GPU Detection**: Real-time GPU information dengan memory details
- ✅ **System Monitoring**: Total sessions processed tracking
- ✅ **Summary JSON**: Structured results dengan detection_summary, class_summary, dan object_count
- ✅ **Image URLs**: Direct download links untuk semua jenis visualisasi (best, yolo, sam, hybrid)
- ✅ **Compare Visualization**: Summary image yang menggabungkan semua hasil

## 🔍 Detection Models

1. **YOLO11m**: Object detection
2. **best.pt**: Custom trained model
3. **SAM2_b**: Segmentation model

## 🖥️ GPU Information

API secara otomatis mendeteksi dan melaporkan informasi GPU yang tersedia:

### GPU Detection Features:
- **Real-time Detection**: Mendeteksi GPU yang tersedia saat runtime
- **Memory Information**: Menampilkan total memory setiap GPU
- **CUDA Support**: Mengecek ketersediaan CUDA dan cuDNN
- **Multi-GPU Support**: Mendukung sistem dengan multiple GPU
- **PyTorch Integration**: Menggunakan PyTorch untuk deteksi GPU

### GPU Info Structure:
```json
{
    "gpu_info": {
        "available_gpus": 1,
        "cuda_available": true,
        "cudnn_enabled": true,
        "gpus": [
            {
                "id": 0,
                "name": "NVIDIA GeForce RTX 3060",
                "memory_gb": 11.63
            }
        ],
        "pytorch_cuda_version": "2.8.0+cu128",
        "status": "success",
        "total_memory_all_gpus_gb": 11.63
    }
}
```

### Supported GPU Types:
- **NVIDIA GPUs**: Semua GPU NVIDIA dengan CUDA support
- **Memory Detection**: Otomatis mendeteksi total memory
- **Multi-GPU Systems**: Mendukung sistem dengan multiple GPU
- **Fallback Support**: Graceful handling jika GPU tidak tersedia

## 🔧 Hardware Information

### Comprehensive Hardware Monitoring
API menyediakan endpoint `/api/hardware` untuk monitoring hardware GPU Server secara lengkap:

#### Hardware Information Includes:
- **System Info**: Architecture, hostname, kernel version
- **GPU Info**: Status, availability, cuDNN enabled, PyTorch CUDA version, GPU details
- **Memory Info**: Total memory (RAM + Swap combined)
- **Disk Info**: Available space, total size, used space, usage percentage
- **Network Info**: Local IP, public IP

#### Usage:
```bash
# Get comprehensive hardware information
curl http://100.98.142.94:5000/api/hardware

# With pretty print
curl http://100.98.142.94:5000/api/hardware | jq
```

#### Testing Commands:
```bash
# 1. Health Check
curl -s http://100.98.142.94:5000/api/health | jq

# 2. API Status (includes hardware info)
curl -s http://100.98.142.94:5000/api/status | jq

# 3. Hardware Information (detailed)
curl -s http://100.98.142.94:5000/api/hardware | jq

# 4. Detection History
curl -s http://100.98.142.94:5000/api/detections | jq

# 5. Upload Test (with actual file)
curl -X POST -F 'files=@/path/to/image.jpg' -F 'user_id=test_user' http://100.98.142.94:5000/api/upload | jq

# 6. Check Processing Status
curl -s http://100.98.142.94:5000/api/process/session_3de9cfac | jq

# 7. Get Detection Results
curl -s http://100.98.142.94:5000/api/results/session_3de9cfac | jq

# 8. Download Result Files
curl -s http://100.98.142.94:5000/api/download/session_3de9cfac/dogs-best_pt-compare.png -o result.png
```

#### Hardware Monitoring Features:
- **Real-time Status**: Live hardware information
- **Resource Monitoring**: Memory, disk, GPU usage
- **Network Detection**: Automatic IP detection (local, public)
- **System Information**: Architecture, hostname, kernel versions
- **Performance Metrics**: GPU memory usage, system memory

#### Live Testing Results:
**Tested on**: 2025-10-01T09:07:45.807590

**Health Check Response:**
```json
{
  "service": "MyCV-GPU-Server",
  "status": "healthy",
  "timestamp": "2025-10-01T09:05:43.528279",
  "uptime": 1759309543.528301,
  "version": "1.0.0"
}
```

**API Status Response:**
```json
{
  "api_status": "online",
  "endpoints": [
    "/api/health",
    "/api/status", 
    "/api/hardware",
    "/api/upload",
    "/api/process/<session_id>",
    "/api/results/<session_id>",
    "/api/download/<session_id>/<filename>",
    "/api/detections"
  ],
  "gpu_info": {
    "available_gpus": 1,
    "cuda_available": true,
    "cudnn_enabled": true,
    "gpus": [
      {
        "id": 0,
        "memory_gb": 11.63,
        "name": "NVIDIA GeForce RTX 3060"
      }
    ],
    "pytorch_cuda_version": "2.8.0+cu128",
    "status": "success",
    "total_memory_all_gpus_gb": 11.63
  },
  "service": "MyCV-GPU-Server",
  "timestamp": "2025-10-01T09:09:25.543460",
  "total_sessions_processed": 50,
  "version": "1.0.0"
}
```

**Hardware Info Response:**
```json
{
  "status": "success",
  "service": "MyCV-GPU-Server",
  "hardware_info": {
    "system_info": {
      "architecture": "x86_64",
      "hostname": "cv-host",
      "kernel_version": "5.15.0-156-generic"
    },
    "gpu_info": {
      "status": "success",
      "cuda_available": true,
      "cudnn_enabled": true,
      "pytorch_cuda_version": "2.8.0+cu128",
      "available_gpus": 1,
      "gpus": [
        {
          "id": 0,
          "name": "NVIDIA GeForce RTX 3060",
          "memory_gb": 11.63
        }
      ],
      "total_memory_all_gpus_gb": 11.63
    },
    "memory_info": {
      "total_gb": 12.75
    },
    "disk_info": {
      "available": "74G",
      "size": "153G",
      "used": "72G",
      "use_percent": "50%"
    },
    "network_info": {
      "local_ip": "10.3.52.184",
      "public_ip": "202.152.145.34"
    },
    "updated_at": "2025-10-01T09:07:45.807590"
  }
}
```

#### Complete API Testing Results:
**Tested on**: 2025-10-01T09:13:45.289538

**Upload Test Response:**
```json
{
  "message": "Files uploaded successfully. Processing started.",
  "results_url": "/api/results/session_3de9cfac",
  "session_id": "session_3de9cfac",
  "status_url": "/api/process/session_3de9cfac",
  "success": true,
  "timestamp": "20251001_091332",
  "uploaded_files": [
    {
      "original_name": "dogs.jpg",
      "path": "../../data/input/remote/20251001_091332/test_api/dogs.jpg",
      "saved_name": "dogs.jpg"
    }
  ],
  "user_id": "test_api"
}
```

**Processing Status Response:**
```json
{
  "end_time": "2025-10-01T09:13:45.289538",
  "message": "Detection completed successfully",
  "start_time": "2025-10-01T09:13:32.140137",
  "status": "completed",
  "timestamp": "20251001_091332",
  "user_id": "test_api"
}
```

**Detection Results Response:**
```json
{
  "results": {
    "class_summary": [
      {
        "class_name": "mineral",
        "count": 1
      }
    ],
    "detection_summary": [
      {
        "datas": [
          {
            "bbox": [
              0.04709620401263237,
              51.37726974487305,
              166.78123474121094,
              566.7282104492188
            ],
            "class_id": 2,
            "class_name": "mineral",
            "confidence": 0.26000073552131653
          }
        ],
        "detection_count": 1,
        "id": 0,
        "images": {
          "best": "http://100.98.142.94:5000/api/download/session_3de9cfac/dogs-best_pt-best.png",
          "hybrid": "http://100.98.142.94:5000/api/download/session_3de9cfac/dogs-best_pt-hybrid.png",
          "sam": "http://100.98.142.94:5000/api/download/session_3de9cfac/dogs-best_pt-segmentation.png",
          "yolo": "http://100.98.142.94:5000/api/download/session_3de9cfac/dogs-yolo11m-detection.png"
        },
        "name": "dogs-best_pt-detection.json",
        "summary_images_url": "http://100.98.142.94:5000/api/download/session_3de9cfac/dogs-best_pt-compare.png"
      }
    ],
    "object_count": 1
  },
  "session_id": "session_3de9cfac",
  "status": "completed",
  "timestamp": "20251001_091332",
  "user_id": "test_api"
}
```

**Detection History Response:**
```json
{
  "recent_detections": [
    {
      "detection_count": 1,
      "detections": [
        {
          "bbox": [
            0.04709620401263237,
            51.37726974487305,
            166.78123474121094,
            566.7282104492188
          ],
          "class_id": 2,
          "class_name": "mineral",
          "confidence": 0.26000073552131653
        }
      ],
      "image_name": "dogs",
      "timestamp": "20251001_091332",
      "user_id": "test_api"
    }
  ],
  "total_sessions": 51
}
```

## 📈 System Monitoring

API menyediakan monitoring sistem secara real-time:

### Monitoring Features:
- **Session Tracking**: Menghitung total sessions yang telah diproses
- **Performance Metrics**: Melacak performa sistem
- **Resource Usage**: Monitoring penggunaan GPU dan memory
- **Health Status**: Real-time health check

### System Status Response:
```json
{
    "api_status": "online",
    "service": "MyCV-Platform Hybrid Detection API",
    "version": "1.0.0",
    "total_sessions_processed": 7,
    "gpu_info": { ... },
    "timestamp": "2025-09-28T15:09:48.393352"
}
```

### Monitoring Endpoints:
- `GET /api/status` - Comprehensive system status
- `GET /api/health` - Basic health check
- `GET /api/hardware` - Comprehensive hardware information
- `GET /api/detections` - Processing history

## 🌐 Web Application Integration

API terintegrasi dengan MyCV-Platform Web Application untuk menampilkan informasi real-time:

### Web App Features:
- **Real-time System Status**: Menampilkan status GPU dan sistem
- **Upload Interface**: Multi-file upload dengan drag & drop
- **Processing Status**: Real-time monitoring processing
- **Results Display**: Frame-based results visualization
- **Download Management**: Download hasil processing

### System Status Display:
Web application menampilkan informasi real dari API:
- **Service**: MyCV-Platform Hybrid Detection API
- **Status**: Online/Offline
- **Version**: 1.0.0
- **Server**: 100.98.142.94
- **GPU Available**: NVIDIA GeForce RTX 3060 (11.63GB)
- **GPU Count**: 1 GPU(s) - 11.63GB Total

### Integration Endpoints:
- Web App: `http://100.98.142.94:5002`
- API Service: `http://100.98.142.94:5000`
- Real-time data exchange antara Web App dan API

## 📊 Output Files

Untuk setiap gambar yang diproses:

- `{image}-best_pt-detection.json` - Detection results (JSON)
- `{image}-best_pt-compare.png` - Compare visualization (4-panel)
- `{image}-best_pt-best.png` - Best detection (best/ folder)
- `{image}-yolo11m-detection.png` - YOLO11m detection (yolo/ folder)
- `{image}-best_pt-segmentation.png` - SAM2 segmentation (segmentasi/ folder)
- `{image}-best_pt-hybrid.png` - Combined result (hybrid/ folder)
- `summary.json` - Session summary dengan detection_summary, class_summary, object_count

## 🚨 Troubleshooting

### API tidak bisa diakses
1. Check firewall: `sudo ufw allow 5000`
2. Check port usage: `netstat -tlnp | grep 5000`
3. Restart API: `./run_api.sh`

### Upload gagal
1. Check file size (max 16MB)
2. Check file format (PNG, JPG, JPEG, GIF, BMP)
3. Check disk space

### Processing gagal
1. Check models tersedia
2. Check virtual environment
3. Check logs di terminal

## 📞 Support

Untuk bantuan atau laporan bug, silakan buka issue di repository GitHub.
