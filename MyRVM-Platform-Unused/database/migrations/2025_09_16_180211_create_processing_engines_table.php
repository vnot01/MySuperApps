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
        Schema::create('processing_engines', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', ['nvidia_cuda', 'jetson_edge']);
            $table->string('server_address');
            $table->integer('port')->default(8000);
            $table->string('gpu_memory_limit')->nullable();
            $table->boolean('docker_gpu_passthrough')->default(false);
            $table->string('model_path')->nullable();
            $table->integer('processing_timeout')->default(30);
            $table->boolean('auto_failover')->default(true);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_online')->default(false);
            $table->timestamp('last_ping_at')->nullable();
            $table->integer('ping_response_time')->nullable();
            $table->json('health_status')->nullable();
            $table->timestamps();
            
            $table->index(['type', 'is_active']);
            $table->index('server_address');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('processing_engines');
    }
};