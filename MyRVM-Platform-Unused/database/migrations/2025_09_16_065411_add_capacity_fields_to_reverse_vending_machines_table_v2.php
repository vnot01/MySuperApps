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
            // Add capacity column (0-100 percentage)
            $table->integer('capacity')->default(0)->after('status');
            
            // Add last_capacity_update timestamp
            $table->timestamp('last_capacity_update')->nullable()->after('last_status_change');
            
            // Add special_status column for maintenance, inactive, error, unknown
            $table->string('special_status')->nullable()->after('capacity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reverse_vending_machines', function (Blueprint $table) {
            $table->dropColumn(['capacity', 'last_capacity_update', 'special_status']);
        });
    }
};
