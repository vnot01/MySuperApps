<?php

namespace App\Http\Controllers;

use App\Models\ReverseVendingMachine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Inertia\Inertia;

class PlaygroundController extends Controller
{
    public function show($rvmId)
    {
        $rvm = ReverseVendingMachine::findOrFail($rvmId);
        
        // Get RVM information
        $rvmInfo = [
            'id' => $rvm->id,
            'name' => $rvm->name,
            'location' => $rvm->location,
            'ip_address' => $rvm->ip_address,
            'status' => $rvm->status,
        ];

        // Get Jetson camera info
        $jetsonCameraInfo = $this->getJetsonCameraInfo($rvm);
        
        // Get Jetson camera status
        $jetsonCameraStatus = $this->getJetsonCameraStatus($rvm);
        
        // Get GPU server info
        $gpuServerInfo = $this->getGpuServerInfo();
        
        // Get available models
        $availableModels = $this->getAvailableModels();

        return Inertia::render('Playground/Show', [
            'rvm' => $rvmInfo,
            'jetsonCameraInfo' => $jetsonCameraInfo,
            'jetsonCameraStatus' => $jetsonCameraStatus,
            'gpuServerInfo' => $gpuServerInfo,
            'availableModels' => $availableModels,
        ]);
    }

    private function getJetsonCameraInfo($rvm)
    {
        if (!$rvm->ip_address) {
            return [
                'cameras_available' => [],
                'nvargus_status' => 'unknown',
                'total_cameras' => 0,
                'camera_ready' => false,
                'system_info' => [],
                'jetson_status' => 'offline',
                'last_updated' => now()->toISOString(),
            ];
        }

        try {
            // Get comprehensive camera discovery
            $response = Http::timeout(5)->get("http://{$rvm->ip_address}:5000/api/cameras/discovery");
            
            if ($response->successful()) {
                $data = $response->json();
                $discovery = $data['discovery'] ?? [];
                $remoteInfo = $discovery['remote_info'] ?? [];
                $simpleStatus = $discovery['simple_status'] ?? [];
                
                // Extract camera information
                $cameras = [];
                if (isset($remoteInfo['cameras'])) {
                    foreach ($remoteInfo['cameras'] as $camera) {
                        $cameras[] = [
                            'id' => $camera['id'],
                            'name' => $camera['device_name'],
                            'path' => $camera['device_path'],
                            'status' => $camera['status'],
                            'is_streaming' => $camera['is_streaming'],
                            'remote_ready' => $camera['remote_ready']
                        ];
                    }
                }
                
                return [
                    'cameras_available' => $cameras,
                    'nvargus_status' => 'active', // Assume active if cameras found
                    'total_cameras' => $remoteInfo['total_cameras'] ?? 0,
                    'camera_ready' => ($remoteInfo['total_cameras'] ?? 0) > 0,
                    'active_cameras' => $remoteInfo['active_cameras'] ?? 0,
                    'service_status' => $remoteInfo['service_status'] ?? 'unknown',
                    'remote_capable' => $remoteInfo['remote_capable'] ?? false,
                    'system_info' => [
                        'cpu_info' => [],
                        'memory_info' => [],
                        'disk_info' => [],
                        'gpu_info' => [],
                    ],
                    'jetson_status' => 'online',
                    'last_updated' => now()->toISOString(),
                ];
            }
            
            // Fallback to dashboard endpoint
            $response = Http::timeout(5)->get("http://{$rvm->ip_address}:5000/api/cameras/dashboard");
            
            if ($response->successful()) {
                $data = $response->json();
                $cameraInfo = $data['camera_info'] ?? [];
                $cameras = $cameraInfo['cameras'] ?? [];
                
                return [
                    'cameras_available' => $cameras,
                    'nvargus_status' => 'active',
                    'total_cameras' => $cameraInfo['total_cameras'] ?? 0,
                    'camera_ready' => ($cameraInfo['total_cameras'] ?? 0) > 0,
                    'active_cameras' => $cameraInfo['active_cameras'] ?? 0,
                    'service_status' => $cameraInfo['service_status'] ?? 'unknown',
                    'remote_capable' => $cameraInfo['remote_capable'] ?? false,
                    'system_info' => [
                        'cpu_info' => [],
                        'memory_info' => [],
                        'disk_info' => [],
                        'gpu_info' => [],
                    ],
                    'jetson_status' => 'online',
                    'last_updated' => now()->toISOString(),
                ];
            }
            
            // Fallback to hardware endpoint
            $response = Http::timeout(5)->get("http://{$rvm->ip_address}:5000/api/hardware");
            
            if ($response->successful()) {
                $data = $response->json();
                $hardwareInfo = $data['hardware_info'] ?? [];
                $cameraInfo = $hardwareInfo['camera_info'] ?? [];
                
                return [
                    'cameras_available' => $cameraInfo['usb_cameras'] ?? [],
                    'nvargus_status' => $cameraInfo['nvargus_status'] ?? 'unknown',
                    'total_cameras' => count($cameraInfo['usb_cameras'] ?? []),
                    'camera_ready' => count($cameraInfo['usb_cameras'] ?? []) > 0,
                    'active_cameras' => count($cameraInfo['usb_cameras'] ?? []),
                    'service_status' => 'unknown',
                    'remote_capable' => false,
                    'system_info' => [
                        'cpu_info' => $hardwareInfo['cpu_info'] ?? [],
                        'memory_info' => $hardwareInfo['memory_info'] ?? [],
                        'disk_info' => $hardwareInfo['disk_info'] ?? [],
                        'gpu_info' => $hardwareInfo['gpu_info'] ?? [],
                    ],
                    'jetson_status' => 'online',
                    'last_updated' => now()->toISOString(),
                ];
            }
        } catch (\Exception $e) {
            \Log::error("Failed to get Jetson camera info for RVM {$rvm->id}: " . $e->getMessage());
        }

        return [
            'cameras_available' => [],
            'nvargus_status' => 'unknown',
            'total_cameras' => 0,
            'camera_ready' => false,
            'active_cameras' => 0,
            'service_status' => 'offline',
            'remote_capable' => false,
            'system_info' => [],
            'jetson_status' => 'offline',
            'last_updated' => now()->toISOString(),
        ];
    }

    private function getJetsonCameraStatus($rvm)
    {
        if (!$rvm->ip_address) {
            return null;
        }

        try {
            $response = Http::timeout(5)->get("http://{$rvm->ip_address}:5000/api/camera/status");
            
            if ($response->successful()) {
                $data = $response->json();
                return [
                    'camera_available' => $data['camera_available'] ?? false,
                    'camera_initialized' => $data['camera_initialized'] ?? false,
                    'camera_streaming' => $data['camera_streaming'] ?? false,
                    'timestamp' => $data['timestamp'] ?? now()->toISOString(),
                ];
            }
        } catch (\Exception $e) {
            \Log::error("Failed to get Jetson camera status for RVM {$rvm->id}: " . $e->getMessage());
        }

        return [
            'camera_available' => false,
            'camera_initialized' => false,
            'camera_streaming' => false,
            'timestamp' => now()->toISOString(),
        ];
    }

    private function getGpuServerInfo()
    {
        try {
            $response = Http::timeout(5)->get('http://100.98.142.94:5000/api/status');
            
            if ($response->successful()) {
                $data = $response->json();
                return [
                    'gpu_available' => $data['gpu_info']['cuda_available'] ?? false,
                    'gpu_name' => $data['gpu_info']['gpus'][0]['name'] ?? 'Unknown',
                    'gpu_memory' => $data['gpu_info']['gpus'][0]['memory_gb'] ?? 0,
                    'cuda_version' => $data['gpu_info']['pytorch_cuda_version'] ?? 'Unknown',
                    'server_status' => 'online',
                    'server_url' => 'http://100.98.142.94:5000',
                    'available_for_all_jetsons' => true,
                    'last_updated' => now()->toISOString(),
                ];
            }
        } catch (\Exception $e) {
            \Log::error("Failed to get GPU server info: " . $e->getMessage());
        }

        return [
            'gpu_available' => false,
            'gpu_name' => 'Unknown',
            'gpu_memory' => 0,
            'cuda_version' => 'Unknown',
            'server_status' => 'offline',
            'server_url' => 'http://100.98.142.94:5000',
            'available_for_all_jetsons' => true,
            'last_updated' => now()->toISOString(),
        ];
    }

    private function getAvailableModels()
    {
        return [
            [
                'id' => 'yolo_v8n',
                'name' => 'YOLO v8 Nano',
                'type' => 'detection',
                'size' => '6.2MB',
                'description' => 'Fastest YOLO model for real-time detection',
                'status' => 'available'
            ],
            [
                'id' => 'yolo_v8s',
                'name' => 'YOLO v8 Small',
                'type' => 'detection',
                'size' => '21.5MB',
                'description' => 'Balanced speed and accuracy',
                'status' => 'available'
            ],
            [
                'id' => 'yolo_v8m',
                'name' => 'YOLO v8 Medium',
                'type' => 'detection',
                'size' => '49.7MB',
                'description' => 'Higher accuracy for complex scenes',
                'status' => 'available'
            ],
            [
                'id' => 'sam2_hiera_large',
                'name' => 'SAM2 Hiera Large',
                'type' => 'segmentation',
                'size' => '2.4GB',
                'description' => 'Advanced segmentation with SAM2',
                'status' => 'available'
            ],
            [
                'id' => 'custom_model',
                'name' => 'Custom Model',
                'type' => 'custom',
                'size' => 'Variable',
                'description' => 'Upload your own trained model',
                'status' => 'upload_required'
            ]
        ];
    }

    /**
     * Get camera dashboard info
     */
    public function getCameraDashboard($rvmId)
    {
        $rvm = ReverseVendingMachine::findOrFail($rvmId);
        
        if (!$rvm->ip_address) {
            return response()->json(['error' => 'RVM IP address not configured'], 400);
        }

        try {
            $response = Http::timeout(5)->get("http://{$rvm->ip_address}:5000/api/cameras/dashboard");
            
            if ($response->successful()) {
                return response()->json($response->json());
            }
        } catch (\Exception $e) {
            \Log::error("Failed to get camera dashboard for RVM {$rvmId}: " . $e->getMessage());
        }

        return response()->json(['error' => 'Failed to get camera dashboard'], 500);
    }

    /**
     * Get remote camera info
     */
    public function getRemoteCameraInfo($rvmId)
    {
        $rvm = ReverseVendingMachine::findOrFail($rvmId);
        
        if (!$rvm->ip_address) {
            return response()->json(['error' => 'RVM IP address not configured'], 400);
        }

        try {
            $response = Http::timeout(5)->get("http://{$rvm->ip_address}:5000/api/cameras/remote");
            
            if ($response->successful()) {
                return response()->json($response->json());
            }
        } catch (\Exception $e) {
            \Log::error("Failed to get remote camera info for RVM {$rvmId}: " . $e->getMessage());
        }

        return response()->json(['error' => 'Failed to get remote camera info'], 500);
    }

    /**
     * Get simple camera status
     */
    public function getSimpleCameraStatus($rvmId)
    {
        $rvm = ReverseVendingMachine::findOrFail($rvmId);
        
        if (!$rvm->ip_address) {
            return response()->json(['error' => 'RVM IP address not configured'], 400);
        }

        try {
            $response = Http::timeout(5)->get("http://{$rvm->ip_address}:5000/api/cameras/status/simple");
            
            if ($response->successful()) {
                return response()->json($response->json());
            }
        } catch (\Exception $e) {
            \Log::error("Failed to get simple camera status for RVM {$rvmId}: " . $e->getMessage());
        }

        return response()->json(['error' => 'Failed to get simple camera status'], 500);
    }

    /**
     * Get camera discovery
     */
    public function getCameraDiscovery($rvmId)
    {
        $rvm = ReverseVendingMachine::findOrFail($rvmId);
        
        if (!$rvm->ip_address) {
            return response()->json(['error' => 'RVM IP address not configured'], 400);
        }

        try {
            $response = Http::timeout(5)->get("http://{$rvm->ip_address}:5000/api/cameras/discovery");
            
            if ($response->successful()) {
                return response()->json($response->json());
            }
        } catch (\Exception $e) {
            \Log::error("Failed to get camera discovery for RVM {$rvmId}: " . $e->getMessage());
        }

        return response()->json(['error' => 'Failed to get camera discovery'], 500);
    }

    /**
     * Start camera
     */
    public function startCamera(Request $request, $rvmId, $cameraId)
    {
        $rvm = ReverseVendingMachine::findOrFail($rvmId);
        
        if (!$rvm->ip_address) {
            return response()->json(['error' => 'RVM IP address not configured'], 400);
        }

        try {
            $response = Http::timeout(10)->post("http://{$rvm->ip_address}:5000/api/cameras/{$cameraId}/start");
            
            if ($response->successful()) {
                return response()->json($response->json());
            }
        } catch (\Exception $e) {
            \Log::error("Failed to start camera {$cameraId} for RVM {$rvmId}: " . $e->getMessage());
        }

        return response()->json(['error' => 'Failed to start camera'], 500);
    }

    /**
     * Capture image from camera
     */
    public function captureImage(Request $request, $rvmId, $cameraId)
    {
        $rvm = ReverseVendingMachine::findOrFail($rvmId);
        
        if (!$rvm->ip_address) {
            return response()->json(['error' => 'RVM IP address not configured'], 400);
        }

        try {
            $response = Http::timeout(15)->post("http://{$rvm->ip_address}:5000/api/cameras/{$cameraId}/capture");
            
            if ($response->successful()) {
                return response()->json($response->json());
            }
        } catch (\Exception $e) {
            \Log::error("Failed to capture image from camera {$cameraId} for RVM {$rvmId}: " . $e->getMessage());
        }

        return response()->json(['error' => 'Failed to capture image'], 500);
    }

    /**
     * Capture image as base64
     */
    public function captureImageBase64(Request $request, $rvmId, $cameraId)
    {
        $rvm = ReverseVendingMachine::findOrFail($rvmId);
        
        if (!$rvm->ip_address) {
            return response()->json(['error' => 'RVM IP address not configured'], 400);
        }

        try {
            $response = Http::timeout(15)->post("http://{$rvm->ip_address}:5000/api/cameras/{$cameraId}/capture/base64");
            
            if ($response->successful()) {
                return response()->json($response->json());
            }
        } catch (\Exception $e) {
            \Log::error("Failed to capture base64 image from camera {$cameraId} for RVM {$rvmId}: " . $e->getMessage());
        }

        return response()->json(['error' => 'Failed to capture base64 image'], 500);
    }


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

    /**
     * Capture and save image to storage
     */
    public function captureAndSaveImage(Request $request, $rvmId, $cameraId)
    {
        $rvm = ReverseVendingMachine::findOrFail($rvmId);
        
        if (!$rvm->ip_address) {
            return response()->json(['error' => 'RVM IP address not configured'], 400);
        }

        try {
            // Create directory structure: storage/app/public/playground/{rvm_id}/{timestamp}
            $timestamp = now()->format('Y-m-d_H-i-s');
            $storagePath = "playground/{$rvmId}/{$timestamp}";
            $fullPath = storage_path("app/public/{$storagePath}");
            
            if (!file_exists($fullPath)) {
                if (!mkdir($fullPath, 0755, true)) {
                    \Log::error("Failed to create directory: {$fullPath}");
                    return response()->json(['error' => 'Failed to create storage directory'], 500);
                }
                // Set proper permissions
                chmod($fullPath, 0755);
            }

            // Request Jetson to capture image as base64
            $filename = "camera_{$cameraId}_capture.jpg";
            $finalPath = "{$fullPath}/{$filename}";
            
            $response = Http::timeout(15)->post("http://{$rvm->ip_address}:5000/api/cameras/{$cameraId}/capture/base64");
            
            if ($response->successful()) {
                $data = $response->json();
                
                if ($data['success'] && isset($data['image_base64'])) {
                    // Decode base64 and save to Laravel storage
                    $imageData = base64_decode($data['image_base64']);
                    file_put_contents($finalPath, $imageData);
                    
                    if (file_exists($finalPath)) {
                        // Create symlink if it doesn't exist
                        $this->ensureStorageSymlink();
                        
                        // Return public URL
                        $publicUrl = asset("storage/{$storagePath}/{$filename}");
                        
                        return response()->json([
                            'success' => true,
                            'message' => 'Image captured and saved successfully',
                            'camera_id' => $cameraId,
                            'file_path' => $finalPath,
                            'public_url' => $publicUrl,
                            'storage_path' => "{$storagePath}/{$filename}",
                            'timestamp' => $timestamp
                        ]);
                    } else {
                        \Log::error("Failed to save image to final path: {$finalPath}");
                        return response()->json(['error' => 'Failed to save image to storage'], 500);
                    }
                } else {
                    \Log::error("Jetson capture failed: " . json_encode($data));
                    return response()->json(['error' => 'Failed to capture image: ' . ($data['error'] ?? 'Unknown error')], 500);
                }
            } else {
                \Log::error("Jetson API request failed: " . $response->body());
                return response()->json(['error' => 'Failed to communicate with Jetson camera'], 500);
            }
        } catch (\Exception $e) {
            \Log::error("Failed to capture and save image from camera {$cameraId} for RVM {$rvmId}: " . $e->getMessage());
        }

        return response()->json(['error' => 'Failed to capture and save image'], 500);
    }

    /**
     * Ensure storage symlink exists
     */
    private function ensureStorageSymlink()
    {
        $link = public_path('storage');
        $target = storage_path('app/public');
        
        if (!file_exists($link)) {
            try {
                symlink($target, $link);
            } catch (\Exception $e) {
                \Log::error("Failed to create storage symlink: " . $e->getMessage());
            }
        }
    }

    /**
     * Process image with Computer Vision models
     */
    public function processCVImage(Request $request)
    {
        try {
            $data = $request->validate([
                'image' => 'required|string',
                'model' => 'required|string',
                'rvm_id' => 'required|integer',
                'timestamp' => 'nullable|string'
            ]);

            $rvm = ReverseVendingMachine::findOrFail($data['rvm_id']);
            
            if (!$rvm->ip_address) {
                return response()->json(['error' => 'RVM IP address not configured'], 400);
            }

            // Forward request to Jetson API
            $response = Http::timeout(30)->post("http://{$rvm->ip_address}:5000/api/cv/process", [
                'image' => $data['image'],
                'model' => $data['model'],
                'rvm_id' => $data['rvm_id'],
                'timestamp' => $data['timestamp'] ?? now()->toISOString()
            ]);

            if ($response->successful()) {
                $results = $response->json();
                
                // Log the processing
                \Log::info("CV processing completed for RVM {$data['rvm_id']} with model {$data['model']}: {$results['detections']} objects detected");
                
                return response()->json($results);
            } else {
                \Log::error("CV processing failed for RVM {$data['rvm_id']}: " . $response->body());
                return response()->json(['error' => 'CV processing failed'], 500);
            }

        } catch (\Exception $e) {
            \Log::error("CV processing error: " . $e->getMessage());
            return response()->json(['error' => 'CV processing failed: ' . $e->getMessage()], 500);
        }
    }
}
