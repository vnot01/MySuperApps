<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ReverseVendingMachine;

class RvmPosSystemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create test RVMs with POS System configuration
        $rvms = [
            [
                'name' => 'RVM Mall Central',
                'location_description' => 'Mall Central - Lantai 1, dekat food court',
                'status' => 'active',
                'api_key' => 'rvm_mall_central_' . \Str::random(16),
                'admin_access_pin' => '1234',
                'remote_access_enabled' => true,
                'kiosk_mode_enabled' => true,
                'pos_settings' => [
                    'auto_fullscreen' => true,
                    'disable_shortcuts' => true,
                    'session_timeout' => 3600,
                    'theme' => 'default',
                    'language' => 'id'
                ],
                'last_status_change' => now()
            ],
            [
                'name' => 'RVM Office Building A',
                'location_description' => 'Office Building A - Lobby area',
                'status' => 'maintenance',
                'api_key' => 'rvm_office_a_' . \Str::random(16),
                'admin_access_pin' => '5678',
                'remote_access_enabled' => true,
                'kiosk_mode_enabled' => true,
                'pos_settings' => [
                    'auto_fullscreen' => true,
                    'disable_shortcuts' => true,
                    'session_timeout' => 7200,
                    'theme' => 'corporate',
                    'language' => 'id'
                ],
                'last_status_change' => now()->subHours(2)
            ],
            [
                'name' => 'RVM University Campus',
                'location_description' => 'University Campus - Student Center',
                'status' => 'active',
                'api_key' => 'rvm_university_' . \Str::random(16),
                'admin_access_pin' => '9999',
                'remote_access_enabled' => true,
                'kiosk_mode_enabled' => false, // Disabled for testing
                'pos_settings' => [
                    'auto_fullscreen' => false,
                    'disable_shortcuts' => false,
                    'session_timeout' => 1800,
                    'theme' => 'student',
                    'language' => 'id'
                ],
                'last_status_change' => now()->subMinutes(30)
            ],
            [
                'name' => 'RVM Hospital Main',
                'location_description' => 'Hospital Main - Emergency area',
                'status' => 'full',
                'api_key' => 'rvm_hospital_' . \Str::random(16),
                'admin_access_pin' => '0000',
                'remote_access_enabled' => false, // Disabled for testing
                'kiosk_mode_enabled' => true,
                'pos_settings' => [
                    'auto_fullscreen' => true,
                    'disable_shortcuts' => true,
                    'session_timeout' => 1800,
                    'theme' => 'medical',
                    'language' => 'id'
                ],
                'last_status_change' => now()->subHours(1)
            ],
            [
                'name' => 'RVM Airport Terminal',
                'location_description' => 'Airport Terminal - Departure area',
                'status' => 'error',
                'api_key' => 'rvm_airport_' . \Str::random(16),
                'admin_access_pin' => '1111',
                'remote_access_enabled' => true,
                'kiosk_mode_enabled' => true,
                'pos_settings' => [
                    'auto_fullscreen' => true,
                    'disable_shortcuts' => true,
                    'session_timeout' => 900,
                    'theme' => 'airport',
                    'language' => 'en'
                ],
                'last_status_change' => now()->subMinutes(15)
            ]
        ];

        foreach ($rvms as $rvmData) {
            ReverseVendingMachine::create($rvmData);
        }

        $this->command->info('RVM POS System test data created successfully!');
        $this->command->info('Created ' . count($rvms) . ' RVMs with different configurations for testing.');
    }
}
