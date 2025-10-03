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
        Schema::create('reverse_vending_machines', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->text('location_description')->nullable();

            // Status operasional mesin
            $table->string('status')->default('inactive'); // active, inactive, maintenance, full

            $table->string('api_key')->unique(); // Kunci API untuk otentikasi RVM ke backend

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reverse_vending_machines');
    }
};
