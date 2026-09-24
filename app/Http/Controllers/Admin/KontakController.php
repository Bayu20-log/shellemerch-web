<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kontak;
use Illuminate\Http\Request;

class KontakController extends Controller
{
    public function index()
    {
        $kontak = Kontak::first();
        
        // Jika database masih kosong, buat data default otomatis
        if (!$kontak) {
            $kontak = Kontak::create([
                'deskripsi' => 'Shellemerch hadir sebagai platform terpercaya yang menyediakan berbagai macam produk berkualitas dengan harga yang kompetitif.',
                'alamat' => 'Kota Balikpapan, Kalimantan Timur, Indonesia',
                'email' => 'hello@shellemerch.com',
                'telepon' => '+62 812 3456 7890'
            ]);
        }

        return view('admin.kontak.index', compact('kontak'));
    }

    public function update(Request $request, $id)
    {
        $kontak = Kontak::findOrFail($id);
        $kontak->update($request->all());
        
        return redirect()->back()->with('success', 'Pengaturan Kontak & Footer Berhasil Diperbarui!');
    }
}