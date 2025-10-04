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
        Schema::create('rvm_monitoring_metrics', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('rvm_id');
            $table->timestamp('timestamp');
            $table->decimal('cpu_percent', 5, 2)->nullable();
            $table->decimal('memory_percent', 5, 2)->nullable();
            $table->decimal('gpu_memory_percent', 5, 2)->nullable();
            $table->decimal('disk_usage_percent', 5, 2)->nullable();
            $table->decimal('processing_time_ms', 10, 2)->nullable();
            $table->integer('detections_count')->default(0);
            $table->integer('error_count')->default(0);
            $table->integer('api_requests_count')->default(0);
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['rvm_id', 'timestamp']);
            $table->index('timestamp');
            $table->index('rvm_id');
            
            // Foreign key constraint
            $table->foreign('rvm_id')->references('id')->on('reverse_vending_machines')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rvm_monitoring_metrics');
    }
};
