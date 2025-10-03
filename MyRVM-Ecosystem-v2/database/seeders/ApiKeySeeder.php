<?php

namespace Database\Seeders;

use App\Models\ReverseVendingMachine;
use Illuminate\Database\Seeder;

class ApiKeySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ReverseVendingMachine::all()->each(function ($rvm) {
            if (!$rvm->api_key) {
                $rvm->generateApiKey();
            }
        });
    }
}