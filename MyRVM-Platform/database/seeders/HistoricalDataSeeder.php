<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ReverseVendingMachine;
use App\Models\Deposit;
use App\Models\Session;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class HistoricalDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * This seeder creates 3 months of historical data for trend calculation
     * All data is marked with 'SIMULATED' prefix for identification
     */
    public function run(): void
    {
        $this->command->info('Creating 3 months of historical data for trend calculation...');
        
        // Get existing RVMs and Users
        $rvms = ReverseVendingMachine::all();
        $users = User::all();
        
        if ($rvms->isEmpty() || $users->isEmpty()) {
            $this->command->error('No RVMs or Users found. Please run RvmSeeder and UserSeeder first.');
            return;
        }
        
        // Create historical data for the last 3 months
        $startDate = Carbon::now()->subMonths(3);
        $endDate = Carbon::now()->subDay();
        
        $this->createHistoricalSessions($rvms, $users, $startDate, $endDate);
        $this->createHistoricalDeposits($rvms, $users, $startDate, $endDate);
        $this->createHistoricalTransactions($users, $startDate, $endDate);
        
        $this->command->info('Historical data creation completed!');
        $this->command->info('Data is marked with "SIMULATED" prefix for identification.');
    }
    
    /**
     * Create historical sessions data
     */
    private function createHistoricalSessions($rvms, $users, $startDate, $endDate)
    {
        $this->command->info('Creating historical sessions...');
        
        $sessions = [];
        $currentDate = $startDate->copy();
        
        while ($currentDate->lte($endDate)) {
            // Create 5-15 sessions per day
            $dailySessions = rand(5, 15);
            
            for ($i = 0; $i < $dailySessions; $i++) {
                $rvm = $rvms->random();
                $user = $users->random();
                
                $sessionStart = $currentDate->copy()->addHours(rand(6, 22))->addMinutes(rand(0, 59));
                $sessionEnd = $sessionStart->copy()->addMinutes(rand(5, 30));
                
                $sessions[] = [
                    'id' => 'SIMULATED_SESSION_' . uniqid(),
                    'user_id' => $user->id,
                    'rvm_id' => $rvm->id,
                    'session_token' => 'SIMULATED_TOKEN_' . Str::random(32),
                    'status' => $this->getRandomSessionStatus(),
                    'expires_at' => $sessionEnd,
                    'claimed_at' => $this->getRandomClaimedAt($sessionStart, $sessionEnd),
                    'created_at' => $sessionStart,
                    'updated_at' => $sessionEnd,
                ];
            }
            
            $currentDate->addDay();
        }
        
        // Insert in batches
        $chunks = array_chunk($sessions, 1000);
        foreach ($chunks as $chunk) {
            DB::table('rvm_sessions')->insert($chunk);
        }
        
        $this->command->info('Created ' . count($sessions) . ' historical sessions');
    }
    
    /**
     * Create historical deposits data
     */
    private function createHistoricalDeposits($rvms, $users, $startDate, $endDate)
    {
        $this->command->info('Creating historical deposits...');
        
        $deposits = [];
        $currentDate = $startDate->copy();
        
        while ($currentDate->lte($endDate)) {
            // Create 10-25 deposits per day
            $dailyDeposits = rand(10, 25);
            
            for ($i = 0; $i < $dailyDeposits; $i++) {
                $rvm = $rvms->random();
                $user = $users->random();
                
                $depositTime = $currentDate->copy()->addHours(rand(6, 22))->addMinutes(rand(0, 59));
                
                $deposits[] = [
                    'user_id' => $user->id,
                    'rvm_id' => $rvm->id,
                    'session_token' => 'SIMULATED_TOKEN_' . Str::random(32),
                    'waste_type' => $this->getRandomWasteType(),
                    'weight' => rand(50, 500) / 100, // 0.5 to 5.0 kg
                    'quantity' => rand(1, 10),
                    'quality_grade' => $this->getRandomQualityGrade(),
                    'ai_confidence' => rand(70, 95) / 100, // 0.7 to 0.95
                    'cv_confidence' => rand(75, 90) / 100, // 0.75 to 0.90
                    'reward_amount' => rand(100, 2000) / 100, // 1.00 to 20.00
                    'status' => $this->getRandomDepositStatus(),
                    'deposited_at' => $depositTime,
                    'processed_at' => $depositTime->copy()->addMinutes(rand(1, 5)),
                    'created_at' => $depositTime,
                    'updated_at' => $depositTime->copy()->addMinutes(rand(1, 5)),
                ];
            }
            
            $currentDate->addDay();
        }
        
        // Insert in batches
        $chunks = array_chunk($deposits, 1000);
        foreach ($chunks as $chunk) {
            DB::table('deposits')->insert($chunk);
        }
        
        $this->command->info('Created ' . count($deposits) . ' historical deposits');
    }
    
    /**
     * Create historical transactions data
     */
    private function createHistoricalTransactions($users, $startDate, $endDate)
    {
        $this->command->info('Creating historical transactions...');
        
        $transactions = [];
        $currentDate = $startDate->copy();
        
        while ($currentDate->lte($endDate)) {
            // Create 8-20 transactions per day
            $dailyTransactions = rand(8, 20);
            
            for ($i = 0; $i < $dailyTransactions; $i++) {
                $user = $users->random();
                
                $transactionTime = $currentDate->copy()->addHours(rand(6, 22))->addMinutes(rand(0, 59));
                
                // Get or create user balance
                $userBalance = \App\Models\UserBalance::firstOrCreate(
                    ['user_id' => $user->id],
                    [
                        'balance' => 0,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]
                );

                $amount = rand(100, 2000) / 100; // 1.00 to 20.00
                $balanceBefore = $userBalance->balance;
                $balanceAfter = $balanceBefore + $amount;
                
                // Update user balance
                $userBalance->balance = $balanceAfter;
                $userBalance->save();

                $transactions[] = [
                    'user_id' => $user->id,
                    'user_balance_id' => $userBalance->id,
                    'type' => 'deposit_reward',
                    'amount' => $amount,
                    'balance_before' => $balanceBefore,
                    'balance_after' => $balanceAfter,
                    'description' => 'SIMULATED: Deposit reward for waste recycling',
                    'sourceable_type' => 'App\\Models\\Deposit',
                    'sourceable_id' => rand(1, 1000), // Random deposit ID
                    'created_at' => $transactionTime,
                    'updated_at' => $transactionTime,
                ];
            }
            
            $currentDate->addDay();
        }
        
        // Insert in batches
        $chunks = array_chunk($transactions, 1000);
        foreach ($chunks as $chunk) {
            DB::table('transactions')->insert($chunk);
        }
        
        $this->command->info('Created ' . count($transactions) . ' historical transactions');
    }
    
    /**
     * Get random session status
     */
    private function getRandomSessionStatus()
    {
        $statuses = ['active', 'claimed', 'expired'];
        $weights = [0.3, 0.6, 0.1]; // 30% active, 60% claimed, 10% expired
        
        return $this->getWeightedRandom($statuses, $weights);
    }
    
    /**
     * Get random deposit status
     */
    private function getRandomDepositStatus()
    {
        $statuses = ['pending', 'processing', 'completed', 'rejected'];
        $weights = [0.1, 0.1, 0.75, 0.05]; // 75% completed, 5% rejected, etc.
        
        return $this->getWeightedRandom($statuses, $weights);
    }
    
    /**
     * Get random waste type
     */
    private function getRandomWasteType()
    {
        $types = ['plastic_bottle', 'aluminum_can', 'glass_bottle', 'paper', 'cardboard'];
        $weights = [0.4, 0.3, 0.15, 0.1, 0.05]; // Plastic bottles most common
        
        return $this->getWeightedRandom($types, $weights);
    }
    
    /**
     * Get random quality grade
     */
    private function getRandomQualityGrade()
    {
        $grades = ['A', 'B', 'C', 'D'];
        $weights = [0.4, 0.3, 0.2, 0.1]; // Grade A most common
        
        return $this->getWeightedRandom($grades, $weights);
    }
    
    /**
     * Get random claimed_at timestamp
     */
    private function getRandomClaimedAt($sessionStart, $sessionEnd)
    {
        // 70% chance of being claimed
        if (rand(1, 100) <= 70) {
            return $sessionStart->copy()->addMinutes(rand(1, 25));
        }
        
        return null;
    }
    
    /**
     * Get weighted random value
     */
    private function getWeightedRandom($values, $weights)
    {
        $totalWeight = array_sum($weights);
        $random = rand(1, $totalWeight * 100) / 100;
        
        $currentWeight = 0;
        foreach ($values as $index => $value) {
            $currentWeight += $weights[$index];
            if ($random <= $currentWeight) {
                return $value;
            }
        }
        
        return end($values);
    }
}
