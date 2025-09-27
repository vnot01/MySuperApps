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
        Schema::table('timezone_sync_logs', function (Blueprint $table) {
            // Add missing columns
            $table->string('device_type', 50)->default('rvm')->after('device_id');
            $table->string('sync_type', 20)->default('manual')->after('device_type');
            $table->string('old_timezone', 50)->nullable()->after('sync_type');
            $table->string('new_timezone', 50)->nullable()->after('old_timezone');
            $table->string('status', 20)->default('success')->after('sync_timestamp');
            $table->json('details')->nullable()->after('status');
            
            // Add indexes
            $table->index(['device_id', 'device_type']);
            $table->index(['device_type', 'status']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('timezone_sync_logs', function (Blueprint $table) {
            // Drop indexes
            $table->dropIndex(['device_id', 'device_type']);
            $table->dropIndex(['device_type', 'status']);
            $table->dropIndex(['status']);
            
            // Drop columns
            $table->dropColumn([
                'device_type',
                'sync_type',
                'old_timezone',
                'new_timezone',
                'status',
                'details'
            ]);
        });
    }
};