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
        Schema::create('remote_commands', function (Blueprint $table) {
            $table->id();
            $table->integer('rvm_id');
            $table->string('command_type', 50);
            $table->string('command_name', 100);
            $table->json('command_payload')->nullable();
            $table->string('status', 20)->default('pending');
            $table->integer('executed_by')->nullable();
            $table->timestamp('executed_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->json('result')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();
            
            $table->foreign('rvm_id')->references('id')->on('reverse_vending_machines');
            $table->foreign('executed_by')->references('id')->on('users');
            $table->index(['rvm_id', 'status']);
            $table->index(['status', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('remote_commands');
    }
};
