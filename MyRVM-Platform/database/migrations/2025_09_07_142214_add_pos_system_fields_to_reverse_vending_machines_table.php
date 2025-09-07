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
        Schema::table('reverse_vending_machines', function (Blueprint $table) {
            $table->string('admin_access_pin', 8)->nullable()->after('api_key');
            $table->boolean('remote_access_enabled')->default(true)->after('admin_access_pin');
            $table->boolean('kiosk_mode_enabled')->default(true)->after('remote_access_enabled');
            $table->json('pos_settings')->nullable()->after('kiosk_mode_enabled');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reverse_vending_machines', function (Blueprint $table) {
            $table->dropColumn(['admin_access_pin', 'remote_access_enabled', 'kiosk_mode_enabled', 'pos_settings']);
        });
    }
};
