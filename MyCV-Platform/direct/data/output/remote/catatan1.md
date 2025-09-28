Sekarang kita edit app.py
untuk endpoint GET /api/results/<session_id>:
Anda kan tau alur dari ./direct/app/api-hybrid-detection/app.py ==> upload images ==> meletakan images tersebut ke folder ./input/remote ==> make directory sesuai dengan format (timestamp/userid).
kemudian memerintahkan script ./direct/run_api_hybrid_detection.py untuk melakukan detection ==> APAKAH yang di deteksi sudah folder yang baru saja di buat berdasarkan timestamp/userid tersebut?

-----
Karena ada Error:
[Error] Failed to load resource: the server responded with a status of 404 (NOT FOUND) (54.mineral_filled-best_pt-compare.png, line 0)
[Error] Failed to load resource: the server responded with a status of 404 (NOT FOUND) (21.mineral-best_pt-compare.png, line 0)
[Error] Failed to load resource: the server responded with a status of 404 (NOT FOUND) (71.non_mineral-best_pt-compare.png, line 0)
> Selected Element
< <div class="frame-container">…</div>
< <img src="http://100.98.142.94:5000/api/download/unknown/54.mineral_filled-best_pt-compare.png" class="frame-image" alt="54.mineral_filled-best_pt-compare.png">



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
      "images": {
        "best": "https://100.98.142.94:5000/api/download/20250928_172931/test_user_001/best/1-botol_mineral-best_pt-best.png",
        "yolo": "https://100.98.142.94:5000/api/download/20250928_172931/test_user_001/yolo/1-botol_mineral-yolo11m-detection.png",
        "sam": "https://100.98.142.94:5000/api/download/20250928_172931/test_user_001/segmentasi/1-botol_mineral-best_pt-segmentation.png",
        "hybrid": "https://100.98.142.94:5000/api/download/20250928_172931/test_user_001/hybrid/1-botol_mineral-best_pt-hybrid.png"
      },
      "object_count": 1,
      "detection_count": 1,
      "images_url": "https://100.98.142.94:5000/api/download/20250928_172931/test_user_001/1-botol_mineral-best_pt-best.png"
    },
    {
      "id": 1,
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
      "images": {
        "best": "https://100.98.142.94:5000/api/download/20250928_172931/test_user_001/best/21_mineral-best_pt-best.png",
        "yolo": "https://100.98.142.94:5000/api/download/20250928_172931/test_user_001/yolo/21_mineral-yolo11m-detection.png",
        "sam": "https://100.98.142.94:5000/api/download/20250928_172931/test_user_001/segmentasi/21_mineral-best_pt-segmentation.png",
        "hybrid": "https://100.98.142.94:5000/api/download/20250928_172931/test_user_001/hybrid/21_mineral-best_pt-hybrid.png"
      },
      "object_count": 1,
      "detection_count": 1,
      "images_url": "https://100.98.142.94:5000/api/download/20250928_172931/test_user_001/21_mineral-best_pt-best.png"
    }
  ],
  "session_id": "session_20250928_172931_test_user_001",
  "status": "completed",
  "timestamp": "20250928_172931",
  "user_id": "test_user_001"
}
```