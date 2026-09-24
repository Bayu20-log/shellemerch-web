<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('payment_proof')->nullable()->after('notes');       // foto bukti bayar (disk privat)
            $table->timestamp('paid_at')->nullable()->after('payment_proof');  // kapan bukti dikirim
            $table->string('payment_note', 500)->nullable()->after('paid_at'); // alasan jika bukti ditolak admin
        });

        // Riwayat setiap perubahan status: siapa, kapan, dari mana ke mana
        Schema::create('order_status_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('from_status', 30)->nullable();
            $table->string('to_status', 30);
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('note', 500)->nullable();
            $table->timestamps();
        });

        // Pengaturan toko sederhana (mis. gambar QRIS)
        Schema::create('settings', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->text('value')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
        Schema::dropIfExists('order_status_logs');
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['payment_proof', 'paid_at', 'payment_note']);
        });
    }
};
