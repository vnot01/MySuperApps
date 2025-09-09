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
        Schema::create('gemini_configs', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // e.g., 'gemini-2.0-flash', 'gemini-2.5-flash', 'gemini-2.5-pro'
            $table->string('display_name'); // e.g., 'Gemini 2.0 Flash', 'Gemini 2.5 Flash', 'Gemini 2.5 Pro'
            $table->string('endpoint_url');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(false);
            $table->boolean('is_default')->default(false);
            $table->decimal('temperature', 3, 2)->default(0.1); // 0.00 to 1.00
            $table->integer('max_tokens')->default(4096);
            $table->integer('max_output_tokens')->default(8192);
            $table->json('safety_settings')->nullable(); // Safety settings configuration
            $table->json('generation_config')->nullable(); // Generation configuration
            $table->integer('priority')->default(0); // Higher number = higher priority
            $table->boolean('supports_vision')->default(true);
            $table->boolean('supports_video')->default(false);
            $table->boolean('supports_audio')->default(false);
            $table->timestamps();
            
            // Indexes
            $table->index(['is_active', 'priority']);
            $table->index(['is_default']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gemini_configs');
    }
};
