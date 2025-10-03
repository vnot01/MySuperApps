<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class DemoCredentialsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Pastikan role-role yang diperlukan ada
        $this->createRolesIfNotExists();
        
        // Buat demo users
        $this->createDemoUsers();
    }

    private function createRolesIfNotExists(): void
    {
        $roles = [
            ['name' => 'Super Admin', 'slug' => 'super-admin'],
            ['name' => 'Admin', 'slug' => 'admin'],
            ['name' => 'Operator', 'slug' => 'operator'],
            ['name' => 'Tenant', 'slug' => 'tenant'],
            ['name' => 'User', 'slug' => 'user'],
        ];

        foreach ($roles as $roleData) {
            Role::firstOrCreate(
                ['slug' => $roleData['slug']],
                $roleData
            );
        }

        // Buat permissions jika belum ada
        $permissions = [
            ['name' => 'Manage All', 'slug' => 'manage.all'],
            ['name' => 'Manage Tenants', 'slug' => 'manage.tenants'],
            ['name' => 'Manage Users', 'slug' => 'manage.users'],
            ['name' => 'Manage Vouchers', 'slug' => 'manage.vouchers'],
            ['name' => 'Manage RVM', 'slug' => 'manage.rvm'],
            ['name' => 'View Dashboard', 'slug' => 'view.dashboard'],
            ['name' => 'Operate RVM', 'slug' => 'operate.rvm'],
        ];

        foreach ($permissions as $permissionData) {
            Permission::firstOrCreate(
                ['slug' => $permissionData['slug']],
                $permissionData
            );
        }

        // Assign permissions to roles
        $this->assignPermissionsToRoles();
    }

    private function assignPermissionsToRoles(): void
    {
        $superAdminRole = Role::where('slug', 'super-admin')->first();
        $adminRole = Role::where('slug', 'admin')->first();
        $operatorRole = Role::where('slug', 'operator')->first();
        $tenantRole = Role::where('slug', 'tenant')->first();
        $userRole = Role::where('slug', 'user')->first();

        // Super Admin - semua permissions
        $allPermissions = Permission::all()->pluck('id')->toArray();
        $superAdminRole->permissions()->sync($allPermissions);

        // Admin - hampir semua kecuali manage all
        $adminPermissions = Permission::whereIn('slug', [
            'manage.tenants',
            'manage.users',
            'manage.vouchers',
            'manage.rvm',
            'view.dashboard'
        ])->pluck('id')->toArray();
        $adminRole->permissions()->sync($adminPermissions);

        // Operator - operasional RVM
        $operatorPermissions = Permission::whereIn('slug', [
            'operate.rvm',
            'manage.vouchers',
            'view.dashboard'
        ])->pluck('id')->toArray();
        $operatorRole->permissions()->sync($operatorPermissions);

        // Tenant - manage vouchers dan view dashboard
        $tenantPermissions = Permission::whereIn('slug', [
            'manage.vouchers',
            'view.dashboard'
        ])->pluck('id')->toArray();
        $tenantRole->permissions()->sync($tenantPermissions);

        // User - hanya view dashboard
        $userPermissions = Permission::whereIn('slug', [
            'view.dashboard'
        ])->pluck('id')->toArray();
        $userRole->permissions()->sync($userPermissions);
    }

    private function createDemoUsers(): void
    {
        $superAdminRole = Role::where('slug', 'super-admin')->first();
        $adminRole = Role::where('slug', 'admin')->first();
        $operatorRole = Role::where('slug', 'operator')->first();
        $tenantRole = Role::where('slug', 'tenant')->first();
        $userRole = Role::where('slug', 'user')->first();

        $demoUsers = [
            [
                'name' => 'Super Admin Demo',
                'email' => 'admin@myrvm.com',
                'password' => Hash::make('password'),
                'role_id' => $superAdminRole->id,
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Admin Demo',
                'email' => 'admin2@myrvm.com',
                'password' => Hash::make('password'),
                'role_id' => $adminRole->id,
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Operator Demo',
                'email' => 'operator@myrvm.com',
                'password' => Hash::make('password'),
                'role_id' => $operatorRole->id,
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Tenant Demo',
                'email' => 'tenant@myrvm.com',
                'password' => Hash::make('password'),
                'role_id' => $tenantRole->id,
                'email_verified_at' => now(),
            ],
            [
                'name' => 'User Demo',
                'email' => 'user@myrvm.com',
                'password' => Hash::make('password'),
                'role_id' => $userRole->id,
                'email_verified_at' => now(),
            ],
        ];

        foreach ($demoUsers as $userData) {
            User::updateOrCreate(
                ['email' => $userData['email']],
                $userData
            );
        }

        $this->command->info('Demo credentials created successfully!');
        $this->command->info('Demo accounts:');
        $this->command->info('- Super Admin: admin@myrvm.com / password');
        $this->command->info('- Admin: admin2@myrvm.com / password');
        $this->command->info('- Operator: operator@myrvm.com / password');
        $this->command->info('- Tenant: tenant@myrvm.com / password');
        $this->command->info('- User: user@myrvm.com / password');
    }
}