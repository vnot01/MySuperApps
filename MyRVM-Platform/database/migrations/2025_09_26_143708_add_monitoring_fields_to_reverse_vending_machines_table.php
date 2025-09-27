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
            if (!Schema::hasColumn('reverse_vending_machines', 'status')) {
                $table->string('status')->default('unknown')->after('location_id');
            }
            if (!Schema::hasColumn('reverse_vending_machines', 'last_pulse_at')) {
                $table->timestamp('last_pulse_at')->nullable()->after('status');
            }
            if (!Schema::hasColumn('reverse_vending_machines', 'health_data')) {
                $table->json('health_data')->nullable()->after('last_pulse_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reverse_vending_machines', function (Blueprint $table) {
            $columnsToDrop = [];
            if (Schema::hasColumn('reverse_vending_machines', 'status')) {
                $columnsToDrop[] = 'status';
            }
            if (Schema::hasColumn('reverse_vending_machines', 'last_pulse_at')) {
                $columnsToDrop[] = 'last_pulse_at';
            }
            if (Schema::hasColumn('reverse_vending_machines', 'health_data')) {
                $columnsToDrop[] = 'health_data';
            }
            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }
};
