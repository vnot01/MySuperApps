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
            $table->unsignedBigInteger('rvm_id');
            $table->string('session_id');
            $table->string('user_id')->nullable();
            $table->json('detection_data');
            $table->string('image_path', 500)->nullable();
            $table->timestamp('detected_at');
            $table->string('status')->default('pending')->index();
            $table->text('error_message')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            // Foreign key constraint
            $table->foreign('rvm_id')->references('id')->on('reverse_vending_machines')->onDelete('cascade');
            
            // Indexes for performance
            $table->index(['rvm_id', 'detected_at']);
            $table->index(['session_id']);
            $table->index(['status', 'detected_at']);
            $table->index(['user_id', 'detected_at']);
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
