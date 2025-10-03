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
            $table->string('api_token', 255)->nullable()->after('api_key');
            $table->timestamp('api_token_expires_at')->nullable()->after('api_token');
            $table->timestamp('last_api_access')->nullable()->after('api_token_expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reverse_vending_machines', function (Blueprint $table) {
            $table->dropColumn(['api_token', 'api_token_expires_at', 'last_api_access']);
        });
    }
};