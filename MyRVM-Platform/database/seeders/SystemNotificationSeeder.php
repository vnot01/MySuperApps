<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\SystemNotification;
use App\Models\User;

class SystemNotificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get first user for testing
        $user = User::first();
        
        if (!$user) {
            $this->command->info('No users found. Please create a user first.');
            return;
        }

        // Create sample system notifications
        $notifications = [
            [
                'title' => 'System Maintenance Scheduled',
                'message' => 'System maintenance is scheduled for tonight at 2:00 AM. Expected downtime: 30 minutes.',
                'type' => 'maintenance',
                'priority' => 'high',
                'target_audience' => 'all',
                'action_url' => '/admin/maintenance',
                'action_text' => 'View Details',
                'scheduled_at' => now(),
                'expires_at' => now()->addDays(7),
                'is_active' => true,
                'created_by' => $user->id,
            ],
            [
                'title' => 'Security Update Available',
                'message' => 'A new security update is available for your system. Please update as soon as possible.',
                'type' => 'security',
                'priority' => 'critical',
                'target_audience' => 'admins',
                'action_url' => '/admin/updates',
                'action_text' => 'Update Now',
                'scheduled_at' => now(),
                'expires_at' => now()->addDays(3),
                'is_active' => true,
                'created_by' => $user->id,
            ],
            [
                'title' => 'New Feature Released',
                'message' => 'We have released a new dashboard feature that allows better monitoring of your RVM devices.',
                'type' => 'feature',
                'priority' => 'medium',
                'target_audience' => 'all',
                'action_url' => '/admin/dashboard',
                'action_text' => 'Explore Feature',
                'scheduled_at' => now(),
                'expires_at' => now()->addDays(30),
                'is_active' => true,
                'created_by' => $user->id,
            ],
            [
                'title' => 'Performance Improvement',
                'message' => 'System performance has been improved by 40% with the latest optimizations.',
                'type' => 'performance',
                'priority' => 'low',
                'target_audience' => 'all',
                'scheduled_at' => now(),
                'expires_at' => now()->addDays(14),
                'is_active' => true,
                'created_by' => $user->id,
            ],
            [
                'title' => 'Backup Completed Successfully',
                'message' => 'Daily system backup has been completed successfully. All data is secure.',
                'type' => 'system',
                'priority' => 'low',
                'target_audience' => 'admins',
                'scheduled_at' => now()->subHours(2),
                'expires_at' => now()->addDays(1),
                'is_active' => true,
                'created_by' => $user->id,
            ],
        ];

        foreach ($notifications as $notification) {
            SystemNotification::create($notification);
        }

        $this->command->info('System notifications seeded successfully!');
    }
}
