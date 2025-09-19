<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ReverseVendingMachine;
use App\Models\TimezoneSyncLog;

class ReverseVendingMachineSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing data
        TimezoneSyncLog::truncate();
        ReverseVendingMachine::truncate();

        // Create 3 RVMs with realistic data
        $rvms = [
            [
                'name' => 'RVM-001',
                'location' => 'Jakarta Airport Terminal 1',
                'address' => 'Soekarno-Hatta International Airport, Tangerang, Banten',
                'ip_address' => '0.0.0.0', // Dummy data
                'port' => 8000,
                'timezone' => 'Asia/Jakarta',
                'timezone_offset' => '+07:00',
                'status' => 'active',
                'capacity' => 45,
                'location_description' => 'Jakarta Airport Terminal 1',
                'api_key' => 'rvm_' . strtoupper(substr(md5(uniqid(rand(), true)), 0, 16)),
                'connection_status' => 'unknown',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'RVM-002',
                'location' => 'Bandung City Center',
                'address' => 'Jl. Asia Afrika, Bandung, Jawa Barat',
                'ip_address' => '0.0.0.0', // Dummy data
                'port' => 8000,
                'timezone' => 'Asia/Jakarta',
                'timezone_offset' => '+07:00',
                'status' => 'active',
                'capacity' => 78,
                'location_description' => 'Bandung City Center',
                'api_key' => 'rvm_' . strtoupper(substr(md5(uniqid(rand(), true)), 0, 16)),
                'connection_status' => 'unknown',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'RVM-003',
                'location' => 'Surabaya Mall',
                'address' => 'Jl. Raya Darmo, Surabaya, Jawa Timur',
                'ip_address' => '0.0.0.0', // Dummy data
                'port' => 8000,
                'timezone' => 'Asia/Jakarta',
                'timezone_offset' => '+07:00',
                'status' => 'active',
                'capacity' => 92,
                'location_description' => 'Surabaya Mall',
                'api_key' => 'rvm_' . strtoupper(substr(md5(uniqid(rand(), true)), 0, 16)),
                'connection_status' => 'unknown',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($rvms as $rvmData) {
            $rvm = ReverseVendingMachine::create($rvmData);
            
            // Create initial timezone sync log for each RVM
            TimezoneSyncLog::create([
                'device_id' => $rvm->id,
                'device_type' => 'rvm',
                'sync_type' => 'automatic',
                'timezone' => $rvm->timezone, // Required field
                'old_timezone' => null,
                'new_timezone' => $rvm->timezone,
                'sync_timestamp' => now(),
                'status' => 'success',
                'details' => json_encode([
                    'timezone' => $rvm->timezone,
                    'timezone_offset' => $rvm->timezone_offset,
                    'sync_method' => 'initial_setup'
                ]),
                'ip_address' => $rvm->ip_address,
                'country' => 'Indonesia',
                'city' => explode(',', $rvm->address)[0] ?? 'Unknown',
                'sync_method' => 'initial_setup'
            ]);
        }

        $this->command->info('✅ Created 3 RVMs with timezone sync logs');
    }
}
