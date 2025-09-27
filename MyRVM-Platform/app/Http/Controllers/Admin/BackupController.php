<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ReverseVendingMachine;
use App\Models\BackupLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BackupController extends Controller
{
    /**
     * Get backup history for a specific RVM
     */
    public function index(Request $request, $id)
    {
        try {
            $rvm = ReverseVendingMachine::findOrFail($id);
            
            $query = $rvm->backupLogs()->orderBy('created_at', 'desc');
            
            // Filter by backup type
            if ($request->has('type') && $request->type) {
                $query->where('backup_type', $request->type);
            }
            
            // Filter by status
            if ($request->has('status') && $request->status) {
                $query->where('upload_status', $request->status);
            }
            
            // Filter by date range
            if ($request->has('date_from') && $request->date_from) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }
            
            if ($request->has('date_to') && $request->date_to) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }
            
            $backups = $query->paginate($request->get('per_page', 15));
            
            return response()->json([
                'success' => true,
                'data' => [
                    'rvm_id' => $rvm->id,
                    'rvm_name' => $rvm->name,
                    'backups' => $backups->items(),
                    'pagination' => [
                        'current_page' => $backups->currentPage(),
                        'last_page' => $backups->lastPage(),
                        'per_page' => $backups->perPage(),
                        'total' => $backups->total(),
                        'from' => $backups->firstItem(),
                        'to' => $backups->lastItem()
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve backup history: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get latest backup status for a specific RVM
     */
    public function latest(Request $request, $id)
    {
        try {
            $rvm = ReverseVendingMachine::findOrFail($id);
            
            $latestBackup = $rvm->backupLogs()
                ->orderBy('created_at', 'desc')
                ->first();
            
            if (!$latestBackup) {
                return response()->json([
                    'success' => true,
                    'data' => [
                        'rvm_id' => $rvm->id,
                        'rvm_name' => $rvm->name,
                        'latest_backup' => null,
                        'status' => 'no_backups'
                    ]
                ]);
            }
            
            return response()->json([
                'success' => true,
                'data' => [
                    'rvm_id' => $rvm->id,
                    'rvm_name' => $rvm->name,
                    'latest_backup' => [
                        'id' => $latestBackup->id,
                        'backup_type' => $latestBackup->backup_type,
                        'upload_status' => $latestBackup->upload_status,
                        'file_size' => $latestBackup->file_size,
                        'formatted_file_size' => $latestBackup->formatted_file_size,
                        'backup_started_at' => $latestBackup->backup_started_at,
                        'backup_completed_at' => $latestBackup->backup_completed_at,
                        'upload_started_at' => $latestBackup->upload_started_at,
                        'upload_completed_at' => $latestBackup->upload_completed_at,
                        'duration' => $latestBackup->duration,
                        'upload_duration' => $latestBackup->upload_duration,
                        'error_message' => $latestBackup->error_message,
                        'created_at' => $latestBackup->created_at
                    ],
                    'status' => $latestBackup->upload_status
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve latest backup: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create a new backup log entry
     */
    public function store(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'backup_type' => 'required|in:full,incremental,data_only,config_only',
            'file_path' => 'nullable|string|max:500',
            'file_size' => 'nullable|integer|min:0',
            'upload_status' => 'nullable|in:pending,uploading,completed,failed',
            'minio_path' => 'nullable|string|max:500',
            'backup_details' => 'nullable|array',
            'backup_started_at' => 'nullable|date',
            'backup_completed_at' => 'nullable|date',
            'upload_started_at' => 'nullable|date',
            'upload_completed_at' => 'nullable|date',
            'error_message' => 'nullable|string'
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
            
            $backup = BackupLog::create([
                'rvm_id' => $id,
                'backup_type' => $request->backup_type,
                'file_path' => $request->file_path,
                'file_size' => $request->file_size,
                'upload_status' => $request->upload_status ?? 'pending',
                'minio_path' => $request->minio_path,
                'backup_details' => $request->backup_details,
                'backup_started_at' => $request->backup_started_at,
                'backup_completed_at' => $request->backup_completed_at,
                'upload_started_at' => $request->upload_started_at,
                'upload_completed_at' => $request->upload_completed_at,
                'error_message' => $request->error_message
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Backup log created successfully',
                'data' => [
                    'rvm_id' => $rvm->id,
                    'rvm_name' => $rvm->name,
                    'backup' => [
                        'id' => $backup->id,
                        'backup_type' => $backup->backup_type,
                        'upload_status' => $backup->upload_status,
                        'file_size' => $backup->file_size,
                        'formatted_file_size' => $backup->formatted_file_size,
                        'backup_started_at' => $backup->backup_started_at,
                        'backup_completed_at' => $backup->backup_completed_at,
                        'upload_started_at' => $backup->upload_started_at,
                        'upload_completed_at' => $backup->upload_completed_at,
                        'duration' => $backup->duration,
                        'upload_duration' => $backup->upload_duration,
                        'error_message' => $backup->error_message,
                        'created_at' => $backup->created_at
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create backup log: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update backup log status
     */
    public function update(Request $request, $id, $backupId)
    {
        $validator = Validator::make($request->all(), [
            'upload_status' => 'nullable|in:pending,uploading,completed,failed',
            'file_path' => 'nullable|string|max:500',
            'file_size' => 'nullable|integer|min:0',
            'minio_path' => 'nullable|string|max:500',
            'backup_details' => 'nullable|array',
            'backup_started_at' => 'nullable|date',
            'backup_completed_at' => 'nullable|date',
            'upload_started_at' => 'nullable|date',
            'upload_completed_at' => 'nullable|date',
            'error_message' => 'nullable|string'
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
            $backup = $rvm->backupLogs()->findOrFail($backupId);
            
            $updateData = $request->only([
                'upload_status', 'file_path', 'file_size', 'minio_path',
                'backup_details', 'backup_started_at', 'backup_completed_at',
                'upload_started_at', 'upload_completed_at', 'error_message'
            ]);
            
            $backup->update($updateData);

            return response()->json([
                'success' => true,
                'message' => 'Backup log updated successfully',
                'data' => [
                    'rvm_id' => $rvm->id,
                    'rvm_name' => $rvm->name,
                    'backup' => [
                        'id' => $backup->id,
                        'backup_type' => $backup->backup_type,
                        'upload_status' => $backup->upload_status,
                        'file_size' => $backup->file_size,
                        'formatted_file_size' => $backup->formatted_file_size,
                        'backup_started_at' => $backup->backup_started_at,
                        'backup_completed_at' => $backup->backup_completed_at,
                        'upload_started_at' => $backup->upload_started_at,
                        'upload_completed_at' => $backup->upload_completed_at,
                        'duration' => $backup->duration,
                        'upload_duration' => $backup->upload_duration,
                        'error_message' => $backup->error_message,
                        'updated_at' => $backup->updated_at
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update backup log: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get backup statistics for a specific RVM
     */
    public function statistics(Request $request, $id)
    {
        try {
            $rvm = ReverseVendingMachine::findOrFail($id);
            
            $stats = [
                'total_backups' => $rvm->backupLogs()->count(),
                'completed_backups' => $rvm->backupLogs()->completed()->count(),
                'failed_backups' => $rvm->backupLogs()->failed()->count(),
                'pending_backups' => $rvm->backupLogs()->where('upload_status', 'pending')->count(),
                'uploading_backups' => $rvm->backupLogs()->where('upload_status', 'uploading')->count(),
                'total_size' => $rvm->backupLogs()->whereNotNull('file_size')->sum('file_size'),
                'backups_by_type' => $rvm->backupLogs()
                    ->selectRaw('backup_type, COUNT(*) as count')
                    ->groupBy('backup_type')
                    ->pluck('count', 'backup_type'),
                'backups_by_status' => $rvm->backupLogs()
                    ->selectRaw('upload_status, COUNT(*) as count')
                    ->groupBy('upload_status')
                    ->pluck('count', 'upload_status'),
                'recent_backups' => $rvm->backupLogs()
                    ->recent(7)
                    ->count(),
                'last_backup_date' => $rvm->backupLogs()
                    ->whereNotNull('backup_completed_at')
                    ->orderBy('backup_completed_at', 'desc')
                    ->value('backup_completed_at')
            ];
            
            // Format total size
            $stats['formatted_total_size'] = $this->formatBytes($stats['total_size']);
            
            return response()->json([
                'success' => true,
                'data' => [
                    'rvm_id' => $rvm->id,
                    'rvm_name' => $rvm->name,
                    'statistics' => $stats
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve backup statistics: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get backup alerts (failed backups, long-running backups, etc.)
     */
    public function alerts(Request $request, $id)
    {
        try {
            $rvm = ReverseVendingMachine::findOrFail($id);
            
            $alerts = [];
            
            // Failed backups in the last 24 hours
            $failedBackups = $rvm->backupLogs()
                ->where('upload_status', 'failed')
                ->where('created_at', '>=', now()->subDay())
                ->get();
            
            if ($failedBackups->count() > 0) {
                $alerts[] = [
                    'type' => 'failed_backups',
                    'severity' => 'high',
                    'message' => "{$failedBackups->count()} backup(s) failed in the last 24 hours",
                    'count' => $failedBackups->count(),
                    'details' => $failedBackups->map(function($backup) {
                        return [
                            'id' => $backup->id,
                            'backup_type' => $backup->backup_type,
                            'error_message' => $backup->error_message,
                            'created_at' => $backup->created_at
                        ];
                    })
                ];
            }
            
            // Long-running backups (started more than 2 hours ago but not completed)
            $longRunningBackups = $rvm->backupLogs()
                ->where('upload_status', 'uploading')
                ->where('backup_started_at', '<', now()->subHours(2))
                ->get();
            
            if ($longRunningBackups->count() > 0) {
                $alerts[] = [
                    'type' => 'long_running_backups',
                    'severity' => 'medium',
                    'message' => "{$longRunningBackups->count()} backup(s) running for more than 2 hours",
                    'count' => $longRunningBackups->count(),
                    'details' => $longRunningBackups->map(function($backup) {
                        return [
                            'id' => $backup->id,
                            'backup_type' => $backup->backup_type,
                            'backup_started_at' => $backup->backup_started_at,
                            'duration' => $backup->duration
                        ];
                    })
                ];
            }
            
            // No backups in the last 7 days
            $lastBackup = $rvm->backupLogs()
                ->where('upload_status', 'completed')
                ->orderBy('backup_completed_at', 'desc')
                ->first();
            
            if (!$lastBackup || $lastBackup->backup_completed_at < now()->subDays(7)) {
                $alerts[] = [
                    'type' => 'no_recent_backups',
                    'severity' => 'medium',
                    'message' => 'No successful backups in the last 7 days',
                    'count' => 1,
                    'details' => [
                        'last_backup_date' => $lastBackup ? $lastBackup->backup_completed_at : null
                    ]
                ];
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
                'message' => 'Failed to retrieve backup alerts: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Helper method to format bytes
     */
    private function formatBytes($bytes, $precision = 2)
    {
        if (!$bytes) {
            return '0 B';
        }
        
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
}
