<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Voucher;

class VoucherSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $vouchers = [
            [
                'tenant_id' => 1,
                'title' => 'Welcome Voucher',
                'description' => '10% discount for new users',
                'cost' => 1000,
                'stock' => 100,
                'valid_from' => now(),
                'valid_until' => now()->addDays(30),
                'is_active' => true,
            ],
            [
                'tenant_id' => 1,
                'title' => 'Save More Voucher',
                'description' => '20% discount for purchases above 50k',
                'cost' => 5000,
                'stock' => 50,
                'valid_from' => now(),
                'valid_until' => now()->addDays(15),
                'is_active' => true,
            ],
            [
                'tenant_id' => 1,
                'title' => 'Fixed Discount Voucher',
                'description' => '5k discount for any purchase',
                'cost' => 2000,
                'stock' => 25,
                'valid_from' => now(),
                'valid_until' => now()->addDays(7),
                'is_active' => true,
            ],
        ];

        foreach ($vouchers as $voucher) {
            Voucher::create($voucher);
        }
    }
}
