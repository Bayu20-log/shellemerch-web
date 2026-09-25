<?php

namespace App\Http\Controllers\Admin;
use App\Models\Product;
use Illuminate\Support\Facades\Storage;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $products = Product::latest()->get(); // Mengambil data produk dari yang paling baru
        return view('admin.products.index', compact('products'));
    }

    public function create()
    {
        // Menampilkan halaman form tambah data
        return view('admin.products.create');
    }

    public function store(Request $request)
    {
        // 1. Validasi data yang masuk
        $request->validate([
            'name' => 'required',
            'price' => 'required|numeric',
            'image' => 'required|image|mimes:jpeg,png,jpg|max:2048', // Maksimal 2MB
            'description' => 'nullable',
            'availability' => ['required', Rule::in([Product::AVAILABLE, Product::SOLD_OUT, Product::COMING_SOON])],
        ]);

        // 2. Proses upload gambar ke folder public/storage/products
        $imagePath = $request->file('image')->store('products', 'public');

        // 3. Simpan data ke database
        Product::create([
            'name' => $request->name,
            'price' => $request->price,
            'image' => $imagePath,
            'description' => $request->description,
            'availability' => $request->availability,
        ]);

        // 4. Kembalikan ke halaman daftar produk
        return redirect()->route('admin.products.index')->with('success', 'Produk berhasil ditambahkan!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    public function edit(Product $product)
    {
        // Menampilkan halaman form edit beserta data lama
        return view('admin.products.edit', compact('product'));
    }

    public function update(Request $request, Product $product)
    {
        // 1. Validasi data (gambar tidak wajib diisi saat edit)
        $request->validate([
            'name' => 'required',
            'price' => 'required|numeric',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'description' => 'nullable',
            'availability' => ['required', Rule::in([Product::AVAILABLE, Product::SOLD_OUT, Product::COMING_SOON])],
        ]);

        // 2. Siapkan data yang akan diupdate
        $data = $request->only(['name', 'price', 'description', 'availability']);

        // 3. Cek apakah user mengupload gambar baru
        if ($request->hasFile('image')) {
            // Hapus gambar lama dari folder
            if (Storage::disk('public')->exists($product->image)) {
                Storage::disk('public')->delete($product->image);
            }
            // Simpan gambar baru
            $data['image'] = $request->file('image')->store('products', 'public');
        }

        // 4. Update data ke database
        $product->update($data);

        return redirect()->route('admin.products.index')->with('success', 'Produk berhasil diperbarui!');
    }

    public function destroy(Product $product)
    {
        // 1. Hapus file gambar dari folder
        if (Storage::disk('public')->exists($product->image)) {
            Storage::disk('public')->delete($product->image);
        }

        // 2. Hapus data dari database
        $product->delete();

        return redirect()->route('admin.products.index')->with('success', 'Produk berhasil dihapus!');
    }
}
