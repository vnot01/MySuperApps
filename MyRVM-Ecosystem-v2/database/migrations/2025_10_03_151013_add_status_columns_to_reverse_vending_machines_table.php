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
            // Add connection status (ping status)
            $table->enum('connection_status', ['connected', 'disconnected'])->default('disconnected')->after('last_ping');
            
            // Add API status (API endpoint health)
            $table->enum('api_status', ['valid', 'invalid'])->default('invalid')->after('connection_status');
            
            // Add last connection check
            $table->timestamp('last_connection_check')->nullable()->after('api_status');
            
            // Add last API check
            $table->timestamp('last_api_check')->nullable()->after('last_connection_check');
            
            // Add indexes for performance
            $table->index(['connection_status']);
            $table->index(['api_status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reverse_vending_machines', function (Blueprint $table) {
            $table->dropIndex(['connection_status']);
            $table->dropIndex(['api_status']);
            
            $table->dropColumn([
                'connection_status',
                'api_status',
                'last_connection_check',
                'last_api_check'
            ]);
        });
    }
};