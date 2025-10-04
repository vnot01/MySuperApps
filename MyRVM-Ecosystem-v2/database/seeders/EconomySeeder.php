<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Voucher;
use App\Models\User;
use App\Models\UserBalance;

class EconomySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create sample vouchers
        $vouchers = [
            [
                'code' => 'WELCOME10',
                'name' => 'Welcome Discount',
                'description' => '10% discount for new users',
                'discount_type' => 'percentage',
                'discount_value' => 10.00,
                'min_purchase' => 10000.00,
                'max_discount' => 5000.00,
                'usage_limit' => 1000,
                'valid_from' => now(),
                'valid_until' => now()->addMonths(3),
                'is_active' => true
            ],
            [
                'code' => 'SAVE5000',
                'name' => 'Fixed Discount',
                'description' => '5,000 IDR off your purchase',
                'discount_type' => 'fixed',
                'discount_value' => 5000.00,
                'min_purchase' => 20000.00,
                'max_discount' => null,
                'usage_limit' => 500,
                'valid_from' => now(),
                'valid_until' => now()->addMonths(2),
                'is_active' => true
            ],
            [
                'code' => 'EARLYBIRD',
                'name' => 'Early Bird Special',
                'description' => '15% discount for early adopters',
                'discount_type' => 'percentage',
                'discount_value' => 15.00,
                'min_purchase' => 5000.00,
                'max_discount' => 10000.00,
                'usage_limit' => 100,
                'valid_from' => now(),
                'valid_until' => now()->addMonth(),
                'is_active' => true
            ]
        ];

        foreach ($vouchers as $voucherData) {
            Voucher::create($voucherData);
        }

        // Create balance for existing users
        $users = User::whereDoesntHave('balance')->get();
        foreach ($users as $user) {
            UserBalance::create([
                'user_id' => $user->id,
                'balance' => 0.00,
                'currency' => 'IDR'
            ]);
        }

        $this->command->info('Economy system seeded successfully!');
    }
}
