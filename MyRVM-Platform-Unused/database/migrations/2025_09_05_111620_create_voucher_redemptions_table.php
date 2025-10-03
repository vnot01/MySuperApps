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
        Schema::create('voucher_redemptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('voucher_id')->constrained('vouchers')->onDelete('restrict');

            $table->string('redemption_code')->unique(); // Kode unik yang akan ditunjukkan ke tenant
            $table->timestamp('redeemed_at'); // Waktu penukaran
            $table->timestamp('used_at')->nullable(); // Waktu voucher digunakan di tenant

            $table->decimal('cost_at_redemption', 15, 4); // Mencatat berapa biaya saat itu

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('voucher_redemptions');
    }
};
