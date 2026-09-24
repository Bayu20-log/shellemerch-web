<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Hero;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class HeroController extends Controller
{
    public function index()
    {
        $heroes = Hero::latest()->get();
        return view('admin.heroes.index', compact('heroes'));
    }

    public function create()
    {
        return view('admin.heroes.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        $imagePath = $request->file('image')->store('heroes', 'public');

        Hero::create([
            'image' => $imagePath,
            'title' => '',     // Diisi kosong otomatis
            'subtitle' => ''   // Diisi kosong otomatis
        ]);

        return redirect()->route('admin.heroes.index')->with('success', 'Hero slide berhasil ditambahkan!');
    }

    public function edit(Hero $hero)
    {
        return view('admin.heroes.edit', compact('hero'));
    }

    public function update(Request $request, Hero $hero)
    {
        $request->validate([
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        $data = []; // Kita tidak lagi mengambil title dan subtitle

        if ($request->hasFile('image')) {
            if (Storage::disk('public')->exists($hero->image)) {
                Storage::disk('public')->delete($hero->image);
            }
            $data['image'] = $request->file('image')->store('heroes', 'public');
        }

        $hero->update($data);

        return redirect()->route('admin.heroes.index')->with('success', 'Hero slide berhasil diperbarui!');
    }

    public function destroy(Hero $hero)
    {
        if (Storage::disk('public')->exists($hero->image)) {
            Storage::disk('public')->delete($hero->image);
        }
        $hero->delete();

        return redirect()->route('admin.heroes.index')->with('success', 'Hero slide berhasil dihapus!');
    }
}