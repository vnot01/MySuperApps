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
        Schema::create('vouchers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');

            $table->string('title');
            $table->text('description');
            $table->decimal('cost', 15, 4); // Berapa saldo yang dibutuhkan untuk menukar voucher ini

            $table->integer('stock')->default(0); // Jumlah voucher yang tersedia
            $table->integer('total_redeemed')->default(0); // Jumlah yang sudah ditukar

            $table->timestamp('valid_from')->nullable(); // Tanggal mulai berlaku
            $table->timestamp('valid_until')->nullable(); // Tanggal kedaluwarsa

            $table->boolean('is_active')->default(false); // Apakah penawaran ini aktif
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vouchers');
    }
};
