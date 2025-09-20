<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class CreateAdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Admin role if it doesn't exist
        $adminRole = Role::firstOrCreate(
            ['name' => 'Admin'],
            ['slug' => 'admin']
        );

        // Create admin user
        $adminUser = User::firstOrCreate(
            ['email' => 'admin@myrvm.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('admin123'),
                'role_id' => $adminRole->id,
                'email_verified_at' => now(),
            ]
        );

        $this->command->info('Admin user created successfully!');
        $this->command->info('Email: admin@myrvm.com');
        $this->command->info('Password: admin123');
    }
}

