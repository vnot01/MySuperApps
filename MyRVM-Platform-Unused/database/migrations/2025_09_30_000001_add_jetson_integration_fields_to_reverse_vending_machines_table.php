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
            // Jetson-specific fields
            $table->string('jetson_ip')->nullable()->after('ip_address')->comment('Jetson Orin IP address');
            $table->integer('jetson_port')->default(5000)->after('jetson_ip')->comment('Jetson Orin API port');
            $table->string('jetson_status')->default('unknown')->after('jetson_port')->comment('Jetson connection status');
            $table->timestamp('last_jetson_ping')->nullable()->after('jetson_status')->comment('Last Jetson ping timestamp');
            $table->json('jetson_health_data')->nullable()->after('last_jetson_ping')->comment('Jetson health data from API');
            $table->json('jetson_gpu_info')->nullable()->after('jetson_health_data')->comment('Jetson GPU information');
            $table->json('jetson_hardware_info')->nullable()->after('jetson_gpu_info')->comment('Jetson hardware information');
            
            // CV Server fields
            $table->string('cv_server_ip')->nullable()->after('jetson_hardware_info')->comment('CV Server IP address');
            $table->integer('cv_server_port')->default(5000)->after('cv_server_ip')->comment('CV Server API port');
            $table->string('cv_server_status')->default('unknown')->after('cv_server_port')->comment('CV Server connection status');
            $table->timestamp('last_cv_server_ping')->nullable()->after('cv_server_status')->comment('Last CV Server ping timestamp');
            $table->json('cv_server_health_data')->nullable()->after('last_cv_server_ping')->comment('CV Server health data');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reverse_vending_machines', function (Blueprint $table) {
            $table->dropColumn([
                'jetson_ip',
                'jetson_port',
                'jetson_status',
                'last_jetson_ping',
                'jetson_health_data',
                'jetson_gpu_info',
                'jetson_hardware_info',
                'cv_server_ip',
                'cv_server_port',
                'cv_server_status',
                'last_cv_server_ping',
                'cv_server_health_data'
            ]);
        });
    }
};




