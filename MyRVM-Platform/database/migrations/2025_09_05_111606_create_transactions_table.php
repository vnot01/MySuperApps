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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('user_balance_id')->constrained('user_balances')->onDelete('cascade');

            // Tipe transaksi: deposit (kredit), voucher_redemption (debit), manual_adjustment (kredit/debit)
            $table->string('type');

            // Menggunakan decimal untuk presisi finansial
            $table->decimal('amount', 15, 4); // Bisa positif (kredit) atau negatif (debit)

            $table->decimal('balance_before', 15, 4);
            $table->decimal('balance_after', 15, 4);

            $table->text('description'); // Deskripsi transaksi, e.g., "Deposit 5 botol PET" atau "Penukaran Voucher Kopi"

            // Kolom polimorfik untuk menghubungkan ke sumber transaksi (opsional tapi sangat berguna)
            // Bisa terhubung ke model Deposit, VoucherRedemption, dll.
            $table->morphs('sourceable'); // Akan membuat sourceable_id dan sourceable_type

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
