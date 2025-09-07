<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V2\DepositCreateRequest;
use App\Http\Requests\Api\V2\DepositProcessRequest;
use App\Models\Deposit;
use App\Models\ReverseVendingMachine;
use App\Services\AiAnalysisService;
use App\Services\DepositService;
use App\Events\AnalisisSelesai;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DepositController extends Controller
{
    protected $depositService;
    protected $aiAnalysisService;

    public function __construct(DepositService $depositService, AiAnalysisService $aiAnalysisService)
    {
        $this->depositService = $depositService;
        $this->aiAnalysisService = $aiAnalysisService;
    }

    /**
     * Create a new deposit
     *
     * @param DepositCreateRequest $request
     * @return JsonResponse
     */
    public function create(DepositCreateRequest $request): JsonResponse
    {
        $user = Auth::user();
        $data = $request->validated();

        try {
            DB::beginTransaction();

            // Create deposit
            $deposit = $this->depositService->createDeposit($user->id, $data);

            // Start AI analysis
            $aiResult = $this->aiAnalysisService->analyzeWaste($data);

            // Update deposit with AI results
            $deposit = $this->depositService->updateWithAiResults($deposit, $aiResult);

            // Calculate reward
            $rewardAmount = $this->depositService->calculateReward($deposit);
            $deposit->update(['reward_amount' => $rewardAmount]);

            DB::commit();

            // Broadcast analysis completion event
            broadcast(new AnalisisSelesai($deposit->rvm_id, $deposit->id, [
                'waste_type' => $deposit->waste_type,
                'quality_grade' => $deposit->quality_grade,
                'ai_confidence' => $deposit->ai_confidence,
                'reward_amount' => $deposit->reward_amount,
                'status' => $deposit->status,
                'analysis_details' => $deposit->ai_analysis,
            ]))->toOthers();

            return response()->json([
                'success' => true,
                'message' => 'Deposit created and analyzed successfully',
                'data' => [
                    'deposit_id' => $deposit->id,
                    'waste_type' => $deposit->waste_type,
                    'quality_grade' => $deposit->quality_grade,
                    'ai_confidence' => $deposit->ai_confidence,
                    'reward_amount' => $deposit->reward_amount,
                    'status' => $deposit->status,
                    'ai_analysis' => $deposit->ai_analysis,
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to create deposit',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user's deposits
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();
        $perPage = $request->get('per_page', 15);
        $status = $request->get('status');

        $query = Deposit::where('user_id', $user->id)
            ->orderBy('created_at', 'desc');

        if ($status) {
            $query->where('status', $status);
        }

        $deposits = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $deposits
        ]);
    }

    /**
     * Get specific deposit details
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $user = Auth::user();
        
        $deposit = Deposit::where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (!$deposit) {
            return response()->json([
                'success' => false,
                'message' => 'Deposit not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $deposit
        ]);
    }

    /**
     * Process deposit (for RVM or admin)
     *
     * @param DepositProcessRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function process(DepositProcessRequest $request, int $id): JsonResponse
    {
        try {
            // Debug: Log request data
            \Log::info('Deposit Process Request', [
                'deposit_id' => $id,
                'request_data' => $request->all(),
                'user_id' => auth()->id()
            ]);

            $data = $request->validated();
            
            $deposit = Deposit::findOrFail($id);

            // Debug: Log deposit data
            \Log::info('Deposit Found', [
                'deposit_id' => $deposit->id,
                'current_status' => $deposit->status,
                'user_id' => $deposit->user_id,
                'reward_amount' => $deposit->reward_amount
            ]);

            if (!in_array($deposit->status, ['pending', 'processing'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Deposit is not in pending or processing status',
                    'debug' => [
                        'current_status' => $deposit->status,
                        'allowed_statuses' => ['pending', 'processing']
                    ]
                ], 400);
            }

            DB::beginTransaction();

            $deposit = $this->depositService->processDeposit($deposit, $data);

            DB::commit();

            // Debug: Log success
            \Log::info('Deposit Processed Successfully', [
                'deposit_id' => $deposit->id,
                'new_status' => $deposit->status,
                'reward_amount' => $deposit->reward_amount
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Deposit processed successfully',
                'data' => [
                    'deposit_id' => $deposit->id,
                    'status' => $deposit->status,
                    'reward_amount' => $deposit->reward_amount,
                    'processed_at' => $deposit->processed_at,
                ],
                'debug' => [
                    'user_id' => $deposit->user_id,
                    'waste_type' => $deposit->waste_type,
                    'ai_confidence' => $deposit->ai_confidence
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            // Debug: Log error
            \Log::error('Deposit Process Error', [
                'deposit_id' => $id,
                'error_message' => $e->getMessage(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'error_trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to process deposit',
                'error' => $e->getMessage(),
                'debug' => [
                    'error_file' => $e->getFile(),
                    'error_line' => $e->getLine(),
                    'deposit_id' => $id,
                    'user_id' => auth()->id()
                ]
            ], 500);
        }
    }

    /**
     * Get deposit statistics for user
     *
     * @return JsonResponse
     */
    public function statistics(): JsonResponse
    {
        $user = Auth::user();

        $stats = Deposit::where('user_id', $user->id)
            ->selectRaw('
                COUNT(*) as total_deposits,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as completed_deposits,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as pending_deposits,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as rejected_deposits,
                SUM(reward_amount) as total_rewards,
                AVG(ai_confidence) as avg_confidence,
                COUNT(DISTINCT waste_type) as waste_types_count
            ', ['completed', 'pending', 'rejected'])
            ->first();

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }
}
