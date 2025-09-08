<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ReverseVendingMachine;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class SessionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rvms = ReverseVendingMachine::all();
        
        // Generate sessions for the last 7 days
        for ($i = 0; $i < 7; $i++) {
            $date = now()->subDays($i);
            
            // Generate 5-15 sessions per day
            $sessionCount = rand(5, 15);
            
            for ($j = 0; $j < $sessionCount; $j++) {
                $rvm = $rvms->random();
                $sessionType = ['guest', 'user'][rand(0, 1)];
                $status = ['active', 'claimed', 'expired'][rand(0, 2)];
                
                $startedAt = $date->copy()->addHours(rand(6, 22))->addMinutes(rand(0, 59));
                $expiresAt = $startedAt->copy()->addMinutes(rand(30, 120));
                $claimedAt = $status === 'claimed' ? $startedAt->copy()->addMinutes(rand(5, 25)) : null;
                
                DB::table('rvm_sessions')->insert([
                    'id' => Str::uuid(),
                    'user_id' => $sessionType === 'user' ? 1 : null,
                    'rvm_id' => $rvm->id,
                    'session_token' => Str::random(32),
                    'status' => $status,
                    'expires_at' => $expiresAt,
                    'claimed_at' => $claimedAt,
                    'created_at' => $startedAt,
                    'updated_at' => $startedAt,
                ]);
            }
        }
    }
}
