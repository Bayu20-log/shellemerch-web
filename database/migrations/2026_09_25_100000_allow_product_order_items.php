<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Item pesanan sekarang bisa berupa pin custom (design_path wajib, pin_size_id terisi)
    // ATAU produk lain dari katalog (product_id terisi, tanpa desain/ukuran).
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->foreignId('product_id')->nullable()->after('order_id')->constrained()->nullOnDelete();
            $table->string('design_path')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('product_id');
            $table->string('design_path')->nullable(false)->change();
        });
    }
};
