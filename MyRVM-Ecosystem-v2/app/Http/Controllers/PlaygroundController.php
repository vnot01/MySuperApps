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
        
        // Get GPU server info
        $gpuServerInfo = $this->getGpuServerInfo();
        
        // Get available models
        $availableModels = $this->getAvailableModels();

        return Inertia::render('Playground/Show', [
            'rvm' => $rvmInfo,
            'jetsonCameraInfo' => $jetsonCameraInfo,
            'gpuServerInfo' => $gpuServerInfo,
            'availableModels' => $availableModels,
        ]);
    }

    private function getJetsonCameraInfo($rvm)
    {
        if (!$rvm->ip_address) {
            return null;
        }

        try {
            $response = Http::timeout(5)->get("http://{$rvm->ip_address}:5000/api/hardware");
            
            if ($response->successful()) {
                $data = $response->json();
                return [
                    'cameras_available' => $data['hardware_info']['camera_info']['usb_cameras'] ?? [],
                    'nvargus_status' => $data['hardware_info']['camera_info']['nvargus_status'] ?? 'unknown',
                    'total_cameras' => count($data['hardware_info']['camera_info']['usb_cameras'] ?? []),
                    'camera_ready' => count($data['hardware_info']['camera_info']['usb_cameras'] ?? []) > 0,
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
}
