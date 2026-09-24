<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sponsor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SponsorController extends Controller
{
    public function index()
    {
        $sponsors = Sponsor::latest()->get();
        return view('admin.sponsors.index', compact('sponsors'));
    }

    public function create()
    {
        return view('admin.sponsors.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'logo' => 'required|image|mimes:jpeg,png,jpg,svg|max:2048',
            'name' => 'required',
            'description' => 'nullable' // Ganti url jadi description
        ]);

        $logoPath = $request->file('logo')->store('sponsors', 'public');

        Sponsor::create([
            'logo' => $logoPath,
            'name' => $request->name,
            'description' => $request->description
        ]);

        return redirect()->route('admin.sponsors.index')->with('success', 'Sponsor berhasil ditambahkan!');
    }

    public function edit(Sponsor $sponsor)
    {
        return view('admin.sponsors.edit', compact('sponsor'));
    }

    public function update(Request $request, Sponsor $sponsor)
    {
        $request->validate([
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,svg|max:2048',
            'name' => 'required',
            'description' => 'nullable'
        ]);

        $data = $request->only(['name', 'description']);

        if ($request->hasFile('logo')) {
            if (Storage::disk('public')->exists($sponsor->logo)) {
                Storage::disk('public')->delete($sponsor->logo);
            }
            $data['logo'] = $request->file('logo')->store('sponsors', 'public');
        }

        $sponsor->update($data);

        return redirect()->route('admin.sponsors.index')->with('success', 'Data Sponsor berhasil diperbarui!');
    }

    public function destroy(Sponsor $sponsor)
    {
        if (Storage::disk('public')->exists($sponsor->logo)) {
            Storage::disk('public')->delete($sponsor->logo);
        }
        $sponsor->delete();

        return redirect()->route('admin.sponsors.index')->with('success', 'Sponsor berhasil dihapus!');
    }
}