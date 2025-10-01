<?php
/**
 * Migration for detection_results table
 * 
 * This migration should be added to your MyRVM-Platform Laravel application
 * to store detection results from MyCV-Platform.
 */

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
            $table->string('session_id')->index();
            $table->json('detection_data'); // Raw detection data from MyCV-Platform
            $table->string('image_path')->nullable(); // Path to processed image
            $table->timestamp('detected_at'); // When detection was performed
            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
            $table->text('error_message')->nullable(); // Error message if failed
            $table->json('metadata')->nullable(); // Additional metadata
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['rvm_id', 'status']);
            $table->index(['rvm_id', 'detected_at']);
            $table->index('session_id');
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
