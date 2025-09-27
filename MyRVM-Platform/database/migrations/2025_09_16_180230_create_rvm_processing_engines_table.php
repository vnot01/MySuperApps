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
        Schema::create('rvm_processing_engines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rvm_id')->constrained('reverse_vending_machines')->onDelete('cascade');
            $table->foreignId('processing_engine_id')->constrained('processing_engines')->onDelete('cascade');
            $table->enum('priority', ['primary', 'secondary', 'backup'])->default('primary');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->unique(['rvm_id', 'processing_engine_id']);
            $table->index(['rvm_id', 'priority']);
            $table->index(['processing_engine_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rvm_processing_engines');
    }
};