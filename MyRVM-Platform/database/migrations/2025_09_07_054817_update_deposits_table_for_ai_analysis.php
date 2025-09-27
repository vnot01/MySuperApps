<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('deposits', function (Blueprint $table) {
            // Add new columns for AI analysis
            $table->string('session_token')->nullable()->after('rvm_id');
            $table->enum('waste_type', ['plastic', 'glass', 'metal', 'paper', 'mixed'])->after('session_token');
            $table->decimal('weight', 8, 3)->after('waste_type');
            $table->integer('quantity')->default(1)->after('weight');
            $table->enum('quality_grade', ['A', 'B', 'C', 'D'])->after('quantity');
            $table->decimal('ai_confidence', 5, 2)->nullable()->after('quality_grade');
            $table->json('ai_analysis')->nullable()->after('ai_confidence');
            $table->decimal('reward_amount', 10, 2)->default(0)->after('ai_analysis');
            $table->enum('status', ['pending', 'processing', 'completed', 'rejected'])->default('pending')->after('reward_amount');
            $table->text('rejection_reason')->nullable()->after('status');
            $table->timestamp('processed_at')->nullable()->after('rejection_reason');
            
            // Add indexes
            $table->index(['user_id', 'status']);
            $table->index(['rvm_id', 'created_at']);
            $table->index('session_token');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('deposits', function (Blueprint $table) {
            // Drop indexes first
            $table->dropIndex(['user_id', 'status']);
            $table->dropIndex(['rvm_id', 'created_at']);
            $table->dropIndex(['session_token']);
            
            // Drop columns
            $table->dropColumn([
                'session_token',
                'waste_type',
                'weight',
                'quantity',
                'quality_grade',
                'ai_confidence',
                'ai_analysis',
                'reward_amount',
                'status',
                'rejection_reason',
                'processed_at'
            ]);
        });
    }
};
