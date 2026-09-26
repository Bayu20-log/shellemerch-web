<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PinSize;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

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
            'availability' => ['required', Rule::in([PinSize::AVAILABLE, PinSize::SOLD_OUT, PinSize::COMING_SOON])],
            'stock' => ['nullable', 'integer', 'min:0', 'max:1000000'],
        ]);
        // Stok kosong = tidak dilacak (tak terbatas). Stok diisi 0 memaksa status jadi "habis".
        $stock = $data['stock'] ?? null;
        $data = array_merge($data, (new PinSize())->applyManualAvailability($stock, $data['availability']));

        // is_active hanya mengontrol apakah ukuran ini tampil di form pemesanan sama sekali.
        // "Tersedia" dan "Segera hadir" tetap tampil (segera hadir sebagai pratinjau nonaktif);
        // hanya "Habis" yang disembunyikan total dari daftar.
        $data['is_active'] = $data['availability'] !== PinSize::SOLD_OUT;

        return $data;
    }
}
