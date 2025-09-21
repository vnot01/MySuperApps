<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ReverseVendingMachine;
use App\Models\SystemMetric;
use App\Models\ApplicationMetric;
use App\Models\NetworkInformation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class EnhancedMetricsController extends Controller
{
    public function getComprehensiveMetrics($id)
    {
        try {
            $rvm = ReverseVendingMachine::findOrFail($id);
            
            // Get latest system metrics
            $systemMetrics = SystemMetric::where('rvm_id', $id)
                ->orderBy('timestamp', 'desc')
                ->first();
            
            // Get latest application metrics
            $applicationMetrics = ApplicationMetric::where('rvm_id', $id)
                ->orderBy('recorded_at', 'desc')
                ->first();
            
            // Get latest network information
            $networkInfo = NetworkInformation::where('rvm_id', $id)
                ->orderBy('recorded_at', 'desc')
                ->first();
            
            return response()->json([
                'success' => true,
                'data' => [
                    'rvm_id' => $rvm->id,
                    'rvm_name' => $rvm->name,
                    'system_metrics' => $systemMetrics,
                    'application_metrics' => $applicationMetrics,
                    'network_information' => $networkInfo,
                    'last_updated' => now()
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get comprehensive metrics: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function getMetricsHistory($id, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'days' => 'nullable|integer|min:1|max:30',
            'metric_type' => 'nullable|in:system,application,network'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }
        
        try {
            $rvm = ReverseVendingMachine::findOrFail($id);
            $days = $request->days ?? 7;
            $metricType = $request->metric_type ?? 'system';
            
            $startDate = now()->subDays($days);
            
            $metrics = [];
            switch ($metricType) {
                case 'system':
                    $metrics = SystemMetric::where('rvm_id', $id)
                        ->where('timestamp', '>=', $startDate)
                        ->orderBy('timestamp', 'desc')
                        ->get();
                    break;
                case 'application':
                    $metrics = ApplicationMetric::where('rvm_id', $id)
                        ->where('recorded_at', '>=', $startDate)
                        ->orderBy('recorded_at', 'desc')
                        ->get();
                    break;
                case 'network':
                    $metrics = NetworkInformation::where('rvm_id', $id)
                        ->where('recorded_at', '>=', $startDate)
                        ->orderBy('recorded_at', 'desc')
                        ->get();
                    break;
            }
            
            return response()->json([
                'success' => true,
                'data' => [
                    'rvm_id' => $rvm->id,
                    'metric_type' => $metricType,
                    'days' => $days,
                    'metrics' => $metrics,
                    'total_records' => $metrics->count()
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get metrics history: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function storeMetrics(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'system_metrics' => 'nullable|array',
            'application_metrics' => 'nullable|array',
            'network_information' => 'nullable|array'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }
        
        try {
            $rvm = ReverseVendingMachine::findOrFail($id);
            
            // Store system metrics
            if ($request->has('system_metrics')) {
                SystemMetric::create([
                    'rvm_id' => $id,
                    'cpu_usage' => $request->system_metrics['cpu_usage'] ?? null,
                    'memory_usage' => $request->system_metrics['memory_usage'] ?? null,
                    'disk_usage' => $request->system_metrics['disk_usage'] ?? null,
                    'gpu_usage' => $request->system_metrics['gpu_usage'] ?? null,
                    'temperature' => $request->system_metrics['temperature'] ?? null,
                    'gpu_temperature' => $request->system_metrics['gpu_temperature'] ?? null,
                    'disk_read_speed' => $request->system_metrics['disk_read_speed'] ?? null,
                    'disk_write_speed' => $request->system_metrics['disk_write_speed'] ?? null,
                    'network_upload_speed' => $request->system_metrics['network_upload_speed'] ?? null,
                    'network_download_speed' => $request->system_metrics['network_download_speed'] ?? null,
                    'memory_available' => $request->system_metrics['memory_available'] ?? null,
                    'disk_available' => $request->system_metrics['disk_available'] ?? null,
                    'process_count' => $request->system_metrics['process_count'] ?? null,
                    'load_average' => $request->system_metrics['load_average'] ?? null,
                    'uptime' => $request->system_metrics['uptime'] ?? null,
                    'timestamp' => now()
                ]);
            }
            
            // Store application metrics
            if ($request->has('application_metrics')) {
                ApplicationMetric::create([
                    'rvm_id' => $id,
                    'software_version' => $request->application_metrics['software_version'] ?? null,
                    'ai_model_version' => $request->application_metrics['ai_model_version'] ?? null,
                    'ai_model_path' => $request->application_metrics['ai_model_path'] ?? null,
                    'uptime_seconds' => $request->application_metrics['uptime_seconds'] ?? null,
                    'deposit_count_since_restart' => $request->application_metrics['deposit_count_since_restart'] ?? null,
                    'last_deposit_time' => $request->application_metrics['last_deposit_time'] ?? null,
                    'error_count' => $request->application_metrics['error_count'] ?? 0,
                    'warning_count' => $request->application_metrics['warning_count'] ?? 0,
                    'recorded_at' => now()
                ]);
            }
            
            // Store network information
            if ($request->has('network_information')) {
                NetworkInformation::create([
                    'rvm_id' => $id,
                    'local_ip' => $request->network_information['local_ip'] ?? null,
                    'virtual_ip' => $request->network_information['virtual_ip'] ?? null,
                    'gateway_ip' => $request->network_information['gateway_ip'] ?? null,
                    'dns_servers' => $request->network_information['dns_servers'] ?? null,
                    'network_interface' => $request->network_information['network_interface'] ?? null,
                    'connection_type' => $request->network_information['connection_type'] ?? null,
                    'signal_strength' => $request->network_information['signal_strength'] ?? null,
                    'last_network_check' => $request->network_information['last_network_check'] ?? null,
                    'recorded_at' => now()
                ]);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Metrics stored successfully'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to store metrics: ' . $e->getMessage()
            ], 500);
        }
    }
}