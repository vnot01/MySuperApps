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
        Schema::create('rvm_configurations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('rvm_id');
            $table->string('config_key', 100);
            $table->text('config_value')->nullable();
            $table->string('config_type', 50)->default('string'); // string, integer, boolean, json
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->foreign('rvm_id')->references('id')->on('reverse_vending_machines')->onDelete('cascade');
            $table->unique(['rvm_id', 'config_key']);
            $table->index(['rvm_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rvm_configurations');
    }
};
