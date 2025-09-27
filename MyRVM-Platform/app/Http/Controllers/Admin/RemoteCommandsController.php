<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ReverseVendingMachine;
use App\Models\RemoteCommand;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Helpers\RvmStatusHelper;

class RemoteCommandsController extends Controller
{
    public function index($id)
    {
        try {
            $rvm = ReverseVendingMachine::findOrFail($id);
            $commands = RemoteCommand::where('rvm_id', $id)
                ->orderBy('created_at', 'desc')
                ->limit(50)
                ->get();
            
            return response()->json([
                'success' => true,
                'data' => [
                    'rvm_id' => $rvm->id,
                    'rvm_name' => $rvm->name,
                    'commands' => $commands
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get remote commands: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function executeCommand(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'command_type' => 'required|in:HARDWARE_CONTROL,PROCESS_MANAGEMENT,SYSTEM_CONTROL,DIAGNOSTICS',
            'command_name' => 'required|string|max:100',
            'command_payload' => 'nullable|array'
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
            $user = Auth::user();
            
            // Create command record
            $command = RemoteCommand::create([
                'rvm_id' => $id,
                'command_type' => $request->command_type,
                'command_name' => $request->command_name,
                'command_payload' => $request->command_payload ?? [],
                'status' => 'pending',
                'executed_by' => $user->id,
                'executed_at' => now()
            ]);
            
            // Send command to RVM via WebSocket
            $this->sendCommandToRVM($rvm, $command);
            
            // Log command execution
            Log::info('Remote command executed', [
                'command_id' => $command->id,
                'rvm_id' => $id,
                'command_type' => $request->command_type,
                'command_name' => $request->command_name,
                'executed_by' => $user->id
            ]);

            // Update RVM status immediately for maintenance commands
            if ($request->command_name === 'enter_maintenance') {
                $statusData = RvmStatusHelper::getStatusData('maintenance');
                $rvm->update([
                    'status' => $statusData['status'],
                    'status_updated_at' => now(),
                ]);
            } elseif ($request->command_name === 'exit_maintenance') {
                $statusData = RvmStatusHelper::getStatusData('active');
                $rvm->update([
                    'status' => $statusData['status'],
                    'status_updated_at' => now(),
                ]);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Command sent successfully',
                'data' => [
                    'command_id' => $command->id,
                    'status' => $command->status,
                    'executed_at' => $command->executed_at
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to execute remote command', [
                'rvm_id' => $id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to execute command: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function getCommandStatus($id, $commandId)
    {
        try {
            $command = RemoteCommand::where('rvm_id', $id)
                ->where('id', $commandId)
                ->firstOrFail();
            
            return response()->json([
                'success' => true,
                'data' => $command
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Command not found: ' . $e->getMessage()
            ], 404);
        }
    }
    
    public function updateCommandStatus(Request $request, $id, $commandId)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:pending,executing,completed,failed',
            'result' => 'nullable|array',
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
            $command = RemoteCommand::where('rvm_id', $id)
                ->where('id', $commandId)
                ->firstOrFail();
            
            $command->status = $request->status;
            $command->result = $request->result;
            $command->error_message = $request->error_message;
            
            if ($request->status === 'completed' || $request->status === 'failed') {
                $command->completed_at = now();
            }
            
            $command->save();
            
            return response()->json([
                'success' => true,
                'message' => 'Command status updated successfully',
                'data' => $command
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update command status: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function getAvailableCommands($id)
    {
        try {
            $rvm = ReverseVendingMachine::findOrFail($id);
            
            $availableCommands = [
                'HARDWARE_CONTROL' => [
                    [
                        'name' => 'open_door',
                        'display_name' => 'Buka Pintu Penerimaan',
                        'description' => 'Membuka pintu penerimaan untuk testing dan diagnostik',
                        'icon' => 'fas fa-door-open',
                        'color' => 'success',
                        'requires_confirmation' => true
                    ],
                    [
                        'name' => 'close_door',
                        'display_name' => 'Tutup Pintu Penerimaan',
                        'description' => 'Menutup pintu penerimaan',
                        'icon' => 'fas fa-door-closed',
                        'color' => 'warning',
                        'requires_confirmation' => false
                    ],
                    [
                        'name' => 'test_motor',
                        'display_name' => 'Tes Motor Pemilah',
                        'description' => 'Menjalankan siklus tes motor pemilah',
                        'icon' => 'fas fa-cogs',
                        'color' => 'info',
                        'requires_confirmation' => true
                    ],
                    [
                        'name' => 'test_sensors',
                        'display_name' => 'Tes Sensor',
                        'description' => 'Menjalankan tes semua sensor',
                        'icon' => 'fas fa-microchip',
                        'color' => 'info',
                        'requires_confirmation' => false
                    ]
                ],
                'PROCESS_MANAGEMENT' => [
                    [
                        'name' => 'restart_app',
                        'display_name' => 'Restart Aplikasi',
                        'description' => 'Me-restart aplikasi MyRVM tanpa reboot sistem',
                        'icon' => 'fas fa-redo',
                        'color' => 'warning',
                        'requires_confirmation' => true
                    ],
                    [
                        'name' => 'reboot_system',
                        'display_name' => 'Reboot Sistem',
                        'description' => 'Me-reboot Jetson Orin',
                        'icon' => 'fas fa-power-off',
                        'color' => 'danger',
                        'requires_confirmation' => true
                    ],
                    [
                        'name' => 'shutdown_system',
                        'display_name' => 'Shutdown Sistem',
                        'description' => 'Mematikan Jetson Orin',
                        'icon' => 'fas fa-stop',
                        'color' => 'danger',
                        'requires_confirmation' => true
                    ]
                ],
                'SYSTEM_CONTROL' => [
                    [
                        'name' => 'enter_maintenance',
                        'display_name' => 'Masuk Mode Maintenance',
                        'description' => 'Menghentikan operasi normal dan menampilkan pesan maintenance',
                        'icon' => 'fas fa-tools',
                        'color' => 'warning',
                        'requires_confirmation' => true
                    ],
                    [
                        'name' => 'exit_maintenance',
                        'display_name' => 'Keluar Mode Maintenance',
                        'description' => 'Mengembalikan RVM ke status operasional normal',
                        'icon' => 'fas fa-check-circle',
                        'color' => 'success',
                        'requires_confirmation' => false
                    ],
                    [
                        'name' => 'update_config',
                        'display_name' => 'Update Konfigurasi',
                        'description' => 'Mengupdate konfigurasi RVM',
                        'icon' => 'fas fa-cog',
                        'color' => 'info',
                        'requires_confirmation' => true
                    ]
                ],
                'DIAGNOSTICS' => [
                    [
                        'name' => 'take_snapshot',
                        'display_name' => 'Ambil Snapshot Kamera',
                        'description' => 'Mengambil gambar dari kamera untuk testing',
                        'icon' => 'fas fa-camera',
                        'color' => 'info',
                        'requires_confirmation' => false
                    ],
                    [
                        'name' => 'get_logs',
                        'display_name' => 'Ambil Log Aplikasi',
                        'description' => 'Mengambil log aplikasi terbaru',
                        'icon' => 'fas fa-file-alt',
                        'color' => 'info',
                        'requires_confirmation' => false
                    ],
                    [
                        'name' => 'system_info',
                        'display_name' => 'Informasi Sistem',
                        'description' => 'Mengambil informasi sistem lengkap',
                        'icon' => 'fas fa-info-circle',
                        'color' => 'info',
                        'requires_confirmation' => false
                    ]
                ]
            ];
            
            return response()->json([
                'success' => true,
                'data' => [
                    'rvm_id' => $rvm->id,
                    'rvm_name' => $rvm->name,
                    'available_commands' => $availableCommands
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get available commands: ' . $e->getMessage()
            ], 500);
        }
    }
    
    private function sendCommandToRVM($rvm, $command)
    {
        // This would integrate with WebSocket or message queue
        // For now, we'll simulate the command sending
        
        $commandData = [
            'command_id' => $command->id,
            'command_type' => $command->command_type,
            'command_name' => $command->command_name,
            'command_payload' => $command->command_payload,
            'timestamp' => now()->toISOString()
        ];
        
        // TODO: Implement actual WebSocket or message queue integration
        // Example: WebSocket::broadcast("rvm.{$rvm->id}", 'remote_command', $commandData);
        
        Log::info('Command sent to RVM', [
            'rvm_id' => $rvm->id,
            'command_id' => $command->id,
            'command_data' => $commandData
        ]);
    }
}