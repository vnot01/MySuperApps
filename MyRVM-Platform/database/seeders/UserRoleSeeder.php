<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class UserRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create roles if they don't exist
        $roles = [
            ['name' => 'Super Admin', 'slug' => 'super-admin'],
            ['name' => 'Admin', 'slug' => 'admin'],
            ['name' => 'Operator', 'slug' => 'operator'],
            ['name' => 'Technician', 'slug' => 'technician'],
            ['name' => 'Tenant', 'slug' => 'tenant'],
            ['name' => 'User', 'slug' => 'user'],
        ];

        foreach ($roles as $roleData) {
            Role::firstOrCreate(
                ['slug' => $roleData['slug']],
                $roleData
            );
        }

        // Create test users
        $users = [
            [
                'name' => 'Super Admin',
                'email' => 'admin@myrvm.com',
                'password' => Hash::make('password'),
                'role_slug' => 'super-admin',
            ],
            [
                'name' => 'Admin User',
                'email' => 'admin2@myrvm.com',
                'password' => Hash::make('password'),
                'role_slug' => 'admin',
            ],
            [
                'name' => 'Operator User',
                'email' => 'operator@myrvm.com',
                'password' => Hash::make('password'),
                'role_slug' => 'operator',
            ],
            [
                'name' => 'Technician User',
                'email' => 'technician@myrvm.com',
                'password' => Hash::make('password'),
                'role_slug' => 'technician',
            ],
            [
                'name' => 'Tenant User',
                'email' => 'tenant@myrvm.com',
                'password' => Hash::make('password'),
                'role_slug' => 'tenant',
            ],
        ];

        foreach ($users as $userData) {
            $role = Role::where('slug', $userData['role_slug'])->first();
            unset($userData['role_slug']);
            
            $user = User::firstOrCreate(
                ['email' => $userData['email']],
                array_merge($userData, [
                    'role_id' => $role->id,
                    'email_verified_at' => now(),
                ])
            );
        }

        $this->command->info('Users and roles created successfully!');
        $this->command->info('Test credentials:');
        $this->command->info('Super Admin: admin@myrvm.com / password');
        $this->command->info('Admin: admin2@myrvm.com / password');
        $this->command->info('Operator: operator@myrvm.com / password');
        $this->command->info('Technician: technician@myrvm.com / password');
        $this->command->info('Tenant: tenant@myrvm.com / password');
    }
}
