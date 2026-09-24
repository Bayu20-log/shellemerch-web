<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PinSize;
use Illuminate\Http\Request;

class PinSizeController extends Controller
{
    public function index()
    {
        $sizes = PinSize::orderBy('price')->orderBy('name')->get();

        return view('admin.pin_sizes.index', compact('sizes'));
    }

    public function store(Request $request)
    {
        PinSize::create($this->validated($request));

        return back()->with('success', 'Ukuran pin ditambahkan.');
    }

    public function update(Request $request, PinSize $pinSize)
    {
        $pinSize->update($this->validated($request));

        return back()->with('success', 'Ukuran pin diperbarui.');
    }

    public function destroy(PinSize $pinSize)
    {
        // Pesanan lama tetap aman: nama & harga sudah disalin ke setiap item pesanan.
        $pinSize->delete();

        return back()->with('success', 'Ukuran pin dihapus.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'price' => ['required', 'integer', 'min:0', 'max:10000000'],
        ]);
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
