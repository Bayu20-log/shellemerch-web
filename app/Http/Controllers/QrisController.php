<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Support\Facades\Storage;

class QrisController extends Controller
{
    // Gambar QRIS disajikan lewat Laravel (bukan /storage/...), jadi tidak bergantung pada
    // symlink `php artisan storage:link` yang sering gagal dibuat, terutama di Windows.
    public function show()
    {
        abort_unless(Setting::qrisConfigured(), 404);

        return Storage::disk('public')->response(Setting::get('qris_image'), null, [
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, max-age=86400', // aman: URL berganti (?v=) saat QRIS diganti
        ]);
    }
}
