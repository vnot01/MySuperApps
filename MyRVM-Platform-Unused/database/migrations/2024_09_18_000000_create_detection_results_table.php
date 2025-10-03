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
        Schema::create('detection_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rvm_id')->constrained('reverse_vending_machines')->onDelete('cascade');
            $table->string('image_path')->nullable();
            $table->json('detections')->nullable();
            $table->timestamp('timestamp')->nullable();
            $table->float('processing_time')->nullable();
            $table->string('model_version')->nullable();
            $table->enum('status', ['completed', 'processing', 'processing_requested', 'failed'])->default('processing_requested');
            $table->string('processing_type')->default('detection');
            $table->enum('priority', ['low', 'normal', 'high'])->default('normal');
            $table->timestamps();

            $table->index(['rvm_id', 'created_at']);
            $table->index(['status', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detection_results');
    }
};
