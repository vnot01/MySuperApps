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
            // Only add columns if they don't exist
            if (!Schema::hasColumn('reverse_vending_machines', 'api_key')) {
                $table->string('api_key', 255)->nullable()->unique()->after('status');
            }
            if (!Schema::hasColumn('reverse_vending_machines', 'api_key_expires_at')) {
                $table->timestamp('api_key_expires_at')->nullable()->after('api_key');
            }
            if (!Schema::hasColumn('reverse_vending_machines', 'last_api_access')) {
                $table->timestamp('last_api_access')->nullable()->after('api_key_expires_at');
            }

            // Index for API key lookups (only if api_key column exists)
            if (Schema::hasColumn('reverse_vending_machines', 'api_key')) {
                $table->index('api_key');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reverse_vending_machines', function (Blueprint $table) {
            $table->dropIndex(['api_key']);
            $table->dropColumn(['api_key', 'api_key_expires_at', 'last_api_access']);
        });
    }
};
