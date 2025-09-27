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
        Schema::create('system_notifications', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('message');
            $table->enum('type', ['system', 'maintenance', 'security', 'feature', 'performance', 'alert', 'info']);
            $table->enum('priority', ['low', 'medium', 'high', 'critical']);
            $table->enum('target_audience', ['all', 'admins', 'users', 'specific']);
            $table->json('target_user_ids')->nullable(); // For specific users
            $table->string('action_url')->nullable();
            $table->string('action_text')->nullable();
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('created_by');
            $table->integer('total_sent')->default(0);
            $table->integer('total_read')->default(0);
            $table->timestamps();

            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            $table->index(['type', 'priority']);
            $table->index(['target_audience', 'is_active']);
            $table->index(['scheduled_at', 'expires_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_notifications');
    }
};
