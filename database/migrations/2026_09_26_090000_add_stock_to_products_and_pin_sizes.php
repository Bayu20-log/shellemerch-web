<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // stock NULL = tidak dilacak (jumlah tak terbatas, perilaku lama tetap berjalan).
    // stock diisi angka = dilacak; berkurang otomatis saat dipesan, dan status
    // otomatis menjadi "habis" ketika mencapai 0.
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->unsignedInteger('stock')->nullable()->after('availability');
        });
        Schema::table('pin_sizes', function (Blueprint $table) {
            $table->unsignedInteger('stock')->nullable()->after('availability');
        });
    }

    public function down(): void
    {
        Schema::table('products', fn (Blueprint $t) => $t->dropColumn('stock'));
        Schema::table('pin_sizes', fn (Blueprint $t) => $t->dropColumn('stock'));
    }
};
