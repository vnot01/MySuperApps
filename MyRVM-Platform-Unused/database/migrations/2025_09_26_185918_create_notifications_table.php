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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->string('notification_id')->unique(); // Unique identifier for notification
            $table->unsignedBigInteger('user_id')->nullable(); // Target user (null for system-wide)
            $table->string('title');
            $table->text('message');
            $table->enum('type', ['success', 'warning', 'info', 'error'])->default('info');
            $table->enum('category', ['rvm_status', 'transaction', 'system', 'user_action'])->default('system');
            $table->json('data')->nullable(); // Additional data (RVM ID, transaction ID, etc.)
            $table->timestamp('read_at')->nullable();
            $table->boolean('is_system_wide')->default(false); // For system-wide notifications
            $table->timestamps();
            
            // Indexes
            $table->index(['user_id', 'read_at']);
            $table->index(['category', 'created_at']);
            $table->index('notification_id');
            
            // Foreign key
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
