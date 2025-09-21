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
        Schema::create('network_information', function (Blueprint $table) {
            $table->id();
            $table->integer('rvm_id');
            $table->string('local_ip', 45)->nullable();
            $table->string('virtual_ip', 45)->nullable();
            $table->string('gateway_ip', 45)->nullable();
            $table->text('dns_servers')->nullable();
            $table->string('network_interface', 50)->nullable();
            $table->string('connection_type', 20)->nullable();
            $table->integer('signal_strength')->nullable();
            $table->timestamp('last_network_check')->nullable();
            $table->timestamp('recorded_at');
            $table->timestamps();
            
            $table->foreign('rvm_id')->references('id')->on('reverse_vending_machines');
            $table->index(['rvm_id', 'recorded_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('network_information');
    }
};
