<?php

namespace App\Services;

use App\Models\ReverseVendingMachine;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class JetsonIntegrationService
{
    /**
     * Default Jetson API endpoints
     */
    const JETSON_API_ENDPOINTS = [
        'health' => '/api/health',
        'status' => '/api/status',
        'hardware' => '/api/hardware'
    ];

    /**
     * Default CV Server API endpoints
     */
    const CV_SERVER_API_ENDPOINTS = [
        'health' => '/api/health',
        'status' => '/api/status'
    ];

    /**
     * Check Jetson health and update RVM data
     */
    public function checkJetsonHealth(ReverseVendingMachine $rvm): array
    {
        $jetsonIp = $rvm->jetson_ip ?? '100.117.234.2';
        $jetsonPort = $rvm->jetson_port ?? 5000;
        
        $result = [
            'success' => false,
            'jetson_status' => 'disconnected',
            'health_data' => null,
            'gpu_info' => null,
            'hardware_info' => null,
            'response_time' => null,
            'error' => null
        ];

        try {
            // Check health endpoint
            $healthUrl = "http://{$jetsonIp}:{$jetsonPort}" . self::JETSON_API_ENDPOINTS['health'];
            $healthResponse = Http::timeout(10)->get($healthUrl);
            
            if ($healthResponse->successful()) {
                $result['success'] = true;
                $result['jetson_status'] = 'connected';
                $result['health_data'] = $healthResponse->json();
                $result['response_time'] = $healthResponse->transferStats->getHandlerStat('total_time') ?? null;
                
                // Get additional data from status and hardware endpoints
                $this->getJetsonAdditionalData($jetsonIp, $jetsonPort, $result);
                
                // Update RVM with Jetson data
                $this->updateRvmWithJetsonData($rvm, $result);
                
            } else {
                $result['error'] = "Jetson health check failed with status: " . $healthResponse->status();
                $this->updateRvmWithJetsonData($rvm, $result);
            }
            
        } catch (\Exception $e) {
            $result['error'] = "Jetson connection failed: " . $e->getMessage();
            Log::error("Jetson health check failed for RVM {$rvm->id}", [
                'rvm_id' => $rvm->id,
                'jetson_ip' => $jetsonIp,
                'jetson_port' => $jetsonPort,
                'error' => $e->getMessage()
            ]);
            $this->updateRvmWithJetsonData($rvm, $result);
        }

        return $result;
    }

    /**
     * Check CV Server health and update RVM data
     */
    public function checkCvServerHealth(ReverseVendingMachine $rvm): array
    {
        $cvServerIp = $rvm->cv_server_ip ?? '100.98.142.94';
        $cvServerPort = $rvm->cv_server_port ?? 5000;
        
        $result = [
            'success' => false,
            'cv_server_status' => 'disconnected',
            'health_data' => null,
            'response_time' => null,
            'error' => null
        ];

        try {
            // Check health endpoint
            $healthUrl = "http://{$cvServerIp}:{$cvServerPort}" . self::CV_SERVER_API_ENDPOINTS['health'];
            $healthResponse = Http::timeout(10)->get($healthUrl);
            
            if ($healthResponse->successful()) {
                $result['success'] = true;
                $result['cv_server_status'] = 'connected';
                $result['health_data'] = $healthResponse->json();
                $result['response_time'] = $healthResponse->transferStats->getHandlerStat('total_time') ?? null;
                
                // Update RVM with CV Server data
                $this->updateRvmWithCvServerData($rvm, $result);
                
            } else {
                $result['error'] = "CV Server health check failed with status: " . $healthResponse->status();
                $this->updateRvmWithCvServerData($rvm, $result);
            }
            
        } catch (\Exception $e) {
            $result['error'] = "CV Server connection failed: " . $e->getMessage();
            Log::error("CV Server health check failed for RVM {$rvm->id}", [
                'rvm_id' => $rvm->id,
                'cv_server_ip' => $cvServerIp,
                'cv_server_port' => $cvServerPort,
                'error' => $e->getMessage()
            ]);
            $this->updateRvmWithCvServerData($rvm, $result);
        }

        return $result;
    }

    /**
     * Get additional data from Jetson status and hardware endpoints
     */
    private function getJetsonAdditionalData(string $jetsonIp, int $jetsonPort, array &$result): void
    {
        try {
            // Get status data (includes GPU info)
            $statusUrl = "http://{$jetsonIp}:{$jetsonPort}" . self::JETSON_API_ENDPOINTS['status'];
            $statusResponse = Http::timeout(5)->get($statusUrl);
            
            if ($statusResponse->successful()) {
                $statusData = $statusResponse->json();
                $result['gpu_info'] = $statusData['gpu_info'] ?? null;
            }
            
            // Get hardware data
            $hardwareUrl = "http://{$jetsonIp}:{$jetsonPort}" . self::JETSON_API_ENDPOINTS['hardware'];
            $hardwareResponse = Http::timeout(5)->get($hardwareUrl);
            
            if ($hardwareResponse->successful()) {
                $result['hardware_info'] = $hardwareResponse->json();
            }
            
        } catch (\Exception $e) {
            Log::warning("Failed to get additional Jetson data", [
                'jetson_ip' => $jetsonIp,
                'jetson_port' => $jetsonPort,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Update RVM with Jetson data
     */
    private function updateRvmWithJetsonData(ReverseVendingMachine $rvm, array $result): void
    {
        $updateData = [
            'jetson_status' => $result['jetson_status'],
            'last_jetson_ping' => now(),
            'jetson_health_data' => $result['health_data'],
            'jetson_gpu_info' => $result['gpu_info'],
            'jetson_hardware_info' => $result['hardware_info']
        ];

        // Set default IP if not set
        if (!$rvm->jetson_ip) {
            $updateData['jetson_ip'] = '100.117.234.2';
        }
        if (!$rvm->jetson_port) {
            $updateData['jetson_port'] = 5000;
        }

        $rvm->update($updateData);
    }

    /**
     * Update RVM with CV Server data
     */
    private function updateRvmWithCvServerData(ReverseVendingMachine $rvm, array $result): void
    {
        $updateData = [
            'cv_server_status' => $result['cv_server_status'],
            'last_cv_server_ping' => now(),
            'cv_server_health_data' => $result['health_data']
        ];

        // Set default IP if not set
        if (!$rvm->cv_server_ip) {
            $updateData['cv_server_ip'] = '100.98.142.94';
        }
        if (!$rvm->cv_server_port) {
            $updateData['cv_server_port'] = 5000;
        }

        $rvm->update($updateData);
    }

    /**
     * Get comprehensive health status for RVM
     */
    public function getRvmHealthStatus(ReverseVendingMachine $rvm): array
    {
        $jetsonHealth = $this->checkJetsonHealth($rvm);
        $cvServerHealth = $this->checkCvServerHealth($rvm);
        
        // Determine overall connection status
        $overallStatus = 'disconnected';
        if ($jetsonHealth['success'] && $cvServerHealth['success']) {
            $overallStatus = 'connected';
        } elseif ($jetsonHealth['success'] || $cvServerHealth['success']) {
            $overallStatus = 'partial';
        }

        return [
            'overall_status' => $overallStatus,
            'jetson' => $jetsonHealth,
            'cv_server' => $cvServerHealth,
            'last_checked' => now()->toISOString()
        ];
    }

    /**
     * Get Jetson GPU information
     */
    public function getJetsonGpuInfo(ReverseVendingMachine $rvm): ?array
    {
        $jetsonIp = $rvm->jetson_ip ?? '100.117.234.2';
        $jetsonPort = $rvm->jetson_port ?? 5000;
        
        try {
            $statusUrl = "http://{$jetsonIp}:{$jetsonPort}" . self::JETSON_API_ENDPOINTS['status'];
            $response = Http::timeout(5)->get($statusUrl);
            
            if ($response->successful()) {
                $data = $response->json();
                return $data['gpu_info'] ?? null;
            }
        } catch (\Exception $e) {
            Log::error("Failed to get Jetson GPU info", [
                'rvm_id' => $rvm->id,
                'jetson_ip' => $jetsonIp,
                'jetson_port' => $jetsonPort,
                'error' => $e->getMessage()
            ]);
        }

        return null;
    }

    /**
     * Get Jetson hardware information
     */
    public function getJetsonHardwareInfo(ReverseVendingMachine $rvm): ?array
    {
        $jetsonIp = $rvm->jetson_ip ?? '100.117.234.2';
        $jetsonPort = $rvm->jetson_port ?? 5000;
        
        try {
            $hardwareUrl = "http://{$jetsonIp}:{$jetsonPort}" . self::JETSON_API_ENDPOINTS['hardware'];
            $response = Http::timeout(5)->get($hardwareUrl);
            
            if ($response->successful()) {
                return $response->json();
            }
        } catch (\Exception $e) {
            Log::error("Failed to get Jetson hardware info", [
                'rvm_id' => $rvm->id,
                'jetson_ip' => $jetsonIp,
                'jetson_port' => $jetsonPort,
                'error' => $e->getMessage()
            ]);
        }

        return null;
    }

    /**
     * Check all RVMs Jetson and CV Server health
     */
    public function checkAllRvmsHealth(): array
    {
        $rvms = ReverseVendingMachine::all();
        $results = [];

        foreach ($rvms as $rvm) {
            $results[$rvm->id] = $this->getRvmHealthStatus($rvm);
        }

        return $results;
    }
}




