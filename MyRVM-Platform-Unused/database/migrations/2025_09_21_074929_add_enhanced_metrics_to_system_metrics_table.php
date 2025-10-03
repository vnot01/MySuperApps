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
        Schema::table('system_metrics', function (Blueprint $table) {
            $table->decimal('gpu_temperature', 5, 2)->nullable();
            $table->integer('disk_read_speed')->nullable();
            $table->integer('disk_write_speed')->nullable();
            $table->integer('network_upload_speed')->nullable();
            $table->integer('network_download_speed')->nullable();
            $table->bigInteger('memory_available')->nullable();
            $table->bigInteger('disk_available')->nullable();
            $table->decimal('load_average', 5, 2)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('system_metrics', function (Blueprint $table) {
            $table->dropColumn([
                'gpu_temperature',
                'disk_read_speed',
                'disk_write_speed',
                'network_upload_speed',
                'network_download_speed',
                'memory_available',
                'disk_available',
                'load_average'
            ]);
        });
    }
};
