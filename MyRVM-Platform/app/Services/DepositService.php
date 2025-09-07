<?php

namespace App\Services;

use App\Models\Deposit;
use App\Models\UserBalance;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

class DepositService
{
    /**
     * Create a new deposit
     *
     * @param int $userId
     * @param array $data
     * @return Deposit
     */
    public function createDeposit(int $userId, array $data): Deposit
    {
        return Deposit::create([
            'user_id' => $userId,
            'rvm_id' => $data['rvm_id'],
            'session_token' => $data['session_token'] ?? null,
            'waste_type' => $data['waste_type'],
            'weight' => $data['weight'],
            'quantity' => $data['quantity'],
            'quality_grade' => 'B', // Default grade, will be updated by AI
            'status' => 'pending',
        ]);
    }

    /**
     * Update deposit with AI analysis results
     *
     * @param Deposit $deposit
     * @param array $aiResult
     * @return Deposit
     */
    public function updateWithAiResults(Deposit $deposit, array $aiResult): Deposit
    {
        $deposit->update([
            'quality_grade' => $aiResult['quality_grade'],
            'ai_confidence' => $aiResult['confidence'],
            'ai_analysis' => $aiResult['analysis_details'],
            'status' => 'processing',
        ]);

        return $deposit->fresh();
    }

    /**
     * Calculate reward amount based on deposit data
     *
     * @param Deposit $deposit
     * @return float
     */
    public function calculateReward(Deposit $deposit): float
    {
        // Base reward rates per kg
        $baseRates = [
            'plastic' => 5000, // Rp 5,000 per kg
            'glass' => 3000,   // Rp 3,000 per kg
            'metal' => 8000,   // Rp 8,000 per kg
            'paper' => 2000,   // Rp 2,000 per kg
            'mixed' => 1500,   // Rp 1,500 per kg
        ];

        // Quality multipliers
        $qualityMultipliers = [
            'A' => 1.2, // Premium quality
            'B' => 1.0, // Good quality
            'C' => 0.8, // Fair quality
            'D' => 0.5, // Poor quality
        ];

        $baseRate = $baseRates[$deposit->waste_type] ?? 1000;
        $qualityMultiplier = $qualityMultipliers[$deposit->quality_grade] ?? 1.0;
        
        // Calculate reward: base_rate * weight * quality_multiplier * confidence_factor
        $confidenceFactor = ($deposit->ai_confidence ?? 50) / 100;
        
        $reward = $baseRate * $deposit->weight * $qualityMultiplier * $confidenceFactor;
        
        return round($reward, 2);
    }

    /**
     * Process deposit (approve or reject)
     *
     * @param Deposit $deposit
     * @param array $data
     * @return Deposit
     */
    public function processDeposit(Deposit $deposit, array $data): Deposit
    {
        // Debug: Log process start
        \Log::info('Processing Deposit', [
            'deposit_id' => $deposit->id,
            'new_status' => $data['status'],
            'current_status' => $deposit->status
        ]);

        $deposit->update([
            'status' => $data['status'],
            'rejection_reason' => $data['rejection_reason'] ?? null,
            'processed_at' => now(),
        ]);

        // If approved, add reward to user balance
        if ($data['status'] === 'completed') {
            \Log::info('Adding reward to user balance', [
                'deposit_id' => $deposit->id,
                'user_id' => $deposit->user_id,
                'reward_amount' => $deposit->reward_amount
            ]);
            
            $this->addRewardToUserBalance($deposit);
        }

        return $deposit->fresh();
    }

    /**
     * Add reward to user balance and create transaction
     *
     * @param Deposit $deposit
     * @return void
     */
    private function addRewardToUserBalance(Deposit $deposit): void
    {
        DB::transaction(function () use ($deposit) {
            // Debug: Log user balance creation
            \Log::info('Creating/Getting UserBalance', [
                'user_id' => $deposit->user_id,
                'deposit_id' => $deposit->id
            ]);

            // Get or create user balance
            $userBalance = UserBalance::firstOrCreate(
                ['user_id' => $deposit->user_id],
                ['balance' => 0]
            );

            // Debug: Log user balance data
            \Log::info('UserBalance Found/Created', [
                'user_balance_id' => $userBalance->id,
                'user_id' => $userBalance->user_id,
                'current_balance' => $userBalance->balance,
                'reward_amount' => $deposit->reward_amount
            ]);

            // Add reward to balance
            $userBalance->increment('balance', $deposit->reward_amount);

            // Debug: Log balance after increment
            \Log::info('Balance Updated', [
                'user_balance_id' => $userBalance->id,
                'new_balance' => $userBalance->fresh()->balance
            ]);

            // Create transaction record
            $transaction = Transaction::create([
                'user_id' => $deposit->user_id,
                'user_balance_id' => $userBalance->id,
                'type' => 'credit',
                'amount' => $deposit->reward_amount,
                'balance_before' => $userBalance->balance - $deposit->reward_amount,
                'balance_after' => $userBalance->balance,
                'description' => "Reward for {$deposit->waste_type} deposit (ID: {$deposit->id})",
                'sourceable_type' => Deposit::class,
                'sourceable_id' => $deposit->id,
            ]);

            // Debug: Log transaction creation
            \Log::info('Transaction Created', [
                'transaction_id' => $transaction->id,
                'user_id' => $transaction->user_id,
                'amount' => $transaction->amount,
                'type' => $transaction->type
            ]);
        });
    }

    /**
     * Get deposit statistics for a user
     *
     * @param int $userId
     * @return array
     */
    public function getUserStatistics(int $userId): array
    {
        return Deposit::where('user_id', $userId)
            ->selectRaw('
                COUNT(*) as total_deposits,
                SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as completed_deposits,
                SUM(CASE WHEN status = "pending" THEN 1 ELSE 0 END) as pending_deposits,
                SUM(CASE WHEN status = "rejected" THEN 1 ELSE 0 END) as rejected_deposits,
                SUM(reward_amount) as total_rewards,
                AVG(ai_confidence) as avg_confidence,
                COUNT(DISTINCT waste_type) as waste_types_count,
                SUM(weight) as total_weight
            ')
            ->first()
            ->toArray();
    }
}
