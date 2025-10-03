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
        Schema::create('reverse_vending_machines', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('location');
            $table->text('address')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->enum('status', ['active', 'inactive', 'maintenance', 'error'])->default('inactive');
            $table->integer('capacity')->default(100)->comment('Capacity in percentage (0-100)');
            $table->integer('current_load')->default(0);
            $table->string('ip_address')->nullable();
            $table->string('api_key')->nullable();
            $table->timestamp('last_ping')->nullable();
            $table->timestamp('last_maintenance')->nullable();
            $table->json('configuration')->nullable();
            $table->json('metrics')->nullable();
            $table->timestamps();
            
            $table->index(['status']);
            $table->index(['ip_address']);
            $table->index(['last_ping']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reverse_vending_machines');
    }
};