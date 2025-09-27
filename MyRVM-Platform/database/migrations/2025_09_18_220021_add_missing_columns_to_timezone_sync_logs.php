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
            // Check if columns don't exist before adding them
            if (!Schema::hasColumn('timezone_sync_logs', 'device_type')) {
                $table->string('device_type', 50)->default('rvm')->after('device_id');
            }
            if (!Schema::hasColumn('timezone_sync_logs', 'sync_type')) {
                $table->string('sync_type', 20)->default('manual')->after('device_type');
            }
            if (!Schema::hasColumn('timezone_sync_logs', 'old_timezone')) {
                $table->string('old_timezone', 50)->nullable()->after('sync_type');
            }
            if (!Schema::hasColumn('timezone_sync_logs', 'new_timezone')) {
                $table->string('new_timezone', 50)->nullable()->after('old_timezone');
            }
            if (!Schema::hasColumn('timezone_sync_logs', 'status')) {
                $table->string('status', 20)->default('success')->after('sync_timestamp');
            }
            if (!Schema::hasColumn('timezone_sync_logs', 'details')) {
                $table->json('details')->nullable()->after('status');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('timezone_sync_logs', function (Blueprint $table) {
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