<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PaymentSettingController extends Controller
{
    public function edit()
    {
        return view('admin.payment.edit', [
            'qrisUrl' => Setting::qrisConfigured() ? route('qris.image', ['v' => Setting::qrisVersion()]) : null,
            'merchant' => Setting::get('qris_merchant'),
        ]);
    }

    public function update(Request $request)
    {
        $hasQris = Setting::qrisConfigured();

        $data = $request->validate([
            'qris' => [$hasQris ? 'nullable' : 'required', 'file', 'image', 'mimes:jpeg,png,jpg,webp', 'max:4096'],
            'merchant' => ['nullable', 'string', 'max:100'],
        ], [
            'qris.required' => 'Unggah gambar QRIS toko.',
            'qris.image' => 'File harus berupa gambar.',
            'qris.mimes' => 'Format gambar harus JPG, PNG, atau WEBP.',
            'qris.max' => 'Ukuran gambar maksimal 4 MB.',
        ]);

        if ($request->hasFile('qris')) {
            $old = Setting::get('qris_image');
            Setting::put('qris_image', $request->file('qris')->store('qris', 'public'));
            if ($old) {
                Storage::disk('public')->delete($old);
            }
        }
        Setting::put('qris_merchant', $data['merchant'] ?? null);

        return back()->with('success', 'Pengaturan pembayaran disimpan.');
    }
}
