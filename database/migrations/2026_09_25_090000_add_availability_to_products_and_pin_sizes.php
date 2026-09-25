<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 'tersedia' | 'habis' | 'segera'. String (bukan enum DB) supaya mudah diperluas nanti.
        Schema::table('products', function (Blueprint $table) {
            $table->string('availability', 20)->default('tersedia')->after('price');
        });
        Schema::table('pin_sizes', function (Blueprint $table) {
            $table->string('availability', 20)->default('tersedia')->after('price');
        });
    }

    public function down(): void
    {
        Schema::table('products', fn (Blueprint $t) => $t->dropColumn('availability'));
        Schema::table('pin_sizes', fn (Blueprint $t) => $t->dropColumn('availability'));
    }
};
