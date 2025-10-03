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
        Schema::create('application_metrics', function (Blueprint $table) {
            $table->id();
            $table->integer('rvm_id');
            $table->string('software_version', 50)->nullable();
            $table->string('ai_model_version', 50)->nullable();
            $table->string('ai_model_path', 500)->nullable();
            $table->integer('uptime_seconds')->nullable();
            $table->integer('deposit_count_since_restart')->nullable();
            $table->timestamp('last_deposit_time')->nullable();
            $table->integer('error_count')->default(0);
            $table->integer('warning_count')->default(0);
            $table->timestamp('recorded_at');
            $table->timestamps();
            
            $table->foreign('rvm_id')->references('id')->on('reverse_vending_machines');
            $table->index(['rvm_id', 'recorded_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('application_metrics');
    }
};
