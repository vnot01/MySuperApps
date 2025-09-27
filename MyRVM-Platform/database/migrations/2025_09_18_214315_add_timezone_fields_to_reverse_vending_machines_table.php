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
            $table->string('location')->nullable()->after('location_description');
            $table->text('address')->nullable()->after('location');
            $table->string('ip_address')->nullable()->after('address');
            $table->integer('port')->default(8000)->after('ip_address');
            $table->string('timezone')->nullable()->after('port');
            $table->string('timezone_offset')->nullable()->after('timezone');
            $table->timestamp('last_timezone_sync')->nullable()->after('timezone_offset');
            $table->timestamp('last_ping')->nullable()->after('last_timezone_sync');
            $table->string('connection_status')->default('unknown')->after('last_ping');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reverse_vending_machines', function (Blueprint $table) {
            $table->dropColumn([
                'location',
                'address',
                'ip_address',
                'port',
                'timezone',
                'timezone_offset',
                'last_timezone_sync',
                'last_ping',
                'connection_status'
            ]);
        });
    }
};