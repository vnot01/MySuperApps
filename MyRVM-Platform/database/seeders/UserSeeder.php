<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get or create User role
        $userRole = Role::where('name', 'User')->first();
        if (!$userRole) {
            $userRole = Role::create([
                'name' => 'User',
                'slug' => 'user'
            ]);
        }

        // Create test users
        $users = [
            [
                'name' => 'John Doe',
                'email' => 'john@test.com',
                'password' => Hash::make('password123'),
                'role_id' => $userRole->id,
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Jane Smith',
                'email' => 'jane@test.com',
                'password' => Hash::make('password123'),
                'role_id' => $userRole->id,
                'email_verified_at' => now(),
            ],
        ];

        foreach ($users as $userData) {
            User::updateOrCreate(
                ['email' => $userData['email']],
                $userData
            );
        }
    }
}
