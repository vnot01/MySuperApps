# 🔧 Streaming Optimization - Implementation Code

## File 1: Show.vue - Optimized Streaming

### Changes Required:

#### Location: Line 660-661
```javascript
// BEFORE:
// Update stream every 2 seconds for smoother experience
streamInterval.value = setInterval(updateStream, 2000)

// AFTER:
// Update stream every 500ms for much smoother experience (4x faster)
streamInterval.value = setInterval(updateStream, 500)
```

### Additional Optimizations:

#### Add Request Throttling (Add after line 474):
```javascript
// Add new reactive variables for optimization
const isUpdating = ref(false)
const streamFPS = ref(0)
const lastUpdateTime = ref(Date.now())
```

#### Update startLiveStream function (Replace lines 632-662):
```javascript
// Live streaming methods - OPTIMIZED
const startLiveStream = () => {
  if (!selectedCameraId.value) return
  
  console.log('🎬 Starting optimized live stream for camera:', selectedCameraId.value)
  isStreaming.value = true
  lastUpdateTime.value = Date.now()
  
  // Optimized update stream with throttling and FPS counter
  const updateStream = async () => {
    if (isUpdating.value) return // Skip if already updating
    
    isUpdating.value = true
    const startTime = Date.now()
    
    try {
      const response = await fetch(`http://${props.rvm.ip_address}:5000/api/cameras/${selectedCameraId.value}/capture/base64`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' }
      })
      
      if (response.ok) {
        const data = await response.json()
        if (data.success && data.image_base64) {
          streamUrl.value = `data:image/jpeg;base64,${data.image_base64}`
          
          // Calculate FPS
          const elapsed = Date.now() - lastUpdateTime.value
          streamFPS.value = Math.round(1000 / elapsed)
          lastUpdateTime.value = Date.now()
          
          console.log(`📊 Stream FPS: ${streamFPS.value} | Latency: ${Date.now() - startTime}ms`)
        }
      }
    } catch (error) {
      console.error('❌ Stream update error:', error)
    } finally {
      isUpdating.value = false
    }
  }
  
  // Initial stream update
  updateStream()
  
  // Update stream every 500ms (2 FPS)
  streamInterval.value = setInterval(updateStream, 500)
}
```

## File 2: app.py (Jetson API) - MJPEG Streaming

### Add New Endpoint (Add before line 1003):
```python
@app.route('/api/cameras/<camera_id>/stream/mjpeg')
def camera_mjpeg_stream(camera_id):
    """MJPEG stream for real-time video - Professional quality streaming"""
    def generate_frames():
        camera_service = get_camera_service()
        if not camera_service.is_initialized:
            camera_service.initialize()
        
        frame_count = 0
        start_time = time.time()
        
        while True:
            try:
                success, frame_base64 = camera_service.capture_image(camera_id, save_path=None)
                
                if success:
                    # Decode base64 to image bytes
                    import base64
                    image_data = base64.b64decode(frame_base64)
                    
                    # Yield frame in MJPEG format
                    yield (b'--frame\r\n'
                           b'Content-Type: image/jpeg\r\n\r\n' + 
                           image_data + b'\r\n')
                    
                    frame_count += 1
                    
                    # Log FPS every 30 frames
                    if frame_count % 30 == 0:
                        elapsed = time.time() - start_time
                        fps = frame_count / elapsed
                        print(f"📊 MJPEG Stream FPS: {fps:.1f}")
                    
                # Control frame rate (~30 FPS)
                time.sleep(0.033)
                
            except Exception as e:
                print(f"❌ Stream error: {e}")
                break

    return Response(
        generate_frames(), 
        mimetype='multipart/x-mixed-replace; boundary=frame',
        headers={
            'Cache-Control': 'no-cache, no-store, must-revalidate',
            'Pragma': 'no-cache',
            'Expires': '0',
            'Connection': 'keep-alive'
        }
    )
```

## File 3: PlaygroundController.php - MJPEG Proxy

### Add New Method (Add after line 451):
```php
/**
 * Stream MJPEG video from camera
 */
public function streamMjpeg(Request $request, $rvmId, $cameraId)
{
    $rvm = ReverseVendingMachine::findOrFail($rvmId);
    
    if (!$rvm->ip_address) {
        return response()->json(['error' => 'RVM IP address not configured'], 400);
    }

    try {
        // Stream MJPEG directly from Jetson
        $streamUrl = "http://{$rvm->ip_address}:5000/api/cameras/{$cameraId}/stream/mjpeg";
        
        // Set headers for streaming
        $headers = [
            'Content-Type' => 'multipart/x-mixed-replace; boundary=frame',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
            'Connection' => 'keep-alive'
        ];
        
        // Stream response
        return response()->stream(function () use ($streamUrl) {
            $ch = curl_init($streamUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_WRITEFUNCTION, function($curl, $data) {
                echo $data;
                flush();
                return strlen($data);
            });
            curl_exec($ch);
            curl_close($ch);
        }, 200, $headers);
        
    } catch (\Exception $e) {
        \Log::error("Failed to stream MJPEG from camera {$cameraId} for RVM {$rvmId}: " . $e->getMessage());
        return response()->json(['error' => 'Failed to stream video'], 500);
    }
}
```

## File 4: web.php - Add MJPEG Route

### Add Route (Add after line 38):
```php
Route::get('/playground/{rvm}/cameras/{cameraId}/stream/mjpeg', [PlaygroundController::class, 'streamMjpeg'])->name('playground.cameras.stream.mjpeg');
```

## Usage in Frontend (Alternative to Base64):

### Option A: Use MJPEG Stream (Recommended for best performance)
```html
<!-- Replace existing <img> with MJPEG stream -->
<img 
  :src="`/playground/${props.rvm.id}/cameras/${selectedCameraId}/stream/mjpeg`" 
  alt="Live Camera Feed"
  class="w-full h-full object-cover"
  @error="handleStreamError"
/>
```

### Option B: Keep Base64 but Optimized (Current approach, just faster)
```javascript
// Just change interval from 2000 to 500
streamInterval.value = setInterval(updateStream, 500)
```

## Testing:

### Test Optimized Base64:
```bash
# Should see updates every 500ms
# Check browser console for FPS counter
```

### Test MJPEG Stream:
```bash
# Test direct access
curl http://100.117.234.2:5000/api/cameras/0/stream/mjpeg

# Should see continuous stream
```

## Expected Performance:

### Phase 1 (Optimized Base64):
- Latency: 500ms
- FPS: 2
- Bandwidth: Same as before
- Implementation: 5 minutes

### Phase 2 (MJPEG Stream):
- Latency: 33-100ms
- FPS: 10-30
- Bandwidth: More efficient
- Implementation: 1-2 hours

## Rollout Plan:

1. ✅ Implement Phase 1 (Quick Fix) - NOW
2. ✅ Test and monitor performance
3. ✅ Push to GitHub
4. ⏳ Implement Phase 2 (MJPEG) - Next
5. ⏳ A/B test both approaches
6. ⏳ Choose best solution for production

---
Created: 2025-10-05
Status: Ready for Implementation
