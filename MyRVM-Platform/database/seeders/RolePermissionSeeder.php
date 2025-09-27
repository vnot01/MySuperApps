<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;
use App\Models\User; // <-- TAMBAHKAN IMPORT USER
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash; // <-- TAMBAHKAN IMPORT HASH

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Hapus data lama untuk seeding yang bersih
        DB::statement('SET CONSTRAINTS ALL DEFERRED');
        DB::table('permission_role')->truncate();
        Permission::truncate();
        // Hapus User sebelum Role agar tidak ada foreign key constraint violation
        User::truncate();
        Role::truncate();
        DB::statement('SET CONSTRAINTS ALL IMMEDIATE');

        // Buat Roles
        $superAdminRole = Role::create(['name' => 'Super Admin', 'slug' => 'super-admin']);
        $adminRole = Role::create(['name' => 'Admin', 'slug' => 'admin']);
        $tenantRole = Role::create(['name' => 'Tenant', 'slug' => 'tenant']);
        $userRole = Role::create(['name' => 'User', 'slug' => 'user']);

        // Buat Permissions (contoh)
        $manageTenantsPermission = Permission::create(['name' => 'Manage Tenants', 'slug' => 'manage.tenants']);
        $manageUsersPermission = Permission::create(['name' => 'Manage Users', 'slug' => 'manage.users']);
        $manageVouchersPermission = Permission::create(['name' => 'Manage Vouchers', 'slug' => 'manage.vouchers']);
        $viewDashboardPermission = Permission::create(['name' => 'View Dashboard', 'slug' => 'view.dashboard']);

        // Hubungkan Permissions ke Roles
        $superAdminRole->permissions()->attach([
            $manageTenantsPermission->id,
            $manageUsersPermission->id,
            $manageVouchersPermission->id,
            $viewDashboardPermission->id,
        ]);

        $adminRole->permissions()->attach([
            $manageUsersPermission->id,
            $manageVouchersPermission->id,
            $viewDashboardPermission->id,
        ]);

        $tenantRole->permissions()->attach([
            $manageVouchersPermission->id,
            $viewDashboardPermission->id,
        ]);

        // --- TAMBAHKAN BLOK INI UNTUK MEMBUAT USER CONTOH ---
        // Buat Super Admin
        User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'role_id' => $superAdminRole->id, // Hubungkan ke role Super Admin
        ]);

        // Buat Admin
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'role_id' => $adminRole->id, // Hubungkan ke role Admin
        ]);

        // (Opsional) Buat User Biasa
        User::create([
            'name' => 'Test User',
            'email' => 'user@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'role_id' => $userRole->id, // Hubungkan ke role User
        ]);
    }
}
