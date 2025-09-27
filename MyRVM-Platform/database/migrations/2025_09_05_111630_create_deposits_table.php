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
        Schema::create('deposits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null'); // Nullable untuk mode donasi/tamu
            $table->foreignId('rvm_id')->constrained('reverse_vending_machines')->onDelete('cascade');

            // Kolom untuk hasil dari AI lokal (YOLO+SAM)
            $table->string('item_type_detected'); // e.g., PET_BOTTLE, ALUMINUM_CAN
            $table->string('item_condition')->nullable(); // e.g., CRUSHED, INTACT, DIRTY
            $table->float('confidence_score')->nullable();
            $table->json('local_ai_result')->nullable(); // Raw JSON dari Edge AI untuk audit

            // Kolom untuk validasi sekunder oleh Gemini (jika ada)
            $table->boolean('gemini_validated')->default(false);
            $table->json('gemini_response')->nullable();

            $table->decimal('reward_value', 15, 4); // Nilai yang diberikan, akan dikreditkan ke saldo

            $table->string('image_path')->nullable(); // Path ke gambar di MinIO (opsional, jika ingin disimpan)

            $table->timestamp('deposited_at'); // Waktu item dimasukkan
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deposits');
    }
};
