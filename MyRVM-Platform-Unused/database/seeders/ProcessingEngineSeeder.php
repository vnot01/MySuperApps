<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ProcessingEngine;
use App\Models\ReverseVendingMachine;
use Illuminate\Support\Facades\DB;

class ProcessingEngineSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing data
        DB::table('rvm_processing_engines')->truncate();
        DB::table('processing_engines')->truncate();

        // NVIDIA CUDA Servers (Real IP + Mock)
        $cudaServers = [
            [
                'name' => 'NVIDIA CUDA VM102 - Production',
                'type' => 'nvidia_cuda',
                'server_address' => '10.3.52.184', // REAL IP
                'port' => 8000,
                'gpu_memory_limit' => '8GB',
                'docker_gpu_passthrough' => true,
                'model_path' => '/models/yolo11n.pt',
                'processing_timeout' => 30,
                'auto_failover' => true,
                'is_active' => true,
                'is_online' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'NVIDIA CUDA VM102 - Secondary',
                'type' => 'nvidia_cuda',
                'server_address' => '192.168.1.51', // MOCK
                'port' => 8000,
                'gpu_memory_limit' => '16GB',
                'docker_gpu_passthrough' => true,
                'model_path' => '/models/yolo11s.pt',
                'processing_timeout' => 45,
                'auto_failover' => true,
                'is_active' => false,
                'is_online' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'NVIDIA CUDA VM102 - Backup',
                'type' => 'nvidia_cuda',
                'server_address' => '192.168.1.52', // MOCK
                'port' => 8000,
                'gpu_memory_limit' => '4GB',
                'docker_gpu_passthrough' => true,
                'model_path' => '/models/yolo11n.pt',
                'processing_timeout' => 60,
                'auto_failover' => false,
                'is_active' => false,
                'is_online' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        // Jetson Edge Computing (Generated from RVM data)
        $jetsonServers = [];
        $rvms = DB::table('reverse_vending_machines')->get();
        
        if ($rvms->count() > 0) {
            foreach ($rvms as $index => $rvm) {
                $jetsonServers[] = [
                    'name' => "Jetson Orin - {$rvm->name}",
                    'type' => 'jetson_edge',
                    'server_address' => '192.168.1.' . (100 + $index), // Generate IP based on RVM index
                    'port' => 8080,
                    'gpu_memory_limit' => null,
                    'docker_gpu_passthrough' => false,
                    'model_path' => '/home/jetson/models/',
                    'processing_timeout' => 30,
                    'auto_failover' => true,
                    'is_active' => true,
                    'is_online' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        } else {
            // Fallback if no RVMs exist
            $jetsonServers = [
                [
                    'name' => 'Jetson Orin - RVM-001',
                    'type' => 'jetson_edge',
                    'server_address' => '192.168.1.100',
                    'port' => 8080,
                    'gpu_memory_limit' => null,
                    'docker_gpu_passthrough' => false,
                    'model_path' => '/home/jetson/models/',
                    'processing_timeout' => 30,
                    'auto_failover' => true,
                    'is_active' => true,
                    'is_online' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'Jetson Orin - RVM-002',
                    'type' => 'jetson_edge',
                    'server_address' => '192.168.1.101',
                    'port' => 8080,
                    'gpu_memory_limit' => null,
                    'docker_gpu_passthrough' => false,
                    'model_path' => '/home/jetson/models/',
                    'processing_timeout' => 30,
                    'auto_failover' => true,
                    'is_active' => true,
                    'is_online' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ];
        }

        // Insert all processing engines
        $allEngines = array_merge($cudaServers, $jetsonServers);
        DB::table('processing_engines')->insert($allEngines);

        // Create RVM-ProcessingEngine relationships
        $this->createRvmEngineRelationships();

        $this->command->info('Processing engines seeded successfully!');
        $this->command->info('NVIDIA CUDA servers: ' . count($cudaServers));
        $this->command->info('Jetson Edge servers: ' . count($jetsonServers));
        $this->command->info('Total engines: ' . count($allEngines));
    }

    /**
     * Create RVM-ProcessingEngine relationships
     */
    private function createRvmEngineRelationships(): void
    {
        $rvms = DB::table('reverse_vending_machines')->get();
        $cudaEngines = DB::table('processing_engines')->where('type', 'nvidia_cuda')->get();
        $jetsonEngines = DB::table('processing_engines')->where('type', 'jetson_edge')->get();

        $relationships = [];

        foreach ($rvms as $rvm) {
            // Assign primary CUDA engine (first active one)
            $primaryCuda = $cudaEngines->where('is_active', true)->first();
            if ($primaryCuda) {
                $relationships[] = [
                    'rvm_id' => $rvm->id,
                    'processing_engine_id' => $primaryCuda->id,
                    'priority' => 'primary',
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            // Assign Jetson engine for this RVM
            $jetsonEngine = $jetsonEngines->where('name', 'like', "%{$rvm->name}%")->first();
            if ($jetsonEngine) {
                $relationships[] = [
                    'rvm_id' => $rvm->id,
                    'processing_engine_id' => $jetsonEngine->id,
                    'priority' => 'secondary',
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            // Assign backup CUDA engine if available
            $backupCuda = $cudaEngines->where('is_active', false)->first();
            if ($backupCuda) {
                $relationships[] = [
                    'rvm_id' => $rvm->id,
                    'processing_engine_id' => $backupCuda->id,
                    'priority' => 'backup',
                    'is_active' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        if (!empty($relationships)) {
            DB::table('rvm_processing_engines')->insert($relationships);
            $this->command->info('Created ' . count($relationships) . ' RVM-ProcessingEngine relationships');
        }
    }
}