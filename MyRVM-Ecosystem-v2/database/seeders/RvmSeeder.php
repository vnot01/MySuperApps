<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ReverseVendingMachine;
use Carbon\Carbon;

class RvmSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rvms = [
            [
                'name' => 'RVM-001',
                'location' => 'Mall Central Jakarta',
                'address' => 'Jl. Sudirman No. 1, Jakarta Pusat',
                'latitude' => -6.2088,
                'longitude' => 106.8456,
                'status' => 'active',
                'capacity' => 100,
                'current_load' => 45,
                'ip_address' => '100.117.234.2',
                'last_ping' => Carbon::now()->subMinutes(2),
                'configuration' => [
                    'auto_sort' => true,
                    'max_items_per_session' => 50,
                    'reward_multiplier' => 1.0
                ],
                'metrics' => [
                    'cpu_usage' => 35.2,
                    'memory_usage' => 67.8,
                    'temperature' => 42.5,
                    'uptime_hours' => 168
                ]
            ],
            [
                'name' => 'RVM-002',
                'location' => 'Station Plaza',
                'address' => 'Jl. Thamrin No. 2, Jakarta Pusat',
                'latitude' => -6.1944,
                'longitude' => 106.8229,
                'status' => 'active',
                'capacity' => 150,
                'current_load' => 78,
                'ip_address' => '100.98.142.94',
                'last_ping' => Carbon::now()->subMinutes(1),
                'configuration' => [
                    'auto_sort' => true,
                    'max_items_per_session' => 75,
                    'reward_multiplier' => 1.2
                ],
                'metrics' => [
                    'cpu_usage' => 42.1,
                    'memory_usage' => 58.3,
                    'temperature' => 38.7,
                    'uptime_hours' => 240
                ]
            ],
            [
                'name' => 'RVM-003',
                'location' => 'University Campus',
                'address' => 'Jl. Salemba No. 3, Jakarta Pusat',
                'latitude' => -6.1944,
                'longitude' => 106.8500,
                'status' => 'maintenance',
                'capacity' => 80,
                'current_load' => 0,
                'ip_address' => '192.168.1.100',
                'last_ping' => Carbon::now()->subHours(2),
                'last_maintenance' => Carbon::now()->subDays(1),
                'configuration' => [
                    'auto_sort' => false,
                    'max_items_per_session' => 40,
                    'reward_multiplier' => 0.8
                ],
                'metrics' => [
                    'cpu_usage' => 0,
                    'memory_usage' => 0,
                    'temperature' => 25.0,
                    'uptime_hours' => 0
                ]
            ],
            [
                'name' => 'RVM-004',
                'location' => 'Shopping Center',
                'address' => 'Jl. Kemang Raya No. 4, Jakarta Selatan',
                'latitude' => -6.2615,
                'longitude' => 106.8106,
                'status' => 'active',
                'capacity' => 120,
                'current_load' => 92,
                'ip_address' => '192.168.1.101',
                'last_ping' => Carbon::now()->subMinutes(3),
                'configuration' => [
                    'auto_sort' => true,
                    'max_items_per_session' => 60,
                    'reward_multiplier' => 1.1
                ],
                'metrics' => [
                    'cpu_usage' => 48.7,
                    'memory_usage' => 72.1,
                    'temperature' => 45.2,
                    'uptime_hours' => 96
                ]
            ],
            [
                'name' => 'RVM-005',
                'location' => 'Office Building',
                'address' => 'Jl. Kuningan No. 5, Jakarta Selatan',
                'latitude' => -6.2297,
                'longitude' => 106.8253,
                'status' => 'inactive',
                'capacity' => 100,
                'current_load' => 15,
                'ip_address' => '192.168.1.102',
                'last_ping' => Carbon::now()->subHours(6),
                'configuration' => [
                    'auto_sort' => true,
                    'max_items_per_session' => 50,
                    'reward_multiplier' => 1.0
                ],
                'metrics' => [
                    'cpu_usage' => 0,
                    'memory_usage' => 0,
                    'temperature' => 28.0,
                    'uptime_hours' => 0
                ]
            ],
            [
                'name' => 'RVM-006',
                'location' => 'Airport Terminal',
                'address' => 'Soekarno-Hatta International Airport',
                'latitude' => -6.1275,
                'longitude' => 106.6537,
                'status' => 'active',
                'capacity' => 200,
                'current_load' => 156,
                'ip_address' => '192.168.1.103',
                'last_ping' => Carbon::now()->subMinutes(1),
                'configuration' => [
                    'auto_sort' => true,
                    'max_items_per_session' => 100,
                    'reward_multiplier' => 1.5
                ],
                'metrics' => [
                    'cpu_usage' => 52.3,
                    'memory_usage' => 78.9,
                    'temperature' => 41.8,
                    'uptime_hours' => 336
                ]
            ]
        ];

        foreach ($rvms as $rvmData) {
            $rvm = ReverseVendingMachine::create($rvmData);
            $rvm->generateApiKey();
        }
    }
}