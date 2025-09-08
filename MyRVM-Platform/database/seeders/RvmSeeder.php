<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ReverseVendingMachine;
use Illuminate\Support\Str;

class RvmSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rvms = [
            // Gedung A
            [
                'name' => 'RVM-001',
                'location_description' => 'Lobby Gedung A, Lantai 1',
                'status' => 'active',
                'api_key' => 'RVM_' . Str::random(16),
                'created_at' => now()->subDays(30),
                'updated_at' => now()->subHours(2),
            ],
            [
                'name' => 'RVM-002',
                'location_description' => 'Food Court, Lantai 2',
                'status' => 'active',
                'api_key' => 'RVM_' . Str::random(16),
                'created_at' => now()->subDays(25),
                'updated_at' => now()->subMinutes(30),
            ],
            [
                'name' => 'RVM-003',
                'location_description' => 'Parking Area, Basement',
                'status' => 'maintenance',
                'api_key' => 'RVM_' . Str::random(16),
                'created_at' => now()->subDays(20),
                'updated_at' => now()->subHours(5),
            ],
            [
                'name' => 'RVM-004',
                'location_description' => 'Kantin Karyawan, Lantai 3',
                'status' => 'active',
                'api_key' => 'RVM_' . Str::random(16),
                'created_at' => now()->subDays(15),
                'updated_at' => now()->subMinutes(15),
            ],
            [
                'name' => 'RVM-005',
                'location_description' => 'Ruang Meeting, Lantai 4',
                'status' => 'inactive',
                'api_key' => 'RVM_' . Str::random(16),
                'created_at' => now()->subDays(12),
                'updated_at' => now()->subDays(1),
            ],
            
            // Gedung B
            [
                'name' => 'RVM-006',
                'location_description' => 'Lobby Gedung B, Lantai 1',
                'status' => 'active',
                'api_key' => 'RVM_' . Str::random(16),
                'created_at' => now()->subDays(10),
                'updated_at' => now()->subMinutes(45),
            ],
            [
                'name' => 'RVM-007',
                'location_description' => 'Café Area, Lantai 2',
                'status' => 'full',
                'api_key' => 'RVM_' . Str::random(16),
                'created_at' => now()->subDays(8),
                'updated_at' => now()->subHours(1),
            ],
            [
                'name' => 'RVM-008',
                'location_description' => 'Ruang Tunggu, Lantai 3',
                'status' => 'active',
                'api_key' => 'RVM_' . Str::random(16),
                'created_at' => now()->subDays(6),
                'updated_at' => now()->subMinutes(20),
            ],
            [
                'name' => 'RVM-009',
                'location_description' => 'Area Smoking, Lantai 4',
                'status' => 'error',
                'api_key' => 'RVM_' . Str::random(16),
                'created_at' => now()->subDays(4),
                'updated_at' => now()->subHours(3),
            ],
            [
                'name' => 'RVM-010',
                'location_description' => 'Ruang Server, Lantai 5',
                'status' => 'active',
                'api_key' => 'RVM_' . Str::random(16),
                'created_at' => now()->subDays(3),
                'updated_at' => now()->subMinutes(10),
            ],
            
            // Gedung C
            [
                'name' => 'RVM-011',
                'location_description' => 'Lobby Gedung C, Lantai 1',
                'status' => 'active',
                'api_key' => 'RVM_' . Str::random(16),
                'created_at' => now()->subDays(2),
                'updated_at' => now()->subMinutes(5),
            ],
            [
                'name' => 'RVM-012',
                'location_description' => 'Food Court, Lantai 2',
                'status' => 'maintenance',
                'api_key' => 'RVM_' . Str::random(16),
                'created_at' => now()->subDays(1),
                'updated_at' => now()->subHours(2),
            ],
            [
                'name' => 'RVM-013',
                'location_description' => 'Ruang Seminar, Lantai 3',
                'status' => 'active',
                'api_key' => 'RVM_' . Str::random(16),
                'created_at' => now()->subHours(12),
                'updated_at' => now()->subMinutes(2),
            ],
            [
                'name' => 'RVM-014',
                'location_description' => 'Area Parkir, Basement',
                'status' => 'inactive',
                'api_key' => 'RVM_' . Str::random(16),
                'created_at' => now()->subHours(8),
                'updated_at' => now()->subHours(6),
            ],
            [
                'name' => 'RVM-015',
                'location_description' => 'Ruang Gym, Lantai 4',
                'status' => 'active',
                'api_key' => 'RVM_' . Str::random(16),
                'created_at' => now()->subHours(4),
                'updated_at' => now()->subMinutes(1),
            ],
            
            // Area Outdoor
            [
                'name' => 'RVM-016',
                'location_description' => 'Taman Depan Gedung',
                'status' => 'active',
                'api_key' => 'RVM_' . Str::random(16),
                'created_at' => now()->subHours(2),
                'updated_at' => now()->subMinutes(30),
            ],
            [
                'name' => 'RVM-017',
                'location_description' => 'Area Parkir Outdoor',
                'status' => 'full',
                'api_key' => 'RVM_' . Str::random(16),
                'created_at' => now()->subHours(1),
                'updated_at' => now()->subMinutes(15),
            ],
            [
                'name' => 'RVM-018',
                'location_description' => 'Pintu Masuk Utama',
                'status' => 'active',
                'api_key' => 'RVM_' . Str::random(16),
                'created_at' => now()->subMinutes(30),
                'updated_at' => now()->subMinutes(5),
            ],
            [
                'name' => 'RVM-019',
                'location_description' => 'Area Drop-off',
                'status' => 'unknown',
                'api_key' => 'RVM_' . Str::random(16),
                'created_at' => now()->subMinutes(15),
                'updated_at' => now()->subMinutes(10),
            ],
            [
                'name' => 'RVM-020',
                'location_description' => 'Pintu Keluar Utama',
                'status' => 'active',
                'api_key' => 'RVM_' . Str::random(16),
                'created_at' => now()->subMinutes(5),
                'updated_at' => now()->subMinutes(1),
            ],
        ];

        foreach ($rvms as $rvm) {
            ReverseVendingMachine::create($rvm);
        }
    }
}
