<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\LandingPageController;
use App\Http\Controllers\Admin\HeroController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\NewsController;
use App\Http\Controllers\Admin\SponsorController;
use App\Http\Controllers\Admin\KontakController;
use App\Http\Controllers\Admin\PinSizeController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\PaymentSettingController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\QrisController;
use App\Http\Controllers\Customer\OrderController;
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

// 2. Route Login, Registrasi & Logout
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'authenticate'])->middleware('throttle:6,1')->name('login.post');
Route::get('/admin/login', [AuthController::class, 'showAdminLoginForm'])->name('admin.login');
Route::post('/admin/login', [AuthController::class, 'authenticateAdmin'])->middleware('throttle:6,1')->name('admin.login.post');
Route::get('/daftar', [AuthController::class, 'showRegisterForm'])->name('register');
Route::post('/daftar', [AuthController::class, 'register'])->middleware('throttle:6,1')->name('register.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Gambar QRIS toko (harus login; tanpa symlink storage)
Route::get('/qris', [QrisController::class, 'show'])->middleware('auth')->name('qris.image');

// 2b. Area Pelanggan (harus login). Pelanggan hanya bisa melihat pesanannya sendiri.
Route::middleware('auth')->prefix('pesanan')->name('customer.orders.')->group(function () {
    Route::get('/', [OrderController::class, 'index'])->name('index');
    Route::get('/baru', [OrderController::class, 'create'])->name('create');
    Route::get('/produk', [OrderController::class, 'products'])->name('products');
    Route::get('/produk/{product}', [OrderController::class, 'createProduct'])->whereNumber('product')->name('create.product');
    Route::post('/produk/{product}', [OrderController::class, 'storeProductItem'])->whereNumber('product')->name('products.store');
    Route::post('/item', [OrderController::class, 'storeItem'])->name('items.store');
    Route::get('/{order}', [OrderController::class, 'show'])->whereNumber('order')->name('show');
    Route::delete('/{order}/item/{item}', [OrderController::class, 'destroyItem'])->whereNumber(['order', 'item'])->name('items.destroy');
    Route::get('/{order}/item/{item}/desain', [OrderController::class, 'design'])->whereNumber(['order', 'item'])->name('items.design');
    Route::post('/{order}/checkout', [OrderController::class, 'checkout'])->whereNumber('order')->name('checkout');
    Route::post('/{order}/ubah', [OrderController::class, 'reopen'])->whereNumber('order')->name('reopen');
    Route::post('/{order}/bayar', [OrderController::class, 'uploadProof'])->whereNumber('order')->name('pay');
    Route::post('/{order}/batal', [OrderController::class, 'cancel'])->whereNumber('order')->name('cancel');
    Route::get('/{order}/bukti', [OrderController::class, 'proof'])->whereNumber('order')->name('proof');
});

// 3. Route Admin Panel (DIKUNCI: harus login DAN berperan admin)
// Pelanggan yang sudah login tetap tidak bisa masuk ke route di dalam kotak ini
Route::middleware(['auth', 'admin'])->group(function () {

    // Pintu masuk admin: ketik /admin di browser (tidak ada tautannya di navbar publik)
    Route::redirect('/admin', '/admin/dashboard');
    
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

    // Pesanan masuk: verifikasi pembayaran & perubahan status
    Route::get('admin/orders', [AdminOrderController::class, 'index'])->name('admin.orders.index');
    Route::get('admin/orders/export', [AdminOrderController::class, 'export'])->name('admin.orders.export');
    Route::get('admin/orders/{order}', [AdminOrderController::class, 'show'])->whereNumber('order')->name('admin.orders.show');
    Route::post('admin/orders/{order}/aksi', [AdminOrderController::class, 'transition'])->whereNumber('order')->name('admin.orders.transition');

    // Laporan & tren penjualan
    Route::get('admin/laporan', [ReportController::class, 'index'])->name('admin.reports.index');

    // Pengaturan QRIS toko
    Route::get('admin/pembayaran', [PaymentSettingController::class, 'edit'])->name('admin.payment.edit');
    Route::put('admin/pembayaran', [PaymentSettingController::class, 'update'])->name('admin.payment.update');

    // Ukuran & harga pin custom
    Route::resource('admin/pin-sizes', PinSizeController::class, ['as' => 'admin'])
        ->only(['index', 'store', 'update', 'destroy'])
        ->parameters(['pin-sizes' => 'pinSize']);
    
    // Rute untuk Kontak & Footer
    Route::get('/kontak', [KontakController::class, 'index'])->name('admin.kontak.index');
    Route::put('/kontak/{id}', [KontakController::class, 'update'])->name('admin.kontak.update');

});