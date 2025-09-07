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
            [
                'name' => 'RVM-001',
                'location_description' => 'Lobby Gedung A, Lantai 1',
                'status' => 'active',
                'api_key' => Str::random(32),
            ],
            [
                'name' => 'RVM-002',
                'location_description' => 'Food Court, Lantai 2',
                'status' => 'active',
                'api_key' => Str::random(32),
            ],
            [
                'name' => 'RVM-003',
                'location_description' => 'Parking Area, Basement',
                'status' => 'maintenance',
                'api_key' => Str::random(32),
            ],
        ];

        foreach ($rvms as $rvm) {
            ReverseVendingMachine::create($rvm);
        }
    }
}
