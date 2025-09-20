<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemMetric;
use App\Models\ReverseVendingMachine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SystemMonitoringController extends Controller
{
    public function index($id)
    {
        try {
            $rvm = ReverseVendingMachine::findOrFail($id);
            $metrics = $rvm->systemMetrics()->recent(24)->orderBy('timestamp', 'desc')->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'rvm_id' => $rvm->id,
                    'rvm_name' => $rvm->name,
                    'metrics' => $metrics->map(function ($metric) {
                        return [
                            'id' => $metric->id,
                            'timestamp' => $metric->timestamp,
                            'cpu_usage' => $metric->cpu_usage,
                            'memory_usage' => $metric->memory_usage,
                            'disk_usage' => $metric->disk_usage,
                            'gpu_usage' => $metric->gpu_usage,
                            'temperature' => $metric->temperature,
                            'formatted_memory' => $metric->formatted_memory,
                            'formatted_disk' => $metric->formatted_disk,
                            'formatted_uptime' => $metric->formatted_uptime,
                            'process_count' => $metric->process_count,
                            'additional_metrics' => $metric->additional_metrics
                        ];
                    })
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get system metrics: ' . $e->getMessage()
            ], 500);
        }
    }

    public function latest($id)
    {
        try {
            $rvm = ReverseVendingMachine::findOrFail($id);
            $metric = $rvm->systemMetrics()->orderBy('timestamp', 'desc')->first();

            if (!$metric) {
                return response()->json([
                    'success' => false,
                    'message' => 'No system metrics found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'rvm_id' => $rvm->id,
                    'rvm_name' => $rvm->name,
                    'metric' => [
                        'id' => $metric->id,
                        'timestamp' => $metric->timestamp,
                        'cpu_usage' => $metric->cpu_usage,
                        'memory_usage' => $metric->memory_usage,
                        'disk_usage' => $metric->disk_usage,
                        'gpu_usage' => $metric->gpu_usage,
                        'temperature' => $metric->temperature,
                        'formatted_memory' => $metric->formatted_memory,
                        'formatted_disk' => $metric->formatted_disk,
                        'formatted_uptime' => $metric->formatted_uptime,
                        'process_count' => $metric->process_count,
                        'additional_metrics' => $metric->additional_metrics
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get latest system metrics: ' . $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'cpu_usage' => 'nullable|numeric|min:0|max:100',
            'memory_usage' => 'nullable|numeric|min:0|max:100',
            'disk_usage' => 'nullable|numeric|min:0|max:100',
            'gpu_usage' => 'nullable|numeric|min:0|max:100',
            'temperature' => 'nullable|numeric|min:-50|max:150',
            'free_memory' => 'nullable|integer|min:0',
            'total_memory' => 'nullable|integer|min:0',
            'free_disk' => 'nullable|integer|min:0',
            'total_disk' => 'nullable|integer|min:0',
            'uptime' => 'nullable|integer|min:0',
            'process_count' => 'nullable|integer|min:0',
            'additional_metrics' => 'nullable|array',
            'timestamp' => 'nullable|date'
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
            
            $metric = SystemMetric::create([
                'rvm_id' => $id,
                'cpu_usage' => $request->cpu_usage,
                'memory_usage' => $request->memory_usage,
                'disk_usage' => $request->disk_usage,
                'gpu_usage' => $request->gpu_usage,
                'temperature' => $request->temperature,
                'free_memory' => $request->free_memory,
                'total_memory' => $request->total_memory,
                'free_disk' => $request->free_disk,
                'total_disk' => $request->total_disk,
                'uptime' => $request->uptime,
                'process_count' => $request->process_count,
                'additional_metrics' => $request->additional_metrics,
                'timestamp' => $request->timestamp ?? now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'System metrics stored successfully',
                'data' => [
                    'rvm_id' => $rvm->id,
                    'rvm_name' => $rvm->name,
                    'metric' => [
                        'id' => $metric->id,
                        'timestamp' => $metric->timestamp,
                        'cpu_usage' => $metric->cpu_usage,
                        'memory_usage' => $metric->memory_usage,
                        'disk_usage' => $metric->disk_usage,
                        'gpu_usage' => $metric->gpu_usage,
                        'temperature' => $metric->temperature,
                        'formatted_memory' => $metric->formatted_memory,
                        'formatted_disk' => $metric->formatted_disk,
                        'formatted_uptime' => $metric->formatted_uptime,
                        'process_count' => $metric->process_count,
                        'additional_metrics' => $metric->additional_metrics
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to store system metrics: ' . $e->getMessage()
            ], 500);
        }
    }

    public function alerts($id)
    {
        try {
            $rvm = ReverseVendingMachine::findOrFail($id);
            $alerts = [];

            // Get latest metrics
            $latestMetric = $rvm->systemMetrics()->orderBy('timestamp', 'desc')->first();
            
            if ($latestMetric) {
                // Check for high CPU usage
                if ($latestMetric->cpu_usage > 80) {
                    $alerts[] = [
                        'type' => 'warning',
                        'metric' => 'cpu_usage',
                        'value' => $latestMetric->cpu_usage,
                        'threshold' => 80,
                        'message' => 'High CPU usage detected'
                    ];
                }

                // Check for high memory usage
                if ($latestMetric->memory_usage > 80) {
                    $alerts[] = [
                        'type' => 'warning',
                        'metric' => 'memory_usage',
                        'value' => $latestMetric->memory_usage,
                        'threshold' => 80,
                        'message' => 'High memory usage detected'
                    ];
                }

                // Check for high disk usage
                if ($latestMetric->disk_usage > 80) {
                    $alerts[] = [
                        'type' => 'warning',
                        'metric' => 'disk_usage',
                        'value' => $latestMetric->disk_usage,
                        'threshold' => 80,
                        'message' => 'High disk usage detected'
                    ];
                }

                // Check for high temperature
                if ($latestMetric->temperature > 70) {
                    $alerts[] = [
                        'type' => 'danger',
                        'metric' => 'temperature',
                        'value' => $latestMetric->temperature,
                        'threshold' => 70,
                        'message' => 'High temperature detected'
                    ];
                }
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'rvm_id' => $rvm->id,
                    'rvm_name' => $rvm->name,
                    'alerts' => $alerts,
                    'alert_count' => count($alerts)
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get system alerts: ' . $e->getMessage()
            ], 500);
        }
    }

    public function statistics($id)
    {
        try {
            $rvm = ReverseVendingMachine::findOrFail($id);
            $metrics = $rvm->systemMetrics()->recent(24)->get();

            if ($metrics->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No metrics data available'
                ], 404);
            }

            $statistics = [
                'cpu_usage' => [
                    'avg' => round($metrics->avg('cpu_usage'), 2),
                    'max' => $metrics->max('cpu_usage'),
                    'min' => $metrics->min('cpu_usage')
                ],
                'memory_usage' => [
                    'avg' => round($metrics->avg('memory_usage'), 2),
                    'max' => $metrics->max('memory_usage'),
                    'min' => $metrics->min('memory_usage')
                ],
                'disk_usage' => [
                    'avg' => round($metrics->avg('disk_usage'), 2),
                    'max' => $metrics->max('disk_usage'),
                    'min' => $metrics->min('disk_usage')
                ],
                'temperature' => [
                    'avg' => round($metrics->avg('temperature'), 2),
                    'max' => $metrics->max('temperature'),
                    'min' => $metrics->min('temperature')
                ]
            ];

            return response()->json([
                'success' => true,
                'data' => [
                    'rvm_id' => $rvm->id,
                    'rvm_name' => $rvm->name,
                    'period' => '24 hours',
                    'metric_count' => $metrics->count(),
                    'statistics' => $statistics
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get system statistics: ' . $e->getMessage()
            ], 500);
        }
    }
}
