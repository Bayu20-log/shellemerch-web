<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_code', 30)->unique();
            // restrict: pesanan tidak ikut terhapus jika akun dihapus (data transaksi harus aman)
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->string('status', 30)->default('draft')->index();
            $table->unsignedInteger('total')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('pin_size_id')->nullable()->constrained()->nullOnDelete();
            // Salinan nama & harga saat dipesan, supaya perubahan harga tidak mengubah pesanan lama
            $table->string('size_name');
            $table->unsignedInteger('unit_price');
            $table->unsignedInteger('quantity');
            $table->string('design_path');                // foto desain dari pelanggan (disk privat)
            $table->string('notes', 500)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};
