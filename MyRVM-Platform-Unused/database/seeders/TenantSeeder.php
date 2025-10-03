<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Tenant;

class TenantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tenants = [
            [
                'name' => 'MyRVM Platform',
                'description' => 'Main platform tenant',
                'is_active' => true,
            ],
            [
                'name' => 'Green Store',
                'description' => 'Eco-friendly store partner',
                'is_active' => true,
            ],
        ];

        foreach ($tenants as $tenant) {
            Tenant::create($tenant);
        }
    }
}
