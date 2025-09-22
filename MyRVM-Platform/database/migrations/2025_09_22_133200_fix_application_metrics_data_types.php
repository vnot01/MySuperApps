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
        Schema::table('application_metrics', function (Blueprint $table) {
            // Change uptime_seconds from integer to decimal to support float values
            $table->decimal('uptime_seconds', 10, 6)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('application_metrics', function (Blueprint $table) {
            // Revert back to integer
            $table->integer('uptime_seconds')->nullable()->change();
        });
    }
};
