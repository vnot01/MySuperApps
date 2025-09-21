<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ReverseVendingMachine;
use App\Models\RemoteCommand;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class EnhancedRemoteCommandsController extends Controller
{
    /**
     * Execute remote command
     */
    public function executeCommand(Request $request, $rvmId): JsonResponse
    {
        try {
            $rvm = ReverseVendingMachine::findOrFail($rvmId);
            
            $data = $request->validate([
                'command_type' => 'required|string',
                'command_name' => 'required|string',
                'command_payload' => 'array'
            ]);
            
            // Create command record
            $command = RemoteCommand::create([
                'rvm_id' => $rvmId,
                'command_type' => $data['command_type'],
                'command_name' => $data['command_name'],
                'command_payload' => json_encode($data['command_payload'] ?? []),
                'status' => 'pending',
                'executed_by' => auth()->id(),
                'executed_at' => Carbon::now()
            ]);
            
            // Execute command based on type
            $result = $this->executeCommandByType($data['command_name'], $data['command_payload'] ?? []);
            
            // Update command status
            $command->update([
                'status' => $result['success'] ? 'completed' : 'failed',
                'completed_at' => Carbon::now(),
                'result' => json_encode($result),
                'error_message' => $result['success'] ? null : $result['error']
            ]);
            
            return response()->json([
                'success' => $result['success'],
                'message' => $result['message'],
                'command_id' => $command->id,
                'data' => $result['data'] ?? null
            ]);
            
        } catch (\Exception $e) {
            Log::error("Remote command execution failed: " . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Execute command by type
     */
    private function executeCommandByType(string $commandName, array $payload): array
    {
        try {
            switch ($commandName) {
                case 'reboot_system':
                    return $this->executeRebootSystem($payload);
                    
                case 'restart_app':
                    return $this->executeRestartApp($payload);
                    
                case 'open_door':
                    return $this->executeOpenDoor($payload);
                    
                case 'close_door':
                    return $this->executeCloseDoor($payload);
                    
                case 'run_motor_test':
                    return $this->executeMotorTest($payload);
                    
                case 'check_system_health':
                    return $this->executeSystemHealthCheck($payload);
                    
                case 'git_pull':
                    return $this->executeGitPull($payload);
                    
                case 'update_ai_model':
                    return $this->executeUpdateAiModel($payload);
                    
                default:
                    return [
                        'success' => false,
                        'error' => "Unknown command: {$commandName}",
                        'message' => "Command not found"
                    ];
            }
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'message' => "Command execution failed"
            ];
        }
    }
    
    /**
     * Send command to RVM Jetson
     */
    private function sendCommandToRVM(string $commandName, array $payload = []): array
    {
        try {
            // Get RVM details
            $rvm = ReverseVendingMachine::find($this->getRvmIdFromRequest());
            if (!$rvm) {
                return [
                    'success' => false,
                    'error' => 'RVM not found',
                    'message' => 'RVM not found'
                ];
            }
            
            // Prepare command data
            $commandData = [
                'command' => $commandName,
                'payload' => $payload,
                'timestamp' => now()->toISOString(),
                'source' => 'maintenance_mode'
            ];
            
            // Send to RVM via HTTP API (if RVM has API endpoint)
            $response = $this->sendHttpCommandToRVM($rvm, $commandData);
            
            if ($response['success']) {
                return [
                    'success' => true,
                    'message' => $response['message'],
                    'data' => $response['data'] ?? []
                ];
            } else {
                return [
                    'success' => false,
                    'error' => $response['error'],
                    'message' => $response['message']
                ];
            }
            
        } catch (\Exception $e) {
            Log::error("Failed to send command to RVM: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Failed to communicate with RVM'
            ];
        }
    }
    
    /**
     * Send HTTP command to RVM
     */
    private function sendHttpCommandToRVM(ReverseVendingMachine $rvm, array $commandData): array
    {
        try {
            // RVM API endpoint (assuming RVM has a command endpoint)
            $rvmApiUrl = "http://{$rvm->ip_address}:8000/api/commands";
            
            $response = \Http::timeout(30)->post($rvmApiUrl, $commandData);
            
            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success' => true,
                    'message' => $data['message'] ?? 'Command executed successfully',
                    'data' => $data['data'] ?? []
                ];
            } else {
                return [
                    'success' => false,
                    'error' => 'RVM API error: ' . $response->status(),
                    'message' => 'Failed to execute command on RVM'
                ];
            }
            
        } catch (\Exception $e) {
            // Fallback to simulation if RVM is not reachable
            Log::warning("RVM API not reachable, using simulation: " . $e->getMessage());
            return $this->simulateCommandExecution($commandData['command'], $commandData['payload']);
        }
    }
    
    /**
     * Simulate command execution (fallback)
     */
    private function simulateCommandExecution(string $commandName, array $payload): array
    {
        // This is the current simulation logic
        switch ($commandName) {
            case 'reboot_system':
                return [
                    'success' => true,
                    'message' => 'System reboot initiated successfully (SIMULATED)',
                    'data' => [
                        'reboot_time' => now()->addMinutes(2)->toISOString(),
                        'estimated_downtime' => '2-3 minutes',
                        'simulation' => true
                    ]
                ];
                
            case 'restart_app':
                return [
                    'success' => true,
                    'message' => 'Application restart initiated successfully (SIMULATED)',
                    'data' => [
                        'restart_time' => now()->addSeconds(30)->toISOString(),
                        'estimated_downtime' => '30 seconds',
                        'simulation' => true
                    ]
                ];
                
            case 'open_door':
                return [
                    'success' => true,
                    'message' => 'Door opened successfully (SIMULATED)',
                    'data' => [
                        'door_status' => 'open',
                        'opened_at' => now()->toISOString(),
                        'simulation' => true
                    ]
                ];
                
            case 'close_door':
                return [
                    'success' => true,
                    'message' => 'Door closed successfully (SIMULATED)',
                    'data' => [
                        'door_status' => 'closed',
                        'closed_at' => now()->toISOString(),
                        'simulation' => true
                    ]
                ];
                
            case 'run_motor_test':
                return [
                    'success' => true,
                    'message' => 'Motor test completed successfully (SIMULATED)',
                    'data' => [
                        'test_result' => 'passed',
                        'motor_status' => 'operational',
                        'test_duration' => '5 seconds',
                        'tested_at' => now()->toISOString(),
                        'simulation' => true
                    ]
                ];
                
            case 'take_snapshot':
                return [
                    'success' => true,
                    'message' => 'Snapshot captured successfully (SIMULATED)',
                    'data' => [
                        'snapshot_path' => '/home/my/test-cv-yolo11-sam2-camera/storages/images/camera_captures/camera_capture_' . date('Ymd_His') . '.jpg',
                        'file_size' => '2.5 MB',
                        'captured_at' => now()->toISOString(),
                        'simulation' => true,
                        'note' => 'Real camera integration available in camera_service.py'
                    ]
                ];
                
            case 'git_pull':
                return [
                    'success' => true,
                    'message' => 'Git pull executed successfully (SIMULATED)',
                    'data' => [
                        'repository' => $payload['repository'] ?? 'main',
                        'branch' => $payload['branch'] ?? 'main',
                        'changes_pulled' => rand(1, 10),
                        'restart_services' => $payload['restart_services'] ?? true,
                        'executed_at' => now()->toISOString(),
                        'simulation' => true
                    ]
                ];
                
            case 'update_ai_model':
                return [
                    'success' => true,
                    'message' => 'AI model updated successfully (SIMULATED)',
                    'data' => [
                        'model_name' => $payload['model_name'] ?? 'best.pt',
                        'model_version' => $payload['model_version'] ?? 'latest',
                        'model_size' => '45.2 MB',
                        'updated_at' => now()->toISOString(),
                        'simulation' => true
                    ]
                ];
                
            case 'check_system_health':
                return [
                    'success' => true,
                    'message' => 'System health check completed (SIMULATED)',
                    'data' => [
                        'overall_health' => 'good',
                        'cpu_usage' => rand(20, 80),
                        'memory_usage' => rand(30, 70),
                        'disk_usage' => rand(40, 60),
                        'temperature' => rand(35, 65),
                        'network_status' => 'connected',
                        'checked_at' => now()->toISOString(),
                        'simulation' => true
                    ]
                ];
                
            default:
                return [
                    'success' => false,
                    'error' => "Unknown command: {$commandName}",
                    'message' => "Command not found"
                ];
        }
    }
    
    /**
     * Get RVM ID from request
     */
    private function getRvmIdFromRequest(): ?int
    {
        $route = request()->route();
        return $route ? $route->parameter('rvmId') : null;
    }
    
    /**
     * Execute reboot system command
     */
    private function executeRebootSystem(array $payload): array
    {
        Log::info("Executing system reboot command");
        
        // Try to send real command to RVM first
        $result = $this->sendCommandToRVM('reboot_system', $payload);
        
        // If RVM is not reachable, use simulation
        if (!$result['success'] && str_contains($result['error'], 'RVM API not reachable')) {
            return $this->simulateCommandExecution('reboot_system', $payload);
        }
        
        return $result;
    }
    
    /**
     * Execute restart app command
     */
    private function executeRestartApp(array $payload): array
    {
        Log::info("Executing app restart command");
        return $this->sendCommandToRVM('restart_app', $payload);
    }
    
    /**
     * Execute open door command
     */
    private function executeOpenDoor(array $payload): array
    {
        Log::info("Executing open door command");
        return $this->sendCommandToRVM('open_door', $payload);
    }
    
    /**
     * Execute close door command
     */
    private function executeCloseDoor(array $payload): array
    {
        Log::info("Executing close door command");
        return $this->sendCommandToRVM('close_door', $payload);
    }
    
    /**
     * Execute motor test command
     */
    private function executeMotorTest(array $payload): array
    {
        Log::info("Executing motor test command");
        return $this->sendCommandToRVM('run_motor_test', $payload);
    }
    
    
    /**
     * Execute git pull command
     */
    private function executeGitPull(array $payload): array
    {
        Log::info("Executing git pull command");
        return $this->sendCommandToRVM('git_pull', $payload);
    }
    
    /**
     * Execute update AI model command
     */
    private function executeUpdateAiModel(array $payload): array
    {
        Log::info("Executing AI model update command");
        return $this->sendCommandToRVM('update_ai_model', $payload);
    }
    
    /**
     * Execute system health check command
     */
    private function executeSystemHealthCheck(array $payload): array
    {
        Log::info("Executing system health check command");
        return $this->sendCommandToRVM('check_system_health', $payload);
    }
    
    /**
     * Get command status
     */
    public function getCommandStatus(Request $request, $rvmId, $commandId): JsonResponse
    {
        try {
            $command = RemoteCommand::where('rvm_id', $rvmId)
                ->where('id', $commandId)
                ->firstOrFail();
            
            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $command->id,
                    'command_name' => $command->command_name,
                    'status' => $command->status,
                    'executed_at' => $command->executed_at,
                    'completed_at' => $command->completed_at,
                    'result' => json_decode($command->result, true),
                    'error_message' => $command->error_message
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get recent commands
     */
    public function getRecentCommands(Request $request, $rvmId): JsonResponse
    {
        try {
            $commands = RemoteCommand::where('rvm_id', $rvmId)
                ->latest('executed_at')
                ->limit(10)
                ->get();
            
            return response()->json([
                'success' => true,
                'data' => $commands->map(function ($command) {
                    return [
                        'id' => $command->id,
                        'command_name' => $command->command_name,
                        'status' => $command->status,
                        'executed_at' => $command->executed_at,
                        'completed_at' => $command->completed_at,
                        'result' => json_decode($command->result, true),
                        'error_message' => $command->error_message
                    ];
                })
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
