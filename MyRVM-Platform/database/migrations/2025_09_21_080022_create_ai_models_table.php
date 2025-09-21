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
        Schema::create('ai_models', function (Blueprint $table) {
            $table->id();
            $table->integer('rvm_id');
            $table->string('model_name', 100);
            $table->string('model_version', 50);
            $table->string('model_path', 500);
            $table->bigInteger('model_size')->nullable();
            $table->string('model_checksum', 64)->nullable();
            $table->string('model_url', 500)->nullable();
            $table->boolean('is_active')->default(false);
            $table->timestamp('deployed_at')->nullable();
            $table->timestamps();
            
            $table->foreign('rvm_id')->references('id')->on('reverse_vending_machines');
            $table->index(['rvm_id', 'is_active']);
            $table->index(['model_version']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_models');
    }
};
