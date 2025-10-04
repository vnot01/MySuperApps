<?php

namespace Database\Seeders;

use App\Models\ReverseVendingMachine;
use Illuminate\Database\Seeder;

class ReverseVendingMachineSeeder extends Seeder
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
                'address' => 'Jl. Thamrin No. 1, Jakarta Pusat',
                'latitude' => -6.1944,
                'longitude' => 106.8229,
                'status' => 'active',
                'capacity' => 100,
                'current_load' => 15,
                'ip_address' => '100.117.234.2',
                'configuration' => [
                    'detection_enabled' => true,
                    'auto_sorting' => true,
                    'maintenance_mode' => false
                ],
                'metrics' => [
                    'total_deposits' => 1250,
                    'last_maintenance' => '2025-01-15',
                    'uptime_percentage' => 98.5
                ]
            ],
            [
                'name' => 'RVM-002',
                'location' => 'Bandara Soekarno-Hatta',
                'address' => 'Terminal 3, Cengkareng, Tangerang',
                'latitude' => -6.1256,
                'longitude' => 106.6558,
                'status' => 'active',
                'capacity' => 100,
                'current_load' => 40,
                'ip_address' => '100.117.234.3',
                'configuration' => [
                    'detection_enabled' => true,
                    'auto_sorting' => true,
                    'maintenance_mode' => false
                ],
                'metrics' => [
                    'total_deposits' => 2100,
                    'last_maintenance' => '2025-01-10',
                    'uptime_percentage' => 99.2
                ]
            ],
            [
                'name' => 'RVM-003',
                'location' => 'Universitas Indonesia',
                'address' => 'Depok, Jawa Barat',
                'latitude' => -6.3614,
                'longitude' => 106.8306,
                'status' => 'maintenance',
                'capacity' => 100,
                'current_load' => 0,
                'ip_address' => '100.117.234.4',
                'configuration' => [
                    'detection_enabled' => false,
                    'auto_sorting' => false,
                    'maintenance_mode' => true
                ],
                'metrics' => [
                    'total_deposits' => 890,
                    'last_maintenance' => '2025-01-20',
                    'uptime_percentage' => 95.8
                ]
            ]
        ];

        foreach ($rvms as $rvmData) {
            ReverseVendingMachine::create($rvmData);
        }
    }
}
