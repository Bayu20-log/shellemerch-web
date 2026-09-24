<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Kontak;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Schema;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Mencegah error jika tabel belum di-migrate
        if (Schema::hasTable('kontaks')) {
            // Membagikan variabel $kontak_footer ke SEMUA file blade di views
            View::share('kontak_footer', Kontak::first());
        }
    }
}