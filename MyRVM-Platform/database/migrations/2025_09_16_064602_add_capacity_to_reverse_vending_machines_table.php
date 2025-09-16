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
            
            // Add index for better performance
            $table->index(['status', 'special_status']);
            $table->index('capacity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reverse_vending_machines', function (Blueprint $table) {
            // Drop indexes if they exist
            try {
                $table->dropIndex(['status', 'special_status']);
            } catch (\Exception $e) {
                // Index doesn't exist, ignore
            }
            
            try {
                $table->dropIndex(['capacity']);
            } catch (\Exception $e) {
                // Index doesn't exist, ignore
            }
            
            // Drop columns if they exist
            if (Schema::hasColumn('reverse_vending_machines', 'capacity')) {
                $table->dropColumn('capacity');
            }
            if (Schema::hasColumn('reverse_vending_machines', 'last_capacity_update')) {
                $table->dropColumn('last_capacity_update');
            }
            if (Schema::hasColumn('reverse_vending_machines', 'special_status')) {
                $table->dropColumn('special_status');
            }
        });
    }
};
