<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pin_sizes', function (Blueprint $table) {
            $table->id();
            $table->string('name');                       // mis. nama/ukuran pin
            $table->unsignedInteger('price');             // harga per pcs (Rp)
            $table->boolean('is_active')->default(true);  // nonaktif = tidak tampil di form pesanan
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pin_sizes');
    }
};
