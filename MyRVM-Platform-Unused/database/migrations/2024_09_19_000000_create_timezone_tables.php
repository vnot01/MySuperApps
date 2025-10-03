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
        // Create timezone_sync_logs table
        Schema::create('timezone_sync_logs', function (Blueprint $table) {
            $table->id();
            $table->string('device_id', 100)->index();
            $table->string('device_type', 50)->default('rvm'); // rvm, jetson, etc
            $table->string('sync_type', 20)->default('manual'); // manual, automatic, bulk, scheduled
            $table->string('old_timezone', 50)->nullable();
            $table->string('new_timezone', 50)->nullable();
            $table->timestamp('sync_timestamp');
            $table->string('status', 20)->default('success'); // success, failed, pending
            $table->json('details')->nullable(); // Additional sync details
            $table->string('ip_address', 45)->nullable();
            $table->string('country', 100)->nullable();
            $table->string('city', 100)->nullable();
            $table->string('sync_method', 20)->default('manual'); // automatic, manual
            $table->timestamps();

            // Indexes
            $table->index(['device_id', 'device_type']);
            $table->index(['device_id', 'sync_timestamp']);
            $table->index(['device_type', 'status']);
            $table->index('sync_timestamp');
            $table->index('status');
        });

        // Create device_timezones table
        Schema::create('device_timezones', function (Blueprint $table) {
            $table->id();
            $table->string('device_id', 100)->unique();
            $table->string('current_timezone', 50);
            $table->string('country', 100)->nullable();
            $table->string('city', 100)->nullable();
            $table->timestamp('last_sync')->nullable();
            $table->string('sync_status', 20)->default('active'); // active, inactive, error
            $table->timestamps();

            // Indexes
            $table->index('device_id');
            $table->index('sync_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('device_timezones');
        Schema::dropIfExists('timezone_sync_logs');
    }
};
