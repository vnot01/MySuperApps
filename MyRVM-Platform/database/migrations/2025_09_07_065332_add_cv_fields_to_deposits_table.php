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
            // Computer Vision fields (YOLO + SAM)
            $table->decimal('cv_confidence', 5, 2)->nullable()->after('ai_confidence');
            $table->json('cv_analysis')->nullable()->after('cv_confidence');
            $table->string('cv_waste_type')->nullable()->after('cv_analysis');
            $table->decimal('cv_weight', 8, 3)->nullable()->after('cv_waste_type');
            $table->integer('cv_quantity')->nullable()->after('cv_weight');
            $table->string('cv_quality_grade', 1)->nullable()->after('cv_quantity');
            
            // AI fields (Gemini/Agent AI) - rename existing fields
            $table->string('ai_waste_type')->nullable()->after('cv_quality_grade');
            $table->decimal('ai_weight', 8, 3)->nullable()->after('ai_waste_type');
            $table->integer('ai_quantity')->nullable()->after('ai_weight');
            $table->string('ai_quality_grade', 1)->nullable()->after('ai_quantity');
            
            // Indexes
            $table->index(['cv_confidence']);
            $table->index(['cv_waste_type']);
            $table->index(['ai_waste_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('deposits', function (Blueprint $table) {
            $table->dropIndex(['cv_confidence']);
            $table->dropIndex(['cv_waste_type']);
            $table->dropIndex(['ai_waste_type']);
            
            $table->dropColumn([
                'cv_confidence',
                'cv_analysis',
                'cv_waste_type',
                'cv_weight',
                'cv_quantity',
                'cv_quality_grade',
                'ai_waste_type',
                'ai_weight',
                'ai_quantity',
                'ai_quality_grade'
            ]);
        });
    }
};
