<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ReverseVendingMachine;
use App\Models\SystemMetric;
use App\Models\ApplicationMetric;
use App\Models\NetworkInformation;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;

class EnhancedMetricsController extends Controller
{
    /**
     * Get comprehensive metrics for RVM
     */
    public function getComprehensiveMetrics(Request $request, $rvmId): JsonResponse
    {
        try {
            $rvm = ReverseVendingMachine::findOrFail($rvmId);
            
            // Get latest metrics
            $systemMetrics = SystemMetric::where('rvm_id', $rvmId)
                ->latest('timestamp')
                ->first();
                
            $applicationMetrics = ApplicationMetric::where('rvm_id', $rvmId)
                ->latest('recorded_at')
                ->first();
                
            $networkInfo = NetworkInformation::where('rvm_id', $rvmId)
                ->latest('recorded_at')
                ->first();
            
            // Simulate real-time data if no data available
            $metrics = [
                'system' => $this->getSystemMetrics($systemMetrics),
                'application' => $this->getApplicationMetrics($applicationMetrics),
                'network' => $this->getNetworkInfo($networkInfo),
                'timestamp' => Carbon::now()->toISOString()
            ];
            
            return response()->json([
                'success' => true,
                'data' => $metrics
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get system metrics
     */
    private function getSystemMetrics($systemMetrics): array
    {
        if (!$systemMetrics) {
            // Try to get real metrics from RVM first
            $realMetrics = $this->getRealMetricsFromRVM();
            if ($realMetrics) {
                return $realMetrics;
            }
            
            // Fallback to simulation if RVM is not reachable
            return [
                'cpu_usage' => rand(20, 90),
                'memory_usage' => rand(30, 80),
                'disk_usage' => rand(40, 70),
                'gpu_usage' => rand(10, 60),
                'temperature' => rand(35, 65),
                'gpu_temperature' => rand(40, 70),
                'disk_read_speed' => rand(50, 200),
                'disk_write_speed' => rand(30, 150),
                'network_upload_speed' => rand(10, 100),
                'network_download_speed' => rand(20, 150),
                'memory_available' => rand(1000, 4000),
                'disk_available' => rand(5000, 20000),
                'load_average' => rand(1, 4),
                'simulation' => true
            ];
        }
        
        return [
            'cpu_usage' => $systemMetrics->cpu_usage ?? 0,
            'memory_usage' => $systemMetrics->memory_usage ?? 0,
            'disk_usage' => $systemMetrics->disk_usage ?? 0,
            'gpu_usage' => $systemMetrics->gpu_usage ?? 0,
            'temperature' => $systemMetrics->temperature ?? 0,
            'gpu_temperature' => $systemMetrics->gpu_temperature ?? 0,
            'disk_read_speed' => $systemMetrics->disk_read_speed ?? 0,
            'disk_write_speed' => $systemMetrics->disk_write_speed ?? 0,
            'network_upload_speed' => $systemMetrics->network_upload_speed ?? 0,
            'network_download_speed' => $systemMetrics->network_download_speed ?? 0,
            'memory_available' => $systemMetrics->memory_available ?? 0,
            'disk_available' => $systemMetrics->disk_available ?? 0,
            'load_average' => $systemMetrics->load_average ?? 0,
            'simulation' => false
        ];
    }
    
    /**
     * Get application metrics
     */
    private function getApplicationMetrics($applicationMetrics): array
    {
        if (!$applicationMetrics) {
            // Simulate real-time data
            return [
                'software_version' => 'v1.2.3-' . substr(md5(time()), 0, 8),
                'ai_model_version' => 'best.pt-v2.1',
                'ai_model_path' => '/models/best.pt',
                'uptime_seconds' => rand(3600, 86400),
                'deposit_count_since_restart' => rand(0, 50),
                'last_deposit_time' => Carbon::now()->subMinutes(rand(1, 60))->toISOString(),
                'error_count' => rand(0, 5),
                'warning_count' => rand(0, 10),
                'simulation' => true
            ];
        }
        
        return [
            'software_version' => $applicationMetrics->software_version ?? 'Unknown',
            'ai_model_version' => $applicationMetrics->ai_model_version ?? 'Unknown',
            'ai_model_path' => $applicationMetrics->ai_model_path ?? '/models/best.pt',
            'uptime_seconds' => $applicationMetrics->uptime_seconds ?? 0,
            'deposit_count_since_restart' => $applicationMetrics->deposit_count_since_restart ?? 0,
            'last_deposit_time' => $applicationMetrics->last_deposit_time ?? null,
            'error_count' => $applicationMetrics->error_count ?? 0,
            'warning_count' => $applicationMetrics->warning_count ?? 0,
            'simulation' => false
        ];
    }
    
    /**
     * Get network information
     */
    private function getNetworkInfo($networkInfo): array
    {
        if (!$networkInfo) {
            // Simulate real-time data
            return [
                'local_ip' => '192.168.1.100',
                'virtual_ip' => '10.0.0.100',
                'gateway_ip' => '192.168.1.1',
                'dns_servers' => ['8.8.8.8', '8.8.4.4'],
                'network_interface' => 'eth0',
                'connection_type' => 'ethernet',
                'signal_strength' => -45,
                'last_network_check' => Carbon::now()->toISOString()
            ];
        }
        
        return [
            'local_ip' => $networkInfo->local_ip ?? 'Unknown',
            'virtual_ip' => $networkInfo->virtual_ip ?? 'Unknown',
            'gateway_ip' => $networkInfo->gateway_ip ?? 'Unknown',
            'dns_servers' => json_decode($networkInfo->dns_servers ?? '[]', true),
            'network_interface' => $networkInfo->network_interface ?? 'Unknown',
            'connection_type' => $networkInfo->connection_type ?? 'Unknown',
            'signal_strength' => $networkInfo->signal_strength ?? 0,
            'last_network_check' => $networkInfo->last_network_check ?? null
        ];
    }
    
    /**
     * Store metrics data
     */
    public function storeMetrics(Request $request, $rvmId): JsonResponse
    {
        try {
            $data = $request->validate([
                'system_metrics' => 'array',
                'application_metrics' => 'array',
                'network_info' => 'array'
            ]);
            
            // Store system metrics
            if (isset($data['system_metrics'])) {
                SystemMetric::create([
                    'rvm_id' => $rvmId,
                    'cpu_usage' => $data['system_metrics']['cpu_usage'] ?? 0,
                    'memory_usage' => $data['system_metrics']['memory_usage'] ?? 0,
                    'disk_usage' => $data['system_metrics']['disk_usage'] ?? 0,
                    'gpu_usage' => $data['system_metrics']['gpu_usage'] ?? 0,
                    'temperature' => $data['system_metrics']['temperature'] ?? 0,
                    'timestamp' => Carbon::now()
                ]);
            }
            
            // Store application metrics
            if (isset($data['application_metrics'])) {
                ApplicationMetric::create([
                    'rvm_id' => $rvmId,
                    'software_version' => $data['application_metrics']['software_version'] ?? 'Unknown',
                    'ai_model_version' => $data['application_metrics']['ai_model_version'] ?? 'Unknown',
                    'ai_model_path' => $data['application_metrics']['ai_model_path'] ?? '/models/best.pt',
                    'uptime_seconds' => $data['application_metrics']['uptime_seconds'] ?? 0,
                    'deposit_count_since_restart' => $data['application_metrics']['deposit_count_since_restart'] ?? 0,
                    'last_deposit_time' => $data['application_metrics']['last_deposit_time'] ?? null,
                    'error_count' => $data['application_metrics']['error_count'] ?? 0,
                    'warning_count' => $data['application_metrics']['warning_count'] ?? 0,
                    'recorded_at' => Carbon::now()
                ]);
            }
            
            // Store network information
            if (isset($data['network_info'])) {
                NetworkInformation::create([
                    'rvm_id' => $rvmId,
                    'local_ip' => $data['network_info']['local_ip'] ?? 'Unknown',
                    'virtual_ip' => $data['network_info']['virtual_ip'] ?? 'Unknown',
                    'gateway_ip' => $data['network_info']['gateway_ip'] ?? 'Unknown',
                    'dns_servers' => json_encode($data['network_info']['dns_servers'] ?? []),
                    'network_interface' => $data['network_info']['network_interface'] ?? 'Unknown',
                    'connection_type' => $data['network_info']['connection_type'] ?? 'Unknown',
                    'signal_strength' => $data['network_info']['signal_strength'] ?? 0,
                    'last_network_check' => Carbon::now(),
                    'recorded_at' => Carbon::now()
                ]);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Metrics stored successfully'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get real metrics from RVM
     */
    private function getRealMetricsFromRVM(): ?array
    {
        try {
            // Get RVM details from request
            $rvmId = request()->route('rvmId');
            $rvm = ReverseVendingMachine::find($rvmId);
            
            if (!$rvm) {
                return null;
            }
            
            // Try to get metrics from RVM API
            $rvmApiUrl = "http://{$rvm->ip_address}:8000/api/metrics";
            
            $response = \Http::timeout(10)->get($rvmApiUrl);
            
            if ($response->successful()) {
                $data = $response->json();
                return $data['system_metrics'] ?? null;
            }
            
            return null;
            
        } catch (\Exception $e) {
            Log::warning("Failed to get real metrics from RVM: " . $e->getMessage());
            return null;
        }
    }
}