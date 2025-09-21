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
        Schema::create('software_updates', function (Blueprint $table) {
            $table->id();
            $table->integer('rvm_id');
            $table->string('update_type', 50);
            $table->string('current_version', 100)->nullable();
            $table->string('target_version', 100);
            $table->string('update_source', 200);
            $table->string('status', 20)->default('pending');
            $table->integer('progress')->default(0);
            $table->text('progress_message')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->string('rollback_version', 100)->nullable();
            $table->text('rollback_reason')->nullable();
            $table->text('error_message')->nullable();
            $table->integer('created_by')->nullable();
            $table->timestamps();
            
            $table->foreign('rvm_id')->references('id')->on('reverse_vending_machines');
            $table->foreign('created_by')->references('id')->on('users');
            $table->index(['rvm_id', 'status']);
            $table->index(['status', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('software_updates');
    }
};
