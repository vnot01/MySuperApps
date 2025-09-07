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
        Schema::table('deposits', function (Blueprint $table) {
            // Make old columns nullable to support new structure
            $table->string('item_type_detected')->nullable()->change();
            $table->decimal('reward_value', 15, 4)->nullable()->change();
            $table->timestamp('deposited_at')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('deposits', function (Blueprint $table) {
            // Revert to non-nullable
            $table->string('item_type_detected')->nullable(false)->change();
            $table->decimal('reward_value', 15, 4)->nullable(false)->change();
            $table->timestamp('deposited_at')->nullable(false)->change();
        });
    }
};
