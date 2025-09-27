<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;
use App\Models\User;
use App\Models\Tenant;
use App\Models\ReverseVendingMachine;
use App\Models\Voucher;
use App\Models\UserBalance;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DummyDataSeeder extends Seeder
{
    public function run(): void
    {
        // Hapus data lama untuk seeding yang bersih
        DB::statement('SET CONSTRAINTS ALL DEFERRED');
        DB::table('permission_role')->truncate();
        DB::table('voucher_redemptions')->truncate();
        Transaction::truncate();
        UserBalance::truncate();
        Voucher::truncate();
        ReverseVendingMachine::truncate();
        Tenant::truncate();
        Permission::truncate();
        User::truncate();
        Role::truncate();
        DB::statement('SET CONSTRAINTS ALL IMMEDIATE');

        // Buat Roles
        $superAdminRole = Role::create(['name' => 'Super Admin', 'slug' => 'super-admin']);
        $adminRole = Role::create(['name' => 'Admin', 'slug' => 'admin']);
        $tenantRole = Role::create(['name' => 'Tenant', 'slug' => 'tenant']);
        $userRole = Role::create(['name' => 'User', 'slug' => 'user']);

        // Buat Permissions
        $permissions = [
            ['name' => 'Manage Tenants', 'slug' => 'manage.tenants'],
            ['name' => 'Manage Users', 'slug' => 'manage.users'],
            ['name' => 'Manage Vouchers', 'slug' => 'manage.vouchers'],
            ['name' => 'Manage RVMs', 'slug' => 'manage.rvms'],
            ['name' => 'View Dashboard', 'slug' => 'view.dashboard'],
            ['name' => 'View Analytics', 'slug' => 'view.analytics'],
            ['name' => 'Manage Deposits', 'slug' => 'manage.deposits'],
            ['name' => 'Manage Transactions', 'slug' => 'manage.transactions'],
            ['name' => 'View Reports', 'slug' => 'view.reports'],
            ['name' => 'Remote Access RVM', 'slug' => 'remote.access.rvm'],
        ];

        $createdPermissions = [];
        foreach ($permissions as $permission) {
            $createdPermissions[$permission['slug']] = Permission::create($permission);
        }

        // Hubungkan Permissions ke Roles
        $superAdminRole->permissions()->attach([
            $createdPermissions['manage.tenants']->id,
            $createdPermissions['manage.users']->id,
            $createdPermissions['manage.vouchers']->id,
            $createdPermissions['manage.rvms']->id,
            $createdPermissions['view.dashboard']->id,
            $createdPermissions['view.analytics']->id,
            $createdPermissions['manage.deposits']->id,
            $createdPermissions['manage.transactions']->id,
            $createdPermissions['view.reports']->id,
            $createdPermissions['remote.access.rvm']->id,
        ]);

        $adminRole->permissions()->attach([
            $createdPermissions['manage.users']->id,
            $createdPermissions['manage.vouchers']->id,
            $createdPermissions['manage.rvms']->id,
            $createdPermissions['view.dashboard']->id,
            $createdPermissions['view.analytics']->id,
            $createdPermissions['manage.deposits']->id,
            $createdPermissions['manage.transactions']->id,
            $createdPermissions['view.reports']->id,
            $createdPermissions['remote.access.rvm']->id,
        ]);

        $tenantRole->permissions()->attach([
            $createdPermissions['manage.vouchers']->id,
            $createdPermissions['view.dashboard']->id,
            $createdPermissions['view.analytics']->id,
            $createdPermissions['view.reports']->id,
        ]);

        $userRole->permissions()->attach([
            $createdPermissions['view.dashboard']->id,
        ]);

        // Buat Tenants
        $tenants = [
            [
                'name' => 'Mall Kelapa Gading',
                'description' => 'Mall terbesar di Jakarta Utara dengan berbagai tenant makanan dan minuman. Contact: admin@mallkelapagading.com, +62-21-12345678. Address: Jl. Boulevard Barat Raya, Kelapa Gading, Jakarta Utara',
                'is_active' => true,
            ],
            [
                'name' => 'Plaza Senayan',
                'description' => 'Mall premium di Jakarta Selatan dengan tenant fashion dan lifestyle. Contact: admin@plazasenayan.com, +62-21-87654321. Address: Jl. Asia Afrika No. 8, Senayan, Jakarta Selatan',
                'is_active' => true,
            ],
            [
                'name' => 'Grand Indonesia',
                'description' => 'Mall terintegrasi dengan hotel dan office di Jakarta Pusat. Contact: admin@grandindonesia.com, +62-21-11223344. Address: Jl. M.H. Thamrin No. 1, Menteng, Jakarta Pusat',
                'is_active' => true,
            ],
        ];

        $createdTenants = [];
        foreach ($tenants as $tenant) {
            $createdTenants[] = Tenant::create($tenant);
        }

        // Buat Users
        $users = [
            // Demo Credentials - Super Admin
            [
                'name' => 'Super Admin',
                'email' => 'admin@myrvm.com',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'role_id' => $superAdminRole->id,
                'tenant_id' => null,
            ],
            // Demo Credentials - Admin
            [
                'name' => 'Admin User',
                'email' => 'admin2@myrvm.com',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'role_id' => $adminRole->id,
                'tenant_id' => null,
            ],
            // Demo Credentials - Operator
            [
                'name' => 'Operator MyRVM',
                'email' => 'operator@myrvm.com',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'role_id' => $adminRole->id, // Operator menggunakan role admin
                'tenant_id' => null,
            ],
            // Tenant Users
            [
                'name' => 'Mall Kelapa Gading Manager',
                'email' => 'manager@mallkelapagading.com',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
                'role_id' => $tenantRole->id,
                'tenant_id' => $createdTenants[0]->id,
            ],
            [
                'name' => 'Plaza Senayan Manager',
                'email' => 'manager@plazasenayan.com',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
                'role_id' => $tenantRole->id,
                'tenant_id' => $createdTenants[1]->id,
            ],
            [
                'name' => 'Grand Indonesia Manager',
                'email' => 'manager@grandindonesia.com',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
                'role_id' => $tenantRole->id,
                'tenant_id' => $createdTenants[2]->id,
            ],
            // Regular Users
            [
                'name' => 'John Doe',
                'email' => 'john.doe@example.com',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
                'role_id' => $userRole->id,
                'tenant_id' => null,
            ],
            [
                'name' => 'Jane Smith',
                'email' => 'jane.smith@example.com',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
                'role_id' => $userRole->id,
                'tenant_id' => null,
            ],
            [
                'name' => 'Ahmad Rahman',
                'email' => 'ahmad.rahman@example.com',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
                'role_id' => $userRole->id,
                'tenant_id' => null,
            ],
            [
                'name' => 'Siti Nurhaliza',
                'email' => 'siti.nurhaliza@example.com',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
                'role_id' => $userRole->id,
                'tenant_id' => null,
            ],
            [
                'name' => 'Budi Santoso',
                'email' => 'budi.santoso@example.com',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
                'role_id' => $userRole->id,
                'tenant_id' => null,
            ],
        ];

        $createdUsers = [];
        foreach ($users as $user) {
            $createdUsers[] = User::create($user);
        }

        // Buat User Balances untuk semua users
        foreach ($createdUsers as $user) {
            UserBalance::create([
                'user_id' => $user->id,
                'balance' => rand(0, 50000), // Random balance 0-50,000
                'currency' => 'IDR_POIN',
            ]);
        }

        // Buat Reverse Vending Machines
        $rvms = [
            [
                'name' => 'RVM-001',
                'location_description' => 'Lantai 1, dekat Food Court - Mall Kelapa Gading',
                'status' => 'active',
                'api_key' => 'RVM_001_' . strtoupper(substr(md5(uniqid()), 0, 8)),
                'last_status_change' => now()->subDays(rand(1, 30)),
                'admin_access_pin' => '1234',
                'remote_access_enabled' => true,
                'kiosk_mode_enabled' => true,
                'pos_settings' => [
                    'auto_accept_deposits' => true,
                    'notification_enabled' => true,
                    'maintenance_mode' => false,
                ],
            ],
            [
                'name' => 'RVM-002',
                'location_description' => 'Lantai 2, dekat Cinema - Mall Kelapa Gading',
                'status' => 'active',
                'api_key' => 'RVM_002_' . strtoupper(substr(md5(uniqid()), 0, 8)),
                'last_status_change' => now()->subDays(rand(1, 30)),
                'admin_access_pin' => '5678',
                'remote_access_enabled' => true,
                'kiosk_mode_enabled' => true,
                'pos_settings' => [
                    'auto_accept_deposits' => true,
                    'notification_enabled' => true,
                    'maintenance_mode' => false,
                ],
            ],
            [
                'name' => 'RVM-003',
                'location_description' => 'Lantai 1, dekat Entrance - Plaza Senayan',
                'status' => 'maintenance',
                'api_key' => 'RVM_003_' . strtoupper(substr(md5(uniqid()), 0, 8)),
                'last_status_change' => now()->subHours(rand(1, 24)),
                'admin_access_pin' => '9999',
                'remote_access_enabled' => true,
                'kiosk_mode_enabled' => false,
                'pos_settings' => [
                    'auto_accept_deposits' => false,
                    'notification_enabled' => true,
                    'maintenance_mode' => true,
                ],
            ],
            [
                'name' => 'RVM-004',
                'location_description' => 'Lantai 3, dekat Food Court - Plaza Senayan',
                'status' => 'active',
                'api_key' => 'RVM_004_' . strtoupper(substr(md5(uniqid()), 0, 8)),
                'last_status_change' => now()->subDays(rand(1, 30)),
                'admin_access_pin' => '0000',
                'remote_access_enabled' => true,
                'kiosk_mode_enabled' => true,
                'pos_settings' => [
                    'auto_accept_deposits' => true,
                    'notification_enabled' => true,
                    'maintenance_mode' => false,
                ],
            ],
            [
                'name' => 'RVM-005',
                'location_description' => 'Lantai 1, dekat Lobby - Grand Indonesia',
                'status' => 'full',
                'api_key' => 'RVM_005_' . strtoupper(substr(md5(uniqid()), 0, 8)),
                'last_status_change' => now()->subHours(rand(1, 12)),
                'admin_access_pin' => '1111',
                'remote_access_enabled' => true,
                'kiosk_mode_enabled' => true,
                'pos_settings' => [
                    'auto_accept_deposits' => false,
                    'notification_enabled' => true,
                    'maintenance_mode' => false,
                ],
            ],
        ];

        $createdRvms = [];
        foreach ($rvms as $rvm) {
            $createdRvms[] = ReverseVendingMachine::create($rvm);
        }

        // Buat Vouchers untuk setiap tenant
        $vouchers = [
            // Mall Kelapa Gading Vouchers
            [
                'tenant_id' => $createdTenants[0]->id,
                'title' => 'Diskon 10% di KFC',
                'description' => 'Dapatkan diskon 10% untuk pembelian minimal Rp 50.000 di KFC Mall Kelapa Gading. Maksimal diskon Rp 20.000.',
                'cost' => 1000,
                'stock' => 100,
                'total_redeemed' => rand(0, 20),
                'valid_from' => now(),
                'valid_until' => now()->addMonths(3),
                'is_active' => true,
            ],
            [
                'tenant_id' => $createdTenants[0]->id,
                'title' => 'Gratis Minuman di Starbucks',
                'description' => 'Dapatkan minuman gratis senilai Rp 25.000 di Starbucks Mall Kelapa Gading.',
                'cost' => 2500,
                'stock' => 50,
                'total_redeemed' => rand(0, 10),
                'valid_from' => now(),
                'valid_until' => now()->addMonths(2),
                'is_active' => true,
            ],
            // Plaza Senayan Vouchers
            [
                'tenant_id' => $createdTenants[1]->id,
                'title' => 'Diskon 15% di Zara',
                'description' => 'Dapatkan diskon 15% untuk pembelian minimal Rp 200.000 di Zara Plaza Senayan. Maksimal diskon Rp 100.000.',
                'cost' => 3000,
                'stock' => 75,
                'total_redeemed' => rand(0, 15),
                'valid_from' => now(),
                'valid_until' => now()->addMonths(4),
                'is_active' => true,
            ],
            [
                'tenant_id' => $createdTenants[1]->id,
                'title' => 'Buy 1 Get 1 di Cinema XXI',
                'description' => 'Dapatkan tiket gratis untuk tiket kedua senilai Rp 35.000 di Cinema XXI Plaza Senayan.',
                'cost' => 2000,
                'stock' => 30,
                'total_redeemed' => rand(0, 5),
                'valid_from' => now(),
                'valid_until' => now()->addMonths(1),
                'is_active' => true,
            ],
            // Grand Indonesia Vouchers
            [
                'tenant_id' => $createdTenants[2]->id,
                'title' => 'Diskon 20% di Sogo',
                'description' => 'Dapatkan diskon 20% untuk pembelian minimal Rp 300.000 di Sogo Grand Indonesia. Maksimal diskon Rp 200.000.',
                'cost' => 5000,
                'stock' => 40,
                'total_redeemed' => rand(0, 8),
                'valid_from' => now(),
                'valid_until' => now()->addMonths(6),
                'is_active' => true,
            ],
        ];

        $createdVouchers = [];
        foreach ($vouchers as $voucher) {
            $createdVouchers[] = Voucher::create($voucher);
        }

        // Buat beberapa transaksi dummy
        $transactionTypes = ['credit', 'debit'];
        $transactionSources = ['deposit', 'voucher_redemption', 'admin_adjustment', 'bonus'];

        for ($i = 0; $i < 50; $i++) {
            $user = $createdUsers[array_rand($createdUsers)];
            $type = $transactionTypes[array_rand($transactionTypes)];
            $source = $transactionSources[array_rand($transactionSources)];
            
            $amount = 0;
            $description = '';
            
            switch ($source) {
                case 'deposit':
                    $amount = rand(100, 5000);
                    $description = 'Reward dari deposit botol plastik';
                    break;
                case 'voucher_redemption':
                    $amount = rand(1000, 5000);
                    $description = 'Penukaran voucher';
                    break;
                case 'admin_adjustment':
                    $amount = rand(-1000, 1000);
                    $description = 'Penyesuaian saldo oleh admin';
                    break;
                case 'bonus':
                    $amount = rand(500, 2000);
                    $description = 'Bonus loyalitas';
                    break;
            }

            // Get current balance
            $currentBalance = $user->balance->balance;
            $balanceBefore = $currentBalance;
            $balanceAfter = $currentBalance + $amount;

            Transaction::create([
                'user_id' => $user->id,
                'user_balance_id' => $user->balance->id,
                'type' => $type,
                'amount' => $amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'description' => $description,
                'sourceable_type' => 'App\\Models\\' . ucfirst(str_replace('_', '', $source)),
                'sourceable_id' => rand(1, 10),
                'created_at' => now()->subDays(rand(1, 30)),
            ]);

            // Update user balance
            $user->balance->update(['balance' => $balanceAfter]);
        }

        $this->command->info('Dummy data seeded successfully!');
        $this->command->info('Created:');
        $this->command->info('- ' . count($createdUsers) . ' Users');
        $this->command->info('- ' . count($createdTenants) . ' Tenants');
        $this->command->info('- ' . count($createdRvms) . ' RVMs');
        $this->command->info('- ' . count($createdVouchers) . ' Vouchers');
        $this->command->info('- 50 Transactions');
        $this->command->info('');
        $this->command->info('Demo Credentials:');
        $this->command->info('Super Admin: admin@myrvm.com / password');
        $this->command->info('Admin: admin2@myrvm.com / password');
        $this->command->info('Operator: operator@myrvm.com / password');
        $this->command->info('');
        $this->command->info('Additional Test Users:');
        $this->command->info('Tenant Manager: manager@mallkelapagading.com / password123');
        $this->command->info('Regular User: john.doe@example.com / password123');
    }
}
