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
        Schema::create('backup_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('rvm_id');
            $table->string('backup_type', 20); // full, incremental, data_only, config_only
            $table->string('file_path', 500)->nullable();
            $table->bigInteger('file_size')->nullable(); // in bytes
            $table->string('upload_status', 20)->default('pending'); // pending, uploading, completed, failed
            $table->string('minio_path', 500)->nullable();
            $table->text('backup_details')->nullable(); // JSON details
            $table->timestamp('backup_started_at')->nullable();
            $table->timestamp('backup_completed_at')->nullable();
            $table->timestamp('upload_started_at')->nullable();
            $table->timestamp('upload_completed_at')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();
            
            $table->foreign('rvm_id')->references('id')->on('reverse_vending_machines')->onDelete('cascade');
            $table->index(['rvm_id', 'backup_type']);
            $table->index(['upload_status']);
            $table->index(['backup_started_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('backup_logs');
    }
};
