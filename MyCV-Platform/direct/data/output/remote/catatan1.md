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
      "detection_count": 2,
      "detections": [
        {
          "id": 0,
          "images_url": "https://100.98.142.94:5000/20250928_152449/web_user/best/21.mineral-best_pt-best.png",
          "name": "nama_images-model-detection.json",
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
          "object_count": 2
        },
        {
          "id": 1,
          "name": "nama_images-model-detection.json",
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
          "object_count": 1
        }
      ]
    }
  ],
  "session_id": "session_9eebbca5",
  "status": "completed",
  "timestamp": "20250928_135630",
  "user_id": "test_userrrr"
}
```