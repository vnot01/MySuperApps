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
        Schema::create('system_metrics', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('rvm_id');
            $table->decimal('cpu_usage', 5, 2)->nullable(); // percentage
            $table->decimal('memory_usage', 5, 2)->nullable(); // percentage
            $table->decimal('disk_usage', 5, 2)->nullable(); // percentage
            $table->decimal('gpu_usage', 5, 2)->nullable(); // percentage
            $table->decimal('temperature', 5, 2)->nullable(); // celsius
            $table->bigInteger('free_memory')->nullable(); // in bytes
            $table->bigInteger('total_memory')->nullable(); // in bytes
            $table->bigInteger('free_disk')->nullable(); // in bytes
            $table->bigInteger('total_disk')->nullable(); // in bytes
            $table->integer('uptime')->nullable(); // in seconds
            $table->integer('process_count')->nullable();
            $table->text('additional_metrics')->nullable(); // JSON for additional metrics
            $table->timestamp('timestamp');
            $table->timestamps();
            
            $table->foreign('rvm_id')->references('id')->on('reverse_vending_machines')->onDelete('cascade');
            $table->index(['rvm_id', 'timestamp']);
            $table->index(['timestamp']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_metrics');
    }
};
