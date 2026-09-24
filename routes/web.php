<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\LandingPageController;
use App\Http\Controllers\Admin\HeroController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\NewsController;
use App\Http\Controllers\Admin\SponsorController;
use App\Http\Controllers\Admin\KontakController;
use App\Models\Product;
use App\Models\News;
use App\Models\Sponsor;
use App\Models\Hero;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// 1. Route Halaman Depan (Bisa diakses siapa saja)
Route::get('/', [LandingPageController::class, 'index'])->name('beranda');

Route::get('/products', [LandingPageController::class, 'products'])->name('products');

Route::get('/about', [LandingPageController::class, 'about'])->name('about');

Route::get('/news', [LandingPageController::class, 'news'])->name('news.index');
Route::get('/news/{id}', [LandingPageController::class, 'newsDetail'])->name('news.detail');

// 2. Route Login & Logout
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'authenticate'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// 3. Route Admin Panel (DIKUNCI OLEH MIDDLEWARE AUTH)
// Hanya user yang sudah login yang bisa mengakses route di dalam kotak ini
Route::middleware('auth')->group(function () {
    
    // Dashboard
    Route::get('/admin/dashboard', function () {
        $productCount = \App\Models\Product::count();
        $newsCount = \App\Models\News::count();
        $sponsorCount = \App\Models\Sponsor::count();
        $heroCount = \App\Models\Hero::count();
        return view('admin.dashboard', compact('productCount', 'newsCount', 'sponsorCount', 'heroCount'));
    })->name('admin.dashboard');

    Route::get('/kontak', [KontakController::class, 'index'])->name('admin.kontak.index');

    // Manajemen Konten
    Route::resource('admin/heroes', App\Http\Controllers\Admin\HeroController::class, ['as' => 'admin']);
    Route::resource('admin/products', App\Http\Controllers\Admin\ProductController::class, ['as' => 'admin']);
    Route::resource('admin/news', App\Http\Controllers\Admin\NewsController::class, ['as' => 'admin']);
    Route::resource('admin/sponsors', App\Http\Controllers\Admin\SponsorController::class, ['as' => 'admin']);
    
    // Rute untuk Kontak & Footer
    Route::get('/kontak', [KontakController::class, 'index'])->name('admin.kontak.index');
    Route::put('/kontak/{id}', [KontakController::class, 'update'])->name('admin.kontak.update');

});