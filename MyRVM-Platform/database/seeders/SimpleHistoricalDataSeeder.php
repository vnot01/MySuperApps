<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class SimpleHistoricalDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Creating simple historical data for trend calculation...');
        
        // Get existing data
        $rvms = \App\Models\ReverseVendingMachine::all();
        $users = \App\Models\User::all();
        
        if ($rvms->isEmpty() || $users->isEmpty()) {
            $this->command->error('No RVMs or Users found. Please run other seeders first.');
            return;
        }
        
        // Create data for the last 60 days to have better trend data
        $endDate = Carbon::now();
        $startDate = $endDate->copy()->subDays(60);
        
        // Update existing RVMs to have more realistic creation dates
        $this->updateExistingRvmDates();
        
        $this->createSimpleDeposits($rvms, $users, $startDate, $endDate);
        $this->createSimpleTransactions($users, $startDate, $endDate);
        
        $this->command->info('Simple historical data created successfully!');
    }
    
    /**
     * Create simple deposits data
     */
    private function createSimpleDeposits($rvms, $users, $startDate, $endDate)
    {
        $this->command->info('Creating simple deposits...');
        
        $deposits = [];
        $currentDate = $startDate->copy();
        
        while ($currentDate->lte($endDate)) {
            // Create deposits with max 30% trend increase
            $daysFromStart = $currentDate->diffInDays($startDate);
            $totalDays = $startDate->diffInDays($endDate);
            
            // Create deposits to achieve target: (26 - 20) / 20 * 100 = 30%
            if ($daysFromStart > ($totalDays * 0.8)) {
                $dailyDeposits = rand(2, 3); // Recent days: 2-3 deposits (target: 26 today)
            } elseif ($daysFromStart > ($totalDays * 0.5)) {
                $dailyDeposits = rand(1, 2);  // Mid period: 1-2 deposits
            } else {
                $dailyDeposits = rand(1, 2);  // Older days: 1-2 deposits (target: 20 on day 30)
            }
            
            for ($i = 0; $i < $dailyDeposits; $i++) {
                $rvm = $rvms->random();
                $user = $users->random();
                
                $depositTime = $currentDate->copy()->addHours(rand(6, 22))->addMinutes(rand(0, 59));
                
                $deposits[] = [
                    'user_id' => $user->id,
                    'rvm_id' => $rvm->id,
                    'session_token' => 'SIM_' . Str::random(20),
                    'waste_type' => $this->getRandomWasteType(),
                    'weight' => rand(50, 500) / 100, // 0.5 to 5.0 kg
                    'quantity' => rand(1, 10),
                    'quality_grade' => $this->getRandomQualityGrade(),
                    'ai_confidence' => rand(70, 95) / 100,
                    'cv_confidence' => rand(75, 90) / 100,
                    'reward_amount' => rand(100, 2000) / 100,
                    'status' => $this->getRandomDepositStatus(),
                    'deposited_at' => $depositTime,
                    'processed_at' => $depositTime->copy()->addMinutes(rand(1, 5)),
                    'created_at' => $depositTime,
                    'updated_at' => $depositTime,
                ];
            }
            
            $currentDate->addDay();
        }
        
        // Insert in smaller batches
        $chunks = array_chunk($deposits, 100);
        foreach ($chunks as $chunk) {
            DB::table('deposits')->insert($chunk);
        }
        
        $this->command->info('Created ' . count($deposits) . ' simple deposits');
    }
    
    /**
     * Create simple transactions data
     */
    private function createSimpleTransactions($users, $startDate, $endDate)
    {
        $this->command->info('Creating simple transactions...');
        
        $transactions = [];
        $currentDate = $startDate->copy();
        
        while ($currentDate->lte($endDate)) {
            // Create 3-10 transactions per day
            $dailyTransactions = rand(3, 10);
            
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

                $amount = rand(100, 2000) / 100;
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
                    'description' => 'SIM: Deposit reward for waste recycling',
                    'sourceable_type' => 'App\\Models\\Deposit',
                    'sourceable_id' => rand(1, 1000),
                    'created_at' => $transactionTime,
                    'updated_at' => $transactionTime,
                ];
            }
            
            $currentDate->addDay();
        }
        
        // Insert in smaller batches
        $chunks = array_chunk($transactions, 100);
        foreach ($chunks as $chunk) {
            DB::table('transactions')->insert($chunk);
        }
        
        $this->command->info('Created ' . count($transactions) . ' simple transactions');
    }
    
    private function getRandomWasteType()
    {
        $types = ['plastic', 'glass', 'metal', 'paper', 'mixed'];
        return $types[array_rand($types)];
    }
    
    private function getRandomQualityGrade()
    {
        $grades = ['A', 'B', 'C', 'D'];
        return $grades[array_rand($grades)];
    }
    
    private function getRandomDepositStatus()
    {
        $statuses = ['completed', 'pending', 'processing', 'rejected'];
        return $statuses[array_rand($statuses)];
    }
    
    private function updateExistingRvmDates()
    {
        $this->command->info('Updating existing RVM creation dates for realistic trends (max 30%)...');
        
        $rvms = \App\Models\ReverseVendingMachine::all();
        $totalRvm = $rvms->count();
        
        // Distribute RVM creation dates to achieve different trend targets
        // Target: Total RVM (20 - 18) / 18 * 100 = 11.1% (low growth)
        // Target: Active Sessions (10 - 8) / 8 * 100 = 25% (higher activity)
        // 90% created 30+ days ago, 10% created in last 30 days (for Total RVM)
        $olderCount = (int)($totalRvm * 0.90); // 90% older (30+ days ago)
        $recentCount = $totalRvm - $olderCount; // 10% recent (last 30 days)
        
        $olderRvms = $rvms->take($olderCount);
        $recentRvms = $rvms->skip($olderCount);
        
        // Update older RVMs (30-120 days ago) - 85% of RVMs
        foreach ($olderRvms as $index => $rvm) {
            $daysAgo = rand(31, 120);
            $createdAt = Carbon::now()->subDays($daysAgo);
            $rvm->update([
                'created_at' => $createdAt,
                'updated_at' => $createdAt->copy()->addHours(rand(1, 24))
            ]);
        }
        
        // Update recent RVMs (last 30 days) - 15% of RVMs
        foreach ($recentRvms as $index => $rvm) {
            $daysAgo = rand(1, 30);
            $createdAt = Carbon::now()->subDays($daysAgo);
            $rvm->update([
                'created_at' => $createdAt,
                'updated_at' => $createdAt->copy()->addHours(rand(1, 24))
            ]);
        }
        
        $this->command->info("Updated {$olderCount} older RVMs (30+ days ago) and {$recentCount} recent RVMs (last 30 days)");
        
        // Update Active Sessions trend separately by updating updated_at for active RVMs
        $this->updateActiveSessionsTrend();
    }
    
    private function updateActiveSessionsTrend()
    {
        $this->command->info('Updating Active Sessions trend separately...');
        
        // Get active RVMs (status active + capacity < 100)
        $activeRvms = \App\Models\ReverseVendingMachine::where('status', 'active')
            ->where('capacity', '<', 100)
            ->get();
        
        $totalActive = $activeRvms->count();
        
        // Target: (10 - 8) / 8 * 100 = 25%
        // 80% updated recently (last 30 days), 20% updated 30+ days ago
        $recentActiveCount = (int)($totalActive * 0.80); // 80% recent
        $olderActiveCount = $totalActive - $recentActiveCount; // 20% older
        
        $recentActiveRvms = $activeRvms->take($recentActiveCount);
        $olderActiveRvms = $activeRvms->skip($recentActiveCount);
        
        // Update recent active RVMs (last 30 days)
        foreach ($recentActiveRvms as $rvm) {
            $daysAgo = rand(1, 30);
            $updatedAt = Carbon::now()->subDays($daysAgo);
            $rvm->update(['updated_at' => $updatedAt]);
        }
        
        // Update older active RVMs (30+ days ago)
        foreach ($olderActiveRvms as $rvm) {
            $daysAgo = rand(31, 90);
            $updatedAt = Carbon::now()->subDays($daysAgo);
            $rvm->update(['updated_at' => $updatedAt]);
        }
        
        $this->command->info("Updated {$recentActiveCount} recent active RVMs and {$olderActiveCount} older active RVMs");
    }
}
