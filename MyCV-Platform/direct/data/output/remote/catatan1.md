sekarang kita modif ./run_api_hybrid_detection.py dan ./app/api-hybrid-detection/app.py
untuk endpoint GET /api/results/<session_id>

Mememiliki tampilan JSON payload seperti ini:



```json
{
  "results": {
    "detection_summary": {  // <== di ambil dari class_name
      "dishwasher": 1, // <== di hitung ada berapa dishwasher yang di detection_processed.detections.datas.class_name
      "soda": 1, // <== di hitung ada berapa soda yang di detection_processed.detections.datas.class_name
      "mineral": 1, // <== di hitung ada berapa soda yang di detection_processed.detections.datas.class_name
      "class_name" : "n",
      "visualizations": [
          {
            "file_name": "21.mineral-best_pt-compare.png",
            "images_url": "https://100.98.142.94:5000/20250928_152449/web_user/21.mineral-best_pt-compare.png",
            "type": "compare"
          }
        ]
    },
    "detection_processed": [
      {
        "detection_count": 2, // <== hitung ada berapa images yang di proses
        "detections": [
          {
            "id": 0,
            "images_url":"https://100.98.142.94:5000/20250928_152449/web_user/best/21.mineral-best_pt-best.png",
            "name" : "nama_images-model-detection.json", // <== json dari hasil deteksi best.pt + sam atau hybrid
            "datas": [
              {
                "bbox": [
                  289.1062316894531,
                  224.7684326171875,
                  368.2486267089844,
                  452.5482177734375
                ],
                "class_id": 5,
                "class_name": "soda",
                "confidence": 0.28023016452789307
              },
              {
                "bbox": [
                  288.54638671875,
                  223.8448944091797,
                  369.2777099609375,
                  452.61724853515625
                ],
                "class_id": 0,
                "class_name": "dishwasher",
                "confidence": 0.27135953307151794
              }
            ],
            "object_count": 2 // <== menghitung object yang terdeteksi di gambar atau melihat ada berapa class_name dari datas nya
          },
          {
          "id": 1,
            "name" : "nama_images-model-detection.json", // <== json dari hasil deteksi best.pt + sam atau hybrid
            "datas": [
              {
                "bbox": [
                  289.1062316894531,
                  224.7684326171875,
                  368.2486267089844,
                  452.5482177734375
                ],
                "class_id": 5,
                "class_name": "mineral",
                "confidence": 0.28023016452789307
              }
            ],
            "object_count": 1 // <== menghitung object yang terdeteksi di gambar atau melihat ada berapa class_name dari datas nya
          }
        ]
      }
    ]
  },
  "session_id": "session_9eebbca5",
  "status": "completed",
  "timestamp": "20250928_135630",
  "user_id": "test_userrrr"
}
```

```json
{
    "result" : [{
      "detection_summary": [
          {
            "id": 0,
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
            "summary_images_url": "https://100.98.142.94:5000/api/download/20250929_014648/test_user_001/24_mineral-best_pt-compare.png",
            "images": {
              "best": "https://100.98.142.94:5000/api/download/20250929_014648/test_user_001/best/24_mineral-best_pt-best.png",
              "yolo": "https://100.98.142.94:5000/api/download/20250929_014648/test_user_001/yolo/24_mineral-yolo11m-detection.png",
              "sam": "https://100.98.142.94:5000/api/download/20250929_014648/test_user_001/segmentasi/24_mineral-best_pt-segmentation.png",
              "hybrid": "https://100.98.142.94:5000/api/download/20250929_014648/test_user_001/hybrid/24_mineral-best_pt-hybrid.png"
            }
          },
          {
            "id": 1,
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
            "summary_images_url": "https://100.98.142.94:5000/api/download/20250929_014648/test_user_001/1-botol_mineral-best_pt-compare.png",
            "images": {
              "best": "https://100.98.142.94:5000/api/download/20250929_014648/test_user_001/best/1-botol_mineral-best_pt-best.png",
              "yolo": "https://100.98.142.94:5000/api/download/20250929_014648/test_user_001/yolo/1-botol_mineral-yolo11m-detection.png",
              "sam": "https://100.98.142.94:5000/api/download/20250929_014648/test_user_001/segmentasi/1-botol_mineral-best_pt-segmentation.png",
              "hybrid": "https://100.98.142.94:5000/api/download/20250929_014648/test_user_001/hybrid/1-botol_mineral-best_pt-hybrid.png"
            }
          },
          {
            "id": 2,
            "name": "21_mineral-best_pt-detection.json",
            "datas": [
              {
                "bbox": [
                  294.4569091796875,
                  308.8126220703125,
                  363.95751953125,
                  448.69964599609375
                ],
                "confidence": 0.8424936532974243,
                "class_id": 2,
                "class_name": "mineral"
              }
            ],
            "detection_count": 1,
            "summary_images_url": "https://100.98.142.94:5000/api/download/20250929_014648/test_user_001/21_mineral-best_pt-compare.png",
            "images": {
              "best": "https://100.98.142.94:5000/api/download/20250929_014648/test_user_001/best/21_mineral-best_pt-best.png",
              "yolo": "https://100.98.142.94:5000/api/download/20250929_014648/test_user_001/yolo/21_mineral-yolo11m-detection.png",
              "sam": "https://100.98.142.94:5000/api/download/20250929_014648/test_user_001/segmentasi/21_mineral-best_pt-segmentation.png",
              "hybrid": "https://100.98.142.94:5000/api/download/20250929_014648/test_user_001/hybrid/21_mineral-best_pt-hybrid.png"
            }
          },
          {
            "id": 3,
            "name": "244.mineral_crush-best_pt-detection.json",
            "datas": [
              {
                "bbox": [
                  252.4176788330078,
                  145.65257263183594,
                  394.87469482421875,
                  367.3836669921875
                ],
                "confidence": 0.9168100953102112,
                "class_id": 2,
                "class_name": "mineral"
              }
            ],
            "detection_count": 1,
            "summary_images_url": "https://100.98.142.94:5000/api/download/20250929_014648/test_user_001/244.mineral_crush-best_pt-compare.png",
            "images": {
              "best": "https://100.98.142.94:5000/api/download/20250929_014648/test_user_001/best/244.mineral_crush-best_pt-best.png",
              "yolo": "https://100.98.142.94:5000/api/download/20250929_014648/test_user_001/yolo/244.mineral_crush-yolo11m-detection.png",
              "sam": "https://100.98.142.94:5000/api/download/20250929_014648/test_user_001/segmentasi/244.mineral_crush-best_pt-segmentation.png",
              "hybrid": "https://100.98.142.94:5000/api/download/20250929_014648/test_user_001/hybrid/244.mineral_crush-best_pt-hybrid.png"
            }
          },
          {
            "id": 4,
            "name": "251.mineral-best_pt-detection.json",
            "datas": [
              {
                "bbox": [
                  250.2359619140625,
                  134.01632690429688,
                  333.72784423828125,
                  365.86627197265625
                ],
                "confidence": 0.8996686935424805,
                "class_id": 2,
                "class_name": "mineral"
              }
            ],
            "detection_count": 1,
            "summary_images_url": "https://100.98.142.94:5000/api/download/20250929_014648/test_user_001/251.mineral-best_pt-compare.png",
            "images": {
              "best": "https://100.98.142.94:5000/api/download/20250929_014648/test_user_001/best/251.mineral-best_pt-best.png",
              "yolo": "https://100.98.142.94:5000/api/download/20250929_014648/test_user_001/yolo/251.mineral-yolo11m-detection.png",
              "sam": "https://100.98.142.94:5000/api/download/20250929_014648/test_user_001/segmentasi/251.mineral-best_pt-segmentation.png",
              "hybrid": "https://100.98.142.94:5000/api/download/20250929_014648/test_user_001/hybrid/251.mineral-best_pt-hybrid.png"
            }
          }
        ],
        "class_summary": [
          {
            "class_name": "mineral",
            "count": 5
          },
          {
            "class_name": "not_empty",
            "count": 1
          }
        ],
        "object_count": 5,
    }],
  "session_id": "session_9eebbca5",
  "status": "completed",
  "timestamp": "20250928_135630",
  "user_id": "test_userrrr"
}
```